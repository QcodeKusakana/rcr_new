<?php
/**
 * Fonctionnalité retirée (audit de sécurité) : cette page de gestion de
 * "commandes/panier client" référence des tables (`commande`, `panier`)
 * absentes du schéma de base de données, et référence des variables
 * jamais définies ($clientpanier, $client, $reqpanier — aucun fichier
 * admin/functions/commande.funct.php n'existe). Aucun menu admin ne
 * pointait vers cette page (dead code confirmé). Retirée à la demande du
 * propriétaire du projet plutôt que supprimée, pour conserver l'historique.
 */
?>
<div class="container-fluid py-3">
    <div class="alert alert-secondary">Cette fonctionnalité n'est plus disponible.</div>
</div>
