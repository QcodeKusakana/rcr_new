<?php
/**
 * 🔧 CORRECTIF BUG RÉEL : la déconnexion se fait désormais dans
 * functions/deconnexion.funct.php, exécuté par index.php AVANT tout
 * HTML — la session y est vidée et une vraie redirection header()
 * est envoyée, ce qui évite l'erreur "headers already sent" que
 * provoquait l'ancien code ici (session_start()/setcookie() appelés
 * après que le head et la navbar avaient déjà été envoyés au
 * navigateur).
 *
 * Ce fichier ne s'exécute normalement JAMAIS : functions/deconnexion.funct.php
 * appelle exit() après sa redirection. Il reste présent uniquement
 * parce que le routeur (index.php) exige qu'un fichier pages/<page>.php
 * existe pour reconnaître "?pages=deconnexion" comme une page valide.
 * Conservé comme filet de sécurité minimal (sans session_start() ni
 * setcookie(), qui échoueraient de toute façon à ce stade de l'affichage).
 */
?>
<div class="container py-5 text-center">
    <p>Vous avez été déconnecté. <a href="?pages=login">Cliquez ici</a> si la redirection automatique n'a pas fonctionné.</p>
</div>
