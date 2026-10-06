<?php
/** Tableau de bord : statistiques utiles, échéances proches, derniers paiements. */
require_once __DIR__ . '/../../includes/admin_guard.php';
require_permission($bdd, 'dashboard.voir');
membres_marquer_expires($bdd);

$one = function (string $sql, array $p = []) use ($bdd) {
    $s = $bdd->prepare($sql); $s->execute($p); return $s->fetchColumn();
};
$debutMois = date('Y-m-01 00:00:00');

$st = [
    'total'      => (int) $one('SELECT COUNT(*) FROM adhesion'),
    'nouveaux'   => (int) $one('SELECT COUNT(*) FROM adhesion WHERE dat_adhesion >= DATE_SUB(NOW(), INTERVAL 30 DAY)'),
    'actifs'     => (int) $one("SELECT COUNT(*) FROM adhesion WHERE statut = 'actif'"),
    'expires'    => (int) $one("SELECT COUNT(*) FROM adhesion WHERE statut = 'expire'"),
    'attente'    => (int) $one("SELECT COUNT(*) FROM adhesion WHERE statut = 'en_attente'"),
    'cotis_mois' => (float) $one("SELECT COALESCE(SUM(montant),0) FROM payments WHERE status = 'paid' AND devise = 'USD' AND created_at >= ?", [$debutMois]),
    'dons_mois'  => (float) $one("SELECT COALESCE(SUM(montant),0) FROM dons WHERE status = 'paid' AND devise = 'USD' AND created_at >= ?", [$debutMois]),
    'pay_attente' => (int) $one("SELECT COUNT(*) FROM payments WHERE status IN ('pending','processing') AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")
                   + (int) $one("SELECT COUNT(*) FROM dons WHERE status IN ('pending','processing') AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"),
    'pay_echoues' => (int) $one("SELECT COUNT(*) FROM payments WHERE status IN ('failed','cancelled') AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")
                   + (int) $one("SELECT COUNT(*) FROM dons WHERE status IN ('failed','cancelled') AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"),
];

