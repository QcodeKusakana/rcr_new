<?php
/**
 * Garde centrale de l'administration (appelée par admin/index.php, avant tout affichage).
 *
 * 1. PERMISSIONS PAR PAGE (RBAC côté serveur) : chaque page admin exige une permission.
 *    Une page absente de la table est réservée à 'systeme.gerer' (refus par défaut).
 * 2. ACTIONS GET HÉRITÉES : les anciennes pages suppriment / valident via des liens
 *    (?supprimer=3, ?valid=5…). Un simple lien piégé suffisait à déclencher l'action (CSRF).
 *    Ces paramètres exigent désormais un jeton de session (_t) : la sortie HTML est filtrée
 *    pour l'ajouter automatiquement aux liens légitimes, et toute requête sans jeton valide
 *    est refusée AVANT l'exécution de la page.
 */
require_once __DIR__ . '/rbac.php';

const ADMIN_PAGES_PUBLIQUES = ['login', 'register', 'deconnexion'];

/** page => [permission pour consulter, permission pour modifier] */
const ADMIN_PAGE_PERMS = [
    // nouvelles pages
    'dashboard'      => ['dashboard.voir', 'dashboard.voir'],
    'membres'        => ['membres.voir', 'membres.gerer'],
    'tarifs'         => ['tarifs.gerer', 'tarifs.gerer'],
    'cotisations'    => ['paiements.voir', 'paiements.voir'],
    'dons'           => ['paiements.voir', 'paiements.voir'],
    'paiements'      => ['paiements.voir', 'paiements.valider'],
    'paiement_diagnostic' => ['paiements.voir', 'paiements.valider'],
    'contenus'       => ['contenu.gerer', 'contenu.gerer'],
    'administrateurs' => ['admins.gerer', 'admins.gerer'],
    'journaux'       => ['logs.voir', 'logs.voir'],
    'messages'       => ['contenu.gerer', 'contenu.gerer'],
    'profil'         => ['', ''], // « Mon compte » : tout administrateur connecté (ne concerne que SON compte)
    // pages historiques
    'home'           => ['contenu.gerer', 'contenu.gerer'],
    'activite'       => ['contenu.gerer', 'contenu.gerer'],
    'categorie'      => ['contenu.gerer', 'contenu.gerer'],
    'scategorie'     => ['contenu.gerer', 'contenu.gerer'],
    'modif_article'  => ['contenu.gerer', 'contenu.gerer'],
    'modify_cat'     => ['contenu.gerer', 'contenu.gerer'],
    'equipe'         => ['contenu.gerer', 'contenu.gerer'],
    'partenaire'     => ['contenu.gerer', 'contenu.gerer'],
    'adhesions'      => ['membres.voir', 'membres.gerer'],
    'demandes'       => ['membres.voir', 'membres.gerer'],
    'provinces'      => ['geo.gerer', 'geo.gerer'],
    'territoires'    => ['geo.gerer', 'geo.gerer'],
    'secteurs'       => ['geo.gerer', 'geo.gerer'],
    'statistique'    => ['dashboard.voir', 'dashboard.voir'],
    'valider_adm'    => ['admins.gerer', 'admins.gerer'],
];

const ADMIN_PAGE_TITRES = [
    'dashboard' => 'Tableau de bord', 'membres' => 'Membres', 'tarifs' => 'Tarifs de cotisation',
    'cotisations' => 'Cotisations', 'dons' => 'Soutiens / Dons', 'paiements' => 'Paiements',
    'contenus' => 'Contenus du site', 'administrateurs' => 'Administrateurs et rôles', 'journaux' => 'Journaux', 'messages' => 'Messages de contact', 'profil' => 'Mon compte',
    'home' => 'Publications', 'activite' => 'Activités', 'adhesions' => 'Adhésions', 'demandes' => 'Demandes',
    'provinces' => 'Provinces', 'territoires' => 'Territoires', 'secteurs' => 'Secteurs',
];

