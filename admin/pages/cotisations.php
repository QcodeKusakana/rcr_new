<?php
/**
 * Administration → Cotisations : paiements d'adhésion/cotisation avec la situation du membre.
 * Filtres : état du membre (actif / expiré / en attente) et état du paiement (payé / impayé).
 * « Impayé » = paiement échoué, annulé, expiré ou encore en attente.
 */
require_once __DIR__ . '/../../includes/admin_guard.php';
require_permission($bdd, 'paiements.voir');
membres_marquer_expires($bdd);

$ST = ['pending' => 'En attente', 'processing' => 'En cours', 'paid' => 'Payé', 'failed' => 'Échoué', 'cancelled' => 'Annulé', 'expired' => 'Expiré'];
$etatM  = in_array($_GET['membre'] ?? '', ['actif', 'expire', 'en_attente', 'suspendu'], true) ? $_GET['membre'] : '';
$etatP  = in_array($_GET['paiement'] ?? '', ['paye', 'impaye'], true) ? $_GET['paiement'] : '';
$idCot  = (int) ($_GET['id_cot'] ?? 0);
$idQt   = (int) ($_GET['id_qt'] ?? 0);
$q      = trim((string) ($_GET['q'] ?? ''));

$where = []; $par = [];
if ($etatM !== '') { $where[] = 'a.statut = ?'; $par[] = $etatM; }
if ($etatP === 'paye') { $where[] = "p.status = 'paid'"; } elseif ($etatP === 'impaye') { $where[] = "p.status <> 'paid'"; }
if ($idCot > 0) { $where[] = 'p.id_cot = ?'; $par[] = $idCot; }
if ($idQt > 0)  { $where[] = 'a.id_qt = ?';  $par[] = $idQt; }
if ($q !== '')  { $where[] = '(a.codes LIKE ? OR a.nom LIKE ? OR a.prenom LIKE ? OR a.mail LIKE ?)'; $l = '%' . addcslashes($q, '%_\\') . '%'; array_push($par, $l, $l, $l, $l); }
$w = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$from = "FROM payments p JOIN adhesion a ON a.id_ad = p.id_ad
         LEFT JOIN qualites qt ON qt.id_qt = a.id_qt LEFT JOIN grades g ON g.id_gd = a.grade LEFT JOIN cotisation c ON c.id_cot = p.id_cot";
$sel = "SELECT p.id, p.reference, p.montant, p.devise, p.status, p.type_transaction, p.created_at, p.periode_debut, p.periode_fin,
               a.codes, a.nom, a.postnom, a.prenom, a.statut AS statut_membre, a.date_echeance, qt.designation, g.nom_gd, c.nom_cot";

if (isset($_GET['export'])) {
    $s = $bdd->prepare("$sel $from $w ORDER BY p.created_at DESC LIMIT 50000"); $s->execute($par);
    $l = [];
    foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $l[] = [$r['codes'], trim($r['nom'] . ' ' . $r['postnom'] . ' ' . $r['prenom']), $r['designation'], $r['nom_gd'], $r['nom_cot'], $r['montant'], $r['devise'],
                $r['periode_debut'], $r['periode_fin'], $r['created_at'], $r['date_echeance'], $r['statut_membre'], $ST[$r['status']] ?? $r['status']];
    }
    audit_log($bdd, 'export.cotisations', 'payments', '', ['lignes' => count($l)]);
    admin_csv_send('cotisations_' . date('Ymd') . '.csv', ['Code', 'Membre', 'Catégorie', 'Grade', 'Fréquence', 'Montant', 'Devise', 'Début', 'Fin', 'Date paiement', 'Prochaine échéance', 'Statut membre', 'Statut paiement'], $l);
}

