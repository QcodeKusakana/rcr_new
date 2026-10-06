<?php
/**
 * Don / soutien (ponctuel ou régulier) — montant LIBRE en USD.
 * Un don n'est jamais mélangé à une cotisation : table dons, référence RCR-D-…, transaction typée 'don'.
 * Un non-membre peut donner ; un membre aussi (code_membre facultatif, vérifié).
 * Don régulier : le 1er versement est payé ici ; la fréquence est enregistrée pour les rappels d'échéance.
 */
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e !== null && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log(sprintf('[don_paiement] FATAL %s (%s:%d)', $e['message'], $e['file'], $e['line']));
        if (!headers_sent()) {
            header('Location: ../index.php?pages=soutenir&don_error=reponse');
        }
    }
});
set_time_limit(60);

require_once __DIR__ . '/main_function.php';
require_once __DIR__ . '/../includes/payment_helpers.php';

function don_back(string $code): void
{
    header('Location: ../index.php?pages=soutenir&don_error=' . $code);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { header('Location: ../index.php?pages=soutenir'); exit; }
if (!csrf_verify()) { don_back('session'); }

$t = static fn(string $k, int $max = 255): string => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);

// Montant libre : positif, numérique, au plus 2 décimales
$brut = str_replace(',', '.', $t('montant', 12));
if (!preg_match('/^\d{1,6}(\.\d{1,2})?$/', $brut) || (float) $brut <= 0 || (float) $brut > 100000) { don_back('montant'); }
$montant = round((float) $brut, 2);

$nom = $t('nom_donateur', 100); $prenom = $t('prenom', 100); $postnom = $t('postnom', 100);
$email = $t('email', 150);
if ($nom === '' || $prenom === '') { don_back('champs'); }
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { don_back('email'); }

$canal = ($_POST['canal'] ?? '') === 'carte' ? 'carte' : 'mobile_money';
$msisdn = flexpay_normalize_phone($t('telephone', 20));
if ($canal === 'mobile_money' && $msisdn === null) { don_back('telephone'); }
$telephone = $msisdn ?? preg_replace('/\D+/', '', $t('telephone', 20));
if (strlen($telephone) < 9) { don_back('telephone'); }

$typeDon = ($_POST['type_don'] ?? '') === 'regulier' ? 'regulier' : 'ponctuel';
$frequence = null;
if ($typeDon === 'regulier') {
    $frequence = in_array($_POST['frequence'] ?? '', ['mensuel', 'trimestriel', 'semestriel', 'annuel'], true) ? $_POST['frequence'] : null;
    if ($frequence === null) { don_back('champs'); }
}

$idProvince = (int) ($_POST['id_province'] ?? 0) ?: null;
if ($idProvince) {
    $q = $bdd->prepare('SELECT 1 FROM provinces WHERE id_p = ?'); $q->execute([$idProvince]);
    if (!$q->fetchColumn()) { $idProvince = null; }
}

// Lien avec un membre existant (facultatif)
$idMembre = null; $codeMembre = strtoupper($t('code_membre', 30));
if ($codeMembre !== '') {
    $q = $bdd->prepare('SELECT id_ad FROM adhesion WHERE codes = ? LIMIT 1'); $q->execute([$codeMembre]);
    $idMembre = $q->fetchColumn() ?: null;
}

$reference = payment_new_reference('D');
$ins = $bdd->prepare("INSERT INTO dons
    (nom_donateur, postnom, prenom, telephone, email, montant, devise, provider, reference, status, ip_donateur,
     type_don, frequence, id_ad, canal, id_province, ville, adresse, code_membre, created_at)
    VALUES (?,?,?,?,?,?, 'USD', 'FLEXPAY', ?, 'pending', ?, ?,?,?,?,?,?,?,?, NOW())");
$ins->execute([$nom, $postnom, $prenom, $telephone, $email, $montant, $reference, substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 50),
    $typeDon, $frequence, $idMembre, $canal, $idProvince, $t('ville', 120), $t('adresse', 255), $idMembre ? $codeMembre : null]);
$donId = (int) $bdd->lastInsertId();
payment_log($bdd, 'don', $donId, $reference, 'created', null, 'pending', ['montant' => $montant, 'type' => $typeDon, 'canal' => $canal]);

if ($canal === 'mobile_money') {
    $r = flexpay_request_mobile($reference, $msisdn, $montant, 'USD');
} else {
    $retour = SITE_URL . '/adhere/carte_retour.php?ref=' . urlencode($reference) . '&r=';
    $r = flexpay_request_card($reference, $montant, 'USD', 'Don RCR', $retour . 'approve', $retour . 'cancel', $retour . 'decline');
}

if ($r['ok']) {
    payment_set_order_number($bdd, 'don', $donId, $r['orderNumber']);
    header('Location: ' . ($canal === 'carte' ? $r['url'] : 'loading.php?id=' . $donId . '&type=don'));
    exit;
}
payment_transition_status($bdd, 'dons', 'id_don', $donId, ['pending'], 'failed');
payment_log($bdd, 'don', $donId, $reference, 'init_failed', 'pending', 'failed', $r['message']);
don_back('reponse');
