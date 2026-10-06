<?php
/**
 * 🔧 CORRECTIF (session PHP perdue par intermittence — cause probable de
 * "Accès refusé" imprévisibles sur print_adherer.php, l'espace membre,
 * le back-office...).
 *
 * Vos propres journaux d'erreur montrent :
 *   PHP Warning: session_start(): Session ID is too long or contains
 *   illegal characters
 *   PHP Warning: session_start(): Failed to read session data: files
 *   (path: /var/cpanel/php/sessions/ea-php84)
 *
 * Le dossier de sessions PHP par défaut de cet hébergement mutualisé est
 * partagé entre tous les comptes du serveur, et un ancien cookie de
 * session (généré sous une configuration PHP différente) peut contenir
 * un identifiant que PHP 8.4 juge invalide. Dans les deux cas, PHP
 * démarre alors une session VIDE au lieu d'échouer proprement — tout ce
 * qui a été stocké juste avant (par exemple $_SESSION['id_ad'] posé par
 * adhere/adhesion.funct.php dès la soumission du formulaire) est
 * silencieusement perdu. Résultat concret : un membre qui vient de payer
 * peut se voir refuser sa propre fiche par print_adherer.php, sans
 * aucune action anormale de sa part.
 *
 * Ce fichier centralise le démarrage de session pour tout le projet :
 * - utilise un dossier de sessions PROPRE à ce compte (au lieu du
 *   dossier partagé par défaut du serveur), créé automatiquement au
 *   premier appel s'il n'existe pas encore ;
 * - protégé par un .htaccess "deny all" (voir tmp_sessions/.htaccess),
 *   comme le fait déjà admin/pages/TCPDF-main/tools/.htaccess ailleurs
 *   dans ce projet — donc jamais accessible par une URL ;
 * - détecte un identifiant de session invalide AVANT d'appeler
 *   session_start() et l'ignore proprement plutôt que de laisser PHP
 *   échouer et repartir sur une session vide.
 *
 * Usage : remplace chaque `session_start();` du projet par
 * `require_once __DIR__ . '/.../includes/session_bootstrap.php';`
 * (adapter le nombre de `../` selon la profondeur du fichier appelant).
 * Sans danger à inclure plusieurs fois, ou après un session_start()
 * déjà actif : ne fait rien dans ce cas (comme le vérifiait déjà
 * admin/menu/navbar.php au cas par cas).
 */

if (session_status() === PHP_SESSION_ACTIVE) {
    return;
}

// Dossier de sessions dédié à ce compte, créé au besoin.
$sessionPath = __DIR__ . '/../tmp_sessions';

if (!is_dir($sessionPath)) {
    @mkdir($sessionPath, 0700, true);
}

if (is_dir($sessionPath) && is_writable($sessionPath)) {
    session_save_path($sessionPath);
}

// Un identifiant de session invalide (cookie corrompu, ou généré sous
// une configuration PHP plus permissive) fait échouer session_start()
// avec un avertissement au lieu de simplement l'ignorer : on l'invalide
// nous-même avant l'appel, PHP en régénère alors un nouveau proprement.
$sessionName = session_name();
if (isset($_COOKIE[$sessionName]) && !preg_match('/^[A-Za-z0-9,\-]{1,128}$/', (string) $_COOKIE[$sessionName])) {
    unset($_COOKIE[$sessionName]);
    session_id('');
}

session_start();
