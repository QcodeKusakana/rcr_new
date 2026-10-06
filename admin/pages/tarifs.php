<?php
/** Administration → Cotisations → Tarifs. Source unique des prix : tables grades (prix mensuel) et cotisation (périodes). */
require_once __DIR__ . '/../../includes/admin_guard.php';
require_permission($bdd, 'tarifs.gerer');

if (admin_post_guard($bdd, 'tarifs.gerer')) {
    $action = (string) ($_POST['action'] ?? '');
    $idAdm  = (int) $_SESSION['id_adm'];
    if ($action === 'grade') {
        $prix = str_replace(',', '.', trim((string) ($_POST['prix'] ?? '')));
        if (!preg_match('/^\d{1,6}(\.\d{1,2})?$/', $prix) || (float) $prix <= 0) {
            admin_flash('Prix invalide : saisissez un nombre positif (ex. 25 ou 12.50).', 'danger');
        } elseif (tarifs_modifier_grade($bdd, (int) ($_POST['id_gd'] ?? 0), (float) $prix, isset($_POST['actif']), $idAdm)) {
            admin_flash('Tarif enregistré. Il s\'applique aux nouvelles souscriptions ; les paiements passés restent inchangés.');
        } else {
            admin_flash('Modification impossible.', 'danger');
        }
    } elseif ($action === 'periode') {
        $ok = tarifs_modifier_periode($bdd, (int) ($_POST['id_cot'] ?? 0), (int) ($_POST['mois'] ?? 0), isset($_POST['actif']));
        admin_flash($ok ? 'Période enregistrée.' : 'Période invalide (1 à 60 mois).', $ok ? 'success' : 'danger');
    } elseif ($action === 'nouveau_grade') {
        $nom = trim((string) ($_POST['nom_gd'] ?? ''));
        $prix = str_replace(',', '.', trim((string) ($_POST['prix'] ?? '')));
        $idQt = (int) ($_POST['id_qt'] ?? 0);
        $okQt = (bool) $bdd->query('SELECT COUNT(*) FROM qualites WHERE id_qt = ' . $idQt)->fetchColumn();
        if ($nom === '' || mb_strlen($nom) > 50 || !$okQt || !preg_match('/^\d{1,6}(\.\d{1,2})?$/', $prix) || (float) $prix <= 0) {
            admin_flash('Données invalides pour le nouveau grade.', 'danger');
        } else {
            $s = $bdd->prepare('SELECT COUNT(*) FROM grades WHERE id_qt = ? AND nom_gd = ? AND ancien = 0');
            $s->execute([$idQt, $nom]);
            if ((int) $s->fetchColumn() > 0) {
                admin_flash('Ce grade existe déjà dans cette catégorie.', 'warning');
            } else {
                $bdd->prepare('INSERT INTO grades (nom_gd, prix, id_qt, actif, ordre, ancien) VALUES (?, ?, ?, 1, (SELECT COALESCE(MAX(g2.ordre),0)+1 FROM (SELECT ordre FROM grades WHERE id_qt = ?) g2), 0)')
                    ->execute([$nom, round((float) $prix, 2), $idQt, $idQt]);
                audit_log($bdd, 'tarif.creer', 'grades', (string) $bdd->lastInsertId(), ['nom' => $nom, 'prix' => $prix, 'id_qt' => $idQt]);
                admin_flash('Grade ajouté.');
            }
        }
    }
    admin_redirect('pages=tarifs');
}

