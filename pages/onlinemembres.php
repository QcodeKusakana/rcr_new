<?php
// Compteur de membres en ligne. Allégé pour le trafic élevé : au plus un passage SQL par session toutes les 30 s
// (avant : 3 à 4 requêtes à CHAQUE page vue, y compris pour les simples visiteurs).
if (isset($_SESSION['_om_t'], $_SESSION['_om_n']) && (time() - (int) $_SESSION['_om_t']) < 30) {
    $user_nbr_mbre = (int) $_SESSION['_om_n'];
} else {
    $temps_actuel = time();
    if (!empty($_SESSION['id_ad'])) {
        $idMembre = (int) $_SESSION['id_ad'];
        $maj = $bdd->prepare('UPDATE onlines SET temps = ? WHERE id_usr = ?');
        $maj->execute([$temps_actuel, $idMembre]);
        if ($maj->rowCount() === 0) {
            $bdd->prepare('INSERT INTO onlines (id_usr, temps) VALUES (?, ?)')->execute([$idMembre, $temps_actuel]);
        }
        $bdd->prepare('DELETE FROM onlines WHERE temps < ?')->execute([$temps_actuel - 60]);
    }
    $user_nbr_mbre = (int) $bdd->query('SELECT COUNT(*) FROM onlines WHERE temps >= ' . ($temps_actuel - 60))->fetchColumn();
    $_SESSION['_om_t'] = time();
    $_SESSION['_om_n'] = $user_nbr_mbre;
}
