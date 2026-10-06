<?php
/**
 * Liste publique des actualités / événements (publiés uniquement), filtrable par catégorie (?cat=).
 * (L'ancien code « panier » hérité d'un modèle e-commerce a été retiré : il visait une table inexistante
 * et permettait des écritures en base sans authentification.)
 */
require_once __DIR__ . '/../includes/actualites.php';

$actuCategorie = trim((string) ($_GET['cat'] ?? ''));
$actuCategorie = $actuCategorie !== '' ? mb_substr($actuCategorie, 0, 50) : null;
$actu = actu_liste($bdd, $actuCategorie, 12);

// Compatibilité avec les variables utilisées par la page
$current = $actu['current'];
$nbPage  = $actu['nbPage'];
