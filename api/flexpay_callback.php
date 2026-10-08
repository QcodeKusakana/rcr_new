<?php
/**
 * Callback FlexPay (POST JSON) :
 *   {"code":"0","reference":"...","provider_reference":"...","orderNumber":"..."}
 *
 * SÉCURITÉ : FlexPay ne signe pas ce message. Le contenu reçu n'est donc qu'un
 * DÉCLENCHEUR : on retrouve la transaction par sa référence, puis on demande
 * l'état réel à FlexPay (API check) et on contrôle référence + montant + devise.
 * Un faux callback ne peut donc rien valider.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/payment_helpers.php';

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
if (is_dir(__DIR__ . '/../storage/logs') && is_writable(__DIR__ . '/../storage/logs')) {
    ini_set('error_log', __DIR__ . '/../storage/logs/php-error.log');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method']);
    exit;
}

$raw = file_get_contents('php://input', false, null, 0, 20000);
$in  = json_decode((string) $raw, true);
if (!is_array($in) || empty($in['reference'])) {
    http_response_code(400);
    echo json_encode(['error' => 'payload']);
    exit;
}

try {
    $bdd = rcr_db_connect();
    $bdd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $reference = substr(trim((string) $in['reference']), 0, 100);
    $found = payment_find_by_reference($bdd, $reference);
    if (!$found && !empty($in['orderNumber'])) {
        $found = payment_find_by_order($bdd, (string) $in['orderNumber']); // repli : référence altérée côté FlexPay
    }

    if (!$found) {
        payment_log($bdd, 'adhesion', null, $reference, 'callback_unknown_reference', null, null, $raw);
        http_response_code(404);
        echo json_encode(['received' => false]);
        exit;
    }

    payment_log($bdd, $found['row']['type_transaction'] ?? 'don', (int) $found['row']['id'], $reference, 'callback_received', $found['row']['status'], null, $raw);

    payment_track($bdd, $found, ['callback' => true]);

    // Idempotence : une transaction déjà payée n'est jamais retraitée (FlexPay peut renvoyer le même callback).
    if ($found['row']['status'] === 'paid') {
        echo json_encode(['received' => true, 'result' => 'already_paid']);
        exit;
    }

    // Un orderNumber différent de celui obtenu à l'initialisation est suspect : journalisé, jamais pris en compte.
    if (!empty($found['row']['order_number']) && !empty($in['orderNumber']) && (string) $in['orderNumber'] !== (string) $found['row']['order_number']) {
        payment_log($bdd, $found['row']['type_transaction'] ?? 'don', (int) $found['row']['id'], $reference, 'callback_order_mismatch', $found['row']['status'], null, $raw);
    }

    // Premier contact : on retient l'orderNumber s'il manquait (jamais d'écrasement)
    if (empty($found['row']['order_number']) && !empty($in['orderNumber']) && preg_match('/^[A-Za-z0-9]{10,60}$/', (string) $in['orderNumber'])) {
        payment_set_order_number($bdd, $found['type'] === 'don' ? 'don' : 'payment', (int) $found['row']['id'], (string) $in['orderNumber']);
        $found = payment_find_by_reference($bdd, $reference);
    }

    if (!empty($in['provider_reference'])) {
        $t = $found['table'];
        $pk = $t === 'dons' ? 'id_don' : 'id';
        $bdd->prepare("UPDATE `{$t}` SET provider_reference = ? WHERE `{$pk}` = ? AND provider_reference IS NULL")
            ->execute([substr((string) $in['provider_reference'], 0, 80), (int) $found['row']['id']]);
    }

    $result = payment_verify_and_confirm($bdd, $found);

    // 200 : reçu (FlexPay ne relance pas). Le code du callback n'est qu'un déclencheur : la décision vient
    // exclusivement du check serveur. Si celui-ci est impossible, le polling de adhere/check_payment.php et
    // la tâche planifiée (tools/cron.php --paiements) reprennent la main.
    echo json_encode(['received' => true, 'result' => $result ?? 'unchanged']);
} catch (Throwable $e) {
    error_log('[flexpay_callback] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'server']);
}