$grille   = tarifs_grille_admin($bdd);
$periodes = tarifs_periodes($bdd, false);
$cats     = tarifs_categories($bdd);
$hist     = $bdd->query('SELECT h.*, g.nom_gd, q.designation, a.pseudo FROM grades_historique h
                         LEFT JOIN grades g ON g.id_gd = h.id_gd LEFT JOIN qualites q ON q.id_qt = g.id_qt LEFT JOIN admin a ON a.id_adm = h.id_adm
                         ORDER BY h.id DESC LIMIT 20')->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container-fluid p-3 p-lg-4">
    <?= admin_flash_render() ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">Grille tarifaire (USD) — le prix mensuel est modifiable, les autres périodes en découlent</div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Catégorie</th><th>Grade</th><th style="width:140px">Mensuel</th>
                        <?php foreach ($periodes as $p): if ((int) $p['mois'] === 1) continue; ?><th class="text-end"><?= e($p['nom_cot']) ?></th><?php endforeach; ?>
                        <th>Actif</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($grille as $g): ?>
                    <tr class="<?= $g['actif'] ? '' : 'table-secondary' ?>">
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="grade">
                            <input type="hidden" name="id_gd" value="<?= (int) $g['id_gd'] ?>">
                            <td>Membre <?= e($g['designation']) ?></td>
                            <td class="fw-semibold"><?= e($g['nom_gd']) ?></td>
                            <td><input class="form-control form-control-sm" name="prix" inputmode="decimal" value="<?= e(rtrim(rtrim(number_format((float) $g['prix'], 2, '.', ''), '0'), '.')) ?>" required></td>
                            <?php foreach ($periodes as $p): if ((int) $p['mois'] === 1) continue; ?>
                                <td class="text-end"><?= e(number_format((float) ($g['montants'][$p['nom_cot']] ?? 0), 2, ',', ' ')) ?></td>
                            <?php endforeach; ?>
                            <td><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="actif" value="1" <?= $g['actif'] ? 'checked' : '' ?>></div></td>
                            <td><button class="btn btn-sm btn-primary">Enregistrer</button></td>
                        </form>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Périodes de paiement (multiplicateur du tarif mensuel)</div>
                <div class="table-responsive"><table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th>Période</th><th style="width:130px">Mois</th><th>Active</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($periodes as $p): ?>
                        <tr>
                            <form method="post">
                                <?= csrf_field() ?><input type="hidden" name="action" value="periode"><input type="hidden" name="id_cot" value="<?= (int) $p['id_cot'] ?>">
                                <td><?= e($p['nom_cot']) ?></td>
                                <td><input type="number" min="1" max="60" class="form-control form-control-sm" name="mois" value="<?= (int) $p['mois'] ?>"></td>
                                <td><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="actif" value="1" <?= $p['actif'] ? 'checked' : '' ?>></div></td>
                                <td><button class="btn btn-sm btn-primary">OK</button></td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                    </tbody></table></div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Ajouter un grade</div>
                <form method="post" class="card-body row g-2">
                    <?= csrf_field() ?><input type="hidden" name="action" value="nouveau_grade">
                    <div class="col-12"><label class="form-label small">Catégorie</label>
                        <select name="id_qt" class="form-select"><?php foreach ($cats as $c): ?><option value="<?= (int) $c['id_qt'] ?>">Membre <?= e($c['designation']) ?></option><?php endforeach; ?></select></div>
                    <div class="col-7"><label class="form-label small">Nom du grade</label><input class="form-control" name="nom_gd" maxlength="50" required></div>
                    <div class="col-5"><label class="form-label small">Prix mensuel (USD)</label><input class="form-control" name="prix" inputmode="decimal" required></div>
                    <div class="col-12"><button class="btn btn-success">Ajouter</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white fw-semibold">Historique des modifications (20 dernières)</div>
        <div class="table-responsive"><table class="table table-sm align-middle mb-0">
            <thead class="table-light"><tr><th>Date</th><th>Grade</th><th>Ancien prix</th><th>Nouveau prix</th><th>Actif</th><th>Par</th></tr></thead>
            <tbody>
            <?php foreach ($hist as $h): ?>
                <tr>
                    <td><?= e(date('d/m/Y H:i', strtotime((string) $h['cree_le']))) ?></td>
                    <td>Membre <?= e((string) $h['designation']) ?> — <?= e((string) $h['nom_gd']) ?></td>
                    <td><?= e((string) $h['ancien_prix']) ?></td><td><?= e((string) $h['nouveau_prix']) ?></td>
                    <td><?= (int) $h['ancien_actif'] ?> → <?= (int) $h['nouveau_actif'] ?></td>
                    <td><?= e((string) ($h['pseudo'] ?? '—')) ?></td>
                </tr>
            <?php endforeach; if (!$hist): ?><tr><td colspan="6" class="text-center text-muted py-3">Aucune modification enregistrée.</td></tr><?php endif; ?>
            </tbody></table></div>
    </div>
</div>