/** Paramètres GET qui modifient des données dans les pages historiques. */
const ADMIN_GET_MUTATIONS = '(?:supprimer|suppr|confirmer|deconfirmer|valid|blq|sup|delttr_id|nomme\d+|retirer\d+)';

if (!function_exists('admin_get_token')) {
    function admin_get_token(): string
    {
        if (empty($_SESSION['admin_get_token'])) {
            $_SESSION['admin_get_token'] = bin2hex(random_bytes(24));
        }
        return $_SESSION['admin_get_token'];
    }
}

if (!function_exists('admin_request_is_mutation')) {
    function admin_request_is_mutation(string $page): bool
    {
        foreach (array_keys($_GET) as $k) {
            if (preg_match('/^' . ADMIN_GET_MUTATIONS . '$/', (string) $k)) {
                return true;
            }
        }
        return $page === 'home' && isset($_GET['id']); // home.funct.php supprime une activité via ?id=
    }
}

if (!function_exists('admin_forbid')) {
    function admin_forbid(PDO $bdd, string $why, string $detail = ''): void
    {
        audit_log($bdd, 'acces.refuse', 'admin', $why, $detail . ' ' . ($_SERVER['REQUEST_URI'] ?? ''));
        http_response_code(403);
        exit('<!DOCTYPE html><meta charset="utf-8"><div style="font-family:sans-serif;max-width:560px;margin:80px auto;text-align:center"><h2>Accès refusé</h2><p>Vous n\'avez pas l\'autorisation d\'effectuer cette action.</p><p><a href="?pages=dashboard">Retour au tableau de bord</a></p></div>');
    }
}

if (!function_exists('admin_guard_start')) {
    function admin_guard_start(PDO $bdd, string $page): void
    {
        if (in_array($page, ADMIN_PAGES_PUBLIQUES, true)) {
            return;
        }
        if (empty($_SESSION['id_adm'])) {
            return; // admin/index.php redirige déjà vers la connexion
        }
        // Expiration de session par inactivité (30 min)
        $now = time();
        if (isset($_SESSION['adm_last']) && ($now - (int) $_SESSION['adm_last']) > 1800) {
            audit_log($bdd, 'admin.session_expiree', 'admin', (string) $_SESSION['id_adm']);
            $_SESSION = [];
            session_destroy();
            header('Location: ?pages=login');
            exit;
        }
        $_SESSION['adm_last'] = $now;

        [$voir, $modifier] = ADMIN_PAGE_PERMS[$page] ?? ['systeme.gerer', 'systeme.gerer'];
        if ($voir !== '' && !has_permission($bdd, $voir)) {
            admin_forbid($bdd, 'page', $page);
        }
        // Tout envoi de formulaire (POST) de l'administration exige le jeton CSRF de la session,
        // y compris sur les pages historiques : un site tiers ne peut pas faire agir un admin connecté.
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && !csrf_verify()) {
            admin_forbid($bdd, 'csrf_post', $page);
        }
        if (admin_request_is_mutation($page)) {
            $t = (string) ($_GET['_t'] ?? '');
            if ($t === '' || !hash_equals(admin_get_token(), $t)) {
                admin_forbid($bdd, 'jeton_get', $page);
            }
            if (!has_permission($bdd, $modifier)) {
                admin_forbid($bdd, 'modification', $page);
            }
        }
        // Ajout automatique du jeton aux liens d'action de la sortie HTML
        $token = admin_get_token();
        $isHome = ($page === 'home');
        ob_start(function (string $html) use ($token, $isHome): string {
            $re = '/href="([^"]*[?&;]' . ADMIN_GET_MUTATIONS . '=[^"]*)"/';
            $html = preg_replace_callback($re, function ($m) use ($token) {
                return strpos($m[1], '_t=') === false ? 'href="' . $m[1] . '&amp;_t=' . $token . '"' : $m[0];
            }, $html);
            if ($isHome) {
                $html = preg_replace_callback('/href="([^"]*[?&;]id=\d+[^"]*)"/', function ($m) use ($token) {
                    return (strpos($m[1], '_t=') === false && strpos($m[1], 'pages=') === false)
                        ? 'href="' . $m[1] . '&amp;_t=' . $token . '"' : $m[0];
                }, $html);
            }
            // Jeton CSRF ajouté automatiquement à chaque formulaire POST (pages historiques comprises)
            $champ = csrf_field();
            $html = preg_replace_callback('/<form\b[^>]*\bmethod\s*=\s*["\']?post["\']?[^>]*>/i', function ($m) use ($champ) {
                return $m[0] . $champ;
            }, $html);
            return $html;
        });
    }
}

