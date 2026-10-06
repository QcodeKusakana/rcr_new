<?php
/**
 * Script TEMPORAIRE d'audit (lecture seule) : exporte la structure + les données de la base
 * dans storage/backups/ pour sauvegarde et audit. Protégé par clé ; neutralisé après usage.
 */
if (($_GET['k'] ?? '') !== 'Qv9-audit-2026-x7') { http_response_code(404); exit; }
require __DIR__ . '/config/database.php';
header('Content-Type: text/plain; charset=utf-8');
$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$dir = __DIR__ . '/storage/backups';
$stamp = date('Ymd_His');
$out = fopen("$dir/audit_dump_$stamp.sql", 'w');
fwrite($out, "-- RCR dump audit $stamp\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");
$tables = $pdo->query("SHOW FULL TABLES WHERE Table_type='BASE TABLE'")->fetchAll(PDO::FETCH_NUM);
$counts = [];
foreach ($tables as $t) {
    $t = $t[0];
    $c = $pdo->query("SHOW CREATE TABLE `$t`")->fetch(PDO::FETCH_NUM)[1];
    fwrite($out, "DROP TABLE IF EXISTS `$t`;\n$c;\n\n");
    $st = $pdo->query("SELECT * FROM `$t`");
    $n = 0;
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
        $vals = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), array_values($r));
        fwrite($out, "INSERT INTO `$t` (`" . implode('`,`', array_keys($r)) . "`) VALUES (" . implode(',', $vals) . ");\n");
        $n++;
    }
    $counts[$t] = $n;
    fwrite($out, "\n");
}
$views = $pdo->query("SHOW FULL TABLES WHERE Table_type='VIEW'")->fetchAll(PDO::FETCH_NUM);
fwrite($out, "SET FOREIGN_KEY_CHECKS=1;\n");
fclose($out);
$info = [
    'php' => PHP_VERSION, 'os' => PHP_OS, 'sapi' => PHP_SAPI,
    'mysql' => $pdo->query('SELECT VERSION()')->fetchColumn(),
    'db_charset' => $pdo->query('SELECT @@character_set_database, @@collation_database')->fetch(PDO::FETCH_NUM),
    'ext' => get_loaded_extensions(),
    'ini' => ['upload_max_filesize' => ini_get('upload_max_filesize'), 'post_max_size' => ini_get('post_max_size'), 'display_errors' => ini_get('display_errors'), 'session.save_path' => ini_get('session.save_path')],
    'server' => ['host' => $_SERVER['HTTP_HOST'] ?? '', 'docroot' => $_SERVER['DOCUMENT_ROOT'] ?? '', 'software' => $_SERVER['SERVER_SOFTWARE'] ?? '', 'script' => $_SERVER['SCRIPT_NAME'] ?? ''],
    'mod_rewrite' => function_exists('apache_get_modules') ? in_array('mod_rewrite', apache_get_modules(), true) : null,
    'counts' => $counts, 'views' => $views,
];
file_put_contents("$dir/audit_info_$stamp.json", json_encode($info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "OK $stamp\n" . json_encode($counts) . "\nPHP " . PHP_VERSION . " / MySQL " . $info['mysql'] . "\n";
