import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'dart:typed_data';

import 'package:http/http.dart' as http;

import '../config.dart';

/// Erreur renvoyée par l'API ({ok:false, code, message}) ou erreur réseau.
class ApiException implements Exception {
  final String code;
  final String message;
  final int status;
  final String? field;
  ApiException(this.code, this.message, {this.status = 0, this.field});

  bool get isNetwork => code == 'reseau';
  bool get isUnauthorized => status == 401;

  @override
  String toString() => message;
}

/// Client HTTP de l'API RCR (jeton Bearer). Aucune donnée sensible n'est journalisée.
class ApiClient {
  String? token;
  void Function()? onUnauthorized;

  Uri _uri(String path, [Map<String, String>? query]) {
    final base = AppConfig.apiBase.endsWith('/') ? AppConfig.apiBase.substring(0, AppConfig.apiBase.length - 1) : AppConfig.apiBase;
    final u = Uri.parse('$base$path');
    return query == null ? u : u.replace(queryParameters: query);
  }

  Map<String, String> _headers({bool json = false}) => {
        'Accept': 'application/json',
        if (json) 'Content-Type': 'application/json; charset=utf-8',
        if (token != null) 'Authorization': 'Bearer $token',
      };

  Future<Map<String, dynamic>> get(String path, {Map<String, String>? query}) =>
      _run(() => http.get(_uri(path, query), headers: _headers()));

  Future<Map<String, dynamic>> post(String path, [Map<String, dynamic>? body]) =>
      _run(() => http.post(_uri(path), headers: _headers(json: true), body: jsonEncode(body ?? <String, dynamic>{})));

  /// Envoi multipart (inscription avec photo).
  Future<Map<String, dynamic>> multipart(String path, Map<String, String> fields, {required Map<String, File> files}) async {
    return _run(() async {
      final req = http.MultipartRequest('POST', _uri(path));
      req.headers.addAll(_headers());
      req.fields.addAll(fields);
      for (final e in files.entries) {
        req.files.add(await http.MultipartFile.fromPath(e.key, e.value.path));
      }
      final streamed = await req.send();
      return http.Response.fromStream(streamed);
    });
  }

  /// Téléchargement binaire authentifié (reçu, carte de membre PDF).
  Future<Uint8List> download(String path, {Map<String, String>? query}) async {
    try {
      final r = await http.get(_uri(path, query), headers: _headers()).timeout(AppConfig.timeout);
      if (r.statusCode == 200) {
        return r.bodyBytes;
      }
      throw _fromResponse(r);
    } on ApiException {
      rethrow;
    } on SocketException {
      throw ApiException('reseau', 'Connexion impossible. Vérifiez votre accès Internet.');
    } on TimeoutException {
      throw ApiException('reseau', 'Le serveur met trop de temps à répondre. Réessayez.');
    }
  }

  Future<Map<String, dynamic>> _run(Future<http.Response> Function() call) async {
    try {
      final r = await call().timeout(AppConfig.timeout);
      final data = _decode(r);
      if (r.statusCode >= 200 && r.statusCode < 300 && data['ok'] == true) {
        return data;
      }
      throw _fromResponse(r);
    } on ApiException {
      rethrow;
    } on SocketException {
      throw ApiException('reseau', 'Connexion impossible. Vérifiez votre accès Internet.');
    } on http.ClientException {
      throw ApiException('reseau', 'Connexion impossible. Vérifiez votre accès Internet.');
    } on TimeoutException {
      throw ApiException('reseau', 'Le serveur met trop de temps à répondre. Réessayez.');
    }
  }

  Map<String, dynamic> _decode(http.Response r) {
    try {
      final d = jsonDecode(utf8.decode(r.bodyBytes));
      return d is Map<String, dynamic> ? d : <String, dynamic>{};
    } catch (_) {
      return <String, dynamic>{};
    }
  }

  ApiException _fromResponse(http.Response r) {
    final d = _decode(r);
    final e = ApiException(
      (d['code'] ?? 'erreur').toString(),
      (d['message'] ?? 'Une erreur est survenue (${r.statusCode}). Réessayez.').toString(),
      status: r.statusCode,
      field: d['champ']?.toString(),
    );
    if (r.statusCode == 401 && e.code == 'non_authentifie') {
      onUnauthorized?.call();
    }
    return e;
  }
}
