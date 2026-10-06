<?php
require_once __DIR__ . '/main_function.php';

$search2 = $_POST['search2'] ?? '';
$outpu = '';

$stmt = $bdd->prepare("SELECT * FROM adhesion WHERE codes LIKE ?");
$stmt->execute(['%' . $search2 . '%']);

if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $outpu .= '<option value="' . htmlspecialchars($row['id_ad']) . '">' . htmlspecialchars($row['nom'] . ' ' . $row['prenom'] . ' ' . $row['postnom']) . '</option>';
    }
} else {
    $outpu .= '<option>Pas de resultat!</option>';
}
echo $outpu;
