<?php
require_once __DIR__ . '/main_function.php';

$search = $_POST['search'] ?? '';
$outpu = '';

$stmt = $bdd->prepare("SELECT * FROM adhesion WHERE codes LIKE ?");
$stmt->execute(['%' . $search . '%']);

if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $outpu .= '<option value="' . htmlspecialchars($row['id_ad']) . '">' . htmlspecialchars($row['nom'] . ' ' . $row['prenom'] . ' ' . $row['postnom']) . '</option>';
    }
} else {
    $outpu .= '<option>Pas de resultat!</option>';
}
echo $outpu;
