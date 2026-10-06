<?php
/**
 * Taux de commission du programme de parrainage.
 *
 * Valeur métier confirmée par l'utilisateur : 1% par défaut, appliqué à
 * CHAQUE paiement réussi d'un filleul (adhésion initiale ET
 * renouvellements) — voir la requête des filleuls dans
 * functions/esp_membre.funct.php.
 *
 * ⚠️ Affichage estimatif uniquement (choix explicite de l'utilisateur,
 * pas de nouvelle table de suivi) : aucun versement réel n'est déclenché
 * automatiquement par le site. Le montant affiché sur pages/esp_membre.php
 * est calculé à la volée à chaque chargement de page, jamais stocké.
 *
 * Centralisé ici (même principe que config/flexpay.php, protégé par la
 * même règle .htaccess bloquant l'accès direct à config/) pour pouvoir
 * changer le taux à un seul endroit si besoin, sans toucher au code
 * métier des pages.
 */

if (!defined('TAUX_COMMISSION_PARRAINAGE')) {
    define('TAUX_COMMISSION_PARRAINAGE', 1.0); // en pourcentage (%)
}