if (!function_exists('admin_post_guard')) {
    /** Pour les nouvelles pages : exige POST + jeton CSRF + permission. Retourne false si ce n'est pas un POST. */
    function admin_post_guard(PDO $bdd, string $permission): bool
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            return false;
        }
        if (!csrf_verify()) {
            admin_forbid($bdd, 'csrf_post');
        }
        if (!has_permission($bdd, $permission)) {
            admin_forbid($bdd, 'post_permission', $permission);
        }
        return true;
    }
}

if (!function_exists('admin_flash')) {
    function admin_flash(?string $msg = null, string $type = 'success'): ?array
    {
        if ($msg !== null) {
            $_SESSION['admin_flash'] = ['m' => $msg, 't' => $type];
            return null;
        }
        $f = $_SESSION['admin_flash'] ?? null;
        unset($_SESSION['admin_flash']);
        return $f;
    }
}

if (!function_exists('admin_flash_render')) {
    function admin_flash_render(): string
    {
        $f = admin_flash();
        return $f ? '<div class="alert alert-' . e($f['t']) . ' m-3">' . e($f['m']) . '</div>' : '';
    }
}

if (!function_exists('admin_redirect')) {
    function admin_redirect(string $query): void
    {
        header('Location: ?' . $query);
        exit;
    }
}

if (!function_exists('admin_paginate')) {
    /** @return array{page:int,pages:int,offset:int,per:int} */
    function admin_paginate(int $total, int $per = 25): array
    {
        $pages = max(1, (int) ceil($total / $per));
        $page  = min($pages, max(1, (int) ($_GET['p'] ?? 1)));
        return ['page' => $page, 'pages' => $pages, 'offset' => ($page - 1) * $per, 'per' => $per];
    }
}

if (!function_exists('admin_pagination_html')) {
    function admin_pagination_html(array $pg, array $keep): string
    {
        if ($pg['pages'] <= 1) {
            return '';
        }
        $h = '<nav class="p-3"><ul class="pagination pagination-sm mb-0 flex-wrap">';
        for ($i = 1; $i <= $pg['pages']; $i++) {
            if ($i !== 1 && $i !== $pg['pages'] && abs($i - $pg['page']) > 2) {
                if (abs($i - $pg['page']) === 3) { $h .= '<li class="page-item disabled"><span class="page-link">…</span></li>'; }
                continue;
            }
            $q = http_build_query($keep + ['p' => $i]);
            $h .= '<li class="page-item' . ($i === $pg['page'] ? ' active' : '') . '"><a class="page-link" href="?' . e($q) . '">' . $i . '</a></li>';
        }
        return $h . '</ul></nav>';
    }
}

if (!function_exists('admin_csv_send')) {
    /** Export CSV (UTF-8 avec BOM pour Excel). Termine le script. */
    function admin_csv_send(string $nom, array $entetes, array $lignes): void
    {
        while (ob_get_level() > 0) { ob_end_clean(); }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $nom) . '"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, $entetes, ';', '"', '\\');
        foreach ($lignes as $l) {
            // neutralise l'injection de formules dans Excel
            fputcsv($out, array_map(fn($v) => (is_string($v) && $v !== '' && strpos('=+-@', $v[0]) !== false) ? "'" . $v : $v, $l), ';', '"', '\\');
        }
        fclose($out);
        exit;
    }
}
