<?php
/**
 * Détail d'une actualité : chargé AVANT tout affichage (redirections et en-têtes possibles).
 * - identifiant invalide ou article non publié -> page 404 propre ;
 * - vue comptée une fois par adresse IP ;
 * - commentaire : jeton CSRF, champ piège anti-robot, 1 commentaire / 30 s par session,
 *   enregistré brut et échappé à l'affichage ; redirection après envoi (pas de double envoi au rechargement).
 */
require_once __DIR__ . '/../includes/actualites.php';

$getid   = (int) ($_GET['id'] ?? 0);
$article = actu_article($bdd, $getid);
if ($article === null) {
    http_response_code(404);
    include __DIR__ . '/../errors/404.php';
    exit;
}

actu_enregistrer_vue($bdd, $getid);

$erreur = null;
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['comment'])) {
    $commentaire = mb_substr(trim((string) ($_POST['commentaire'] ?? '')), 0, 1000);
    $pseudo      = mb_substr(trim((string) ($_POST['pseudo'] ?? '')), 0, 60);
    if (!csrf_verify()) {
        $erreur = "Session expirée, merci de recharger la page et de réessayer.";
    } elseif (trim((string) ($_POST['site_web'] ?? '')) !== '') {
        $erreur = "Votre commentaire n'a pas pu être enregistré."; // robot (champ piège rempli)
    } elseif ($commentaire === '' || $pseudo === '') {
        $erreur = "Merci d'indiquer votre nom et votre commentaire.";
    } elseif (time() - (int) ($_SESSION['dernier_commentaire'] ?? 0) < 30) {
        $erreur = "Merci de patienter quelques secondes avant de publier un nouveau commentaire.";
    } else {
        $bdd->prepare('INSERT INTO commentaire (commentaire, pseudo, id_act, date_pub) VALUES (?, ?, ?, NOW())')
            ->execute([$commentaire, $pseudo, $getid]);
        $_SESSION['dernier_commentaire'] = time();
        $_SESSION['flash_commentaire'] = 'Merci, votre commentaire a été publié.';
        header('Location: ?pages=detail&id=' . $getid . '#commentaires');
        exit;
    }
}

$commentaires = actu_commentaires($bdd, $getid);
$recents      = actu_recents($bdd, $getid, 5);
$flashCommentaire = $_SESSION['flash_commentaire'] ?? null;
unset($_SESSION['flash_commentaire']);
