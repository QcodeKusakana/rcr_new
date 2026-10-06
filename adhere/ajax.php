<?php
require_once __DIR__ . '/main_function.php';

$provinceID = $_POST['provinceID'] ?? '';
$outpu = "<option value=''>Selectionner un territoire</option>";

$stmt = $bdd->prepare("SELECT * FROM territoires WHERE id_p = ?");
$stmt->execute([$provinceID]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $outpu .= '<option value="' . htmlspecialchars($row['id_tr']) . '">' . htmlspecialchars($row['nom_tr']) . '</option>';
}
echo $outpu;
