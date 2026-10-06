<?php
/**
 * Point d'entrée unique de l'administration RCR.
 * Routage sur liste réelle des fichiers de pages/, contrôle d'accès centralisé (session + RBAC + CSRF,
 * voir includes/admin_guard.php), puis gabarit commun (barre latérale, barre supérieure, messages).
 */
include_once __DIR__ . '/functions/main_function.php';

$pagesDisponibles = array_map(fn($f) => basename($f, '.php'), glob(__DIR__ . '/pages/*.php') ?: []);
$demande = (string) ($_GET['pages'] ?? '');
$page = ($demande !== '' && preg_match('/^[a-z_]+$/', $demande) && in_array($demande, $pagesDisponibles, true)) ? $demande : 'dashboard';

$pages_accessibles_sans_connexion = ['login', 'register', 'deconnexion'];
if (!in_array($page, $pages_accessibles_sans_connexion, true) && !isset($_SESSION['id_adm'])) {
    header('Location: ?pages=login');
    exit;
}
// Un administrateur sans le droit « tableau de bord » arrive sur une page qu'il peut voir
if ($page === 'dashboard' && $demande === '' && isset($_SESSION['id_adm']) && !has_permission($bdd, 'dashboard.voir')) {
    $page = 'home';
}

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store, private');

admin_guard_start($bdd, $page);

if (is_file(__DIR__ . '/functions/' . $page . '.funct.php')) {
    include __DIR__ . '/functions/' . $page . '.funct.php';
}

$connecte = isset($_SESSION['id_adm']) && !in_array($page, $pages_accessibles_sans_connexion, true);
$titrePage = ADMIN_PAGE_TITRES[$page] ?? [
    'statistique' => 'Statistiques', 'categorie' => 'Catégories', 'scategorie' => 'Sous-catégories', 'modif_article' => 'Modifier une publication',
    'modify_cat' => 'Modifier une catégorie', 'equipe' => 'Équipe', 'partenaire' => 'Partenaires', 'valider_adm' => 'Comptes à valider',
    'login' => 'Connexion', 'register' => 'Demande de compte',
][$page] ?? 'Administration';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($titrePage) ?> · Administration RCR</title>
    <link rel="icon" href="../media/lo/logo1.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="assets/css/admin.css?v=5" rel="stylesheet">
</head>
<body class="adm-body<?= $connecte ? '' : ' adm-body-auth' ?>">

<?php if ($connecte): ?>
<?php include __DIR__ . '/menu/navbar.php'; ?>
<div class="adm-main">
    <header class="adm-topbar">
        <button class="btn adm-burger d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#admMobileMenu" aria-controls="admMobileMenu" aria-label="Ouvrir le menu">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="adm-title"><?= e($titrePage) ?></h1>
        <div class="ms-auto d-flex align-items-center gap-2">
            <a class="btn btn-sm btn-light d-none d-md-inline-flex align-items-center gap-1" href="../index.php" target="_blank" rel="noopener"><i class="bi bi-globe2"></i> Site</a>
            <div class="dropdown">
                <button class="btn btn-sm adm-user dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="adm-avatar"><?= e(mb_strtoupper(mb_substr((string) ($_SESSION['pseudo'] ?? 'A'), 0, 1))) ?></span>
                    <span class="d-none d-sm-inline"><?= e((string) ($_SESSION['pseudo'] ?? 'Administrateur')) ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><span class="dropdown-item-text small text-muted"><?= e((string) ($_SESSION['mail'] ?? '')) ?></span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="?pages=profil"><i class="bi bi-key me-1"></i> Mon compte / mot de passe</a></li>
                    <li><a class="dropdown-item text-danger" href="?pages=deconnexion"><i class="bi bi-box-arrow-right me-1"></i> Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </header>
    <main class="adm-content" id="contenu">
        <?= admin_flash_render() ?>
        <?php include __DIR__ . '/pages/' . $page . '.php'; ?>
    </main>
    <footer class="adm-footer">© <?= date('Y') ?> Rassemblement des Chrétiens Républicains · Administration</footer>
</div>
<?php else: ?>
    <?php include __DIR__ . '/pages/' . $page . '.php'; ?>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/admin.js?v=5"></script>
</body>
</html>
