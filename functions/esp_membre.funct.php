<?php
if (isset($_SESSION['id_ad'])) {
    // Échéances dépassées -> statut 'expire' (idempotent, une seule requête)
    $bdd->exec("UPDATE adhesion SET statut = 'expire' WHERE statut = 'actif' AND date_echeance IS NOT NULL AND date_echeance < CURDATE()");

    $id_ad = $_SESSION['id_ad'];

    // 🔧 CORRECTIF (bug "$0.00" sur PAIEMENT & ABONNEMENT) : la requête
    // utilisait "SELECT *" sur plusieurs tables jointes qui partagent des
    // colonnes de même nom (ex : "prix" existe à la fois dans qualites,
    // cotisation ET grades). PDO::FETCH_ASSOC ne garde que la DERNIÈRE
    // valeur rencontrée pour chaque nom de colonne : ici cotisation.prix
    // (toujours 0 en base) écrasait silencieusement grades.prix (le vrai
    // montant du grade). On sélectionne maintenant explicitement chaque
    // colonne utile avec un alias propre pour éliminer toute collision.
    //
    // Le "LEFT JOIN payments" a aussi été retiré : aucune colonne de
    // payments n'était utilisée sur cette page, et cette jointure pouvait
    // (a) dupliquer la ligne du membre s'il avait plusieurs tentatives de
    // paiement (voir rcr.sql, id_ad=12 a 2 lignes payments), et (b) risquer
    // une collision sur la colonne "id_ad" partagée entre adhesion et
    // payments.
    $sql = "SELECT
            adhesion.*,
            provinces.nom_p,
            territoires.nom_tr,
            secteurs.nom_sec,
            qualites.designation,
            grades.nom_gd,
            grades.prix AS prix_grade,
            cotisation.nom_cot,
            cotisation.jours,
            cotisation.mois,
            cotisation.prix AS prix_cotisation
    FROM adhesion
    LEFT JOIN provinces ON adhesion.province = provinces.id_p
    LEFT JOIN territoires ON adhesion.territoire = territoires.id_tr
    LEFT JOIN secteurs ON adhesion.secteur = secteurs.id_sec
    LEFT JOIN qualites ON adhesion.id_qt = qualites.id_qt
    LEFT JOIN grades ON adhesion.grade = grades.id_gd
    LEFT JOIN cotisation ON adhesion.reglement = cotisation.id_cot
    WHERE adhesion.id_ad = ?";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([$id_ad]);

    $l_adherers = $stmt;
    $infoAd = $l_adherers->fetch(PDO::FETCH_ASSOC);
    if (!$infoAd) {
        // Compte supprimé entre-temps : session membre fermée proprement
        unset($_SESSION['id_ad'], $_SESSION['codes'], $_SESSION['passeport']);
        header('Location: ?pages=login');
        exit;
    }
    if ($infoAd['statut'] === 'suspendu') {
        unset($_SESSION['id_ad'], $_SESSION['codes'], $_SESSION['passeport']);
        $_SESSION['login_info'] = 'Votre compte est suspendu. Contactez le secrétariat du RCR.';
        header('Location: ?pages=login');
        exit;
    }

    // sponsor
    $sponsor = $bdd->prepare("
        SELECT *
        FROM adhesion
        INNER JOIN parner ON adhesion.id_ad = parner.id_dest
        WHERE adhesion.id_ad = ?
    ");

    $sponsor->execute([$id_ad]);
    $rows = $sponsor->rowCount();

    // =========================
    // HISTORIQUE DE MES PAIEMENTS
    // =========================
    // Tous les paiements de CE membre (adhésion + renouvellements),
    // quel que soit leur statut, du plus récent au plus ancien — la
    // page affiche un badge coloré par statut plutôt que de ne montrer
    // que les paiements réussis, pour que le membre voie aussi ses
    // tentatives échouées.
    $stmtPaiements = $bdd->prepare("
        SELECT id, montant, devise, status, reference, provider, created_at
        FROM payments
        WHERE id_ad = ?
        ORDER BY created_at DESC
    ");
    $stmtPaiements->execute([$id_ad]);
    $mesPaiements = $stmtPaiements->fetchAll(PDO::FETCH_ASSOC);

    // =========================
    // FILLEULS PARRAINÉS + MONTANT TOTAL PAYÉ PAR CHACUN
    // =========================
    // ⚠️ Sémantique réelle des colonnes de `parner` (vérifiée dans
    // adhere/adhesion.funct.php, l'INSERT au moment de l'adhésion) :
    // id_exp = le NOUVEAU membre qui s'inscrit, id_dest = le PARRAIN
    // (dont l'id est transmis via ?idmbre= dans le lien de parrainage).
    // C'est l'inverse de ce que suggèrent les noms de colonnes
    // ("expéditeur"/"destinataire"), mais c'est le comportement réel du
    // code existant qu'il faut suivre pour que les données soient
    // justes. Agrégation en une seule requête (plutôt qu'une requête
    // par filleul) pour rester performant même avec beaucoup de filleuls.
    $stmtFilleuls = $bdd->prepare("
        SELECT
            adhesion.id_ad,
            adhesion.nom,
            adhesion.postnom,
            adhesion.prenom,
            adhesion.codes,
            adhesion.dat_adhesion,
            grades.nom_gd,
            COALESCE(SUM(CASE WHEN payments.status = 'paid' THEN payments.montant ELSE 0 END), 0) AS total_paye,
            COUNT(CASE WHEN payments.status = 'paid' THEN payments.id END) AS nb_paiements
        FROM adhesion
        INNER JOIN parner ON adhesion.id_ad = parner.id_exp
        LEFT JOIN grades ON adhesion.grade = grades.id_gd
        LEFT JOIN payments ON payments.id_ad = adhesion.id_ad
        WHERE parner.id_dest = ?
        GROUP BY adhesion.id_ad, adhesion.nom, adhesion.postnom, adhesion.prenom, adhesion.codes, adhesion.dat_adhesion, grades.nom_gd
        ORDER BY adhesion.dat_adhesion DESC
    ");
    $stmtFilleuls->execute([$id_ad]);
    $filleuls = $stmtFilleuls->fetchAll(PDO::FETCH_ASSOC);

    // =========================
    // COMMISSION DE PARRAINAGE (ESTIMATION)
    // =========================
    // Taux confirmé par l'utilisateur : 1% par défaut (voir
    // config/commission.php), appliqué à CHAQUE paiement réussi d'un
    // filleul (total_paye ci-dessus inclut déjà l'adhésion initiale et
    // les renouvellements). Calcul fait en PHP, pas en SQL, pour garder
    // le taux à un seul endroit (config/commission.php) plutôt que de le
    // dupliquer dans une requête. Purement estimatif et affiché à la
    // volée : rien n'est stocké en base, aucun versement réel n'est
    // déclenché.
    require_once __DIR__ . '/../config/commission.php';

    $totalCommissionsEstimees = 0.0;

    foreach ($filleuls as &$filleul) {
        $commission = (float) $filleul['total_paye'] * (TAUX_COMMISSION_PARRAINAGE / 100);
        $filleul['commission_estimee'] = $commission;
        $totalCommissionsEstimees += $commission;
    }
    unset($filleul);

} else {
    // ⚠️ CORRECTIF (audit) : il manquait un exit; après ce header() — le
    // script continuait à s'exécuter (et pages/esp_membre.php à essayer
    // d'utiliser $infoAd, jamais définie dans ce cas), gaspillant du temps
    // serveur et générant des avertissements PHP inutiles avant que la
    // redirection ne prenne effet côté navigateur.
    header("Location:?pages=login");
    exit;
}
?>