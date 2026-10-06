<?php
/**
 * Socle de l'API mobile RCR (api/v1) : réponses JSON, lecture de la requête, authentification par jeton Bearer.
 *
 * Sécurité :
 *  - Jetons aléatoires (256 bits) ; seule l'empreinte SHA-256 est stockée (table api_tokens) : une fuite de la
 *    base ne donne aucun jeton utilisable. Expiration 60 jours (glissante), révocation à la déconnexion.
 *  - Aucun cookie / session PHP : pas de CSRF possible (le jeton n'est jamais envoyé automatiquement par le navigateur).
 *  - Limitation des essais de connexion par couple (adresse IP + identifiant) : plusieurs membres derrière la même
 *    adresse IP d'opérateur (CGNAT) ne se bloquent pas entre eux.
 *  - Montants TOUJOURS recalculés côté serveur ; un paiement n'est « paid » qu'après vérification FlexPay.
 */
define('RCR_NO_SESSION', true);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/login_attempts.php';
require_once __DIR__ . '/payment_helpers.php';

const API_TOKEN_JOURS = 60;

function api_send(array $data, int $http = 200): void
{
    http_response_code($http);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
    exit;
}

function api_ok(array $data = [], int $http = 200): void
{
    api_send(['ok' => true] + $data, $http);
}

/** Erreur normalisée : {ok:false, code:"...", message:"..."} — le code est stable (utilisé par l'application). */
function api_error(string $code, string $message, int $http = 400, array $extra = []): void
{
    api_send(['ok' => false, 'code' => $code, 'message' => $message] + $extra, $http);
}

/** Corps de la requête : JSON (application/json) ou formulaire (multipart / urlencoded). */
function api_input(): array
{
    static $in = null;
    if ($in !== null) {
        return $in;
    }
    $ct = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
    if (strpos($ct, 'application/json') === 0) {
        $d = json_decode((string) file_get_contents('php://input'), true);
        $in = is_array($d) ? $d : [];
    } else {
        $in = $_POST;
    }
    return $in;
}

function api_str(string $k, int $max = 255): string
{
    $v = api_input()[$k] ?? '';
    return is_scalar($v) ? mb_substr(trim((string) $v), 0, $max) : '';
}

function api_base_url(): string
{
    return rtrim((string) SITE_URL, '/');
}

/** Table des jetons présente ? (sinon : migration phase6 à lancer) */
function api_table_ok(PDO $bdd): bool
{
    static $ok = null;
    if ($ok === null) {
        try {
            $bdd->query('SELECT 1 FROM api_tokens LIMIT 1');
            $ok = true;
        } catch (Throwable $e) {
            $ok = false;
        }
    }
    return $ok;
}

function api_token_create(PDO $bdd, int $idAd, string $appareil): string
{
    $token = bin2hex(random_bytes(32));
    $bdd->prepare('INSERT INTO api_tokens (id_ad, token_hash, appareil, ip, expire_le) VALUES (?,?,?,?, DATE_ADD(NOW(), INTERVAL ' . API_TOKEN_JOURS . ' DAY))')
        ->execute([$idAd, hash('sha256', $token), mb_substr($appareil, 0, 120), mb_substr(login_attempts_client_ip(), 0, 45)]);
    return $token;
}

function api_bearer(): ?string
{
    $h = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if ($h === '' && function_exists('getallheaders')) {
        foreach (getallheaders() as $k => $v) {
            if (strtolower($k) === 'authorization') { $h = $v; }
        }
    }
    return preg_match('/^Bearer\s+([a-f0-9]{64})$/i', trim((string) $h), $m) ? strtolower($m[1]) : null;
}

