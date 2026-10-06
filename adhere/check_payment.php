<?php
/**
 * Vérification du statut d'un paiement — appelé en polling depuis
 * adhere/loading.php pendant l'attente de la confirmation FlexPay.
 *
 * Générique aux deux flux de paiement du projet : ?type=don interroge
 * la table `dons`, sinon (par défaut, rétrocompatible) la table
 * `payments` (paiement d'adhésion).
 *
 * 🔧 ÉVOLUTION (lié au correctif du bug "paiement débité mais affiché
 * comme refusé") : pendant la phase de vérification prolongée qui suit
 * le délai initial de 60s (statut "expired_pending", voir
 * adhere/expire_payment.php et adhere/loading.php), ce endpoint tente
 * maintenant, en plus de la simple lecture, une vérification active
 * auprès de FlexPay via payment_flexpay_check() — en complément du
 * webhook, au cas où celui-ci n'arriverait jamais. Cet appel actif est
 * volontairement limité à cette phase (paramètre ?active=1, envoyé par
 * adhere/loading.php uniquement après les 60 premières secondes) pour ne
 * pas solliciter l'API FlexPay à chaque poll de 3s de la phase normale.
 */

require_once __DIR__ . '/main_function.php';
require_once __DIR__ . '/../includes/payment_helpers.php';

header('Content-Type: application/json');

$id   = (int) ($_GET['id'] ?? 0);
$type = ($_GET['type'] ?? 'adhesion') === 'don' ? 'don' : 'adhesion';

$row    = payment_get_row($bdd, $type, $id);
$status = $row['status'] ?? 'pending';

if (in_array($status, ['pending', 'processing', 'expired'], true)
    && (($_GET['active'] ?? '') === '1' || $status === 'processing' || $status === 'pending')
    && (time() - (int) ($_SESSION['chk_' . $type . $id] ?? 0)) >= 4) {
    $_SESSION['chk_' . $type . $id] = time(); // pas plus d'un appel FlexPay toutes les 4 s par transaction
    require_once __DIR__ . '/../includes/flexpay_client.php';

    $checked = payment_flexpay_check($bdd, $type, $id);

    if ($checked !== null) {
        $status = $checked;
    }
}

echo json_encode([
    'status' => $status,
]);
