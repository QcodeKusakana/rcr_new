<?php
/**
 * Barre latérale de l'administration (bureau : fixe ; mobile : panneau coulissant).
 * Les entrées sont filtrées par permission (RBAC). Masquer un lien ne protège rien en soi :
 * chaque page vérifie aussi sa permission côté serveur (includes/admin_guard.php).
 */
if (empty($_SESSION['id_adm'])) {
    return;
}

$pageCourante = (string) ($page ?? ($_GET['pages'] ?? 'dashboard'));

// [page, libellé, icône, permission]
$adminMenu = [
    'Pilotage' => [
        ['dashboard', 'Tableau de bord', 'bi-speedometer2', 'dashboard.voir'],
        ['statistique', 'Statistiques', 'bi-bar-chart-line', 'dashboard.voir'],
    ],
    'Membres' => [
        ['membres', 'Membres', 'bi-people', 'membres.voir'],
        ['adhesions', 'Adhésions', 'bi-person-vcard', 'membres.voir'],
        ['demandes', 'Demandes', 'bi-inbox', 'membres.voir'],
        ['provinces', 'Provinces', 'bi-map', 'geo.gerer'],
        ['territoires', 'Territoires & secteurs', 'bi-geo-alt', 'geo.gerer'],
    ],
    'Finances' => [
        ['paiements', 'Paiements', 'bi-credit-card', 'paiements.voir'],
        ['cotisations', 'Cotisations', 'bi-cash-coin', 'paiements.voir'],
        ['dons', 'Soutiens / Dons', 'bi-heart', 'paiements.voir'],
        ['tarifs', 'Tarifs', 'bi-tags', 'tarifs.gerer'],
    ],
    'Site public' => [
        ['home', 'Publications', 'bi-newspaper', 'contenu.gerer'],
        ['activite', 'Actualités / activités', 'bi-calendar-event', 'contenu.gerer'],
        ['categorie', 'Catégories', 'bi-folder2', 'contenu.gerer'],
        ['equipe', 'Équipe', 'bi-person-badge', 'contenu.gerer'],
        ['partenaire', 'Partenaires', 'bi-building', 'contenu.gerer'],
        ['contenus', 'Textes, menus & réglages', 'bi-file-richtext', 'contenu.gerer'],
        ['messages', 'Messages de contact', 'bi-envelope', 'contenu.gerer'],
    ],
    'Système' => [
        ['administrateurs', 'Administrateurs & rôles', 'bi-shield-lock', 'admins.gerer'],
        ['valider_adm', 'Comptes à valider', 'bi-person-check', 'admins.gerer'],
        ['journaux', 'Journaux', 'bi-journal-text', 'logs.voir'],
    ],
];

// Badge « messages non lus » (requête légère, ignorée si la table n'existe pas encore)
$nbMessagesNonLus = 0;
if (has_permission($bdd, 'contenu.gerer')) {
    try {
        $nbMessagesNonLus = (int) $bdd->query("SELECT COUNT(*) FROM messages_contact WHERE statut = 'nouveau'")->fetchColumn();
    } catch (Throwable $e) {
        $nbMessagesNonLus = 0;
    }
}

$adminMenuHtml = function () use ($adminMenu, $bdd, $pageCourante, $nbMessagesNonLus): string {
    $h = '';
    foreach ($adminMenu as $groupe => $liens) {
        $items = '';
        foreach ($liens as [$cle, $lib, $ico, $perm]) {
            if (!has_permission($bdd, $perm)) {
                continue;
            }
            $actif = $pageCourante === $cle
                || ($cle === 'territoires' && $pageCourante === 'secteurs')
                || ($cle === 'home' && $pageCourante === 'modif_article')
                || ($cle === 'categorie' && in_array($pageCourante, ['scategorie', 'modify_cat'], true));
            $badge = ($cle === 'messages' && $nbMessagesNonLus > 0) ? '<span class="badge rounded-pill text-bg-danger ms-auto">' . $nbMessagesNonLus . '</span>' : '';
            $items .= '<li><a class="adm-nav-link' . ($actif ? ' active' : '') . '" href="?pages=' . e($cle) . '"' . ($actif ? ' aria-current="page"' : '') . '>'
                    . '<i class="bi ' . e($ico) . '" aria-hidden="true"></i><span>' . e($lib) . '</span>' . $badge . '</a></li>';
        }
        if ($items !== '') {
            $h .= '<div class="adm-nav-group"><div class="adm-nav-title">' . e($groupe) . '</div><ul>' . $items . '</ul></div>';
        }
    }
    return $h;
};
$menuRendu = $adminMenuHtml();
?>
<!-- Barre latérale (écrans larges) -->
<aside class="adm-sidebar d-none d-lg-flex" aria-label="Menu d'administration">
    <a class="adm-brand" href="?pages=dashboard">
        <img src="../media/lo/logorcr.png" alt="" width="38" height="38">
        <span><strong>RCR</strong><small>Administration</small></span>
    </a>
    <nav class="adm-nav"><?= $menuRendu ?></nav>
    <div class="adm-sidebar-foot">
        <a href="../index.php" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Voir le site</a>
    </div>
</aside>

<!-- Panneau coulissant (mobile / tablette) -->
<div class="offcanvas offcanvas-start adm-offcanvas" tabindex="-1" id="admMobileMenu" aria-labelledby="admMobileMenuTitre">
    <div class="offcanvas-header">
        <a class="adm-brand p-0" href="?pages=dashboard" id="admMobileMenuTitre">
            <img src="../media/lo/logorcr.png" alt="" width="34" height="34">
            <span><strong>RCR</strong><small>Administration</small></span>
        </a>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Fermer le menu"></button>
    </div>
    <div class="offcanvas-body p-0">
        <nav class="adm-nav"><?= $menuRendu ?></nav>
        <div class="adm-sidebar-foot"><a href="../index.php" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Voir le site</a></div>
    </div>
</div>
