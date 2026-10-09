#!/usr/bin/env bash
# Prépare le dossier android/ : génération Flutter standard + 3 réglages minimaux (Internet, nom, icône).
# Aucune clé, aucun secret, aucune modification de Gradle autre que le SDK minimum.
set -euo pipefail
flutter create --org cd.rcr --project-name rcr_mobile --platforms=android .

M=android/app/src/main/AndroidManifest.xml
# 1. Permission Internet (absente du manifeste de production généré par Flutter)
grep -q 'android.permission.INTERNET' "$M" || sed -i '0,/<manifest[^>]*>/s//&\n    <uses-permission android:name="android.permission.INTERNET"\/>/' "$M"
# 2. Nom affiché sous l'icône
sed -i 's/android:label="[^"]*"/android:label="RCR"/' "$M"
# 3. Icône RCR (PNG classiques uniquement)
for d in tool/icons_android/mipmap-*; do
  n=$(basename "$d")
  mkdir -p "android/app/src/main/res/$n"
  cp -f "$d/ic_launcher.png" "android/app/src/main/res/$n/ic_launcher.png"
done
# 4. SDK minimum 23 (exigé par le coffre sécurisé pour le jeton de connexion)
for g in android/app/build.gradle.kts android/app/build.gradle; do
  [ -f "$g" ] && sed -i -E 's/minSdk(Version)?[ =]+flutter\.minSdkVersion/minSdk = 23/' "$g" || true
done
grep -n "minSdk" android/app/build.gradle* || true
echo "Préparation Android terminée."
