/// Configuration de l'application.
///
/// L'adresse de l'API se règle à la compilation (aucune clé secrète dans l'application) :
///   flutter run --dart-define=API_BASE=http://10.0.2.2:8000/api/v1        (émulateur Android -> Laragon)
///   flutter build apk --release --dart-define=API_BASE=https://rcr.cd/api/v1
class AppConfig {
  static const String apiBase = String.fromEnvironment('API_BASE', defaultValue: 'https://rcr.cd/api/v1');
  static const String devise = 'USD';
  static const Duration timeout = Duration(seconds: 30);
}
