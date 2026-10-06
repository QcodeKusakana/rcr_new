<?php
/**
 * Administration → Membres : recherche, filtres, export, suspension / réactivation.
 * Consultation : membres.voir. Modification du statut : membres.gerer (POST + CSRF, journalisé).
 */
require_once __DIR__ . '/../../includes/admin_guard.php';
require_permission($bdd, 'membres.voir');
membres_marquer_expires($bdd);

if (admin_post_guard($bdd, 'membres.gerer')) {
    $id  = (int) ($_POST['id_ad'] ?? 0);
    $act = (string) ($_POST['action'] ?? '');
    $s = $bdd->prepare('SELECT statut, date_echeance, codes FROM adhesion WHERE id_ad = ?');
    $s->execute([$id]);
    $m = $s->fetch(PDO::FETCH_ASSOC);
    if (!$m) {
        admin_flash('Membre introuvable.', 'danger');
    } elseif ($act === 'suspendre') {
        $bdd->prepare("UPDATE adhesion SET statut = 'suspendu' WHERE id_ad = ?")->execute([$id]);
        audit_log($bdd, 'membre.suspendre', 'adhesion', (string) $id, ['codes' => $m['codes'], 'avant' => $m['statut']]);
        admin_flash('Membre suspendu.');
    } elseif ($act === 'reactiver') {
        // retour au statut cohérent avec l'échéance (jamais « actif » sans cotisation à jour)
        $nouveau = empty($m['date_echeance']) ? 'en_attente' : ($m['date_echeance'] >= date('Y-m-d') ? 'actif' : 'expire');
        $bdd->prepare('UPDATE adhesion SET statut = ? WHERE id_ad = ?')->execute([$nouveau, $id]);
        audit_log($bdd, 'membre.reactiver', 'adhesion', (string) $id, ['codes' => $m['codes'], 'nouveau' => $nouveau]);
        admin_flash('Membre réactivé (statut : ' . membre_statut_libelle($nouveau)[0] . ').');
    }
    admin_redirect('pages=membres&' . http_build_query(array_intersect_key($_GET, array_flip(['statut', 'id_qt', 'province', 'q', 'p']))));
}

$statut = in_array($_GET['statut'] ?? '', ['actif', 'expire', 'en_attente', 'suspendu'], true) ? $_GET['statut'] : '';
$idQt   = (int) ($_GET['id_qt'] ?? 0);
$prov   = (int) ($_GET['province'] ?? 0);
$q      = trim((string) ($_GET['q'] ?? ''));
$where = []; $par = [];
if ($statut !== '') { $where[] = 'a.statut = ?'; $par[] = $statut; }
if ($idQt > 0)      { $where[] = 'a.id_qt = ?';  $par[] = $idQt; }
if ($prov > 0)      { $where[] = 'a.province = ?'; $par[] = $prov; }
if ($q !== '')      { $where[] = '(a.codes LIKE ? OR a.nom LIKE ? OR a.postnom LIKE ? OR a.prenom LIKE ? OR a.mail LIKE ? OR a.telephone LIKE ?)';
                      $l = '%' . addcslashes($q, '%_\\') . '%'; array_push($par, $l, $l, $l, $l, $l, $l); }
$w = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$from = 'FROM adhesion a LEFT JOIN qualites qt ON qt.id_qt = a.id_qt LEFT JOIN grades g ON g.id_gd = a.grade LEFT JOIN provinces pr ON pr.id_p = a.province';
$sel  = 'SELECT a.id_ad, a.codes, a.nom, a.postnom, a.prenom, a.mail, a.telephone, a.statut, a.date_echeance, a.dat_adhesion, qt.designation, g.nom_gd, pr.nom_p';

if (isset($_GET['export'])) {
    $s = $bdd->prepare("$sel $from $w ORDER BY a.id_ad DESC LIMIT 50000"); $s->execute($par);
    $l = [];
    foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $l[] = [$r['codes'], $r['nom'], $r['postnom'], $r['prenom'], $r['mail'], $r['telephone'], $r['designation'], $r['nom_gd'], $r['nom_p'], $r['statut'], $r['date_echeance'], $r['dat_adhesion']];
    }
    audit_log($bdd, 'export.membres', 'adhesion', '', ['lignes' => count($l)]);
    admin_csv_send('membres_' . date('Ymd') . '.csv', ['Code', 'Nom', 'Post-nom', 'Prénom', 'E-mail', 'Téléphone', 'Catégorie', 'Grade', 'Province', 'Statut', 'Échéance', 'Adhésion'], $l);
}

