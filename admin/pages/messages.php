<?php
/**
 * Administration → Messages reçus via le formulaire de contact (table messages_contact).
 * Permission : contenu.gerer. Lecture, changement de statut, recherche, filtres, export CSV.
 */
require_once __DIR__ . '/../../includes/admin_guard.php';
require_permission($bdd, 'contenu.gerer');

$statuts = ['nouveau' => ['Nouveau', 'danger'], 'lu' => ['Lu', 'secondary'], 'traite' => ['Traité', 'success'], 'archive' => ['Archivé', 'dark']];

try {
    $bdd->query('SELECT 1 FROM messages_contact LIMIT 1');
    $tableOk = true;
} catch (Throwable $e) {
    $tableOk = false;
}

if ($tableOk && admin_post_guard($bdd, 'contenu.gerer')) {
    $id = (int) ($_POST['id'] ?? 0);
    $st = (string) ($_POST['statut'] ?? '');
    if ($id > 0 && isset($statuts[$st])) {
        $bdd->prepare('UPDATE messages_contact SET statut = ?, traite_par = ? WHERE id = ?')->execute([$st, (int) $_SESSION['id_adm'], $id]);
        audit_log($bdd, 'message.statut', 'messages_contact', (string) $id, $st);
        admin_flash('Statut du message mis à jour.');
    }
    admin_redirect('pages=messages' . (isset($_POST['retour']) ? '&id=' . $id : ''));
}

if (!$tableOk): ?>
    <div class="container-fluid p-3 p-lg-4">
        <div class="alert alert-warning" data-no-toast>
            La table des messages n'existe pas encore. Lancez la migration :
            <code>php migrations/phase5_migrate.php --apply</code>
        </div>
    </div>
<?php return; endif;

$q      = trim((string) ($_GET['q'] ?? ''));
$fStat  = isset($statuts[$_GET['statut'] ?? '']) ? $_GET['statut'] : '';
$where  = ['1=1'];
$params = [];
if ($q !== '') {
    $where[] = '(nom LIKE ? OR email LIKE ? OR objet LIKE ? OR message LIKE ?)';
    $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
    array_push($params, $like, $like, $like, $like);
}
if ($fStat !== '') {
    $where[] = 'statut = ?';
    $params[] = $fStat;
}
$w = implode(' AND ', $where);

if (isset($_GET['export'])) {
    $s = $bdd->prepare("SELECT cree_le, nom, email, objet, message, statut FROM messages_contact WHERE $w ORDER BY cree_le DESC");
    $s->execute($params);
    admin_csv_send('messages_' . date('Ymd') . '.csv', ['Date', 'Nom', 'E-mail', 'Objet', 'Message', 'Statut'], $s->fetchAll(PDO::FETCH_NUM));
}

// Lecture d'un message (passe automatiquement de « nouveau » à « lu »)
$detail = null;
if (isset($_GET['id'])) {
    $s = $bdd->prepare('SELECT * FROM messages_contact WHERE id = ?');
    $s->execute([(int) $_GET['id']]);
    $detail = $s->fetch(PDO::FETCH_ASSOC) ?: null;
    if ($detail && $detail['statut'] === 'nouveau') {
        $bdd->prepare("UPDATE messages_contact SET statut = 'lu' WHERE id = ? AND statut = 'nouveau'")->execute([(int) $detail['id']]);
        $detail['statut'] = 'lu';
    }
}

