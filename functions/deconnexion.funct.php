<?php
/**
 * 🔧 CORRECTIF BUG RÉEL (signalé par le client) : le bouton
 * "Déconnexion" (pages/esp_membre.php → ?pages=deconnexion)
 * affichait une erreur PHP au clic.
 *
 * Cause : l'ancien pages/deconnexion.php appelait session_start()
 * une seconde fois (déjà fait par functions/main_function.php, voir
 * index.php) puis surtout setcookie() APRÈS que le HTML (<head>,
 * navbar...) avait déjà été envoyé au navigateur — or setcookie() et
 * header() exigent qu'aucun octet de sortie n'ait encore été émis,
 * d'où l'avertissement "Cannot modify header information - headers
 * already sent...".
 *
 * Comme functions/<page>.funct.php est inclus par index.php AVANT
 * tout HTML (même mécanisme déjà utilisé par functions/login.funct.php
 * pour sa redirection après connexion), la déconnexion est déplacée
 * ici : la session est vidée et le cookie supprimé avant l'envoi de
 * la moindre sortie, avec une vraie redirection header() — plus
 * fiable que le contournement en JavaScript utilisé précédemment.
 */

// Vider les données de session.
$_SESSION = [];

// Supprimer le cookie de session côté navigateur (bonne pratique).
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Détruire la session côté serveur.
session_destroy();

header('Location: ?pages=login');
exit();
