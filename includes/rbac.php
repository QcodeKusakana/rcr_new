<?php
/**
 * RBAC : les permissions sont toujours vérifiées CÔTÉ SERVEUR (cacher un bouton ne suffit pas).
 *
 * Tables : roles, permissions, role_permissions ; admin.id_role relie un admin à un rôle.
 * Transition : tant qu'un admin existant n'a pas d'id_role, son ancien champ `niveau`
 * est converti en rôle (7 = administrateur principal, voir NIVEAU_VERS_ROLE).
 *
 * Usage en tête d'une page/action admin :
 *   require_once __DIR__ . '/../includes/rbac.php';
 *   require_permission($bdd, 'tarifs.gerer');
 */
require_once __DIR__ . '/audit.php';

const NIVEAU_VERS_ROLE = [
    7 => 'admin_principal',
    6 => 'resp_numerique', 91 => 'resp_numerique',
    5 => 'gest_effectifs', 92 => 'gest_effectifs',
    4 => 'resp_publications', 93 => 'resp_publications',
    3 => 'resp_publications', 94 => 'resp_publications',
];

if (!function_exists('rbac_permissions')) {
    /** @return string[] codes de permissions de l'admin connecté (mis en cache en session 10 min). */
    function rbac_permissions(PDO $bdd): array
    {
        if (empty($_SESSION['id_adm'])) {
            return [];
        }
        $cache = $_SESSION['rbac_cache'] ?? null;
        if (is_array($cache) && $cache['id'] === (int) $_SESSION['id_adm'] && (time() - $cache['t']) < 600) {
            return $cache['p'];
        }
        $s = $bdd->prepare('SELECT id_role, niveau FROM admin WHERE id_adm = ?');
        $s->execute([(int) $_SESSION['id_adm']]);
        $adm = $s->fetch(PDO::FETCH_ASSOC);
        if (!$adm) {
            return [];
        }
        $idRole = (int) $adm['id_role'];
        if ($idRole === 0 && isset(NIVEAU_VERS_ROLE[(int) $adm['niveau']])) {
            $r = $bdd->prepare('SELECT id_role FROM roles WHERE code = ?');
            $r->execute([NIVEAU_VERS_ROLE[(int) $adm['niveau']]]);
            $idRole = (int) $r->fetchColumn();
        }
        $perms = [];
        if ($idRole > 0) {
            $p = $bdd->prepare('SELECT p.code FROM role_permissions rp JOIN permissions p ON p.id_perm = rp.id_perm WHERE rp.id_role = ?');
            $p->execute([$idRole]);
            $perms = $p->fetchAll(PDO::FETCH_COLUMN);
        }
        $_SESSION['rbac_cache'] = ['id' => (int) $_SESSION['id_adm'], 't' => time(), 'p' => $perms];
        return $perms;
    }
}

if (!function_exists('has_permission')) {
    function has_permission(PDO $bdd, string $code): bool
    {
        return in_array($code, rbac_permissions($bdd), true);
    }
}

if (!function_exists('require_permission')) {
    /** Bloque l'accès (403) si la permission manque ; redirige vers la connexion si non connecté. */
    function require_permission(PDO $bdd, string $code): void
    {
        if (empty($_SESSION['id_adm'])) {
            header('Location: ' . (defined('ADMIN_LOGIN_URL') ? ADMIN_LOGIN_URL : 'index.php'));
            exit;
        }
        if (!has_permission($bdd, $code)) {
            audit_log($bdd, 'acces.refuse', 'permission', $code, $_SERVER['REQUEST_URI'] ?? '');
            http_response_code(403);
            exit('Accès refusé.');
        }
    }
}

const ROLE_VERS_NIVEAU = ['admin_principal' => 7, 'resp_numerique' => 6, 'gest_effectifs' => 5, 'resp_publications' => 4];

if (!function_exists('rbac_role_code')) {
    /** Code du rôle de l'admin connecté (admin_principal, …) ou null. */
    function rbac_role_code(PDO $bdd): ?string
    {
        if (empty($_SESSION['id_adm'])) {
            return null;
        }
        $s = $bdd->prepare('SELECT r.code, a.niveau FROM admin a LEFT JOIN roles r ON r.id_role = a.id_role WHERE a.id_adm = ?');
        $s->execute([(int) $_SESSION['id_adm']]);
        $r = $s->fetch(PDO::FETCH_ASSOC);
        if (!$r) {
            return null;
        }
        return $r['code'] ?: (NIVEAU_VERS_ROLE[(int) $r['niveau']] ?? null);
    }
}

if (!function_exists('rbac_flush')) {
    /** À appeler quand le rôle d'un admin change, ou à la déconnexion. */
    function rbac_flush(): void
    {
        unset($_SESSION['rbac_cache']);
    }
}
