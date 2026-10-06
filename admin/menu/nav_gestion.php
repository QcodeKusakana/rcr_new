<?php
/**
 * Entrées de menu de la gestion (Phase 3), filtrées par permission côté serveur.
 * Inclus dans admin/menu/navbar.php (menu mobile et menu bureau). Le simple fait de masquer un lien ne protège rien :
 * chaque page vérifie elle-même sa permission (voir includes/admin_guard.php).
 */
$navGestion = [
    ['dashboard',       'Tableau de bord',     'bi-speedometer2',     'dashboard.voir'],
    ['membres',         'Membres',             'bi-people-fill',      'membres.voir'],
    ['cotisations',     'Cotisations',         'bi-cash-coin',        'paiements.voir'],
    ['dons',            'Soutiens / Dons',     'bi-heart-fill',       'paiements.voir'],
    ['paiements',       'Paiements',           'bi-credit-card-fill', 'paiements.voir'],
    ['tarifs',          'Tarifs',              'bi-tags-fill',        'tarifs.gerer'],
    ['contenus',        'Contenus du site',    'bi-file-richtext',    'contenu.gerer'],
    ['administrateurs', 'Administrateurs',     'bi-shield-lock-fill', 'admins.gerer'],
    ['journaux',        'Journaux',            'bi-journal-text',     'logs.voir'],
];
$pageCourante = $_GET['pages'] ?? 'home';
foreach ($navGestion as [$cle, $libelle, $icone, $perm]):
    if (!isset($bdd) || !has_permission($bdd, $perm)) { continue; } ?>
            <li class="nav-item">
                <a href="?pages=<?= $cle ?>" class="nav-link <?= $pageCourante === $cle ? 'active' : '' ?>">
                    <i class="bi <?= $icone ?>"></i>
                    <?= htmlspecialchars($libelle, ENT_QUOTES, 'UTF-8') ?>
                </a>
            </li>
<?php endforeach; ?>
