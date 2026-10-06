<?php
/**
 * Fonctionnalité retirée (découverte lors du travail de modernisation) :
 * cette page interrogeait une table `organe` totalement absente du schéma
 * de base de données (rcr.sql) — la requête échouait donc systématiquement
 * (PDO en mode silencieux : $organe->fetch() sur un résultat "false"
 * provoquait une erreur fatale PHP 8 pour tout visiteur qui l'atteignait).
 *
 * Son contenu ("LES ORGANES DE L'INADD - Institut Africain de Recherche
 * sur le Développement Durable") est en outre sans rapport avec le RCR :
 * il s'agit visiblement d'un reliquat du template d'origine, jamais adapté
 * au projet. Aucun lien entrant vers cette page n'existe dans le site
 * (menu, navbar ou autres pages) — confirmé par recherche exhaustive.
 *
 * Retirée par prudence plutôt que supprimée, pour conserver l'historique.
 * Remarque technique : pas de header('Location: ...') ici, le HTML du
 * gabarit étant déjà envoyé à ce stade (voir pages/client.php pour la
 * même remarque).
 */
?>
<section class="inner-page mt-5 mb-5">
    <div class="container text-center">
        <div class="alert alert-secondary d-inline-block px-5 py-4">
            <p class="mb-0">Cette page n'est plus disponible.</p>
        </div>
    </div>
</section>
