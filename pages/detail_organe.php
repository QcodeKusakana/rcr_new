<?php
/**
 * Fonctionnalité retirée : voir pages/organe.php pour le détail. Cette
 * page listait le détail d'un "organe" dont la table SQL correspondante
 * n'existe pas dans le schéma — accessible uniquement depuis pages/organe.php
 * (elle-même orpheline, aucun lien entrant dans tout le site).
 * L'injection SQL initialement identifiée ici (interpolation directe de
 * $_GET['id']) a été corrigée par précaution avant de constater que la
 * fonctionnalité entière était déjà non fonctionnelle et sans utilisateur
 * possible ; la page est neutralisée plutôt que laissée accessible.
 */
?>
<section class="inner-page mt-5 mb-5">
    <div class="container text-center">
        <div class="alert alert-secondary d-inline-block px-5 py-4">
            <p class="mb-0">Cette page n'est plus disponible.</p>
        </div>
    </div>
</section>
