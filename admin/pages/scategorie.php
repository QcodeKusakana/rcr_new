<?php
// ⚠️ AVERTISSEMENT (détecté lors de l'audit de sécurité, non corrigé sans
// votre accord) : la table `sous_categorie` du schéma actuel ne possède
// que les colonnes `id` et `nom`, alors que ce module lit/écrit des
// colonnes `id_scat` et `id_cat` qui n'existent pas. En pratique, la liste
// ci-dessous restera donc vide et les ajouts échoueront silencieusement
// tant que la structure de la table n'aura pas été mise à jour (ou cette
// fonctionnalité retirée) — voir le rapport d'audit.
?>

<div class="container-fluid py-3">

    <div class="mb-4">
        <h3 class="fw-bold text-primary">
            <i class="bi bi-diagram-2"></i>
            Sous-catégories
        </h3>
    </div>

    <div class="alert alert-warning" data-no-toast="1">
        <i class="bi bi-exclamation-triangle-fill"></i>
        Cette fonctionnalité a une incohérence de base de données détectée lors de l'audit
        (colonnes manquantes dans <code>sous_categorie</code>) : les ajouts ci-dessous ne
        seront pas enregistrés tant que ce n'est pas corrigé. Voir le rapport d'audit pour
        la marche à suivre.
    </div>

    <?php if (isset($smss)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($smss) ?></div>
    <?php endif; ?>

    <?php if (isset($erreurs)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erreurs) ?></div>
    <?php endif; ?>

    <div class="row g-4">

        <div class="col-lg-5">

            <div class="card border-0 shadow-sm rounded-4">

                <div class="card-header bg-dark text-white">
                    <i class="bi bi-plus-circle"></i>
                    Ajouter une sous-catégorie
                </div>

                <div class="card-body">

                    <form method="post">

                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label">Nom</label>
                            <input type="text" name="nom" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catégorie parente</label>
                            <select class="form-select" name="id_cat">
                                <option value="">Sélectionner une catégorie</option>
                                <?php while ($d = $reqcate->fetch()): ?>
                                    <option value="<?= $d['id_cat'] ?>"><?= htmlspecialchars($d['nom_cat']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <button type="submit" name="scategorie" class="btn btn-success">
                            <i class="bi bi-check-circle"></i>
                            Ajouter
                        </button>

                    </form>

                </div>

            </div>

        </div>

        <div class="col-lg-7">

            <div class="card border-0 shadow-sm rounded-4">

                <div class="card-header bg-dark text-white">
                    <i class="bi bi-table"></i>
                    Liste des sous-catégories
                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#ID</th>
                                    <th>Nom</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $reqcategorie = $bdd->query("SELECT * FROM sous_categorie ORDER BY id DESC");
                            while ($cat = $reqcategorie->fetch()):
                            ?>
                                <tr>
                                    <td><?= $cat['id'] ?></td>
                                    <td><?= htmlspecialchars($cat['nom']) ?></td>
                                    <td class="text-end text-muted small">—</td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
