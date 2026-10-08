<?php
/**
 * Outil de test FlexPay (CLI uniquement) — ne contient et n'affiche AUCUN secret.
 *
 *   php tools/flexpay_test.php config                 -> configuration présente ? (jeton : oui/non, longueur seulement)
 *   php tools/flexpay_test.php check ORDER_NUMBER     -> interroge /check/ORDER_NUMBER : connexion, HTTP, JSON, statut, durée
 *   php tools/flexpay_test.php reverify ORDER_NUMBER  -> comme check + applique les règles RCR (finalise si FlexPay confirme)
 *   php tools/flexpay_test.php init-real TEL MONTANT DEVISE --yes-real
 *        -> crée une VRAIE demande Mobile Money (argent réel !). Refusé sans --yes-real. Aucune ligne RCR n'est créée.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Accès interdit.'); }
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/payment_helpers.php';

$cmd = $argv[1] ?? 'config';
$out = fn(string $k, $v) => printf("%-22s %s\n", $k, is_bool($v) ? ($v ? 'OUI' : 'NON') : $v);

if ($cmd === 'config') {
    $out('Jeton configuré', FLEXPAY_TOKEN !== '');
    $out('Longueur du jeton', strlen(FLEXPAY_TOKEN) . ' caractères');
    $out('Code marchand', FLEXPAY_MERCHANT !== '' ? FLEXPAY_MERCHANT : '(vide)');
    $out('URL Mobile Money', FLEXPAY_MOBILE_URL);
    $out('URL Carte', FLEXPAY_CARD_URL);
    $out('URL Check', FLEXPAY_CHECK_URL);
    $out('URL Callback', FLEXPAY_CALLBACK_ENDPOINT);
    $out('Extension curl', function_exists('curl_init'));
    exit(0);
}
if (in_array($cmd, ['check', 'reverify'], true)) {
    $order = (string) ($argv[2] ?? '');
    $t0 = microtime(true);
    $v = verifyFlexPayTransaction($order);
    $out('Durée', round((microtime(true) - $t0) * 1000) . ' ms');
    $out('État', $v['state']);
    $out('HTTP', $v['http']);
    $out('Statut FlexPay brut', $v['flexpay_status'] ?? '—');
    $out('Montant', ($v['amount'] ?? '—') . ' ' . ($v['currency'] ?? ''));
    $out('Message', $v['message'] ?: '—');
    $out('Erreur', $v['error'] ?: '—');
    if ($cmd === 'reverify') {
        $found = payment_find_by_order($bdd, $order);
        if (!$found) { echo "Aucune transaction RCR avec cet ORDER_NUMBER.\n"; exit(1); }
        $res = payment_verify_and_confirm($bdd, $found);
        $out('Résultat RCR', $res ?? 'inchangé');
    }
    exit(0);
}
if ($cmd === 'init-real') {
    if (!in_array('--yes-real', $argv, true)) { fwrite(STDERR, "Refusé : cette commande déclenche un VRAI paiement. Ajoutez --yes-real pour confirmer.\n"); exit(2); }
    [$tel, $montant, $devise] = [$argv[2] ?? '', (float) ($argv[3] ?? 0), strtoupper($argv[4] ?? 'USD')];
    $ref = 'RCR-T-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(5)));
    $r = flexpay_request_mobile($ref, $tel, $montant, $devise);
    $out('Référence de test', $ref);
    $out('Succès init', $r['ok']);
    $out('orderNumber', $r['orderNumber'] ?? '—');
    $out('Message', $r['message']);
    exit($r['ok'] ? 0 : 1);
}
fwrite(STDERR, "Commande inconnue. Voir l'en-tête du fichier.\n");
exit(2);
