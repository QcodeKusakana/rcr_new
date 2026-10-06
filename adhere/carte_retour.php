<?php
/**
 * Retour du client après paiement par carte (approve / cancel / decline).
 * La référence (aléatoire) identifie la transaction ; AUCUN statut n'est cru sur parole :
 *  - approve  : on renvoie vers loading.php qui vérifie auprès de FlexPay ;
 *  - cancel   : on vérifie d'abord (au cas où), puis 'cancelled' si rien n'a été payé ;
 *  - decline  : idem avec 'failed'.
 */
require_once __DIR__ . '/main_function.php';
require_once __DIR__ . '/../includes/payment_helpers.php';

$ref = substr(trim((string) ($_GET['ref'] ?? '')), 0, 100);
$r   = $_GET['r'] ?? '';
$found = preg_match('/^RCR-[ACD]-\d{6}-[A-F0-9]{10}$/', $ref) ? payment_find_by_reference($bdd, $ref) : null;
if (!$found) {
    http_response_code(404);
    die('Transaction introuvable.');
}
$type = $found['type'] === 'don' ? 'don' : 'adhesion';
$id   = (int) $found['row']['id'];

$etat = payment_verify_and_confirm($bdd, $found);
if ($etat === 'paid') {
    header("Location: success.php?id=$id&type=$type");
    exit;
}
if ($r === 'approve') {
    header("Location: loading.php?id=$id&type=$type");
    exit;
}
payment_apply_status($bdd, $found, $r === 'cancel' ? 'cancelled' : 'failed', false);
header("Location: failed.php?id=$id&type=$type");
exit;
