<?php
/**
 * Connexion à l'administration — traitée AVANT tout affichage (redirection HTTP propre).
 * password_verify, compte validé obligatoire, blocage après 5 échecs (login_attempts),
 * régénération de l'identifiant de session, journalisation (audit_logs).
 * (L'ancienne connexion sha1() a été retirée lors d'un audit précédent.)
 */
require_once __DIR__ . '/../../includes/login_attempts.php';
require_once __DIR__ . '/../../includes/audit.php';
require_once __DIR__ . '/../../includes/rbac.php';

if (!empty($_SESSION['id_adm'])) {
    header('Location: ?pages=dashboard');
    exit;
}

$message = '';
$type    = 'danger';
if (!empty($_SESSION['admin_login_info'])) {
    $message = (string) $_SESSION['admin_login_info'];
    $type    = 'info';
    unset($_SESSION['admin_login_info']);
}
if (isset($_GET['bye']) && $message === '') {
    $message = 'Vous êtes déconnecté.';
    $type    = 'info';
}
$loginIp = login_attempts_client_ip();

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['login'])) {
    $pseudo   = trim((string) ($_POST['pseudo'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!csrf_verify()) {
        $message = 'Session expirée, merci de recharger la page et réessayer.';
    } elseif (($verrou = login_attempts_check($bdd, $loginIp)) !== null) {
        audit_log($bdd, 'admin.connexion_bloquee', 'admin', '', 'trop de tentatives');
        $message = $verrou;
    } elseif ($pseudo === '' || $password === '') {
        $message = 'Tous les champs sont obligatoires.';
    } else {
        $req = $bdd->prepare('SELECT * FROM admin WHERE pseudo = ? LIMIT 1');
        $req->execute([$pseudo]);
        $u = $req->fetch(PDO::FETCH_ASSOC) ?: null;
        // Hash factice si le compte n'existe pas : même temps de calcul (ne révèle pas les pseudos existants)
        $hash = $u['password'] ?? '$2y$10$abcdefghijklmnopqrstuuYF8d1x0p4w0Zr1m3o5q7s9u1w3y5a7c';
        $ok   = password_verify($password, (string) $hash) && $u !== null;

        if ($ok && (int) $u['confirmer'] !== 1) {
            $message = "Votre compte n'est pas encore validé par l'administration.";
            $type    = 'warning';
        } elseif ($ok) {
            session_regenerate_id(true);
            $_SESSION['id_adm']    = (int) $u['id_adm'];
            $_SESSION['pseudo']    = $u['pseudo'];
            $_SESSION['mail']      = $u['mail'];
            $_SESSION['niveau']    = (int) $u['niveau'];
            $_SESSION['confirmer'] = (int) $u['confirmer'];
            $_SESSION['adm_last']  = time();
            rbac_flush();
            login_attempts_reset($bdd, $loginIp);
            audit_log($bdd, 'admin.connexion', 'admin', (string) $u['id_adm']);
            // Rehachage transparent si l'algorithme par défaut a évolué
            if (password_needs_rehash((string) $u['password'], PASSWORD_DEFAULT)) {
                $bdd->prepare('UPDATE admin SET password = ? WHERE id_adm = ?')->execute([password_hash($password, PASSWORD_DEFAULT), (int) $u['id_adm']]);
            }
            header('Location: ?pages=' . (has_permission($bdd, 'dashboard.voir') ? 'dashboard' : 'home'));
            exit;
        } else {
            login_attempts_record_failure($bdd, $loginIp);
            audit_log($bdd, 'admin.connexion_echec', 'admin', (string) ($u['id_adm'] ?? ''), $u ? 'mot de passe incorrect' : 'pseudo inconnu');
            $message = 'Pseudo ou mot de passe incorrect.';
        }
    }
}