/** Membre authentifié ou null (jeton absent / invalide / expiré / compte suspendu). */
function api_member(PDO $bdd): ?array
{
    static $cache = false;
    if ($cache !== false) {
        return $cache;
    }
    $cache = null;
    $t = api_bearer();
    if ($t === null || !api_table_ok($bdd)) {
        return null;
    }
    $s = $bdd->prepare('SELECT a.*, k.id_tok FROM api_tokens k JOIN adhesion a ON a.id_ad = k.id_ad
                        WHERE k.token_hash = ? AND k.revoque = 0 AND k.expire_le > NOW() LIMIT 1');
    $s->execute([hash('sha256', $t)]);
    $m = $s->fetch(PDO::FETCH_ASSOC) ?: null;
    if (!$m || $m['statut'] === 'suspendu') {
        return null;
    }
    // Prolongation glissante, au plus une fois par heure
    $bdd->prepare('UPDATE api_tokens SET derniere_utilisation = NOW(), expire_le = DATE_ADD(NOW(), INTERVAL ' . API_TOKEN_JOURS . ' DAY)
                   WHERE id_tok = ? AND (derniere_utilisation IS NULL OR derniere_utilisation < DATE_SUB(NOW(), INTERVAL 1 HOUR))')
        ->execute([(int) $m['id_tok']]);
    return $cache = $m;
}

function api_require_member(PDO $bdd): array
{
    $m = api_member($bdd);
    if (!$m) {
        api_error('non_authentifie', 'Session expirée ou invalide. Veuillez vous reconnecter.', 401);
    }
    return $m;
}

/** Clé secrète pour les liens de suivi des dons faits sans compte (générée une fois, config/api_secret.php). */
function api_secret(): string
{
    $f = __DIR__ . '/../config/api_secret.php';
    if (!is_file($f)) {
        @file_put_contents($f, "<?php\nreturn '" . bin2hex(random_bytes(32)) . "';\n", LOCK_EX);
        @chmod($f, 0640);
    }
    $s = is_file($f) ? include $f : null;
    return is_string($s) && $s !== '' ? $s : hash('sha256', (string) DB_NAME . (string) DB_USER . (string) DB_PASS);
}

function api_suivi_cle(string $reference): string
{
    return substr(hash_hmac('sha256', $reference, api_secret()), 0, 32);
}

function api_member_public(PDO $bdd, array $m): array
{
    $s = $bdd->prepare('SELECT a.id_ad, a.codes, a.nom, a.postnom, a.prenom, a.mail, a.telephone, a.statut, a.date_echeance, a.dat_adhesion,
                               a.passeport, a.id_qt, a.grade AS id_gd, a.reglement AS id_cot, a.sexe, a.civilite,
                               q.designation AS categorie, g.nom_gd AS grade, c.nom_cot AS periode, p.nom_p AS province
                        FROM adhesion a
                        LEFT JOIN qualites q ON q.id_qt = a.id_qt LEFT JOIN grades g ON g.id_gd = a.grade
                        LEFT JOIN cotisation c ON c.id_cot = a.reglement LEFT JOIN provinces p ON p.id_p = a.province
                        WHERE a.id_ad = ?');
    $s->execute([(int) $m['id_ad']]);
    $r = $s->fetch(PDO::FETCH_ASSOC) ?: [];
    $statut = (string) ($r['statut'] ?? '');
    $ech = $r['date_echeance'] ?? null;
    if ($statut === 'actif' && $ech && $ech < date('Y-m-d')) {
        $statut = 'expire'; // l'échéance est dépassée même si la tâche planifiée n'a pas encore tourné
    }
    $jours = $ech ? (int) floor((strtotime($ech) - strtotime(date('Y-m-d'))) / 86400) : null;
    $tarif = $r ? tarifs_calculer($bdd, (int) $r['id_qt'], (int) $r['id_gd'], (int) $r['id_cot']) : null;
    $paye = $bdd->prepare("SELECT COUNT(*) FROM payments WHERE id_ad = ? AND status = 'paid'");
    $paye->execute([(int) $m['id_ad']]);
    return [
        'id'              => (int) $r['id_ad'],
        'code'            => $r['codes'],
        'nom'             => $r['nom'], 'postnom' => $r['postnom'], 'prenom' => $r['prenom'],
        'email'           => $r['mail'], 'telephone' => $r['telephone'],
        'categorie'       => $r['categorie'], 'grade' => $r['grade'], 'periode' => $r['periode'], 'province' => $r['province'],
        'statut'          => $statut,
        'date_echeance'   => $ech,
        'jours_restants'  => $jours,
        'a_jour'          => $statut === 'actif' && $jours !== null && $jours >= 0,
        'premier_paiement_fait' => (int) $paye->fetchColumn() > 0,
        'photo_url'       => !empty($r['passeport']) ? api_base_url() . '/media/passeport/' . rawurlencode($r['passeport']) : null,
        'tarif_actuel'    => $tarif ? ['montant' => $tarif['montant'], 'devise' => $tarif['devise'], 'mois' => $tarif['mois'], 'id_cot' => $tarif['id_cot']] : null,
    ];
}
