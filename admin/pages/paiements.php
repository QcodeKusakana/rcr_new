<?php
/**
 * Administration → Paiements : adhésions, cotisations et dons dans une seule vue.
 * Consultation : paiements.voir. Actions (revérifier / annuler) : paiements.valider.
 *
 * Il n'existe volontairement PAS de bouton "marquer comme payé" : un paiement n'est
 * validé que si FlexPay le confirme (revérification serveur). L'administrateur peut
 * seulement relancer cette vérification ou annuler une transaction restée ouverte.
 */
require_once __DIR__ . '/../../includes/admin_guard.php';
require_once __DIR__ . '/../../includes/payment_helpers.php';
require_permission($bdd, 'paiements.voir');

const PAI_STATUTS = ['pending' => 'En attente', 'processing' => 'En cours', 'paid' => 'Payé', 'failed' => 'Échoué', 'cancelled' => 'Annulé', 'expired' => 'Expiré'];
const PAI_BADGES  = ['pending' => 'warning', 'processing' => 'info', 'paid' => 'success', 'failed' => 'danger', 'cancelled' => 'secondary', 'expired' => 'dark'];

/* ---------------------------------------------------------------- actions */
if (admin_post_guard($bdd, 'paiements.valider')) {
    $ref   = substr(trim((string) ($_POST['reference'] ?? '')), 0, 100);
    $found = $ref !== '' ? payment_find_by_reference($bdd, $ref) : null;
    $act   = (string) ($_POST['action'] ?? '');
    if (!$found) {
        admin_flash('Transaction introuvable.', 'danger');
    } elseif ($act === 'reverifier') {
        $res = payment_verify_and_confirm($bdd, $found);
        audit_log($bdd, 'paiement.reverifier', $found['table'], (string) $found['row']['id'], ['reference' => $ref, 'resultat' => $res ?? 'inchange']);
        admin_flash($res === 'paid' ? 'FlexPay confirme le paiement : il est maintenant validé.'
            : ($res === 'failed' ? 'FlexPay indique que la transaction n\'a pas abouti.'
            : 'Aucun changement : FlexPay n\'a pas (encore) confirmé cette transaction.'), $res === 'paid' ? 'success' : 'info');
    } elseif ($act === 'annuler') {
        $ok = payment_apply_status($bdd, $found, 'cancelled', false);
        audit_log($bdd, 'paiement.annuler', $found['table'], (string) $found['row']['id'], ['reference' => $ref, 'ok' => $ok]);
        admin_flash($ok ? 'Transaction annulée.' : 'Annulation impossible (statut déjà définitif).', $ok ? 'success' : 'danger');
    }
    admin_redirect('pages=paiements&' . http_build_query(array_intersect_key($_GET, array_flip(['type', 'statut', 'canal', 'du', 'au', 'q', 'p']))));
}

/* ---------------------------------------------------------------- filtres */
$type   = in_array($_GET['type'] ?? '', ['adhesion', 'cotisation', 'don'], true) ? $_GET['type'] : '';
$statut = array_key_exists($_GET['statut'] ?? '', PAI_STATUTS) ? $_GET['statut'] : '';
$canal  = in_array($_GET['canal'] ?? '', ['mobile_money', 'carte'], true) ? $_GET['canal'] : '';
$du     = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['du'] ?? '') ? $_GET['du'] : '';
$au     = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['au'] ?? '') ? $_GET['au'] : '';
$q      = trim((string) ($_GET['q'] ?? ''));

$union = "SELECT 'p' AS src, p.id AS id, p.type_transaction AS type, p.reference AS reference, TRIM(CONCAT_WS(' ', a.nom, a.prenom)) AS personne,
                 p.montant AS montant, p.devise AS devise, p.canal AS canal, p.status AS status, p.created_at AS created_at
          FROM payments p LEFT JOIN adhesion a ON a.id_ad = p.id_ad
          UNION ALL
          SELECT 'd', d.id_don, 'don', d.reference, TRIM(CONCAT_WS(' ', d.nom_donateur, d.prenom)), d.montant, d.devise, d.canal, d.status, d.created_at FROM dons d";
$where = []; $par = [];
if ($type !== '')   { $where[] = 't.type = ?';   $par[] = $type; }
if ($statut !== '') { $where[] = 't.status = ?'; $par[] = $statut; }
if ($canal !== '')  { $where[] = 't.canal = ?';  $par[] = $canal; }
if ($du !== '')     { $where[] = 't.created_at >= ?'; $par[] = $du . ' 00:00:00'; }
if ($au !== '')     { $where[] = 't.created_at <= ?'; $par[] = $au . ' 23:59:59'; }
if ($q !== '')      { $where[] = '(t.reference LIKE ? OR t.personne LIKE ?)'; $like = '%' . addcslashes($q, '%_\\') . '%'; $par[] = $like; $par[] = $like; }
$w = $where ? ' WHERE ' . implode(' AND ', $where) : '';

if (isset($_GET['export'])) {
    require_permission($bdd, 'paiements.voir');
    $s = $bdd->prepare("SELECT t.* FROM ($union) t $w ORDER BY t.created_at DESC LIMIT 50000");
    $s->execute($par);
    $lignes = [];
    foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $lignes[] = [$r['created_at'], $r['type'], $r['reference'], $r['personne'], $r['montant'], $r['devise'], $r['canal'], PAI_STATUTS[$r['status']] ?? $r['status']];
    }
    audit_log($bdd, 'export.paiements', 'paiements', '', ['lignes' => count($lignes)]);
    admin_csv_send('paiements_' . date('Ymd') . '.csv', ['Date', 'Type', 'Référence', 'Personne', 'Montant', 'Devise', 'Canal', 'Statut'], $lignes);
}

