<?php
/**
 * Initialisation du paiement d'adhésion / de cotisation (inclus par paiement.php).
 *
 * - Le MONTANT est recalculé ici depuis le barème (tarifs.php) : jamais lu du navigateur.
 * - Deux canaux : Mobile Money (push sur le téléphone) ou carte bancaire (redirection FlexPay).
 * - Le paiement n'est "paid" qu'après vérification serveur auprès de FlexPay (payment_helpers.php).
 * - 1er paiement = type 'adhesion', suivants = 'cotisation' (renouvellement).
 * Variables exposées à paiement.php : $user, $tarif, $montant, $message.
 */
include_once('main_function.php');
require_once __DIR__ . '/../includes/payment_helpers.php';
require_once __DIR__ . '/../includes/tarifs.php';

$token = trim((string) ($_GET['token'] ?? ($_SESSION['payment_token'] ?? '')));
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    http_response_code(400);
    die('Lien de paiement invalide.');
}
$_SESSION['payment_token'] = $token;

$stmt = $bdd->prepare('SELECT a.*, q.designation, g.nom_gd, c.nom_cot
                       FROM adhesion a
                       JOIN qualites q ON q.id_qt = a.id_qt
                       JOIN grades g ON g.id_gd = a.grade
                       LEFT JOIN cotisation c ON c.id_cot = a.reglement
                       WHERE a.payment_token = ? LIMIT 1');
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    http_response_code(404);
    die('Dossier introuvable.');
}

$tarif = tarifs_calculer($bdd, (int) $user['id_qt'], (int) $user['grade'], (int) $user['reglement']);
$message = '';
if (!$tarif) {
    $message = "<div class='alert alert-danger'>Ce tarif n'est plus disponible. Merci de contacter le secrétariat du RCR.</div>";
    $montant = 0.0;
} else {
    $montant = $tarif['montant'];
}

$deja = $bdd->prepare("SELECT COUNT(*) FROM payments WHERE id_ad = ? AND status = 'paid'");
$deja->execute([(int) $user['id_ad']]);
$typeTransaction = ((int) $deja->fetchColumn() > 0) ? 'cotisation' : 'adhesion';

if ($tarif && isset($_POST['btn_payer'])) {
    if (!csrf_verify()) {
        die('Session expirée, merci de recharger la page et de réessayer.');
    }
    $canal = ($_POST['canal'] ?? '') === 'carte' ? 'carte' : 'mobile_money';
    $msisdn = null;
    if ($canal === 'mobile_money') {
        $msisdn = flexpay_normalize_phone((string) ($_POST['telephone'] ?? ''));
        if ($msisdn === null) {
            $message = "<div class='alert alert-danger'>Numéro Mobile Money invalide (ex. 0812345678).</div>";
        }
    }

    // Anti double paiement : une demande Mobile Money encore en cours (< 3 min) pour ce membre est reprise
    // au lieu d'envoyer un second push (double clic, retour arrière, rechargement de la page).
    if ($message === '') {
        $enCours = $bdd->prepare("SELECT id, canal FROM payments WHERE id_ad = ? AND status IN ('pending','processing')
                                  AND order_number IS NOT NULL AND created_at > (NOW() - INTERVAL 3 MINUTE) ORDER BY id DESC LIMIT 1");
        $enCours->execute([(int) $user['id_ad']]);
        $ec = $enCours->fetch(PDO::FETCH_ASSOC);
        if ($ec && $ec['canal'] === 'mobile_money' && $canal === 'mobile_money') {
            header('Location: loading.php?id=' . (int) $ec['id'] . '&type=adhesion');
            exit;
        }
    }

    // Jamais de second paiement si FlexPay a DÉJÀ encaissé une demande précédente de ce membre (même ancienne, même
    // « expirée » côté site) : on la finalise d'abord ; la page de succès est affichée au lieu de redemander de l'argent.
    if ($message === '') {
        $ouverts = $bdd->prepare("SELECT id FROM payments WHERE id_ad = ? AND status IN ('pending','processing','expired','failed')
                                  AND order_number IS NOT NULL AND type_transaction = ? AND created_at > (NOW() - INTERVAL 3 DAY) ORDER BY id DESC LIMIT 5");
        $ouverts->execute([(int) $user['id_ad'], $typeTransaction]);
        foreach ($ouverts->fetchAll(PDO::FETCH_COLUMN) as $oid) {
            if (payment_flexpay_check($bdd, 'adhesion', (int) $oid) === 'paid') {
                header('Location: success.php?id=' . (int) $oid . '&type=adhesion');
                exit;
            }
        }
    }

    if ($message === '') {
        $reference = payment_new_reference($typeTransaction === 'adhesion' ? 'A' : 'C');
        $ins = $bdd->prepare("INSERT INTO payments
            (id_ad, adhesion_id, reference, reference_payment, codes_ad, telephone_py, montant, devise, provider, status,
             type_transaction, id_cot, canal, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'USD', 'FLEXPAY', 'pending', ?, ?, ?, NOW())");
        $ins->execute([(int) $user['id_ad'], (int) $user['id_ad'], $reference, $reference, $user['codes'],
            $msisdn ?? '', $montant, $typeTransaction, $tarif['id_cot'], $canal]);
        $paymentId = (int) $bdd->lastInsertId();
        payment_log($bdd, $typeTransaction, $paymentId, $reference, 'created', null, 'pending', ['canal' => $canal, 'montant' => $montant]);

        if ($canal === 'mobile_money') {
            $r = flexpay_request_mobile($reference, $msisdn, $montant, 'USD');
        } else {
            $retour = SITE_URL . '/adhere/carte_retour.php?ref=' . urlencode($reference) . '&r=';
            $r = flexpay_request_card($reference, $montant, 'USD',
                'RCR - ' . ($typeTransaction === 'adhesion' ? 'Adhésion' : 'Cotisation') . ' ' . $user['codes'],
                $retour . 'approve', $retour . 'cancel', $retour . 'decline');
        }

        if ($r['ok']) {
            payment_set_order_number($bdd, 'payment', $paymentId, $r['orderNumber']);
            if ($canal === 'carte') {
                header('Location: ' . $r['url']);
            } else {
                header('Location: loading.php?id=' . $paymentId . '&type=adhesion');
            }
            exit;
        }
        payment_transition_status($bdd, 'payments', 'id', $paymentId, ['pending'], 'failed');
        payment_log($bdd, $typeTransaction, $paymentId, $reference, 'init_failed', 'pending', 'failed', $r['message']);
        $message = "<div class='alert alert-danger'>" . htmlspecialchars($r['message'], ENT_QUOTES, 'UTF-8') . '</div>';
    }
}
