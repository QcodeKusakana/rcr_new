<?php
/**
 * Contrôle avant / après mise en production (CLI, à lancer SUR LE SERVEUR) :
 *   php tools/go_live_check.php                       -> configuration, droits, base, données
 *   php tools/go_live_check.php --url=https://rcr.cd  -> + tests HTTP depuis l'extérieur (fichiers sensibles, en-têtes)
 * Code de sortie 0 = prêt ; 1 = au moins un point BLOQUANT. Les « conseils » ne bloquent pas.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Accès interdit.'); }
$root = dirname(__DIR__);
$bloq = 0; $conseils = 0;
function ok(string $m): void { echo "  [OK ] $m\n"; }
function bloquant(string $m): void { global $bloq; $bloq++; echo "  [BLOQUANT] $m\n"; }
function conseil(string $m): void { global $conseils; $conseils++; echo "  [conseil] $m\n"; }
function chk(bool $c, string $okMsg, string $koMsg, bool $bloquant = true): void { $c ? ok($okMsg) : ($bloquant ? bloquant($koMsg) : conseil($koMsg)); }

echo "== PHP et extensions\n";
chk(version_compare(PHP_VERSION, '8.0.0', '>='), 'PHP ' . PHP_VERSION, 'PHP 8.0 minimum requis (match, fn, str_contains…) : ' . PHP_VERSION);
foreach (['pdo_mysql', 'curl', 'mbstring', 'fileinfo', 'gd', 'zlib', 'json', 'openssl'] as $e) { chk(extension_loaded($e), "extension $e", "extension $e manquante"); }

echo "\n== Configuration\n";
require_once $root . '/config/database.php';
if (!is_file($root . '/config/flexpay.php')) { bloquant('config/flexpay.php absent : copiez config/flexpay.example.php en flexpay.php et renseignez le jeton'); }
if (is_file($root . '/config/flexpay.php')) { require_once $root . '/config/flexpay.php'; }
chk(!defined('APP_DEBUG') || !APP_DEBUG, 'APP_DEBUG désactivé', 'APP_DEBUG est activé : les erreurs seraient visibles');
chk(defined('FLEXPAY_TOKEN') && strlen((string) FLEXPAY_TOKEN) > 40 && stripos((string) FLEXPAY_TOKEN, 'xxxx') === false, 'jeton FlexPay renseigné', 'jeton FlexPay absent ou factice (config/flexpay.php)');
chk(defined('FLEXPAY_MERCHANT') && FLEXPAY_MERCHANT !== '', 'code marchand : ' . (defined('FLEXPAY_MERCHANT') ? FLEXPAY_MERCHANT : '?'), 'code marchand FlexPay absent');
if (defined('FLEXPAY_TOKEN') && preg_match('/^[^.]+\.([^.]+)\./', (string) FLEXPAY_TOKEN, $m)) {
    $pl = json_decode((string) base64_decode(strtr($m[1], '-_', '+/')), true);
    if (isset($pl['exp'])) {
        $j = (int) floor(($pl['exp'] - time()) / 86400);
        chk($j > 30, "jeton FlexPay valide encore $j jours (expire le " . date('d/m/Y', (int) $pl['exp']) . ')', "jeton FlexPay expire dans $j jours : demandez-en un nouveau à FlexPay");
    }
}
require_once $root . '/includes/flexpay_client.php';
chk(defined('SITE_URL') && strpos((string) SITE_URL, 'https://') === 0, 'SITE_URL en https : ' . (defined('SITE_URL') ? SITE_URL : '?'), 'SITE_URL doit commencer par https://');
chk(strpos((string) FLEXPAY_CALLBACK_ENDPOINT, 'https://') === 0, 'URL de callback : ' . FLEXPAY_CALLBACK_ENDPOINT, 'URL de callback non https');

echo "\n== Dossiers inscriptibles\n";
foreach (['storage', 'storage/logs', 'storage/backups', 'storage/cache', 'tmp_sessions', 'media/passeport', 'media/cv'] as $d) {
    $p = "$root/$d";
    if (!is_dir($p)) { @mkdir($p, 0750, true); }
    chk(is_dir($p) && is_writable($p), "$d inscriptible", "$d absent ou non inscriptible");
}

echo "\n== Fichiers à ne pas laisser en production\n";
foreach (['rcr.sql', 'rcr.zip', 'phpinfo.php', 'info.php', 'allfiles.txt', 'files.txt', 'index1.php', 'indexxxx.html', 'anim.html', 'php.ini'] as $f) {
    chk(!file_exists("$root/$f"), "$f absent", "$f présent : à supprimer", false);
}
chk(!is_dir("$root/tests") || is_file("$root/tests/.htaccess"), 'tests/ protégé', 'tests/ sans .htaccess');
$g = glob("$root/storage/backups/*") ?: [];
chk(count($g) > 0, count($g) . ' sauvegarde(s) présente(s) dans storage/backups', 'aucune sauvegarde : lancez php tools/backup.php', false);

echo "\n== E-mail\n";
require_once $root . '/includes/mailer.php';
chk(mail_configure(), 'SMTP configuré (' . (defined('MAIL_HOST') ? MAIL_HOST : '?') . ')', "config/mail.php absent ou incomplet : aucun e-mail (confirmations, rappels, mot de passe oublié) ne sera envoyé. Copier config/mail.example.php en mail.php", false);

echo "\n== Base de données\n";
try {
    $bdd = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    ok('connexion à ' . DB_NAME);
    $cs = (string) $bdd->query('SELECT @@character_set_database')->fetchColumn();
    chk($cs === 'utf8mb4', 'base en utf8mb4', "jeu de caractères de la base : $cs (utf8mb4 attendu)", false);
    $col = fn(string $t, string $c) => (int) $bdd->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = '$t' AND column_name = '$c'")->fetchColumn() > 0;
    $tab = fn(string $t) => (int) $bdd->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '$t'")->fetchColumn() > 0;
    foreach (['site_blocs', 'site_reglages', 'site_menu', 'roles', 'permissions', 'role_permissions', 'audit_logs', 'payment_logs', 'grades_historique', 'notifications', 'login_attempts', 'password_resets', 'messages_contact'] as $t) { chk($tab($t), "table $t", "table $t manquante : lancer les migrations"); }
    foreach ([['payments', 'order_number'], ['payments', 'type_transaction'], ['payments', 'periode_fin'], ['dons', 'type_don'], ['dons', 'prochaine_echeance'], ['adhesion', 'statut'], ['adhesion', 'password_hash'], ['adhesion', 'date_echeance'], ['grades', 'actif'], ['cotisation', 'mois'], ['admin', 'id_role'], ['site_menu', 'classe']] as [$t, $c]) {
        chk($tab($t) && $col($t, $c), "$t.$c", "colonne $t.$c manquante : lancer les migrations");
    }
    $n = fn(string $sql) => (int) $bdd->query($sql)->fetchColumn();
    chk($n("SELECT COUNT(*) FROM admin a JOIN roles r ON r.id_role = a.id_role WHERE r.code = 'admin_principal' AND a.confirmer = 1") >= 1, 'au moins un administrateur principal actif', 'aucun administrateur principal actif : php migrations/create_admin.php');
    chk($n('SELECT COUNT(*) FROM grades WHERE actif = 1 AND ancien = 0') === 12, '12 grades actifs', 'le barème devrait compter 12 grades actifs (4 par catégorie)');
    chk($n('SELECT COUNT(*) FROM cotisation WHERE actif = 1') === 4, '4 périodes actives', 'il devrait y avoir 4 périodes actives');
    chk($n('SELECT COUNT(*) FROM provinces') > 0 && $n('SELECT COUNT(*) FROM territoires') > 0, 'provinces et territoires présents', 'provinces/territoires vides');
    chk($n('SELECT COUNT(*) FROM secteurs') > 10, 'secteurs : ' . $n('SELECT COUNT(*) FROM secteurs') . ' ligne(s)', 'secteurs très incomplets (' . $n('SELECT COUNT(*) FROM secteurs') . ') : les adhérents ne trouveront pas leur secteur', false);
    chk($n('SELECT COUNT(*) FROM site_blocs') > 0 && $n('SELECT COUNT(*) FROM site_menu') > 0, 'contenus et menu en base', 'contenus ou menu vides');
    $vide = $n("SELECT COUNT(*) FROM site_reglages WHERE groupe = 'contact' AND valeur = ''");
    chk($vide === 0, 'coordonnées de contact renseignées', "$vide coordonnée(s) de contact vide(s) dans Admin → Contenus → Réglages (e-mail, téléphone, adresse, réseaux)", false);
    chk($n("SELECT COUNT(*) FROM payments WHERE status IN ('paid') AND (order_number IS NULL OR order_number = '')") === 0, 'tout paiement confirmé a un numéro de commande FlexPay', 'des paiements « paid » sans numéro de commande FlexPay');
} catch (Throwable $e) { bloquant('base de données : ' . $e->getMessage()); }

$url = null; foreach ($argv ?? [] as $a) { if (strpos($a, '--url=') === 0) { $url = rtrim(substr($a, 6), '/'); } }
if ($url) {
    echo "\n== Tests HTTP externes sur $url\n";
    $http = function (string $u, string $method = 'GET', ?string $body = null) {
        $c = curl_init($u);
        curl_setopt_array($c, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 15, CURLOPT_CUSTOMREQUEST => $method]);
        if ($body !== null) { curl_setopt($c, CURLOPT_POSTFIELDS, $body); curl_setopt($c, CURLOPT_HTTPHEADER, ['Content-Type: application/json']); }
        $r = curl_exec($c); $code = (int) curl_getinfo($c, CURLINFO_HTTP_CODE); $hs = (int) curl_getinfo($c, CURLINFO_HEADER_SIZE); curl_close($c);
        return [$code, $r === false ? '' : substr($r, 0, $hs), $r === false ? '' : substr($r, $hs)];
    };
    foreach (['/config/database.php', '/config/flexpay.php', '/includes/bootstrap.php', '/storage/logs/php-error.log', '/migrations/phase1_migrate.php', '/tests/run_tests.php', '/tools/backup.php', '/rcr.sql', '/.htaccess', '/media/test.php', '/api/'] as $p) {
        [$code] = $http($url . $p);
        chk(in_array($code, [403, 404, 410], true), "$p inaccessible ($code)", "$p répond $code : fichier sensible exposé !");
    }
    foreach (['/robots.txt', '/sitemap.xml', '/'] as $p) { [$code] = $http($url . $p); chk($code === 200, "$p → 200", "$p répond $code"); }
    [, $h] = $http($url . '/');
    chk(stripos($h, 'x-content-type-options: nosniff') !== false, 'en-tête X-Content-Type-Options', 'en-tête X-Content-Type-Options absent (mod_headers actif ?)');
    chk(stripos($h, 'x-frame-options') !== false, 'en-tête X-Frame-Options', 'en-tête X-Frame-Options absent');
    chk(stripos($h, 'x-powered-by') === false, 'X-Powered-By masqué', 'X-Powered-By visible', false);
    [$code] = $http(str_replace('https://', 'http://', $url) . '/');
    chk(in_array($code, [301, 302, 308], true), 'http redirige vers https', 'http ne redirige pas vers https : activer la règle dans .htaccess', false);
    [$code] = $http($url . '/index.php?pages=page_inexistante_xyz');
    chk(in_array($code, [200, 404], true), "page inexistante → $code (pas de 500)", "page inexistante → $code");
    [$code, , $b] = $http($url . '/api/flexpay_callback.php', 'POST', '{"reference":"RCR-A-000000-AAAAAAAAAA","code":"0"}');
    chk($code === 404, 'callback : fausse référence rejetée (404)', "callback : réponse inattendue $code pour une fausse référence");
    [$code] = $http($url . '/api/flexpay_callback.php');
    chk($code === 405, 'callback : GET refusé (405)', "callback : GET → $code (405 attendu)");
    [$code] = $http($url . '/admin/index.php?pages=dashboard');
    chk(in_array($code, [200, 302, 303], true), 'admin : redirige vers la connexion sans session', "admin : réponse $code");
}

echo "\n================ " . ($bloq === 0 ? 'PRÊT' : "$bloq POINT(S) BLOQUANT(S)") . " — $conseils conseil(s) ================\n";
exit($bloq === 0 ? 0 : 1);
