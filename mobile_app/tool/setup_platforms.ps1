# Génère les dossiers android/ et ios/ puis applique la configuration RCR (Windows / PowerShell).
# Usage (dans le dossier mobile_app) :  powershell -ExecutionPolicy Bypass -File tool\setup_platforms.ps1
$ErrorActionPreference = 'Stop'
if (-not (Get-Command flutter -ErrorAction SilentlyContinue)) { throw "Flutter n'est pas installé ou absent du PATH (https://docs.flutter.dev/get-started/install)." }

flutter create --org cd.rcr --project-name rcr_mobile --platforms=android,ios .

# --- Android : permission Internet (release), nom affiché, HTTP en développement uniquement ---
$main = 'android\app\src\main\AndroidManifest.xml'
$xml = Get-Content $main -Raw -Encoding UTF8
if ($xml -notmatch 'android.permission.INTERNET') {
  $xml = $xml -replace '(<manifest[^>]*>)', "`$1`r`n    <uses-permission android:name=`"android.permission.INTERNET`"/>"
}
$xml = $xml -replace 'android:label="[^"]*"', 'android:label="RCR"'
Set-Content $main $xml -Encoding UTF8

# Le manifeste de debug généré par Flutter ne contient que la permission Internet : on le remplace en y autorisant
# http://10.0.2.2 (Laragon depuis l'émulateur) UNIQUEMENT en debug. La version publiée exige HTTPS.
$debugDir = 'android\app\src\debug'
if (-not (Test-Path $debugDir)) { New-Item -ItemType Directory -Path $debugDir | Out-Null }
@'
<manifest xmlns:android="http://schemas.android.com/apk/res/android">
    <uses-permission android:name="android.permission.INTERNET"/>
    <application android:usesCleartextTraffic="true"/>
</manifest>
'@ | Set-Content "$debugDir\AndroidManifest.xml" -Encoding UTF8

# --- iOS : textes de demande d'autorisation (appareil photo, photothèque) ---
$plist = 'ios\Runner\Info.plist'
$p = Get-Content $plist -Raw -Encoding UTF8
if ($p -notmatch 'NSCameraUsageDescription') {
  $ajout = "`t<key>NSCameraUsageDescription</key>`r`n`t<string>La photo de profil est demandée pour votre carte de membre RCR.</string>`r`n`t<key>NSPhotoLibraryUsageDescription</key>`r`n`t<string>Choisissez votre photo de profil pour votre carte de membre RCR.</string>`r`n"
  $p = $p -replace '</dict>\s*</plist>', ($ajout + "</dict>`r`n</plist>")
}
$p = $p -replace '(<key>CFBundleDisplayName</key>\s*<string>)[^<]*', '${1}RCR'
Set-Content $plist $p -Encoding UTF8

# Icônes RCR, manifeste assaini, signature de production (non bloquant)
dart tool/configure_android.dart
if ($LASTEXITCODE -ne 0) { Write-Warning "Configuration Android partielle (voir ci-dessus)." }
$icons = 'ios\Runner\Assets.xcassets\AppIcon.appiconset'
if (Test-Path $icons) { Copy-Item 'tool\icons\ios\*.png' $icons -Force }

flutter pub get
Write-Host "`nConfiguration terminée. Lancer l'application :" -ForegroundColor Green
Write-Host "  flutter run --dart-define=API_BASE=http://10.0.2.2:8000/api/v1   (émulateur Android + Laragon sur le port 8000)"
