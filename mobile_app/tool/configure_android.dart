// Configuration Android de l'application RCR, appliquée après `flutter create` (identique sous Windows, macOS, Linux).
// Usage (dossier mobile_app) :  dart tool/configure_android.dart
//
// Ce que fait ce script (idempotent, n'écrase jamais rien d'utile) :
//   1. icônes officielles RCR (classiques + adaptatives) à la place de l'icône Flutter ;
//   2. manifeste « propre » : seules les permissions utiles, aucune permission de stockage ni d'installation
//      (ce sont celles qui déclenchent les alertes de Google Play Protect), sauvegarde système désactivée, HTTP interdit ;
//   3. signature de l'APK avec VOTRE clé (android/key.properties) lorsqu'elle existe, sinon clé de débogage.
import 'dart:io';

const navy = '#1B2A44';

void main() {
  final android = Directory('android');
  if (!android.existsSync()) {
    stderr.writeln("Dossier android/ introuvable : lancez d'abord tool/setup_platforms.");
    exit(1);
  }
  _icones();
  _manifeste();
  _signature();
  stdout.writeln('Configuration Android RCR appliquée.');
}

void _icones() {
  final src = Directory('tool/icons/android');
  if (!src.existsSync()) {
    stderr.writeln('! tool/icons/android absent : icônes non appliquées.');
    return;
  }
  const res = 'android/app/src/main/res';
  for (final f in src.listSync(recursive: true).whereType<File>()) {
    final rel = f.path.replaceAll('\\', '/').split('tool/icons/android/').last; // mipmap-xhdpi/ic_launcher.png
    final dest = File('$res/$rel');
    dest.parent.createSync(recursive: true);
    f.copySync(dest.path);
  }
  File('$res/values/ic_launcher_background.xml')
    ..parent.createSync(recursive: true)
    ..writeAsStringSync('<?xml version="1.0" encoding="utf-8"?>\n<resources>\n    <color name="ic_launcher_background">$navy</color>\n</resources>\n');
  const adaptive = '<?xml version="1.0" encoding="utf-8"?>\n'
      '<adaptive-icon xmlns:android="http://schemas.android.com/apk/res/android">\n'
      '    <background android:drawable="@color/ic_launcher_background"/>\n'
      '    <foreground android:drawable="@mipmap/ic_launcher_foreground"/>\n'
      '</adaptive-icon>\n';
  for (final n in ['ic_launcher', 'ic_launcher_round']) {
    File('$res/mipmap-anydpi-v26/$n.xml')
      ..parent.createSync(recursive: true)
      ..writeAsStringSync(adaptive);
  }
  stdout.writeln('✓ icônes RCR installées');
}

void _manifeste() {
  final f = File('android/app/src/main/AndroidManifest.xml');
  var x = f.readAsStringSync();

  if (!x.contains('xmlns:tools=')) {
    x = x.replaceFirst('<manifest ', '<manifest xmlns:tools="http://schemas.android.com/tools" ');
  }
  if (!x.contains('android.permission.INTERNET')) {
    x = x.replaceFirstMapped(RegExp(r'<manifest[^>]*>'), (m) => '${m[0]}\n    <uses-permission android:name="android.permission.INTERNET"/>');
  }
  // Permissions que des bibliothèques pourraient ajouter et qui n'ont aucun usage ici : retirées explicitement.
  const retirees = [
    'android.permission.REQUEST_INSTALL_PACKAGES',
    'android.permission.READ_EXTERNAL_STORAGE',
    'android.permission.WRITE_EXTERNAL_STORAGE',
    'android.permission.MANAGE_EXTERNAL_STORAGE',
    'android.permission.READ_MEDIA_IMAGES',
    'android.permission.READ_MEDIA_VIDEO',
    'android.permission.READ_MEDIA_AUDIO',
    'android.permission.SYSTEM_ALERT_WINDOW',
    'android.permission.QUERY_ALL_PACKAGES',
  ];
  final lignes = StringBuffer();
  for (final p in retirees) {
    if (!x.contains('android:name="$p"')) {
      lignes.write('\n    <uses-permission android:name="$p" tools:node="remove"/>');
    }
  }
  if (lignes.isNotEmpty) {
    x = x.replaceFirstMapped(RegExp(r'<manifest[^>]*>'), (m) => '${m[0]}${lignes.toString()}');
  }

  x = _attr(x, 'android:label', 'RCR');
  x = _attr(x, 'android:icon', '@mipmap/ic_launcher');
  x = _attr(x, 'android:roundIcon', '@mipmap/ic_launcher_round');
  x = _attr(x, 'android:allowBackup', 'false');
  x = _attr(x, 'android:usesCleartextTraffic', 'false'); // HTTP autorisé uniquement par le manifeste debug
  f.writeAsStringSync(x);
  stdout.writeln('✓ manifeste assaini (permissions, icônes, HTTPS)');
}