$c = $bdd->prepare("SELECT COUNT(*) FROM messages_contact WHERE $w");
$c->execute($params);
$pg = admin_paginate((int) $c->fetchColumn(), 25);
$s = $bdd->prepare("SELECT id, nom, email, objet, statut, cree_le FROM messages_contact WHERE $w ORDER BY cree_le DESC LIMIT {$pg['offset']}, {$pg['per']}");
$s->execute($params);
$liste = $s->fetchAll(PDO::FETCH_ASSOC);
$nbNouveaux = (int) $bdd->query("SELECT COUNT(*) FROM messages_contact WHERE statut = 'nouveau'")->fetchColumn();
?>
<div class="container-fluid p-3 p-lg-4">
    <?= admin_flash_render() ?>

    <?php if ($detail): ?>
    <div class="card adm-card mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <strong><?= e($detail['objet']) ?></strong>
                <div class="small text-muted"><?= e($detail['nom']) ?> · <a href="mailto:<?= e($detail['email']) ?>?subject=<?= e(rawurlencode('Re : ' . $detail['objet'])) ?>"><?= e($detail['email']) ?></a> · <?= e(date('d/m/Y H:i', strtotime($detail['cree_le']))) ?></div>
            </div>
            <a href="?pages=messages" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i> Fermer</a>
        </div>
        <div class="card-body">
            <p class="mb-4" style="white-space:pre-wrap"><?= e($detail['message']) ?></p>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-primary btn-sm" href="mailto:<?= e($detail['email']) ?>?subject=<?= e(rawurlencode('Re : ' . $detail['objet'])) ?>"><i class="bi bi-reply"></i> Répondre par e-mail</a>
                <?php foreach (['traite' => 'Marquer traité', 'archive' => 'Archiver', 'nouveau' => 'Marquer non lu'] as $st => $lib): if ($st === $detail['statut']) { continue; } ?>
                <form method="post"><input type="hidden" name="id" value="<?= (int) $detail['id'] ?>"><input type="hidden" name="statut" value="<?= $st ?>"><input type="hidden" name="retour" value="1">
                    <button class="btn btn-outline-secondary btn-sm"><?= e($lib) ?></button></form>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card adm-card">
        <div class="card-header">
            <form method="get" class="row g-2 align-items-center">
                <input type="hidden" name="pages" value="messages">
                <div class="col-md-5"><input type="search" name="q" value="<?= e($q) ?>" class="form-control form-control-sm" placeholder="Rechercher (nom, e-mail, objet, message)…" aria-label="Rechercher"></div>
                <div class="col-md-3"><select name="statut" class="form-select form-select-sm" aria-label="Statut"><option value="">Tous les statuts</option>
                    <?php foreach ($statuts as $k => [$lib]): ?><option value="<?= $k ?>" <?= $fStat === $k ? 'selected' : '' ?>><?= e($lib) ?></option><?php endforeach; ?></select></div>
                <div class="col-auto"><button class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i> Filtrer</button></div>
                <div class="col-auto ms-auto"><span class="badge text-bg-danger"><?= $nbNouveaux ?> nouveau(x)</span>
                    <a class="btn btn-outline-success btn-sm ms-2" href="?<?= e(http_build_query(['pages' => 'messages', 'q' => $q, 'statut' => $fStat, 'export' => 1])) ?>"><i class="bi bi-filetype-csv"></i> Export</a></div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Date</th><th>Expéditeur</th><th>Objet</th><th>Statut</th><th></th></tr></thead>
                <tbody>
                <?php if (!$liste): ?><tr><td colspan="5" class="text-center text-muted py-4">Aucun message.</td></tr><?php endif; ?>
                <?php foreach ($liste as $m): ?>
                    <tr class="<?= $m['statut'] === 'nouveau' ? 'fw-semibold' : '' ?>">
                        <td class="text-nowrap small"><?= e(date('d/m/Y H:i', strtotime($m['cree_le']))) ?></td>
                        <td><?= e($m['nom']) ?><div class="small text-muted"><?= e($m['email']) ?></div></td>
                        <td><?= e(mb_strimwidth($m['objet'], 0, 70, '…')) ?></td>
                        <td><span class="badge text-bg-<?= $statuts[$m['statut']][1] ?>"><?= e($statuts[$m['statut']][0]) ?></span></td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="?pages=messages&amp;id=<?= (int) $m['id'] ?>"><i class="bi bi-envelope-open"></i> Lire</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= admin_pagination_html($pg, ['pages' => 'messages', 'q' => $q, 'statut' => $fStat]) ?>
    </div>
</div>
