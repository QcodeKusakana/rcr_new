<?php
/**
 * Administration → Journaux : actions des administrateurs, événements de paiement, blocages de connexion.
 * Permission : logs.voir (lecture seule).
 */
require_once __DIR__ . '/../../includes/admin_guard.php';
require_permission($bdd, 'logs.voir');

$onglet = in_array($_GET['onglet'] ?? '', ['audit', 'paiements', 'connexions'], true) ? $_GET['onglet'] : 'audit';
$q = trim((string) ($_GET['q'] ?? ''));
$like = '%' . addcslashes($q, '%_\\') . '%';
$keep = array_filter(['pages' => 'journaux', 'onglet' => $onglet, 'q' => $q], fn($v) => $v !== '');

if ($onglet === 'audit') {
    $w = $q !== '' ? ' WHERE (l.action LIKE ? OR l.cible LIKE ? OR l.detail LIKE ? OR a.pseudo LIKE ?)' : '';
    $par = $q !== '' ? [$like, $like, $like, $like] : [];
    $c = $bdd->prepare("SELECT COUNT(*) FROM audit_logs l LEFT JOIN admin a ON a.id_adm = l.id_adm $w"); $c->execute($par);
    $pg = admin_paginate((int) $c->fetchColumn(), 30);
    $s = $bdd->prepare("SELECT l.*, a.pseudo FROM audit_logs l LEFT JOIN admin a ON a.id_adm = l.id_adm $w ORDER BY l.id DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}"); $s->execute($par);
} elseif ($onglet === 'paiements') {
    $w = $q !== '' ? ' WHERE (reference LIKE ? OR evenement LIKE ?)' : '';
    $par = $q !== '' ? [$like, $like] : [];
    $c = $bdd->prepare("SELECT COUNT(*) FROM payment_logs $w"); $c->execute($par);
    $pg = admin_paginate((int) $c->fetchColumn(), 30);
    $s = $bdd->prepare("SELECT * FROM payment_logs $w ORDER BY id DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}"); $s->execute($par);
} else {
    $pg = admin_paginate((int) $bdd->query('SELECT COUNT(*) FROM login_attempts')->fetchColumn(), 30);
    $s = $bdd->query("SELECT * FROM login_attempts ORDER BY derniere_tentative DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}");
}
$rows = $s->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container-fluid p-3 p-lg-4">
    <ul class="nav nav-tabs mb-3">
        <?php foreach (['audit' => 'Actions administrateurs', 'paiements' => 'Événements de paiement', 'connexions' => 'Tentatives de connexion'] as $k => $l): ?>
            <li class="nav-item"><a class="nav-link <?= $onglet === $k ? 'active' : '' ?>" href="?pages=journaux&amp;onglet=<?= $k ?>"><?= e($l) ?></a></li><?php endforeach; ?>
    </ul>
    <?php if ($onglet !== 'connexions'): ?>
    <form method="get" class="mb-3 d-flex gap-2"><input type="hidden" name="pages" value="journaux"><input type="hidden" name="onglet" value="<?= e($onglet) ?>">
        <input name="q" value="<?= e($q) ?>" class="form-control form-control-sm" style="max-width:320px" placeholder="Rechercher"><button class="btn btn-sm btn-primary">Rechercher</button></form>
    <?php endif; ?>
    <div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-sm table-hover align-middle mb-0">
    <?php if ($onglet === 'audit'): ?>
        <thead class="table-light"><tr><th>Date</th><th>Administrateur</th><th>Action</th><th>Cible</th><th>Détail</th><th>IP</th></tr></thead><tbody>
        <?php foreach ($rows as $r): ?><tr><td class="text-nowrap"><?= e(date('d/m/Y H:i:s', strtotime((string) $r['cree_le']))) ?></td><td><?= e((string) ($r['pseudo'] ?? '—')) ?></td>
            <td><code><?= e((string) $r['action']) ?></code></td><td><?= e($r['cible'] . ($r['cible_id'] !== '' ? ' #' . $r['cible_id'] : '')) ?></td>
            <td><small class="text-muted text-break"><?= e(mb_substr((string) $r['detail'], 0, 220)) ?></small></td><td><small><?= e((string) $r['ip']) ?></small></td></tr><?php endforeach; ?>
    <?php elseif ($onglet === 'paiements'): ?>
        <thead class="table-light"><tr><th>Date</th><th>Type</th><th>Référence</th><th>Événement</th><th>Avant → après</th><th>Détail</th></tr></thead><tbody>
        <?php foreach ($rows as $r): ?><tr><td class="text-nowrap"><?= e(date('d/m/Y H:i:s', strtotime((string) $r['cree_le']))) ?></td><td><?= e((string) $r['type_transaction']) ?></td>
            <td><small><?= e((string) $r['reference']) ?></small></td><td><code><?= e((string) $r['evenement']) ?></code></td>
            <td><?= e((string) $r['statut_avant']) ?> → <?= e((string) $r['statut_apres']) ?></td><td><small class="text-muted text-break"><?= e(mb_substr((string) $r['payload'], 0, 200)) ?></small></td></tr><?php endforeach; ?>
    <?php else: ?>
        <thead class="table-light"><tr><th>Adresse IP</th><th>Échecs</th><th>Dernière tentative</th><th>Bloquée jusqu'à</th></tr></thead><tbody>
        <?php foreach ($rows as $r): ?><tr><td><?= e((string) $r['ip_adresse']) ?></td><td><?= (int) $r['tentatives'] ?></td><td><?= e(date('d/m/Y H:i', strtotime((string) $r['derniere_tentative']))) ?></td>
            <td><?= $r['bloque_jusqu_a'] && $r['bloque_jusqu_a'] > date('Y-m-d H:i:s') ? '<span class="badge text-bg-danger">' . e(date('d/m/Y H:i', strtotime($r['bloque_jusqu_a']))) . '</span>' : '—' ?></td></tr><?php endforeach; ?>
    <?php endif; if (!$rows): ?><tr><td colspan="6" class="text-center text-muted py-4">Aucune entrée.</td></tr><?php endif; ?>
        </tbody></table></div><?= admin_pagination_html($pg, $keep) ?></div>
</div>