$c = $bdd->prepare("SELECT COUNT(*) $from $w"); $c->execute($par);
$pg = admin_paginate((int) $c->fetchColumn(), 25);
$s = $bdd->prepare("$sel $from $w ORDER BY p.created_at DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}"); $s->execute($par);
$rows = $s->fetchAll(PDO::FETCH_ASSOC);
$cats = tarifs_categories($bdd); $periodes = tarifs_periodes($bdd, false);
$keep = array_filter(['pages' => 'cotisations', 'membre' => $etatM, 'paiement' => $etatP, 'id_cot' => $idCot ?: '', 'id_qt' => $idQt ?: '', 'q' => $q], fn($v) => $v !== '');
?>
<div class="container-fluid p-3 p-lg-4">
    <form class="card border-0 shadow-sm mb-3" method="get"><input type="hidden" name="pages" value="cotisations">
        <div class="card-body row g-2 align-items-end">
            <div class="col-6 col-md-2"><label class="form-label small">Membre</label><select name="membre" class="form-select form-select-sm"><option value="">Tous</option>
                <?php foreach (['actif' => 'Actif', 'expire' => 'Expiré', 'en_attente' => 'En attente', 'suspendu' => 'Suspendu'] as $k => $lab): ?><option value="<?= $k ?>" <?= $etatM === $k ? 'selected' : '' ?>><?= $lab ?></option><?php endforeach; ?></select></div>
            <div class="col-6 col-md-2"><label class="form-label small">Paiement</label><select name="paiement" class="form-select form-select-sm"><option value="">Tous</option>
                <option value="paye" <?= $etatP === 'paye' ? 'selected' : '' ?>>Payé</option><option value="impaye" <?= $etatP === 'impaye' ? 'selected' : '' ?>>Impayé</option></select></div>
            <div class="col-6 col-md-2"><label class="form-label small">Catégorie</label><select name="id_qt" class="form-select form-select-sm"><option value="">Toutes</option>
                <?php foreach ($cats as $ct): ?><option value="<?= (int) $ct['id_qt'] ?>" <?= $idQt === (int) $ct['id_qt'] ? 'selected' : '' ?>><?= e($ct['designation']) ?></option><?php endforeach; ?></select></div>
            <div class="col-6 col-md-2"><label class="form-label small">Fréquence</label><select name="id_cot" class="form-select form-select-sm"><option value="">Toutes</option>
                <?php foreach ($periodes as $pe): ?><option value="<?= (int) $pe['id_cot'] ?>" <?= $idCot === (int) $pe['id_cot'] ? 'selected' : '' ?>><?= e($pe['nom_cot']) ?></option><?php endforeach; ?></select></div>
            <div class="col-12 col-md-4"><label class="form-label small">Code, nom ou e-mail</label><input name="q" value="<?= e($q) ?>" class="form-control form-control-sm"></div>
            <div class="col-12 d-flex gap-2"><button class="btn btn-sm btn-primary">Filtrer</button><a class="btn btn-sm btn-outline-secondary" href="?pages=cotisations">Réinitialiser</a>
                <a class="btn btn-sm btn-outline-success ms-auto" href="?<?= e(http_build_query($keep + ['export' => 1])) ?>"><i class="bi bi-download"></i> CSV</a></div>
        </div></form>
    <div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>Membre</th><th>Catégorie / grade</th><th>Fréquence</th><th class="text-end">Montant</th><th>Période</th><th>Payé le</th><th>Prochaine échéance</th><th>Statut</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): [$lm, $cm] = membre_statut_libelle((string) $r['statut_membre']); ?>
            <tr>
                <td><?= e(trim($r['nom'] . ' ' . $r['postnom'] . ' ' . $r['prenom'])) ?><div class="small text-muted"><?= e((string) $r['codes']) ?></div></td>
                <td><?= e((string) $r['designation']) ?><div class="small text-muted"><?= e((string) $r['nom_gd']) ?></div></td>
                <td><?= e((string) ($r['nom_cot'] ?? '—')) ?></td>
                <td class="text-end text-nowrap"><?= e(number_format((float) $r['montant'], 2, ',', ' ')) ?> <?= e((string) $r['devise']) ?></td>
                <td class="text-nowrap small"><?= $r['periode_debut'] ? e(date('d/m/Y', strtotime($r['periode_debut']))) . ' → ' . e(date('d/m/Y', strtotime($r['periode_fin']))) : '—' ?></td>
                <td class="text-nowrap small"><?= e(date('d/m/Y', strtotime((string) $r['created_at']))) ?></td>
                <td class="text-nowrap"><?= $r['date_echeance'] ? e(date('d/m/Y', strtotime($r['date_echeance']))) : '—' ?> <span class="badge text-bg-<?= $cm ?>"><?= e($lm) ?></span></td>
                <td><span class="badge text-bg-<?= $r['status'] === 'paid' ? 'success' : ($r['status'] === 'failed' || $r['status'] === 'cancelled' ? 'danger' : 'warning') ?>"><?= e($ST[$r['status']] ?? $r['status']) ?></span></td>
            </tr>
        <?php endforeach; if (!$rows): ?><tr><td colspan="8" class="text-center text-muted py-4">Aucune cotisation.</td></tr><?php endif; ?>
        </tbody></table></div><?= admin_pagination_html($pg, $keep) ?></div>
</div>
