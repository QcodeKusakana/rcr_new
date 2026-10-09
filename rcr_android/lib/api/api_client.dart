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

  String get _base => AppConfig.apiBase.endsWith('/') ? AppConfig.apiBase.substring(0, AppConfig.apiBase.length - 1) : AppConfig.apiBase;

  /// URL « propre » : https://site/api/v1/adhesion
  Uri _uri(String path, [Map<String, String>? query]) {
    final u = Uri.parse('$_base$path');
    return query == null ? u : u.replace(queryParameters: query);
  }

  /// URL de secours (hébergements sans réécriture d'URL) : https://site/api/v1/index.php?r=/adhesion
  Uri _uriSecours(String path, [Map<String, String>? query]) {
    return Uri.parse('$_base/index.php').replace(queryParameters: {'r': path, ...?query});
  }

  Map<String, String> _headers({bool json = false}) => {
        'Accept': 'application/json',
        'User-Agent': 'RCR-Mobile/1.0 (Android)', // certains hébergeurs bloquent l'agent Dart par défaut
        if (json) 'Content-Type': 'application/json; charset=utf-8',
        if (token != null) 'Authorization': 'Bearer $token',
      };

  Future<Map<String, dynamic>> get(String path, {Map<String, String>? query}) =>
      _run((u) => http.get(u, headers: _headers()), path, query);

  Future<Map<String, dynamic>> post(String path, [Map<String, dynamic>? body]) =>
      _run((u) => http.post(u, headers: _headers(json: true), body: jsonEncode(body ?? <String, dynamic>{})), path, null);

  /// Envoi multipart (inscription avec photo).
  Future<Map<String, dynamic>> multipart(String path, Map<String, String> fields, {required Map<String, File> files}) async {
    return _run((u) async {
      final req = http.MultipartRequest('POST', u);
      req.headers.addAll(_headers());
      req.fields.addAll(fields);
      for (final e in files.entries) {
        req.files.add(await http.MultipartFile.fromPath(e.key, e.value.path));
      }
      final streamed = await req.send();
      return http.Response.fromStream(streamed);
    }, path, null);
  }

  /// Téléchargement binaire authentifié (reçu, carte de membre PDF).
  Future<Uint8List> download(String path, {Map<String, String>? query}) async {
    try {
      var r = await http.get(_uri(path, query), headers: _headers()).timeout(AppConfig.timeout);
      if (r.statusCode == 404 && _decode(r).isEmpty) {
        r = await http.get(_uriSecours(path, query), headers: _headers()).timeout(AppConfig.timeout);
      }
      if (r.statusCode == 200) {
        return r.bodyBytes;
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

  /// Exécute l'appel ; si le serveur répond par un 404 « brut » (page HTML, pas du JSON de l'API),
  /// l'adresse propre n'est pas réécrite par l'hébergement : on réessaie une fois en mode de secours.
  Future<Map<String, dynamic>> _run(Future<http.Response> Function(Uri) call, String path, Map<String, String>? query) async {
    try {
      var r = await call(_uri(path, query)).timeout(AppConfig.timeout);
      var data = _decode(r);
      if (r.statusCode == 404 && data.isEmpty) {
        r = await call(_uriSecours(path, query)).timeout(AppConfig.timeout);
        data = _decode(r);
      }
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
    final introuvable = r.statusCode == 404 && d.isEmpty;
    final e = ApiException(
      (d['code'] ?? (introuvable ? 'service_introuvable' : 'erreur')).toString(),
      (d['message'] ??
              (introuvable
                  ? 'Le service RCR est introuvable sur le serveur (404). Contactez l\'administrateur du site.'
                  : 'Une erreur est survenue (${r.statusCode}). Réessayez.'))
          .toString(),
      status: r.statusCode,
      field: d['champ']?.toString(),
    );
    if (r.statusCode == 401 && e.code == 'non_authentifie') {
      onUnauthorized?.call();
    }
    return e;
  }
}
