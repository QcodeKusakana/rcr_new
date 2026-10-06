<?php
/**
 * Appelé par adhere/loading.php quand le délai d'attente initial (60s)
 * côté navigateur est écoulé sans confirmation reçue par le polling.
 *
 * 🔧 CORRECTIF (cause racine du bug "paiement débité mais affiché comme
 * refusé", volet n°2 — le principal) : ce fichier marquait auparavant le
 * paiement "cancelled" de façon DÉFINITIVE (payment_expire_pending()
 * écrivait alors ce statut). Le webhook FlexPay, qui n'écrit jamais
 * par-dessus un statut déjà final, ignorait alors silencieusement toute
 * confirmation arrivant après ces 60 secondes — alors que 60s est souvent
 * trop court pour une confirmation Mobile Money en RDC (1 à 3 minutes ne
 * sont pas rares). Le client payait réellement, FlexPay débitait, mais
 * l'adhésion restait bloquée sur "paiement refusé".
 *
 * Nouveau comportement, en deux temps :
 * 1. On tente d'abord une vérification active auprès de FlexPay
 *    (payment_flexpay_check()) : si FlexPay a déjà la réponse à cet
 *    instant précis, on l'applique immédiatement (paid ou failed), sans
 *    jamais passer par un statut intermédiaire inutile.
 * 2. Si FlexPay ne répond rien d'exploitable, le paiement passe à
 *    "expired" (et non plus "cancelled") — un statut qui reste
 *    ouvert à une confirmation tardive (webhook ou nouvelle vérification
 *    active depuis adhere/check_payment.php). Voir includes/payment_helpers.php
 *    et migrations/004_add_expired_status.sql.
 *
 * Générique aux deux flux de paiement du projet :
 * - ?type=adhesion (valeur par défaut, rétrocompatible) → table `payments` ;
 * - ?type=don → table `dons`.
 */

require_once __DIR__ . '/main_function.php';
require_once __DIR__ . '/../includes/payment_helpers.php';
require_once __DIR__ . '/../includes/flexpay_client.php';

header('Content-Type: application/json');

$id = (int) ($_GET['id'] ?? 0);
$type = ($_GET['type'] ?? 'adhesion') === 'don' ? 'don' : 'adhesion';

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Identifiant invalide']);
    exit;
}

// 1. Dernière chance de résolution immédiate auprès de FlexPay.
$resolved = payment_flexpay_check($bdd, $type, $id);

if ($resolved !== null) {
    echo json_encode([
        'expired' => false,
        'status'  => $resolved,
    ]);
    exit;
}

// 2. Sinon, on passe en vérification prolongée (jamais "cancelled" ici).
$ok  = payment_expire_pending($bdd, $type, $id);
$row = payment_get_row($bdd, $type, $id);

echo json_encode([
    'expired' => $ok,
    'status'  => $row['status'] ?? 'expired',
]);
