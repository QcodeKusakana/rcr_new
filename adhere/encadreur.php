<?php
// Recherche exacte par code membre (membres ACTIFS uniquement) pour le parrain / l'encadreur.
require_once __DIR__ . '/main_function.php';
$search = strtoupper(trim((string) ($_POST['search2'] ?? '')));
if (!preg_match('/^[A-Z0-9]{6,20}$/', $search)) { echo '<option value="">Code invalide</option>'; exit; }
$stmt = $bdd->prepare("SELECT id_ad, nom, prenom FROM adhesion WHERE codes = ? AND statut = 'actif' LIMIT 1");
$stmt->execute([$search]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);
echo $r ? '<option value="' . (int) $r['id_ad'] . '">' . htmlspecialchars($r['nom'] . ' ' . $r['prenom']) . '</option>' : '<option value="">Aucun membre actif avec ce code</option>';