$c = $bdd->prepare("SELECT COUNT(*), COALESCE(SUM(CASE WHEN t.status = 'paid' AND t.devise = 'USD' THEN t.montant END), 0) FROM ($union) t $w");
$c->execute($par);
[$total, $somme] = $c->fetch(PDO::FETCH_NUM);
$pg = admin_paginate((int) $total, 25);
$s = $bdd->prepare("SELECT t.* FROM ($union) t $w ORDER BY t.created_at DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}");
$s->execute($par);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);
$keep = array_filter(['pages' => 'paiements', 'type' => $type, 'statut' => $statut, 'canal' => $canal, 'du' => $du, 'au' => $au, 'q' => $q], fn($v) => $v !== '');
$peutAgir = has_permission($bdd, 'paiements.valider');
?>
<div class="container-fluid p-3 p-lg-4">
    <?= admin_flash_render() ?>
    <form class="card border-0 shadow-sm mb-3" method="get">
        <input type="hidden" name="pages" value="paiements">
        <div class="card-body row g-2 align-items-end">
            <div class="col-6 col-md-2"><label class="form-label small">Type</label>
                <select name="type" class="form-select form-select-sm"><option value="">Tous</option>
                    <?php foreach (['adhesion' => 'Adhésion', 'cotisation' => 'Cotisation', 'don' => 'Don'] as $k => $l): ?><option value="<?= $k ?>" <?= $type === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
            <div class="col-6 col-md-2"><label class="form-label small">Statut</label>
                <select name="statut" class="form-select form-select-sm"><option value="">Tous</option>
                    <?php foreach (PAI_STATUTS as $k => $l): ?><option value="<?= $k ?>" <?= $statut === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
            <div class="col-6 col-md-2"><label class="form-label small">Moyen</label>
                <select name="canal" class="form-select form-select-sm"><option value="">Tous</option>
                    <option value="mobile_money" <?= $canal === 'mobile_money' ? 'selected' : '' ?>>Mobile Money</option>
                    <option value="carte" <?= $canal === 'carte' ? 'selected' : '' ?>>Carte bancaire</option></select></div>
            <div class="col-6 col-md-2"><label class="form-label small">Du</label><input type="date" name="du" value="<?= e($du) ?>" class="form-control form-control-sm"></div>
            <div class="col-6 col-md-2"><label class="form-label small">Au</label><input type="date" name="au" value="<?= e($au) ?>" class="form-control form-control-sm"></div>
            <div class="col-6 col-md-2"><label class="form-label small">Référence / nom</label><input name="q" value="<?= e($q) ?>" class="form-control form-control-sm"></div>
            <div class="col-12 d-flex gap-2"><button class="btn btn-sm btn-primary">Filtrer</button>
                <a class="btn btn-sm btn-outline-secondary" href="?pages=paiements">Réinitialiser</a>
                <a class="btn btn-sm btn-outline-success ms-auto" href="?<?= e(http_build_query($keep + ['export' => 1])) ?>"><i class="bi bi-download"></i> CSV</a></div>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between flex-wrap gap-2">
            <span class="fw-semibold"><?= (int) $total ?> transaction(s) <a class="btn btn-sm btn-outline-dark ms-2" href="?pages=paiement_diagnostic"><i class="bi bi-activity"></i> Diagnostic FlexPay</a></span>
            <span class="text-muted small">Total payé (USD) sur ce filtre : <strong><?= e(number_format((float) $somme, 2, ',', ' ')) ?></strong></span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Date</th><th>Type</th><th>Référence</th><th>Personne</th><th class="text-end">Montant</th><th>Moyen</th><th>Statut</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($rows as $r): $ouvert = in_array($r['status'], ['pending', 'processing', 'expired', 'failed'], true); ?>
                    <tr>
                        <td class="text-nowrap"><?= e(date('d/m/Y H:i', strtotime((string) $r['created_at']))) ?></td>
                        <td><?= e(ucfirst((string) $r['type'])) ?></td>
                        <td><a class="small text-muted" href="?pages=paiement_diagnostic&amp;q=<?= urlencode((string) $r['reference']) ?>"><?= e((string) $r['reference']) ?></a></td>
                        <td><?= e((string) ($r['personne'] ?: '—')) ?></td>
                        <td class="text-end text-nowrap"><?= e(number_format((float) $r['montant'], 2, ',', ' ')) ?> <?= e((string) $r['devise']) ?></td>
                        <td><?= $r['canal'] === 'carte' ? 'Carte' : 'Mobile Money' ?></td>
                        <td><span class="badge text-bg-<?= PAI_BADGES[$r['status']] ?? 'light' ?>"><?= e(PAI_STATUTS[$r['status']] ?? $r['status']) ?></span></td>
                        <td class="text-nowrap">
                            <?php if ($r['status'] === 'paid'): ?>
                                <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" href="../member/recu.php?t=<?= $r['src'] ?>&amp;id=<?= (int) $r['id'] ?>">Reçu</a>
                            <?php elseif ($peutAgir && $ouvert): ?>
                                <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="reference" value="<?= e((string) $r['reference']) ?>">
                                    <button name="action" value="reverifier" class="btn btn-sm btn-outline-primary">Revérifier</button>
                                    <?php if ($r['status'] !== 'failed'): ?><button name="action" value="annuler" class="btn btn-sm btn-outline-danger" onclick="return confirm('Annuler cette transaction ?')">Annuler</button><?php endif; ?>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; if (!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4">Aucune transaction.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?= admin_pagination_html($pg, $keep) ?>
    </div>
</div>
