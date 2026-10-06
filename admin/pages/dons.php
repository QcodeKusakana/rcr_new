<?php
/**
 * Administration → Soutiens / Dons (consultation, filtres, export).
 * Filtres : ponctuel / régulier, membre / non-membre, période, statut, montant, moyen de paiement.
 * Un don est comptabilisé UNIQUEMENT s'il est au statut « paid » (confirmé par FlexPay côté serveur).
 */
require_once __DIR__ . '/../../includes/admin_guard.php';
require_permission($bdd, 'paiements.voir');

$ST = ['pending' => 'En attente', 'processing' => 'En cours', 'paid' => 'Payé', 'failed' => 'Échoué', 'cancelled' => 'Annulé', 'expired' => 'Expiré'];
$BG = ['pending' => 'warning', 'processing' => 'info', 'paid' => 'success', 'failed' => 'danger', 'cancelled' => 'secondary', 'expired' => 'dark'];

$typeDon = in_array($_GET['type_don'] ?? '', ['ponctuel', 'regulier'], true) ? $_GET['type_don'] : '';
$membre  = in_array($_GET['membre'] ?? '', ['oui', 'non'], true) ? $_GET['membre'] : '';
$statut  = array_key_exists($_GET['statut'] ?? '', $ST) ? $_GET['statut'] : '';
$canal   = in_array($_GET['canal'] ?? '', ['mobile_money', 'carte'], true) ? $_GET['canal'] : '';
$du      = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['du'] ?? '') ? $_GET['du'] : '';
$au      = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['au'] ?? '') ? $_GET['au'] : '';
$min     = isset($_GET['min']) && is_numeric($_GET['min']) ? max(0, (float) $_GET['min']) : null;
$max     = isset($_GET['max']) && is_numeric($_GET['max']) ? max(0, (float) $_GET['max']) : null;

$where = []; $par = [];
if ($typeDon !== '') { $where[] = 'd.type_don = ?'; $par[] = $typeDon; }
if ($membre === 'oui') { $where[] = 'd.id_ad IS NOT NULL'; } elseif ($membre === 'non') { $where[] = 'd.id_ad IS NULL'; }
if ($statut !== '')  { $where[] = 'd.status = ?'; $par[] = $statut; }
if ($canal !== '')   { $where[] = 'd.canal = ?';  $par[] = $canal; }
if ($du !== '')      { $where[] = 'd.created_at >= ?'; $par[] = $du . ' 00:00:00'; }
if ($au !== '')      { $where[] = 'd.created_at <= ?'; $par[] = $au . ' 23:59:59'; }
if ($min !== null)   { $where[] = 'd.montant >= ?'; $par[] = $min; }
if ($max !== null)   { $where[] = 'd.montant <= ?'; $par[] = $max; }
$w = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$cols = "d.id_don, d.nom_donateur, d.postnom, d.prenom, d.email, d.telephone, d.montant, d.devise, d.type_don, d.frequence, d.canal,
         d.status, d.reference, d.order_number, d.created_at, d.id_ad, d.code_membre";

if (isset($_GET['export'])) {
    $s = $bdd->prepare("SELECT $cols FROM dons d $w ORDER BY d.created_at DESC LIMIT 50000");
    $s->execute($par);
    $l = [];
    foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $l[] = [$r['created_at'], trim($r['nom_donateur'] . ' ' . $r['postnom'] . ' ' . $r['prenom']), $r['email'], $r['telephone'], $r['montant'], $r['devise'],
                $r['type_don'], $r['frequence'], $r['id_ad'] ? 'Membre ' . $r['code_membre'] : 'Non-membre', $r['canal'], $r['reference'], $ST[$r['status']] ?? $r['status']];
    }
    audit_log($bdd, 'export.dons', 'dons', '', ['lignes' => count($l)]);
    admin_csv_send('dons_' . date('Ymd') . '.csv', ['Date', 'Donateur', 'E-mail', 'Téléphone', 'Montant', 'Devise', 'Type', 'Fréquence', 'Membre', 'Moyen', 'Référence', 'Statut'], $l);
}

$c = $bdd->prepare("SELECT COUNT(*), COALESCE(SUM(CASE WHEN d.status = 'paid' AND d.devise = 'USD' THEN d.montant END), 0) FROM dons d $w");
$c->execute($par);
[$total, $somme] = $c->fetch(PDO::FETCH_NUM);
$pg = admin_paginate((int) $total, 25);
$s = $bdd->prepare("SELECT $cols FROM dons d $w ORDER BY d.created_at DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}");
$s->execute($par);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);
$keep = array_filter(['pages' => 'dons', 'type_don' => $typeDon, 'membre' => $membre, 'statut' => $statut, 'canal' => $canal, 'du' => $du, 'au' => $au,
                      'min' => $min === null ? '' : $min, 'max' => $max === null ? '' : $max], fn($v) => $v !== '');
