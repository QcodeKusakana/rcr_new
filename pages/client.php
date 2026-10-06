<?php
/**
 * Fonctionnalité retirée (audit de sécurité) : cet ancien système de compte
 * "client" ciblait une table `client` absente du schéma de base de données
 * et n'était plus accessible depuis aucun menu du site (dead code confirmé
 * par recherche exhaustive des liens entrants). Retiré à la demande du
 * propriétaire du projet plutôt que supprimé, pour conserver l'historique.
 *
 * Le système actif équivalent est l'espace membre/adhérent
 * (pages/login.php, pages/esp_membre.php).
 *
 * Remarque technique : on n'utilise pas header('Location: ...') ici car à
 * ce stade du gabarit, le HTML (doctype, head, navbar) est déjà envoyé au
 * navigateur — un header() échouerait silencieusement ("headers already
 * sent"). D'où l'usage d'un lien simple.
 */
?>
<section class="inner-page mt-5 mb-5">
    <div class="container text-center">
        <div class="alert alert-secondary d-inline-block px-5 py-4">
            <p class="mb-3">Cette fonctionnalité n'est plus disponible.</p>
            <a href="?pages=login" class="btn btn-danger">Accéder à l'espace membre</a>
        </div>
    </div>
</section>
