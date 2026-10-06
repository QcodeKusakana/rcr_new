<?php
/**
 * Amorçage commun du site (session sécurisée, erreurs, base de données, modules).
 * Inclus par functions/main_function.php et adhere/main_function.php : les anciennes
 * pages continuent donc de fonctionner sans changement.
 */

if (!defined('RCR_BOOTSTRAPPED')) {
    define('RCR_BOOTSTRAPPED', true);

    require_once __DIR__ . '/db.php'; // config/database.php + fuseau horaire + connexion PDO commune

    // Erreurs : jamais affichées aux visiteurs, toujours journalisées (storage/logs)
    if (!defined('APP_DEBUG') || !APP_DEBUG) {
        ini_set('display_errors', '0');
    }
    ini_set('log_errors', '1');
    $logDir = __DIR__ . '/../storage/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0750, true);
    }
    if (is_dir($logDir) && is_writable($logDir)) {
        ini_set('error_log', $logDir . '/php-error.log');
    }

    mb_internal_encoding('UTF-8');

    // Erreurs : jamais de détail technique au visiteur ; tout est journalisé dans storage/logs/php-error.log.
    $rcrErreur500 = function () {
        if (PHP_SAPI === 'cli' || headers_sent()) {
            return;
        }
        if (strpos((string) ($_SERVER['SCRIPT_NAME'] ?? ''), '/api/') !== false || strpos((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json') === 0) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'server']);
            return;
        }
        while (ob_get_level() > 0) { @ob_end_clean(); }
        include __DIR__ . '/../errors/500.php';
    };
    // Référence d'incident : affichée au visiteur et écrite dans le journal, pour retrouver la ligne exacte.
    $GLOBALS['rcr_incident'] = ['ref' => strtoupper(substr(bin2hex(random_bytes(4)), 0, 6)), 'detail' => ''];
    set_exception_handler(function (Throwable $e) use ($rcrErreur500) {
        $msg = get_class($e) . ': ' . $e->getMessage() . ' @ ' . str_replace(dirname(__DIR__) . '/', '', $e->getFile()) . ':' . $e->getLine();
        error_log('[exception #' . $GLOBALS['rcr_incident']['ref'] . '] ' . $msg);
        $GLOBALS['rcr_incident']['detail'] = $msg;
        $rcrErreur500();
        exit;
    });
    register_shutdown_function(function () use ($rcrErreur500) {
        $err = error_get_last();
        if ($err !== null && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            $msg = $err['message'] . ' @ ' . str_replace(dirname(__DIR__) . '/', '', $err['file']) . ':' . $err['line'];
            error_log('[fatal #' . $GLOBALS['rcr_incident']['ref'] . '] ' . $msg);
            $GLOBALS['rcr_incident']['detail'] = $msg;
            $rcrErreur500();
        }
    });

    // Session : cookie HttpOnly, Secure en HTTPS, SameSite=Lax
    // L'API mobile (api/v1) s'authentifie par jeton : elle définit RCR_NO_SESSION pour ne créer aucune session PHP.
    if (session_status() !== PHP_SESSION_ACTIVE && !defined('RCR_NO_SESSION')) {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
        session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax']);
        session_start();
    }

    // Espace membre : déconnexion automatique après 2 h d'inactivité (l'administration a sa propre règle de 30 min)
    if (!empty($_SESSION['id_ad'])) {
        if (isset($_SESSION['membre_last']) && time() - (int) $_SESSION['membre_last'] > 7200) {
            unset($_SESSION['id_ad'], $_SESSION['codes'], $_SESSION['nom'], $_SESSION['prenom'], $_SESSION['postnom'],
                  $_SESSION['nationalite'], $_SESSION['civilite'], $_SESSION['passeport'], $_SESSION['membre_last']);
            $_SESSION['login_info'] = 'Votre session a expiré. Merci de vous reconnecter.';
        } else {
            $_SESSION['membre_last'] = time();
        }
    }

    require_once __DIR__ . '/csrf.php';
    require_once __DIR__ . '/helpers.php';

    try {
        $bdd = rcr_db_connect();
    } catch (PDOException $e) {
        error_log('[bootstrap] connexion BDD : ' . $e->getMessage());
        http_response_code(503);
        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, "Connexion à la base impossible (voir storage/logs/php-error.log).\n");
            exit(1);
        }
        include __DIR__ . '/../errors/500.php';
        exit;
    }

    require_once __DIR__ . '/audit.php';
    require_once __DIR__ . '/contenu.php';
    require_once __DIR__ . '/tarifs.php';
}