/// Définit (ou remplace) un attribut de la balise <application>.
String _attr(String x, String nom, String valeur) {
  final m = RegExp(r'<application\b[^>]*>').firstMatch(x);
  if (m == null) return x;
  var tag = m[0]!;
  final existant = RegExp('$nom="[^"]*"');
  tag = existant.hasMatch(tag) ? tag.replaceFirst(existant, '$nom="$valeur"') : tag.replaceFirst('<application', '<application\n        $nom="$valeur"');
  return x.replaceRange(m.start, m.end, tag);
}

void _signature() {
  final kts = File('android/app/build.gradle.kts');
  final groovy = File('android/app/build.gradle');
  final estKts = kts.existsSync();
  final f = estKts ? kts : groovy;
  if (!f.existsSync()) {
    stderr.writeln('! build.gradle introuvable : signature non configurée (clé de débogage conservée).');
    return;
  }
  var g = f.readAsStringSync();
  if (g.contains('key.properties')) {
    stdout.writeln('✓ signature déjà configurée');
    return;
  }

  final String charge, bloc, usage;
  final RegExp ligneDebug;
  if (estKts) {
    charge = 'import java.io.FileInputStream\nimport java.util.Properties\n\n'
        'val keystoreProperties = Properties()\n'
        'val keystorePropertiesFile = rootProject.file("key.properties")\n'
        'if (keystorePropertiesFile.exists()) {\n    keystoreProperties.load(FileInputStream(keystorePropertiesFile))\n}\n\n';
    bloc = '    signingConfigs {\n        create("release") {\n            if (keystorePropertiesFile.exists()) {\n'
        '                keyAlias = keystoreProperties["keyAlias"] as String\n'
        '                keyPassword = keystoreProperties["keyPassword"] as String\n'
        '                storeFile = file(keystoreProperties["storeFile"] as String)\n'
        '                storePassword = keystoreProperties["storePassword"] as String\n            }\n        }\n    }\n\n';
    usage = 'signingConfig = if (keystorePropertiesFile.exists()) signingConfigs.getByName("release") else signingConfigs.getByName("debug")';
    ligneDebug = RegExp(r'signingConfig\s*=\s*signingConfigs\.getByName\("debug"\)');
  } else {
    charge = 'def keystoreProperties = new Properties()\n'
        'def keystorePropertiesFile = rootProject.file("key.properties")\n'
        'if (keystorePropertiesFile.exists()) {\n    keystoreProperties.load(new FileInputStream(keystorePropertiesFile))\n}\n\n';
    bloc = '    signingConfigs {\n        release {\n            if (keystorePropertiesFile.exists()) {\n'
        '                keyAlias keystoreProperties["keyAlias"]\n'
        '                keyPassword keystoreProperties["keyPassword"]\n'
        '                storeFile file(keystoreProperties["storeFile"])\n'
        '                storePassword keystoreProperties["storePassword"]\n            }\n        }\n    }\n\n';
    usage = 'signingConfig keystorePropertiesFile.exists() ? signingConfigs.release : signingConfigs.debug';
    ligneDebug = RegExp(r'signingConfig\s+signingConfigs\.debug');
  }

  final iBuild = g.indexOf(RegExp(r'\n\s*buildTypes\s*\{'));
  if (!ligneDebug.hasMatch(g) || iBuild < 0) {
    stderr.writeln('! Structure build.gradle inattendue : signature non configurée (clé de débogage conservée).');
    return;
  }
  g = g.replaceFirst(ligneDebug, usage);
  final i2 = g.indexOf(RegExp(r'\n\s*buildTypes\s*\{'));
  g = '${g.substring(0, i2 + 1)}$bloc${g.substring(i2 + 1)}';
  // Les « import » Kotlin doivent rester en tête de fichier ; en Groovy, le code de chargement précède aussi le bloc android.
  if (estKts) {
    g = charge + g;
  } else {
    final iAndroid = g.indexOf(RegExp(r'\nandroid\s*\{'));
    g = '${g.substring(0, iAndroid + 1)}$charge${g.substring(iAndroid + 1)}';
  }
  f.writeAsStringSync(g);
  stdout.writeln('✓ signature de production configurée (utilise android/key.properties si présent)');
}
