<?php
require_once __DIR__ . '/main_function.php';

$territoireID = $_POST['territoireID'] ?? '';
$outpu = "<option value=''>Selectionner un secteur</option>";

$stmt = $bdd->prepare("SELECT * FROM secteurs WHERE id_tr = ?");
$stmt->execute([$territoireID]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $outpu .= '<option value="' . htmlspecialchars($row['id_sec']) . '">' . htmlspecialchars($row['nom_sec']) . '</option>';
}
echo $outpu;
