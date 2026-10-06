# Application mobile RCR (Android + iPhone) — Flutter

Périmètre : **adhésion, cotisation, renouvellement, dons, renouvellement de don**, historique, reçus PDF, carte de membre.
Même base de données et **mêmes identifiants que le site** (code d'adhésion ou e-mail + mot de passe) via l'API `api/v1`.

## Architecture
```
Application Flutter  --HTTPS/JSON (jeton Bearer)-->  api/v1 (PHP)  -->  MySQL rcr_db (inchangée) + FlexPay
```
- Aucune clé FlexPay ni accès base dans l'application : tout passe par l'API (le montant est **recalculé côté serveur**,
  un paiement n'est « payé » qu'après vérification FlexPay, comme sur le site).
- Jeton d'accès stocké dans le coffre sécurisé du téléphone (Keychain / Keystore), 60 jours glissants, révoqué à la déconnexion.
- Inscription depuis l'application = mêmes règles que le site (tarif, localisation, photo vérifiée, code membre généré).

## Côté serveur (une seule fois)
```
php migrations\phase6_api_mobile.php            :: simulation
php migrations\phase6_api_mobile.php --apply    :: crée la table api_tokens
```
Test rapide : `http://rcr_new.test:8000/api/v1/ping` doit répondre `{"ok":true,...}`. Documentation : `api/v1/README.md`.

## Prérequis poste de développement
- Flutter SDK 3.27+ (https://docs.flutter.dev/get-started/install/windows) et Android Studio (SDK Android + émulateur).
- **iPhone : un Mac avec Xcode est obligatoire** (limite Apple) + compte Apple Developer (99 USD/an) pour l'App Store/TestFlight.
- Android : compte Google Play Console (25 USD, paiement unique) pour publier.

## Première mise en route
```
cd C:\laragon\www\rcr_new\mobile_app
powershell -ExecutionPolicy Bypass -File tool\setup_platforms.ps1     :: génère android/ et ios/, permissions, flutter pub get
flutter run --dart-define=API_BASE=http://10.0.2.2:8000/api/v1        :: émulateur Android -> Laragon (port 8000)
```
(Sur Mac : `bash tool/setup_platforms.sh`.) Sur téléphone réel en Wi-Fi : remplacer `10.0.2.2` par l'IP du PC.
Le HTTP en clair n'est autorisé qu'en **debug** ; la version publiée exige HTTPS.

## Compiler les versions publiables
```
:: Android — créer d'abord une clé de signature (à conserver précieusement, hors de Git) :
keytool -genkey -v -keystore rcr-release.jks -keyalg RSA -keysize 2048 -validity 10000 -alias rcr
:: puis suivre https://docs.flutter.dev/deployment/android#sign-the-app (android/key.properties, déjà dans .gitignore)
flutter build appbundle --release --dart-define=API_BASE=https://rcr.cd/api/v1      :: .aab pour le Play Store
flutter build apk --release --dart-define=API_BASE=https://rcr.cd/api/v1            :: .apk installable directement

:: iPhone (sur Mac) :
flutter build ipa --release --dart-define=API_BASE=https://rcr.cd/api/v1
```
Icône et écran de démarrage : ajouter `flutter_launcher_icons` si souhaité (le logo est dans `assets/logo.png`).

## Écrans
Connexion · Adhésion en 4 étapes (adhésion, identité, localisation, photo/mot de passe) · Paiement (Mobile Money / carte, vérification
temps réel, confirmation tardive gérée) · Accueil (carte de membre, échéance, jours restants) · Historique + reçus PDF ·
Dons (ponctuel/régulier, avec ou sans compte) + renouvellement · Carte de membre PDF · Changement de mot de passe.

## Avant la mise en ligne
1. HTTPS actif sur `rcr.cd` (l'application refuse le HTTP en production).
2. Test réel de 1 USD (Mobile Money puis carte) **depuis l'application**.
3. Politique de confidentialité à jour (l'application collecte les mêmes données que le site, dont la photo) : lien exigé par Apple et Google.
4. Apple impose une option de **suppression de compte** dans l'application pour toute app permettant de créer un compte
   (règle 5.1.1(v)) : à ajouter avant la soumission App Store (non incluse dans cette version).
5. Les dons et cotisations à une organisation politique : vérifier les règles de catégorie/restrictions de chaque store pour les
   applications liées à la politique (déclaration du compte développeur).

## Limites connues de cette première version
- Code écrit et relu **sans compilateur Flutter** (non disponible dans l'environnement de génération) : lancer `flutter analyze`
  puis corriger d'éventuelles erreurs mineures ; l'API, elle, est testée de bout en bout (49 tests).
- Pas de CV joint depuis l'application (facultatif sur le site), pas de parrainage/encadreur, pas de notifications push.
- Dons réguliers : comme sur le site, pas de prélèvement automatique (FlexPay n'en propose pas) ; l'application signale « À renouveler ».
