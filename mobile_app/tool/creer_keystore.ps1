# Crée la clé de signature RCR (UNE SEULE FOIS) et vous guide pour l'enregistrer dans GitHub.
# Usage : clic droit sur le fichier > "Exécuter avec PowerShell", ou
#   powershell -ExecutionPolicy Bypass -File tool\creer_keystore.ps1
#
# Ce que fait le script : installe Java si nécessaire (winget), crée la clé avec un mot de passe aléatoire fort,
# la range HORS du dépôt Git (dossier "rcr-cle-signature" de votre profil Windows), puis ouvre la page GitHub
# des secrets et copie chaque valeur dans le presse-papiers, l'une après l'autre.
$ErrorActionPreference = 'Stop'

function Trouver-Keytool {
  $c = (Get-Command keytool -ErrorAction SilentlyContinue)
  if ($c) { return $c.Source }
  $dirs = @()
  if ($env:JAVA_HOME) { $dirs += (Join-Path $env:JAVA_HOME 'bin\keytool.exe') }
  foreach ($base in @("$env:ProgramFiles\Eclipse Adoptium", "$env:ProgramFiles\Java", "$env:ProgramFiles\Microsoft", "$env:ProgramFiles\Android\Android Studio\jbr", "$env:LOCALAPPDATA\Programs\Android Studio\jbr")) {
    if (Test-Path $base) {
      $dirs += (Get-ChildItem $base -Recurse -Filter keytool.exe -ErrorAction SilentlyContinue | Select-Object -ExpandProperty FullName)
    }
  }
  foreach ($d in $dirs) { if ($d -and (Test-Path $d)) { return $d } }
  return $null
}

$keytool = Trouver-Keytool
if (-not $keytool) {
  Write-Host "Java n'est pas installe : installation de Temurin JDK 17 (winget)..." -ForegroundColor Cyan
  winget install -e --id EclipseAdoptium.Temurin.17.JDK --silent --accept-package-agreements --accept-source-agreements
  $keytool = Trouver-Keytool
}
if (-not $keytool) { throw "keytool introuvable. Installez Java 17 (https://adoptium.net), fermez/rouvrez PowerShell, puis relancez ce script." }

$dossier = Join-Path $env:USERPROFILE 'rcr-cle-signature'
New-Item -ItemType Directory -Force -Path $dossier | Out-Null
$jks = Join-Path $dossier 'rcr-release.jks'
if (Test-Path $jks) { throw "$jks existe deja : NE LE REMPLACEZ PAS (les mises a jour deviendraient impossibles). Utilisez-le pour recreer les secrets GitHub si besoin." }

# Mot de passe aleatoire (lettres et chiffres uniquement : aucun probleme de caracteres speciaux)
$rng = New-Object System.Security.Cryptography.RNGCryptoServiceProvider
$alphabet = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789'.ToCharArray()
$bytes = New-Object byte[] 28
$rng.GetBytes($bytes)
$mdp = -join ($bytes | ForEach-Object { $alphabet[$_ % $alphabet.Length] })
$alias = 'rcr'

$ErrorActionPreference = 'Continue'
& $keytool -genkeypair -keystore $jks -alias $alias -keyalg RSA -keysize 2048 -validity 10000 -storetype PKCS12 `
  -storepass $mdp -keypass $mdp -dname "CN=RCR, O=Rassemblement des Chretiens Republicains, C=CD" | Out-Null
if ($LASTEXITCODE -ne 0 -or -not (Test-Path $jks)) { throw "Creation de la cle echouee." }
& $keytool -list -keystore $jks -storepass $mdp -alias $alias | Out-Null
if ($LASTEXITCODE -ne 0) { throw "La cle creee n'est pas lisible : recommencez." }
$ErrorActionPreference = 'Stop'

$b64 = [Convert]::ToBase64String([IO.File]::ReadAllBytes($jks))
$infos = Join-Path $dossier 'rcr-cle-INFOS.txt'
@("CLE DE SIGNATURE RCR - A SAUVEGARDER EN LIEU SUR (gestionnaire de mots de passe, cle USB). NE JAMAIS METTRE DANS GIT.",
  "Fichier : $jks", "Alias : $alias", "Mot de passe (store et cle) : $mdp") | Set-Content -Path $infos -Encoding UTF8

Write-Host "`nCle creee : $jks" -ForegroundColor Green
Write-Host "Infos (mot de passe) : $infos" -ForegroundColor Green
Write-Host "SAUVEGARDEZ ce dossier ailleurs (cle USB / gestionnaire de mots de passe). Sans lui, plus aucune mise a jour possible.`n" -ForegroundColor Yellow

$url = 'https://github.com/QcodeKusakana/rcr_new/settings/secrets/actions/new'
try {
  $remote = (git remote get-url origin 2>$null)
  if ($remote -match 'github\.com[:/](.+?)(\.git)?$') { $url = "https://github.com/$($Matches[1])/settings/secrets/actions/new" }
} catch {}

$secrets = @(
  @('ANDROID_KEYSTORE_BASE64', $b64),
  @('ANDROID_KEYSTORE_PASSWORD', $mdp),
  @('ANDROID_KEY_ALIAS', $alias),
  @('ANDROID_KEY_PASSWORD', $mdp)
)
$i = 0
foreach ($s in $secrets) {
  $i++
  Set-Clipboard -Value $s[1]
  Start-Process $url
  Write-Host "[$i/4] Sur la page GitHub qui vient de s'ouvrir :" -ForegroundColor Cyan
  Write-Host "      Name   : $($s[0])"
  Write-Host "      Secret : COLLEZ (Ctrl+V) - la valeur est deja dans le presse-papiers"
  Write-Host "      Puis cliquez 'Add secret'."
  Read-Host "      Quand c'est fait, appuyez sur Entree pour passer au secret suivant" | Out-Null
}
Set-Clipboard -Value ' '
Write-Host "`nTermine. Sur GitHub : Actions > Build Android APK > Run workflow > mode 'release'." -ForegroundColor Green
