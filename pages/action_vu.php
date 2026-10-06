<?php

if (!isset($_GET['id']) || empty($_GET['id'])) {
    exit;
}

$id = (int) $_GET['id'];
$ip = $_SERVER['REMOTE_ADDR'];

// vérifier article
$check = $bdd->prepare("SELECT id_act FROM activite WHERE id_act = ?");
$check->execute([$id]);

if ($check->rowCount() == 1) {

    // vérifier si déjà vu
    $checkVu = $bdd->prepare("
        SELECT id_vu 
        FROM vu 
        WHERE id_act = ? 
        AND ip = ?
    ");

    $checkVu->execute([$id, $ip]);

    if ($checkVu->rowCount() == 0) {

        $insert = $bdd->prepare("
            INSERT INTO vu (id_act, ip) 
            VALUES (?, ?)
        ");

        $insert->execute([$id, $ip]);
    }
}
?>