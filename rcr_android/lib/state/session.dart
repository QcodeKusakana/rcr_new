import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../api/api_client.dart';
import '../models/models.dart';

enum AuthStatus { loading, loggedOut, loggedIn, offline }

/// Session du membre : jeton stocké dans le coffre sécurisé du téléphone (Keychain iOS / Keystore Android).
class Session extends ChangeNotifier {
  static const _kToken = 'rcr_api_token';
  final ApiClient api = ApiClient();
  final FlutterSecureStorage _store = const FlutterSecureStorage();

  AuthStatus status = AuthStatus.loading;
  Membre? membre;
  Tarifs? _tarifs;

  Session() {
    api.onUnauthorized = () {
      if (status == AuthStatus.loggedIn) {
        _clear();
      }
    };
  }

  /// Au démarrage : relit le jeton et recharge le profil.
  Future<void> init() async {
    try {
      final t = await _store.read(key: _kToken);
      if (t == null || t.isEmpty) {
        status = AuthStatus.loggedOut;
        notifyListeners();
        return;
      }
      api.token = t;
      await refresh();
    } catch (_) {
      status = AuthStatus.loggedOut;
      notifyListeners();
    }
  }

  Future<void> refresh() async {
    try {
      final r = await api.get('/me');
      membre = Membre.fromJson(r['membre'] as Map<String, dynamic>);
      status = AuthStatus.loggedIn;
    } on ApiException catch (e) {
      if (e.isNetwork) {
        status = membre != null ? AuthStatus.loggedIn : AuthStatus.offline;
      } else {
        await _clear();
        return;
      }
    }
    notifyListeners();
  }

  Future<void> login(String identifiant, String motDePasse) async {
    final r = await api.post('/auth/login', {'identifiant': identifiant, 'mot_de_passe': motDePasse, 'appareil': defaultTargetPlatform.name});
    await _ouvrir(r);
  }

  /// Après une adhésion réussie (l'API renvoie directement un jeton).
  Future<void> ouvrirDepuisReponse(Map<String, dynamic> r) => _ouvrir(r);

  Future<void> _ouvrir(Map<String, dynamic> r) async {
    final t = r['token'].toString();
    api.token = t;
    membre = Membre.fromJson(r['membre'] as Map<String, dynamic>);
    status = AuthStatus.loggedIn;
    await _store.write(key: _kToken, value: t);
    notifyListeners();
  }

  Future<void> logout() async {
    try {
      await api.post('/auth/logout');
    } catch (_) {/* le jeton sera purgé localement quoi qu'il arrive */}
    await _clear();
  }

  Future<void> _clear() async {
    api.token = null;
    membre = null;
    status = AuthStatus.loggedOut;
    try {
      await _store.delete(key: _kToken);
    } catch (_) {}
    notifyListeners();
  }

  Future<Tarifs> tarifs({bool forcer = false}) async {
    if (_tarifs == null || forcer) {
      _tarifs = Tarifs.fromJson(await api.get('/tarifs'));
    }
    return _tarifs!;
  }
}
