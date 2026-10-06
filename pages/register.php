<?php
/**
 * Fonctionnalité retirée (audit de sécurité) : ancien formulaire
 * d'inscription "client" ciblant une table `client` absente du schéma de
 * base de données. Aucun menu du site n'y renvoyait (dead code confirmé).
 * Retiré à la demande du propriétaire du projet plutôt que supprimé, pour
 * conserver l'historique.
 *
 * L'inscription active se fait désormais via le formulaire d'adhésion
 * (adhere/adhesion.php). Pas de header() ici pour la même raison que dans
 * pages/client.php (HTML déjà envoyé à ce stade du gabarit).
 */
?>
<section class="inner-page mt-5 mb-5">
    <div class="container text-center">
        <div class="alert alert-secondary d-inline-block px-5 py-4">
            <p class="mb-3">Cette fonctionnalité n'est plus disponible.</p>
            <a href="adhere/adhesion.php" class="btn btn-danger">Faire une demande d'adhésion</a>
        </div>
    </div>
</section>
