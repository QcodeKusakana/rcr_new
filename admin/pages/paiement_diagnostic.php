<?php
/**
 * Administration → Diagnostic d'un paiement FlexPay.
 * Recherche par numéro de commande FlexPay (ORDER_NUMBER), référence RCR ou transaction_id.
 * Lecture : paiements.voir. « Vérifier maintenant » : paiements.valider (POST + CSRF).
 * Aucun secret affiché (ni jeton, ni mot de passe marchand) ; les journaux affichés sont ceux de payment_logs.
 */
require_once __DIR__ . '/../../includes/admin_guard.php';
require_once __DIR__ . '/../../includes/payment_helpers.php';
require_permission($bdd, 'paiements.voir');

$q = substr(trim((string) ($_GET['q'] ?? $_POST['q'] ?? '')), 0, 100);

/** @return array{table:string,type:string,row:array}|null */
$chercher = function (PDO $bdd, string $q): ?array {
    if ($q === '') { return null; }
    foreach (['payments' => 'id', 'dons' => 'id_don'] as $t => $pk) {
        $s = $bdd->prepare("SELECT * FROM `$t` WHERE reference = ? OR order_number = ? OR transaction_id = ? LIMIT 1");
        $s->execute([$q, $q, $q]);
        if ($r = $s->fetch(PDO::FETCH_ASSOC)) {
            $r['id'] = $r[$pk];
            if ($t === 'dons') { $r['type_transaction'] = 'don'; }
            return ['table' => $t, 'type' => $t === 'dons' ? 'don' : 'payment', 'row' => $r];
        }
    }
    return null;
};

$verif = null;
if (admin_post_guard($bdd, 'paiements.valider') && ($_POST['action'] ?? '') === 'verifier') {
    $f = $chercher($bdd, $q);
    if (!$f) {
        admin_flash('Transaction introuvable.', 'danger');
    } else {
        $order = (string) ($f['row']['order_number'] ?: $f['row']['transaction_id']);
        $verif = $order !== '' ? verifyFlexPayTransaction($order) : null;      // réponse brute FlexPay affichée ci-dessous
        $res   = payment_verify_and_confirm($bdd, $f);                          // et application des règles habituelles
        audit_log($bdd, 'paiement.diagnostic_verifier', $f['table'], (string) $f['row']['id'], ['reference' => $f['row']['reference'], 'resultat' => $res ?? 'inchange']);
        admin_flash($res === 'paid' ? 'FlexPay confirme le paiement : validé.' : ($res === 'failed' ? 'FlexPay : la transaction n\'a pas abouti.' : 'Aucun changement (FlexPay n\'a pas confirmé, ou données incohérentes : voir le journal).'), $res === 'paid' ? 'success' : 'info');
    }
}

