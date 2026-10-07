#!/usr/bin/env bash
# Même rôle que setup_platforms.ps1 (macOS / Linux). Usage : bash tool/setup_platforms.sh
set -euo pipefail
command -v flutter >/dev/null || { echo "Flutter n'est pas installé (https://docs.flutter.dev/get-started/install)"; exit 1; }
flutter create --org cd.rcr --project-name rcr_mobile --platforms=android,ios .

M=android/app/src/main/AndroidManifest.xml
grep -q 'android.permission.INTERNET' "$M" || sed -i.bak '0,/<manifest[^>]*>/s//&\n    <uses-permission android:name="android.permission.INTERNET"\/>/' "$M"
sed -i.bak 's/android:label="[^"]*"/android:label="RCR"/' "$M" && rm -f "$M.bak"

mkdir -p android/app/src/debug
cat > android/app/src/debug/AndroidManifest.xml <<'XML'
<manifest xmlns:android="http://schemas.android.com/apk/res/android">
    <uses-permission android:name="android.permission.INTERNET"/>
    <application android:usesCleartextTraffic="true"/>
</manifest>
XML

P=ios/Runner/Info.plist
if ! grep -q NSCameraUsageDescription "$P"; then
  /usr/libexec/PlistBuddy -c "Add :NSCameraUsageDescription string 'La photo de profil est demandée pour votre carte de membre RCR.'" "$P" 2>/dev/null || true
  /usr/libexec/PlistBuddy -c "Add :NSPhotoLibraryUsageDescription string 'Choisissez votre photo de profil pour votre carte de membre RCR.'" "$P" 2>/dev/null || true
fi
/usr/libexec/PlistBuddy -c "Set :CFBundleDisplayName RCR" "$P" 2>/dev/null || true
# Icônes RCR, manifeste assaini, signature de production (non bloquant : le build reste possible en cas d'avertissement)
dart tool/configure_android.dart || echo "Avertissement : configuration Android partielle (voir ci-dessus)."
ICONS=ios/Runner/Assets.xcassets/AppIcon.appiconset
[ -d "$ICONS" ] && cp -f tool/icons/ios/*.png "$ICONS/" || true
flutter pub get
echo "Terminé. Exemple : flutter run --dart-define=API_BASE=http://10.0.2.2:8000/api/v1"
