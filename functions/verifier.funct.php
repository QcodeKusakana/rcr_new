<?php
/**
 * 🔧 NOUVEAU FICHIER — logique de la page publique de vérification
 * d'authenticité d'une fiche d'adhésion (cible du QR code imprimé sur
 * admin/pages/print/print_adherer.php).
 *
 * Accessible publiquement et sans connexion (n'importe qui scannant une
 * fiche imprimée doit pouvoir vérifier son authenticité), donc :
 * - AUCUNE donnée personnelle sensible n'est exposée (pas d'email, pas de
 *   téléphone, pas de date de naissance, pas d'adresse) — uniquement ce
 *   qui est nécessaire pour confirmer qu'une fiche est authentique ;
 * - la recherche se fait sur `codes` (le code d'adhérent imprimé sur la
 *   fiche elle-même), jamais sur l'identifiant interne `id_ad`.
 */

require_once __DIR__ . '/main_function.php';

$codeVerif = strtoupper(substr(trim((string) ($_GET['code'] ?? '')), 0, 30));
$resultat  = null;

if ($codeVerif !== '') {
    $req = $bdd->prepare("
        SELECT
            adhesion.codes,
            adhesion.nom,
            adhesion.postnom,
            adhesion.prenom,
            adhesion.dat_adhesion,
            qualites.designation,
            grades.nom_gd,
            provinces.nom_p,
            adhesion.date_echeance,
            (adhesion.statut = 'actif' AND adhesion.date_echeance IS NOT NULL AND adhesion.date_echeance >= CURDATE()) AS est_valide
        FROM adhesion
        INNER JOIN qualites  ON adhesion.id_qt     = qualites.id_qt
        INNER JOIN grades    ON adhesion.grade     = grades.id_gd
        INNER JOIN provinces ON adhesion.province  = provinces.id_p
        WHERE adhesion.codes = ?
        LIMIT 1
    ");
    $req->execute([$codeVerif]);
    $resultat = $req->fetch(PDO::FETCH_ASSOC) ?: false;
}
