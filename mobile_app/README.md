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
:: Android — clé de signature (une seule fois, voir « Signer l'application » ci-dessous) :
powershell -ExecutionPolicy Bypass -File tool\creer_keystore.ps1
flutter build appbundle --release --dart-define=API_BASE=https://rcr.cd/api/v1      :: .aab pour le Play Store
flutter build apk --release --dart-define=API_BASE=https://rcr.cd/api/v1            :: .apk installable directement

:: iPhone (sur Mac) :
flutter build ipa --release --dart-define=API_BASE=https://rcr.cd/api/v1
```
Icône : le logo officiel RCR est généré par `tool/make_icons.py` (fichiers prêts dans `tool/icons/`) et installé automatiquement par
`tool/setup_platforms` (Android : icônes classiques + adaptatives ; iOS : jeu AppIcon complet).

## Installer sur le maximum de téléphones (et ce qui reste hors de notre contrôle)

**Il est impossible de garantir l'installation sur « tous » les téléphones et sous toutes les protections** : Google Play Protect, Samsung Auto Blocker,
les contrôles parentaux ou d'entreprise et, bientôt, la vérification des développeurs d'Android sont des décisions du téléphone, pas de l'APK.
Ce qui est sous notre contrôle est appliqué (voir `tool/configure_android.dart` et `.github/workflows/build-android.yml`) :

1. **Clé de signature stable (cause n°1 des « application non installée »)** : exécuter UNE fois `tool\creer_keystore.ps1`, puis enregistrer les 4 secrets GitHub
   (ANDROID_KEYSTORE_BASE64, ANDROID_KEYSTORE_PASSWORD, ANDROID_KEY_ALIAS, ANDROID_KEY_PASSWORD). Le workflow **refuse désormais** de produire un APK « release »
   sans cette clé : une clé jetable change à chaque build, Android refuse alors de mettre à jour l'application et Play Protect se méfie.
   Sauvegardez le `.jks` et ses mots de passe (perdre la clé = plus aucune mise à jour possible).
2. **Numéro de version croissant** : `--build-number` reçoit le numéro d'exécution GitHub, donc chaque APK peut s'installer par-dessus le précédent.
3. **Compatibilité large** : Android 6.0 et plus (minSdk 23), processeurs arm, arm64 et x64, matériel (appareil photo, écran tactile, téléphonie) déclaré
   facultatif pour ne jamais exclure un modèle.
4. **Permissions minimales** : Internet uniquement. Stockage, installation de paquets, superposition d'écran et liste des applications sont retirés
   explicitement ; sauvegarde système désactivée ; trafic HTTP interdit en production.
5. **Contrôle automatique** : à chaque build, `apksigner verify` est exécuté et l'empreinte SHA-256 du certificat est affichée dans le résumé de l'exécution.
   Elle doit rester **identique** d'une version à l'autre. Un fichier `rcr.apk.sha256` est fourni pour vérifier le téléchargement.
6. **Hébergement** : servir l'APK en HTTPS (`https://rcr.cd/telechargements/rcr.apk`) avec le type MIME `application/vnd.android.package-archive`.

### Si Play Protect affiche encore un avertissement
- Sur le téléphone : « Plus de détails » puis « Installer quand même », ou « Analyser l'application » (Google mémorise alors la signature).
- Samsung : désactiver temporairement « Auto Blocker » (Paramètres > Sécurité et confidentialité).
- Autoriser « Installer des applications inconnues » pour le navigateur ou le gestionnaire de fichiers utilisé.
- Désinstaller toute ancienne version signée avec une autre clé (clé de débogage) avant la première installation signée avec la clé RCR.

### Les deux solutions durables
1. **Google Play** (compte développeur, frais uniques de 25 USD) : installation sans avertissement. Commencer par un test interne ou fermé.
2. **Vérification des développeurs Android** : selon la page officielle de Google (https://developer.android.com/developer-verification), les protections
   débutent le 30 septembre 2026 au Brésil, en Indonésie, à Singapour et en Thaïlande, puis s'étendent à tous les téléphones certifiés à partir de 2027.
   Il faudra enregistrer l'application (nom de paquet `cd.rcr.rcr_mobile` + clé de signature) dans la Android Developer Console : une raison de plus
   de garder la même clé pour toujours.

## Écrans
Connexion · Adhésion en 4 étapes (adhésion, identité, localisation, photo/mot de passe) · Paiement (Mobile Money / carte, vérification
temps réel, confirmation tardive gérée) · Accueil (carte de membre, échéance, jours restants) · Historique + reçus PDF ·
Dons (ponctuel/régulier, avec ou sans compte) + renouvellement · Carte de membre PDF · Changement de mot de passe · **Mon compte** : abonnement, identité, circonscriptions, parrainage (lien, filleuls, commission estimée), fiche d'adhésion et carte PDF.

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
- Pas de CV joint depuis l'application (facultatif sur le site), pas de notifications push. Le profil est en lecture seule (comme sur le site).
- Dons réguliers : comme sur le site, pas de prélèvement automatique (FlexPay n'en propose pas) ; l'application signale « À renouveler ».
