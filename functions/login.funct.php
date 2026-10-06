<?php
$erreur = null;
if (!empty($_SESSION['login_info'])) { $erreur = (string) $_SESSION['login_info']; unset($_SESSION['login_info']); }
/**
 * Connexion membre : code d'adhésion OU e-mail + mot de passe (password_verify).
 * Limitation des tentatives par IP (includes/login_attempts.php). Messages volontairement génériques.
 */
require_once __DIR__ . '/../includes/login_attempts.php';

if (isset($_POST['login']) && !csrf_verify()) {
    $erreur = "Session expirée, merci de recharger la page et réessayer.";
} elseif (isset($_POST['login']) || isset($_POST['identifiant'])) {
    $ip = login_attempts_client_ip();
    $verrouillage = login_attempts_check($bdd, $ip);
    if ($verrouillage !== null) {
        $erreur = $verrouillage;
    } else {
        $identifiant = trim((string) ($_POST['identifiant'] ?? ''));
        $mdp = (string) ($_POST['mot_de_passe'] ?? '');
        $user = null;
        if ($identifiant !== '' && $mdp !== '') {
            $req = $bdd->prepare('SELECT * FROM adhesion WHERE codes = ? OR mail = ? LIMIT 1');
            $req->execute([strtoupper($identifiant), mb_strtolower($identifiant)]);
            $user = $req->fetch(PDO::FETCH_ASSOC) ?: null;
        }
        // Hash factice si le compte n'existe pas : même durée de calcul (évite de révéler quels comptes existent)
        $hash = $user['password_hash'] ?? '$2y$10$abcdefghijklmnopqrstuuYF8d1x0p4w0Zr1m3o5q7s9u1w3y5a7c';
        $ok = password_verify($mdp, $hash) && $user && !empty($user['password_hash']);

        if ($ok && ($user['statut'] ?? '') === 'suspendu') {
            $ok = false;
            $erreur = "Votre compte est suspendu. Contactez le secrétariat du RCR.";
        }
        if ($ok) {
            session_regenerate_id(true);
            $_SESSION['id_ad']       = $user['id_ad'];
            $_SESSION['codes']       = $user['codes'];
            $_SESSION['nom']         = $user['nom'];
            $_SESSION['prenom']      = $user['prenom'];
            $_SESSION['postnom']     = $user['postnom'];
            $_SESSION['nationalite'] = $user['nationalite'];
            $_SESSION['civilite']    = $user['civilite'];
            $_SESSION['passeport']   = $user['passeport'];
            $bdd->prepare('UPDATE adhesion SET derniere_connexion = NOW() WHERE id_ad = ?')->execute([$user['id_ad']]);
            login_attempts_reset($bdd, $ip);
            header('Location: ?pages=esp_membre');
            exit;
        }
        login_attempts_record_failure($bdd, $ip);
        $erreur = $erreur ?? "Identifiants incorrects.";
    }
}