$FREQ = ['mensuel' => 'Mensuel', 'trimestriel' => 'Trimestriel', 'semestriel' => 'Semestriel', 'annuel' => 'Annuel'];
?>
<div class="container-fluid p-3 p-lg-4">
    <form class="card border-0 shadow-sm mb-3" method="get">
        <input type="hidden" name="pages" value="dons">
        <div class="card-body row g-2 align-items-end">
            <div class="col-6 col-md-2"><label class="form-label small">Type de don</label><select name="type_don" class="form-select form-select-sm"><option value="">Tous</option>
                <option value="ponctuel" <?= $typeDon === 'ponctuel' ? 'selected' : '' ?>>Ponctuel</option><option value="regulier" <?= $typeDon === 'regulier' ? 'selected' : '' ?>>Régulier</option></select></div>
            <div class="col-6 col-md-2"><label class="form-label small">Donateur</label><select name="membre" class="form-select form-select-sm"><option value="">Tous</option>
                <option value="oui" <?= $membre === 'oui' ? 'selected' : '' ?>>Membre</option><option value="non" <?= $membre === 'non' ? 'selected' : '' ?>>Non-membre</option></select></div>
            <div class="col-6 col-md-2"><label class="form-label small">Statut</label><select name="statut" class="form-select form-select-sm"><option value="">Tous</option>
                <?php foreach ($ST as $k => $l): ?><option value="<?= $k ?>" <?= $statut === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
            <div class="col-6 col-md-2"><label class="form-label small">Moyen</label><select name="canal" class="form-select form-select-sm"><option value="">Tous</option>
                <option value="mobile_money" <?= $canal === 'mobile_money' ? 'selected' : '' ?>>Mobile Money</option><option value="carte" <?= $canal === 'carte' ? 'selected' : '' ?>>Carte bancaire</option></select></div>
            <div class="col-6 col-md-2"><label class="form-label small">Du</label><input type="date" name="du" value="<?= e($du) ?>" class="form-control form-control-sm"></div>
            <div class="col-6 col-md-2"><label class="form-label small">Au</label><input type="date" name="au" value="<?= e($au) ?>" class="form-control form-control-sm"></div>
            <div class="col-6 col-md-2"><label class="form-label small">Montant min</label><input name="min" inputmode="decimal" value="<?= $min === null ? '' : e((string) $min) ?>" class="form-control form-control-sm"></div>
            <div class="col-6 col-md-2"><label class="form-label small">Montant max</label><input name="max" inputmode="decimal" value="<?= $max === null ? '' : e((string) $max) ?>" class="form-control form-control-sm"></div>
            <div class="col-12 d-flex gap-2"><button class="btn btn-sm btn-primary">Filtrer</button><a class="btn btn-sm btn-outline-secondary" href="?pages=dons">Réinitialiser</a>
                <a class="btn btn-sm btn-outline-success ms-auto" href="?<?= e(http_build_query($keep + ['export' => 1])) ?>"><i class="bi bi-download"></i> CSV</a></div>
        </div>
    </form>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between flex-wrap gap-2">
            <span class="fw-semibold"><?= (int) $total ?> don(s)</span>
            <span class="text-muted small">Total reçu (USD, dons payés) : <strong><?= e(number_format((float) $somme, 2, ',', ' ')) ?></strong></span>
        </div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Date</th><th>Donateur</th><th class="text-end">Montant</th><th>Don</th><th>Moyen</th><th>Transaction</th><th>Statut</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td class="text-nowrap"><?= e(date('d/m/Y H:i', strtotime((string) $r['created_at']))) ?></td>
                    <td><?= e(trim($r['nom_donateur'] . ' ' . $r['postnom'] . ' ' . $r['prenom'])) ?>
                        <div class="small text-muted"><?= e((string) $r['telephone']) ?><?= $r['id_ad'] ? ' · membre ' . e((string) $r['code_membre']) : ' · non-membre' ?></div></td>
                    <td class="text-end text-nowrap"><?= e(number_format((float) $r['montant'], 2, ',', ' ')) ?> <?= e((string) $r['devise']) ?></td>
                    <td><?= $r['type_don'] === 'regulier' ? 'Régulier<div class="small text-muted">' . e($FREQ[$r['frequence']] ?? '') . '</div>' : 'Ponctuel' ?></td>
                    <td><?= $r['canal'] === 'carte' ? 'Carte' : 'Mobile Money' ?></td>
                    <td><small class="text-muted"><?= e((string) $r['reference']) ?></small></td>
                    <td><span class="badge text-bg-<?= $BG[$r['status']] ?? 'light' ?>"><?= e($ST[$r['status']] ?? $r['status']) ?></span></td>
                    <td><?php if ($r['status'] === 'paid'): ?><a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" href="../member/recu.php?t=d&amp;id=<?= (int) $r['id_don'] ?>">Reçu</a><?php endif; ?></td>
                </tr>
            <?php endforeach; if (!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4">Aucun don.</td></tr><?php endif; ?>
            </tbody></table></div>
        <?= admin_pagination_html($pg, $keep) ?>
    </div>
</div>
