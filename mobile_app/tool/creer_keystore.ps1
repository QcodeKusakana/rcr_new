# Crée la clé de signature de production de l'application RCR (à faire UNE SEULE FOIS) et prépare le secret GitHub.
# Usage (dossier mobile_app) :  powershell -ExecutionPolicy Bypass -File tool\creer_keystore.ps1
#
# IMPORTANT : sauvegardez rcr-release.jks et ses mots de passe dans un endroit sûr (gestionnaire de mots de passe,
# copie hors ligne). Sans cette clé, il est impossible de publier une mise à jour installable par-dessus l'application.
# Ne l'ajoutez JAMAIS à Git (déjà exclue par .gitignore).
$ErrorActionPreference = 'Stop'

$keytool = (Get-Command keytool -ErrorAction SilentlyContinue).Source
if (-not $keytool) {
  foreach ($c in @("$env:ProgramFiles\Android\Android Studio\jbr\bin\keytool.exe", "$env:LOCALAPPDATA\Programs\Android Studio\jbr\bin\keytool.exe")) {
    if (Test-Path $c) { $keytool = $c; break }
  }
}
if (-not $keytool) { throw "keytool introuvable : installez Android Studio ou un JDK 17 (https://adoptium.net)." }

$jks = Join-Path $PWD 'rcr-release.jks'
if (Test-Path $jks) { throw "rcr-release.jks existe déjà : ne le remplacez pas (les mises à jour deviendraient impossibles)." }

Write-Host "keytool va demander un mot de passe (choisissez-en un long) puis quelques informations (vous pouvez les laisser vides)." -ForegroundColor Cyan
& $keytool -genkeypair -v -keystore $jks -alias rcr -keyalg RSA -keysize 2048 -validity 10000 -dname "CN=RCR, O=Rassemblement des Chretiens Republicains, C=CD"
if ($LASTEXITCODE -ne 0) { throw "Création de la clé échouée." }

$b64 = [Convert]::ToBase64String([IO.File]::ReadAllBytes($jks))
Set-Clipboard -Value $b64
Write-Host "`nClé créée : $jks" -ForegroundColor Green
Write-Host "Le contenu encodé de la clé est dans le presse-papiers. Sur GitHub : Settings > Secrets and variables > Actions > New repository secret :"
Write-Host "  ANDROID_KEYSTORE_BASE64   = (coller le presse-papiers)"
Write-Host "  ANDROID_KEYSTORE_PASSWORD = le mot de passe saisi"
Write-Host "  ANDROID_KEY_ALIAS         = rcr"
Write-Host "  ANDROID_KEY_PASSWORD      = le même mot de passe (ou celui de la clé si différent)"
