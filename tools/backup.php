<?php
/**
 * Sauvegarde de production (ligne de commande uniquement).
 *   php tools/backup.php            -> base de données + fichiers téléversés
 *   php tools/backup.php --db       -> base de données seulement
 * Écrit dans storage/backups/ (inaccessible depuis le web). Conserve les 14 dernières sauvegardes.
 *
 * Planification conseillée (cPanel > Tâches cron), tous les jours à 02h00 :
 *   0 2 * * *  /usr/bin/php /chemin/du/site/tools/backup.php >> /chemin/du/site/storage/logs/backup.log 2>&1
 * IMPORTANT : copiez régulièrement le contenu de storage/backups/ HORS du serveur (autre machine / cloud).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Accès interdit.'); }

$root = dirname(__DIR__);
require_once $root . '/config/database.php';
$dbSeule = in_array('--db', $argv ?? [], true);
$dir = $root . '/storage/backups';
if (!is_dir($dir) && !mkdir($dir, 0750, true)) { fwrite(STDERR, "Impossible de créer $dir\n"); exit(1); }
$stamp = date('Ymd_His');

$bdd = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false]);

/* ---------- 1. Base de données : export SQL complet (structure + données), sans dépendre de mysqldump ---------- */
$fichierSql = "$dir/bdd_$stamp.sql.gz";
$gz = gzopen($fichierSql, 'wb9');
if (!$gz) { fwrite(STDERR, "Écriture impossible : $fichierSql\n"); exit(1); }
gzwrite($gz, "-- Sauvegarde RCR " . date('c') . "\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");
$tables = $bdd->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);
$nbLignes = 0;
foreach ($tables as $t) {
    $create = $bdd->query("SHOW CREATE TABLE `$t`")->fetch(PDO::FETCH_NUM)[1];
    gzwrite($gz, "DROP TABLE IF EXISTS `$t`;\n$create;\n\n");
    $rows = $bdd->query("SELECT * FROM `$t`");
    $lot = [];
    while ($r = $rows->fetch(PDO::FETCH_NUM)) {
        $vals = array_map(fn($v) => $v === null ? 'NULL' : $bdd->quote((string) $v), $r);
        $lot[] = '(' . implode(',', $vals) . ')';
        $nbLignes++;
        if (count($lot) >= 200) { gzwrite($gz, "INSERT INTO `$t` VALUES " . implode(",\n", $lot) . ";\n"); $lot = []; }
    }
    if ($lot) { gzwrite($gz, "INSERT INTO `$t` VALUES " . implode(",\n", $lot) . ";\n"); }
    gzwrite($gz, "\n");
}
gzwrite($gz, "SET FOREIGN_KEY_CHECKS=1;\n");
gzclose($gz);
chmod($fichierSql, 0640);
echo date('c') . " BDD : " . count($tables) . " tables, $nbLignes lignes -> " . basename($fichierSql) . ' (' . round(filesize($fichierSql) / 1024) . " Ko)\n";

/* ---------- 2. Fichiers téléversés (photos, CV, images) ---------- */
if (!$dbSeule && class_exists('ZipArchive')) {
    $fichierZip = "$dir/media_$stamp.zip";
    $zip = new ZipArchive();
    if ($zip->open($fichierZip, ZipArchive::CREATE) === true) {
        $n = 0;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/media', FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            if ($f->isFile() && $f->getBasename() !== '.htaccess') {
                $zip->addFile($f->getPathname(), 'media/' . substr($f->getPathname(), strlen($root . '/media/')));
                $n++;
            }
        }
        $zip->close();
        chmod($fichierZip, 0640);
        echo date('c') . " Fichiers : $n -> " . basename($fichierZip) . ' (' . round(filesize($fichierZip) / 1048576, 1) . " Mo)\n";
    }
} elseif (!$dbSeule) {
    echo "Extension ZipArchive absente : fichiers non sauvegardés.\n";
}

/* ---------- 3. Rotation : on garde les 14 dernières sauvegardes de chaque type ---------- */
foreach (['bdd_*.sql.gz', 'media_*.zip'] as $motif) {
    $liste = glob("$dir/$motif") ?: [];
    rsort($liste);
    foreach (array_slice($liste, 14) as $vieux) { @unlink($vieux); echo "Supprimé (rotation) : " . basename($vieux) . "\n"; }
}

/* ---------- 4. Vérification de restauration : le fichier est lisible et complet ---------- */
$contenu = gzopen($fichierSql, 'rb');
$ok = false;
while ($contenu && !gzeof($contenu)) { $ligne = gzgets($contenu, 65536); if (strpos((string) $ligne, 'FOREIGN_KEY_CHECKS=1') !== false) { $ok = true; } }
if ($contenu) { gzclose($contenu); }
echo date('c') . ($ok ? " Vérification : archive SQL complète.\n" : " ATTENTION : archive SQL incomplète !\n");
exit($ok ? 0 : 1);