$f = $chercher($bdd, $q);
$logs = [];
if ($f) {
    $s = $bdd->prepare('SELECT evenement, statut_avant, statut_apres, payload, ip, cree_le FROM payment_logs WHERE reference = ? ORDER BY id DESC LIMIT 60');
    $s->execute([$f['row']['reference']]);
    $logs = $s->fetchAll(PDO::FETCH_ASSOC);
}
$r = $f['row'] ?? [];
$ligne = fn(string $l, $v) => '<tr><th class="text-muted fw-normal" style="width:240px">' . e($l) . '</th><td>' . ($v === null || $v === '' ? '—' : e((string) $v)) . '</td></tr>';
?>
<div class="container-fluid p-3 p-lg-4">
    <?= admin_flash_render() ?>
    <form class="card border-0 shadow-sm mb-3" method="get">
        <input type="hidden" name="pages" value="paiement_diagnostic">
        <div class="card-body d-flex gap-2 flex-wrap">
            <input name="q" value="<?= e($q) ?>" class="form-control" style="max-width:480px" placeholder="ORDER_NUMBER FlexPay, référence RCR-… ou transaction_id" required>
            <button class="btn btn-primary">Rechercher</button>
            <a class="btn btn-outline-secondary" href="?pages=paiements">Retour aux paiements</a>
        </div>
    </form>
    <?php if ($q !== '' && !$f): ?><div class="alert alert-warning">Aucune transaction RCR ne correspond à « <?= e($q) ?> ».</div><?php endif; ?>
    <?php if ($f): ?>
    <div class="row g-3">
        <div class="col-lg-6"><div class="card border-0 shadow-sm"><div class="card-header bg-white fw-semibold">Transaction RCR</div>
            <table class="table table-sm mb-0"><tbody>
                <?= $ligne('Type', $r['type_transaction'] ?? '') ?>
                <?= $ligne('Référence RCR', $r['reference'] ?? '') ?>
                <?= $ligne('Montant', number_format((float) $r['montant'], 2, ',', ' ') . ' ' . ($r['devise'] ?? '')) ?>
                <?= $ligne('Moyen', ($r['canal'] ?? '') === 'carte' ? 'Carte bancaire' : 'Mobile Money') ?>
                <?= $ligne('Statut RCR', $r['status'] ?? '') ?>
                <?= $ligne('Créée le', $r['created_at'] ?? '') ?>
                <?= $ligne('Validée / modifiée le', $r['verifie_le'] ?? '') ?>
                <?= $ligne('Membre (id_ad)', $r['id_ad'] ?? '') ?>
            </tbody></table></div></div>
        <div class="col-lg-6"><div class="card border-0 shadow-sm"><div class="card-header bg-white fw-semibold">Côté FlexPay</div>
            <table class="table table-sm mb-0"><tbody>
                <?= $ligne('ORDER_NUMBER', $r['order_number'] ?? '') ?>
                <?= $ligne('Référence opérateur', $r['provider_reference'] ?? '') ?>
                <?= $ligne('Dernier statut FlexPay (brut)', $r['flexpay_status'] ?? '') ?>
                <?= $ligne('Dernière vérification', $r['derniere_verif'] ?? '') ?>
                <?= $ligne('Nombre de vérifications', $r['nb_verifs'] ?? '') ?>
                <?= $ligne('Dernier callback reçu', $r['dernier_callback'] ?? '') ?>
                <?= $ligne('Dernière erreur', $r['derniere_erreur'] ?? '') ?>
            </tbody></table></div></div>
    </div>
    <?php if ($verif): ?>
        <div class="alert alert-info mt-3 mb-0"><strong>Réponse FlexPay à l'instant :</strong>
            état <code><?= e($verif['state']) ?></code>, statut brut <code><?= e((string) ($verif['flexpay_status'] ?? '—')) ?></code>,
            HTTP <?= (int) $verif['http'] ?>, montant <?= e((string) ($verif['amount'] ?? '—')) ?> <?= e((string) ($verif['currency'] ?? '')) ?>
            <?= $verif['error'] ? '— ' . e($verif['error']) : '' ?></div>
    <?php endif; ?>
    <?php if (has_permission($bdd, 'paiements.valider') && ($r['status'] ?? '') !== 'paid'): ?>
        <form method="post" class="mt-3"><?= csrf_field() ?><input type="hidden" name="q" value="<?= e($q) ?>">
            <button name="action" value="verifier" class="btn btn-warning"><i class="bi bi-arrow-repeat"></i> Vérifier maintenant auprès de FlexPay</button>
            <span class="text-muted small ms-2">Si FlexPay confirme (montant, devise et numéro de commande cohérents), le paiement est finalisé et l'adhésion validée.</span>
        </form>
    <?php endif; ?>
    <div class="card border-0 shadow-sm mt-3"><div class="card-header bg-white fw-semibold">Journal (60 derniers événements)</div>
        <div class="table-responsive"><table class="table table-sm mb-0 align-middle"><thead class="table-light"><tr><th>Date</th><th>Événement</th><th>Avant → après</th><th>Détail</th><th>IP</th></tr></thead><tbody>
        <?php foreach ($logs as $l): ?><tr><td class="text-nowrap small"><?= e((string) $l['cree_le']) ?></td><td><code><?= e((string) $l['evenement']) ?></code></td>
            <td class="small"><?= e((string) $l['statut_avant']) ?> → <?= e((string) $l['statut_apres']) ?></td>
            <td class="small text-break"><?= e(mb_substr((string) $l['payload'], 0, 300)) ?></td><td class="small"><?= e((string) $l['ip']) ?></td></tr>
        <?php endforeach; if (!$logs): ?><tr><td colspan="5" class="text-muted text-center py-3">Aucun événement.</td></tr><?php endif; ?>
        </tbody></table></div></div>
    <?php endif; ?>
</div>
