<?php
/**
 * Fonctionnalité retirée (audit de sécurité) : cette page de gestion
 * documentaire ciblait une table `document` et une colonne `id_dos`
 * absentes du schéma de base de données, n'était référencée par aucun
 * menu admin, et traitait un upload de fichier sans authentification.
 * Retirée à la demande du propriétaire du projet plutôt que supprimée,
 * pour conserver l'historique.
 */
echo '<div class="alert alert-secondary m-4">Cette fonctionnalité n\'est plus disponible.</div>';
