<?php
/**
 * Liste partagée des pages « Adhésions » (membres validés) et « Demandes » (adhésions en attente de paiement).
 *
 * RÈGLE : la validation d'une adhésion est AUTOMATIQUE et repose uniquement sur la confirmation de FlexPay
 * (includes/payment_helpers.php → payment_activate_membership). Aucun bouton ne permet de « valider » à la main :
 * l'ancien bouton « Confirmer » (qui ne faisait d'ailleurs rien) a été retiré.
 *
 * Variables attendues : $bdd, $adhMode ('valides' | 'demandes').
 * Une ligne par MEMBRE (plus une ligne par paiement), montants réellement encaissés, pagination, recherche.
 */
require_once __DIR__ . '/../../includes/membres.php';

$adhMode = $adhMode === 'demandes' ? 'demandes' : 'valides';
$adhPage = $adhMode === 'demandes' ? 'demandes' : 'adhesions';
$peutGerer = has_permission($bdd, 'membres.gerer');
membres_marquer_expires($bdd);

/* ---------- Action : suppression d'une demande ABANDONNÉE (jamais payée) ---------- */
// Un membre ayant au moins un paiement confirmé n'est jamais supprimé (historique financier) :
// on le suspend depuis la page « Membres ». Requête POST + jeton CSRF (contrôlé par admin_guard).
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'supprimer_demande') {
    if (!$peutGerer) {
        admin_forbid($bdd, 'modification', $adhPage);
    }
    $id = (int) ($_POST['id_ad'] ?? 0);
    $s = $bdd->prepare("SELECT a.id_ad, a.codes, a.passeport, a.cv,
                               (SELECT COUNT(*) FROM payments p WHERE p.id_ad = a.id_ad AND p.status = 'paid') AS nb_payes
                        FROM adhesion a WHERE a.id_ad = ?");
    $s->execute([$id]);
    $m = $s->fetch(PDO::FETCH_ASSOC);
    if (!$m) {
        admin_flash('Demande introuvable.', 'danger');
    } elseif ((int) $m['nb_payes'] > 0) {
        admin_flash('Ce membre a un paiement confirmé : il ne peut pas être supprimé (utilisez « Suspendre » dans Membres).', 'warning');
    } else {
        $bdd->beginTransaction();
        try {
            // Les tentatives de paiement non abouties restent tracées dans payment_logs
            $bdd->prepare("DELETE FROM payments WHERE id_ad = ? AND status <> 'paid'")->execute([$id]);
            $bdd->prepare('DELETE FROM parner WHERE id_exp = ?')->execute([$id]);
            $bdd->prepare('DELETE FROM encadreur WHERE id_exp = ?')->execute([$id]);
            $bdd->prepare("DELETE FROM adhesion WHERE id_ad = ? AND statut = 'en_attente'")->execute([$id]);
            $bdd->commit();
            require_once __DIR__ . '/../../includes/upload.php';
            upload_supprimer(dirname(__DIR__, 2) . '/media/passeport', $m['passeport']);
            upload_supprimer(dirname(__DIR__, 2) . '/media/cv', $m['cv']);
            audit_log($bdd, 'adhesion.demande_supprimee', 'adhesion', (string) $id, $m['codes']);
            admin_flash('Demande supprimée.');
        } catch (Throwable $e) {
            $bdd->rollBack();
            error_log('[demandes] suppression : ' . $e->getMessage());
            admin_flash('Suppression impossible.', 'danger');
        }
    }
    admin_redirect('pages=' . $adhPage);
}

/* ---------- Données ---------- */
$q = trim((string) ($_GET['q'] ?? ''));
$where = $adhMode === 'valides'
    ? "EXISTS (SELECT 1 FROM payments px WHERE px.id_ad = a.id_ad AND px.status = 'paid')"
    : "NOT EXISTS (SELECT 1 FROM payments px WHERE px.id_ad = a.id_ad AND px.status = 'paid')";
$params = [];
if ($q !== '') {
    $where .= ' AND (a.codes LIKE ? OR a.nom LIKE ? OR a.postnom LIKE ? OR a.prenom LIKE ? OR a.mail LIKE ? OR a.telephone LIKE ?)';
    $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
    array_push($params, $like, $like, $like, $like, $like, $like);
}
$c = $bdd->prepare("SELECT COUNT(*) FROM adhesion a WHERE $where");
$c->execute($params);
$totalAdh = (int) $c->fetchColumn();
$pg = admin_paginate($totalAdh, 15);

$s = $bdd->prepare("SELECT a.id_ad, a.codes, a.nom, a.postnom, a.prenom, a.mail, a.telephone, a.nationalite, a.civilite,
            a.passeport, a.cv, a.statut, a.date_echeance, a.dat_adhesion,
            pr.nom_p, tr.nom_tr, se.nom_sec, q.designation, g.nom_gd, g.prix AS prix_mensuel, co.nom_cot, co.mois,
            (SELECT COALESCE(SUM(p.montant), 0) FROM payments p WHERE p.id_ad = a.id_ad AND p.status = 'paid') AS total_paye,
            (SELECT p.status FROM payments p WHERE p.id_ad = a.id_ad ORDER BY p.id DESC LIMIT 1) AS dernier_statut,
            (SELECT MAX(p.created_at) FROM payments p WHERE p.id_ad = a.id_ad AND p.status = 'paid') AS date_validation
        FROM adhesion a
        LEFT JOIN provinces pr ON pr.id_p = a.province
        LEFT JOIN territoires tr ON tr.id_tr = a.territoire
        LEFT JOIN secteurs se ON se.id_sec = a.secteur
        LEFT JOIN qualites q ON q.id_qt = a.id_qt
        LEFT JOIN grades g ON g.id_gd = a.grade
        LEFT JOIN cotisation co ON co.id_cot = a.reglement
        WHERE $where
        ORDER BY a.dat_adhesion DESC, a.id_ad DESC
        LIMIT {$pg['offset']}, {$pg['per']}");
$s->execute($params);
$adherents = $s->fetchAll(PDO::FETCH_ASSOC);

$libPaiement = [
    null         => ['Aucun paiement lancé', 'secondary'],
    'pending'    => ['Paiement initié', 'warning'],
    'processing' => ['En attente de confirmation FlexPay', 'warning'],
    'expired'    => ['Confirmation en attente (délai dépassé)', 'warning'],
    'failed'     => ['Paiement échoué', 'danger'],
    'cancelled'  => ['Paiement annulé', 'secondary'],
    'paid'       => ['Payé', 'success'],
];
?>
<div class="container-fluid p-3 p-lg-4">

    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
        <div>
            <h2 class="h4 fw-bold mb-1"><?= $adhMode === 'valides' ? 'Membres validés' : 'Demandes d\'adhésion en attente' ?></h2>
            <p class="text-muted small mb-0">
                <i class="bi bi-shield-check text-success"></i>
                La validation est <strong>automatique</strong> dès que FlexPay confirme l'encaissement — aucune validation manuelle.
            </p>
        </div>
        <form method="get" class="d-flex gap-2" role="search">
            <input type="hidden" name="pages" value="<?= e($adhPage) ?>">
            <input type="search" name="q" value="<?= e($q) ?>" class="form-control form-control-sm" placeholder="Code, nom, e-mail, téléphone…" aria-label="Rechercher">
            <button class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
        </form>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-people"></i> <?= $totalAdh ?> <?= $adhMode === 'valides' ? 'membre(s) validé(s)' : 'demande(s)' ?></span>
            <a class="small" href="?pages=<?= $adhMode === 'valides' ? 'demandes' : 'adhesions' ?>"><?= $adhMode === 'valides' ? 'Voir les demandes en attente →' : 'Voir les membres validés →' ?></a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr><th>Membre</th><th>Catégorie / localisation</th><th>Statut</th><th class="text-end">Encaissé</th><th class="text-end">Documents</th></tr>
                </thead>
                <tbody>
                <?php if (!$adherents): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4"><?= $q !== '' ? 'Aucun résultat pour cette recherche.' : ($adhMode === 'valides' ? 'Aucun membre validé pour le moment.' : 'Aucune demande en attente.') ?></td></tr>
                <?php endif; ?>
                <?php foreach ($adherents as $m):
                    [$libStatut, $coulStatut] = membre_statut_libelle((string) $m['statut']);
                    [$libPay, $coulPay] = $libPaiement[$m['dernier_statut']] ?? [$m['dernier_statut'], 'light'];
                    $photo = basename((string) $m['passeport']);
                ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($photo !== ''): ?>
                                    <a href="../media/passeport/<?= e(rawurlencode($photo)) ?>" target="_blank" rel="noopener"><img src="../media/passeport/<?= e(rawurlencode($photo)) ?>" width="44" height="44" class="rounded-circle border object-fit-cover flex-shrink-0" style="background:#E3E6EC" alt="" title="Photo de <?= e($m['nom']) ?>" onerror="this.style.visibility='hidden'"></a>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-semibold"><?= e(trim($m['nom'] . ' ' . $m['postnom'] . ' ' . $m['prenom'])) ?></div>
                                    <div class="small text-muted"><?= e($m['codes']) ?> · <?= e($m['mail']) ?> · <?= e($m['telephone']) ?></div>
                                    <div class="small text-muted">Adhésion du <?= e(date('d/m/Y', strtotime((string) $m['dat_adhesion']))) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="small">
                            <div class="fw-semibold"><?= e(trim(($m['designation'] ?? '') . ' · ' . ($m['nom_gd'] ?? ''), ' ·')) ?></div>
                            <div class="text-muted"><?= e(($m['nom_cot'] ?? '—') . ' — ' . number_format((float) $m['prix_mensuel'] * max(1, (int) $m['mois']), 2, ',', ' ')) ?> $</div>
                            <div class="text-muted"><?= e(implode(' / ', array_filter([$m['nom_p'], $m['nom_tr'], $m['nom_sec']]))) ?></div>
                        </td>
                        <td>
                            <span class="badge text-bg-<?= e($coulStatut) ?>"><?= e($libStatut) ?></span>
                            <?php if ($adhMode === 'valides'): ?>
                                <div class="small text-muted mt-1"><i class="bi bi-patch-check text-success"></i> Validé automatiquement<?= $m['date_validation'] ? ' le ' . e(date('d/m/Y', strtotime((string) $m['date_validation']))) : '' ?></div>
                                <?php if ($m['date_echeance']): ?><div class="small text-muted">Échéance : <?= e(date('d/m/Y', strtotime((string) $m['date_echeance']))) ?></div><?php endif; ?>
                            <?php else: ?>
                                <div class="small mt-1"><span class="badge rounded-pill text-bg-<?= e($coulPay) ?> bg-opacity-75"><?= e($libPay) ?></span></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-end fw-semibold text-nowrap"><?= e(number_format((float) $m['total_paye'], 2, ',', ' ')) ?> $</td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener" href="pages/print/print_adherer.php?cod=<?= (int) $m['id_ad'] ?>" title="Fiche d'adhésion"><i class="bi bi-printer"></i> Fiche</a>
                            <?php if (basename((string) $m['cv']) !== ''): ?>
                                <a class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener" href="../media/cv/<?= e(rawurlencode(basename((string) $m['cv']))) ?>" title="CV"><i class="bi bi-file-earmark-text"></i> CV</a>
                            <?php endif; ?>
                            <?php if ($adhMode === 'demandes' && $peutGerer): ?>
                                <form method="post" class="d-inline js-confirm" data-confirm="Supprimer cette demande abandonnée ? (aucun paiement confirmé)">
                                    <input type="hidden" name="action" value="supprimer_demande">
                                    <input type="hidden" name="id_ad" value="<?= (int) $m['id_ad'] ?>">
                                    <button class="btn btn-sm btn-outline-danger" aria-label="Supprimer la demande"><i class="bi bi-trash"></i></button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= admin_pagination_html($pg, ['pages' => $adhPage, 'q' => $q]) ?>
    </div>
</div>