$c = $bdd->prepare("SELECT COUNT(*) $from $w"); $c->execute($par);
$pg = admin_paginate((int) $c->fetchColumn(), 25);
$s = $bdd->prepare("$sel $from $w ORDER BY a.id_ad DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}"); $s->execute($par);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);
$cats = tarifs_categories($bdd);
$provs = $bdd->query('SELECT id_p, nom_p FROM provinces ORDER BY nom_p')->fetchAll(PDO::FETCH_ASSOC);
$keep = array_filter(['pages' => 'membres', 'statut' => $statut, 'id_qt' => $idQt ?: '', 'province' => $prov ?: '', 'q' => $q], fn($v) => $v !== '');
$peutGerer = has_permission($bdd, 'membres.gerer');
?>
<div class="container-fluid p-3 p-lg-4">
    <?= admin_flash_render() ?>
    <form class="card border-0 shadow-sm mb-3" method="get"><input type="hidden" name="pages" value="membres">
        <div class="card-body row g-2 align-items-end">
            <div class="col-6 col-md-2"><label class="form-label small">Statut</label><select name="statut" class="form-select form-select-sm"><option value="">Tous</option>
                <?php foreach (['actif' => 'Actif', 'expire' => 'Expiré', 'en_attente' => 'En attente', 'suspendu' => 'Suspendu'] as $k => $lab): ?><option value="<?= $k ?>" <?= $statut === $k ? 'selected' : '' ?>><?= $lab ?></option><?php endforeach; ?></select></div>
            <div class="col-6 col-md-2"><label class="form-label small">Catégorie</label><select name="id_qt" class="form-select form-select-sm"><option value="">Toutes</option>
                <?php foreach ($cats as $ct): ?><option value="<?= (int) $ct['id_qt'] ?>" <?= $idQt === (int) $ct['id_qt'] ? 'selected' : '' ?>><?= e($ct['designation']) ?></option><?php endforeach; ?></select></div>
            <div class="col-6 col-md-3"><label class="form-label small">Province</label><select name="province" class="form-select form-select-sm"><option value="">Toutes</option>
                <?php foreach ($provs as $pv): ?><option value="<?= (int) $pv['id_p'] ?>" <?= $prov === (int) $pv['id_p'] ? 'selected' : '' ?>><?= e($pv['nom_p']) ?></option><?php endforeach; ?></select></div>
            <div class="col-6 col-md-3"><label class="form-label small">Recherche</label><input name="q" value="<?= e($q) ?>" class="form-control form-control-sm" placeholder="code, nom, e-mail, téléphone"></div>
            <div class="col-12 col-md-2 d-flex gap-2"><button class="btn btn-sm btn-primary">Filtrer</button>
                <a class="btn btn-sm btn-outline-success" href="?<?= e(http_build_query($keep + ['export' => 1])) ?>"><i class="bi bi-download"></i> CSV</a></div>
        </div></form>
    <div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>Code</th><th>Membre</th><th>Catégorie / grade</th><th>Province</th><th>Échéance</th><th>Statut</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): [$lm, $cm] = membre_statut_libelle((string) $r['statut']); ?>
            <tr>
                <td><small><?= e((string) $r['codes']) ?></small></td>
                <td><?= e(trim($r['nom'] . ' ' . $r['postnom'] . ' ' . $r['prenom'])) ?><div class="small text-muted"><?= e((string) $r['mail']) ?> · <?= e((string) $r['telephone']) ?></div></td>
                <td><?= e((string) $r['designation']) ?><div class="small text-muted"><?= e((string) $r['nom_gd']) ?></div></td>
                <td><?= e((string) $r['nom_p']) ?></td>
                <td class="text-nowrap"><?= $r['date_echeance'] ? e(date('d/m/Y', strtotime($r['date_echeance']))) : '—' ?></td>
                <td><span class="badge text-bg-<?= $cm ?>"><?= e($lm) ?></span></td>
                <td class="text-nowrap">
                    <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" href="pages/print/print_adherer.php?cod=<?= (int) $r['id_ad'] ?>">Fiche</a>
                    <?php if ($peutGerer): ?>
                        <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="id_ad" value="<?= (int) $r['id_ad'] ?>">
                        <?php if ($r['statut'] === 'suspendu'): ?><button name="action" value="reactiver" class="btn btn-sm btn-outline-success">Réactiver</button>
                        <?php else: ?><button name="action" value="suspendre" class="btn btn-sm btn-outline-danger" onclick="return confirm('Suspendre ce membre ?')">Suspendre</button><?php endif; ?></form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; if (!$rows): ?><tr><td colspan="7" class="text-center text-muted py-4">Aucun membre.</td></tr><?php endif; ?>
        </tbody></table></div><?= admin_pagination_html($pg, $keep) ?></div>
</div>
