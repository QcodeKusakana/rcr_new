<?php
/** Déconnexion administrateur : journalisée, session détruite et cookie supprimé AVANT tout affichage. */
require_once __DIR__ . '/../../includes/audit.php';
if (!empty($_SESSION['id_adm'])) {
    audit_log($bdd, 'admin.deconnexion', 'admin', (string) $_SESSION['id_adm']);
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
header('Location: ?pages=login&bye=1');
exit;
