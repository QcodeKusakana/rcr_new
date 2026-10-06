<?php
// Compatibilité : liste des grades ACTIFS d'une catégorie avec le tarif mensuel (le formulaire utilise désormais window.RCR_TARIFS).
require_once __DIR__ . '/main_function.php';
require_once __DIR__ . '/../includes/tarifs.php';
$out = '';
foreach (tarifs_grades($bdd, (int) ($_POST['qualiteID'] ?? 0)) as $g) {
    $out .= '<option value="' . (int) $g['id_gd'] . '">' . htmlspecialchars($g['nom_gd']) . ' - ' . number_format((float) $g['prix'], 2) . ' USD / mois</option>';
}
echo $out;