$parCat = $bdd->query("SELECT q.designation, COUNT(*) AS n FROM adhesion a JOIN qualites q ON q.id_qt = a.id_qt WHERE a.statut = 'actif' GROUP BY q.id_qt, q.designation ORDER BY q.id_qt")->fetchAll(PDO::FETCH_ASSOC);
$echeances = $bdd->query("SELECT a.id_ad, a.codes, a.nom, a.postnom, a.prenom, a.date_echeance, q.designation, g.nom_gd
                          FROM adhesion a JOIN qualites q ON q.id_qt = a.id_qt LEFT JOIN grades g ON g.id_gd = a.grade
                          WHERE a.statut = 'actif' AND a.date_echeance BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                          ORDER BY a.date_echeance LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
$derniers = $bdd->query("(SELECT p.created_at, p.type_transaction AS type, CONCAT(a.nom,' ',a.prenom) AS qui, p.montant, p.devise, p.status
                           FROM payments p LEFT JOIN adhesion a ON a.id_ad = p.id_ad)
                         UNION ALL
                         (SELECT d.created_at, 'don' AS type, CONCAT(d.nom_donateur,' ',COALESCE(d.prenom,'')) AS qui, d.montant, d.devise, d.status FROM dons d)
                         ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

// Encaissements des 6 derniers mois (cotisations / dons confirmés, USD) pour le graphique
$mois = [];
for ($i = 5; $i >= 0; $i--) { $mois[date('Y-m', strtotime("first day of -$i month"))] = ['c' => 0.0, 'd' => 0.0]; }
$debut6 = array_key_first($mois) . '-01 00:00:00';
foreach ($bdd->query("SELECT DATE_FORMAT(created_at, '%Y-%m') m, SUM(montant) t FROM payments WHERE status = 'paid' AND devise = 'USD' AND created_at >= " . $bdd->quote($debut6) . " GROUP BY m")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    if (isset($mois[$r['m']])) { $mois[$r['m']]['c'] = (float) $r['t']; }
}
foreach ($bdd->query("SELECT DATE_FORMAT(created_at, '%Y-%m') m, SUM(montant) t FROM dons WHERE status = 'paid' AND devise = 'USD' AND created_at >= " . $bdd->quote($debut6) . " GROUP BY m")->fetchAll(PDO::FETCH_ASSOC) as $r) {
    if (isset($mois[$r['m']])) { $mois[$r['m']]['d'] = (float) $r['t']; }
}
$moisFr = ['01' => 'janv.', '02' => 'févr.', '03' => 'mars', '04' => 'avr.', '05' => 'mai', '06' => 'juin', '07' => 'juil.', '08' => 'août', '09' => 'sept.', '10' => 'oct.', '11' => 'nov.', '12' => 'déc.'];
$graph = ['labels' => [], 'c' => [], 'd' => []];
foreach ($mois as $k => $v) { $graph['labels'][] = $moisFr[substr($k, 5, 2)] . ' ' . substr($k, 2, 2); $graph['c'][] = round($v['c'], 2); $graph['d'][] = round($v['d'], 2); }

$badge = ['paid' => 'success', 'pending' => 'warning', 'processing' => 'warning', 'failed' => 'danger', 'cancelled' => 'secondary', 'expired' => 'secondary'];
$lib   = ['paid' => 'Payé', 'pending' => 'En attente', 'processing' => 'En cours', 'failed' => 'Échoué', 'cancelled' => 'Annulé', 'expired' => 'Expiré'];
$cartes = [
    ['Membres', $st['total'], 'bi-people-fill', 'primary'],
    ['Nouveaux (30 j)', $st['nouveaux'], 'bi-person-plus-fill', 'info'],
    ['Membres actifs', $st['actifs'], 'bi-patch-check-fill', 'success'],
    ['Membres expirés', $st['expires'], 'bi-hourglass-bottom', 'danger'],
    ['Cotisations du mois', number_format($st['cotis_mois'], 2, ',', ' ') . ' $', 'bi-cash-coin', 'success'],
    ['Dons du mois', number_format($st['dons_mois'], 2, ',', ' ') . ' $', 'bi-heart-fill', 'warning'],
    ['Paiements en attente', $st['pay_attente'], 'bi-clock-history', 'warning'],
    ['Paiements échoués (30 j)', $st['pay_echoues'], 'bi-x-octagon-fill', 'danger'],
];
?>
<div class="container-fluid p-3 p-lg-4">
    <div class="row g-3">
        <?php foreach ($cartes as [$titre, $val, $ico, $col]): ?>
            <div class="col-6 col-md-4 col-xl-3">
                <div class="card h-100">
                    <div class="adm-stat">
                        <div class="adm-stat-ico bg-<?= e($col) ?> bg-opacity-10 text-<?= e($col) ?>"><i class="bi <?= e($ico) ?>" aria-hidden="true"></i></div>
                        <div>
                            <div class="adm-stat-val"><?= e((string) $val) ?></div>
                            <div class="adm-stat-lib"><?= e($titre) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center"><span>Encaissements confirmés — 6 derniers mois (USD)</span>
            <a class="btn btn-sm btn-outline-primary" href="?pages=paiements">Tous les paiements</a></div>
        <div class="card-body"><div style="position:relative;height:260px"><canvas id="graphEncaissements" aria-label="Graphique des encaissements par mois" role="img"></canvas></div></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.Chart) { return; }
        var d = <?= json_encode($graph, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        new Chart(document.getElementById('graphEncaissements'), {
            type: 'bar',
            data: { labels: d.labels, datasets: [
                { label: 'Cotisations', data: d.c, backgroundColor: '#1B2A44', borderRadius: 4 },
                { label: 'Dons', data: d.d, backgroundColor: '#B8933F', borderRadius: 4 }
            ] },
            options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' },
                tooltip: { callbacks: { label: function (c) { return c.dataset.label + ' : ' + c.parsed.y.toLocaleString('fr-FR', { minimumFractionDigits: 2 }) + ' $'; } } } },
                scales: { y: { beginAtZero: true, ticks: { callback: function (v) { return v + ' $'; } } }, x: { grid: { display: false } } } }
        });
    });
    </script>

    <div class="row g-3 mt-1">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Derniers paiements et dons</div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light"><tr><th>Date</th><th>Type</th><th>De</th><th class="text-end">Montant</th><th>Statut</th></tr></thead>
                        <tbody>
                        <?php foreach ($derniers as $d): ?>
                            <tr>
                                <td><?= e(date('d/m/Y H:i', strtotime((string) $d['created_at']))) ?></td>
                                <td><?= e($d['type']) ?></td>
                                <td><?= e(trim((string) $d['qui']) ?: '—') ?></td>
                                <td class="text-end"><?= e(number_format((float) $d['montant'], 2, ',', ' ')) ?> <?= e($d['devise']) ?></td>
                                <td><span class="badge text-bg-<?= e($badge[$d['status']] ?? 'light') ?>"><?= e($lib[$d['status']] ?? $d['status']) ?></span></td>
                            </tr>
                        <?php endforeach; if (!$derniers): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">Aucune transaction pour le moment.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-semibold">Membres actifs par catégorie</div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($parCat as $c): ?>
                        <li class="list-group-item d-flex justify-content-between"><span>Membre <?= e($c['designation']) ?></span><strong><?= (int) $c['n'] ?></strong></li>
                    <?php endforeach; if (!$parCat): ?>
                        <li class="list-group-item text-muted">Aucun membre actif.</li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Échéances dans les 30 jours</div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($echeances as $m): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= e(trim($m['nom'] . ' ' . $m['prenom'])) ?> <small class="text-muted">(<?= e($m['codes']) ?>)</small></span>
                            <span class="text-nowrap"><?= e(date('d/m/Y', strtotime($m['date_echeance']))) ?></span>
                        </li>
                    <?php endforeach; if (!$echeances): ?>
                        <li class="list-group-item text-muted">Aucune échéance proche.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <p class="text-muted small mt-3">Montants en USD. « En attente » : 7 derniers jours ; « échoués » : 30 derniers jours.</p>
</div>
