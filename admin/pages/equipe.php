<?php
// 🔐 DELETE SECURE (réservé au niveau 7, comme avant)
if (isset($_GET['supprimer']) && !empty($_GET['supprimer'])) {

    if ((int) ($_SESSION['niveau'] ?? 0) === 7) {

        $supprim_id = e($_GET['supprimer']);

        $supprimer = $bdd->prepare("DELETE FROM equipes WHERE photo=?");
        $supprimer->execute([$supprim_id]);

        if ($supprimer->rowCount() == 1) {
            @unlink("./media/img_equipe/" . $supprim_id);
        }
    }

    header("Location:?pages=equipe");
    exit;
}

$reqequipe = $bdd->query("SELECT * FROM equipes ORDER BY id_eq DESC");
?>

<div class="container-fluid py-3">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold text-primary">
                <i class="bi bi-people-fill"></i>
                Membres de l'équipe
            </h3>
            <small class="text-muted">Gérer les membres affichés sur le site public</small>
        </div>

        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addEquipeModal">
            <i class="bi bi-plus-circle"></i>
            Ajouter un membre
        </button>

    </div>

    <!-- ALERTES -->
    <?php if (isset($errprs)): ?>
        <div class="alert alert-success"><?= e($errprs) ?></div>
    <?php endif; ?>

    <?php if (isset($erreur)): ?>
        <div class="alert alert-danger"><?= e($erreur) ?></div>
    <?php endif; ?>

    <!-- TABLE -->
    <div class="card border-0 shadow-sm rounded-4">

        <div class="card-header bg-dark text-white">
            <i class="bi bi-table"></i>
            Liste des membres
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0 datatable-auto">

                    <thead class="table-light">
                        <tr>
                            <th>Photo</th>
                            <th>Nom</th>
                            <th>Fonction</th>
                            <th>Téléphone</th>
                            <th>Email</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php while ($part = $reqequipe->fetch()): ?>

                        <tr>
                            <td>
                                <img src="./media/img_equipe/<?= e($part['photo']) ?>"
                                     width="42" height="42"
                                     class="rounded-circle border"
                                     style="object-fit:cover"
                                     alt="">
                            </td>
                            <td class="fw-semibold"><?= e($part['name']) ?></td>
                            <td><span class="badge bg-primary"><?= e($part['function']) ?></span></td>
                            <td><?= e($part['telephone']) ?></td>
                            <td><?= e($part['mail']) ?></td>
                            <td class="text-end">
                                <?php if ((int) ($_SESSION['niveau'] ?? 0) === 7): ?>
                                    <a href="?pages=equipe&supprimer=<?= urlencode($part['photo']) ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirmAction(event, 'Supprimer ce membre ? Cette action est irréversible.', 'Oui, supprimer')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">Lecture seule</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<!-- MODAL FORM -->
<div class="modal fade" id="addEquipeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Ajouter un membre</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form method="post" enctype="multipart/form-data">

                <?= csrf_field() ?>

                <div class="modal-body">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Nom et Prénom *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Téléphone *</label>
                            <input type="text" name="telephone" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="mail" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Fonction *</label>
                            <select name="function" class="form-select" required>
                                <option value="">Sélectionner...</option>
                                <option>Président</option>
                                <option>Vice-Président</option>
                                <option>Secrétaire Général</option>
                                <option>Rapporteur Général</option>
                                <option>Trésorier Général</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Photo *</label>
                            <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png" required>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Résumé</label>
                            <textarea name="resume" class="form-control" rows="3"></textarea>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" name="save" class="btn btn-success">
                        <i class="bi bi-check-circle"></i>
                        Enregistrer
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
