<?php
// 🔐 DELETE SECURE (réservé au niveau 7, comme avant)
if (isset($_GET['supprimer']) && !empty($_GET['supprimer'])) {

    if ((int) ($_SESSION['niveau'] ?? 0) === 7) {

        $supprim_id = e($_GET['supprimer']);

        $supprimer = $bdd->prepare("DELETE FROM partenaire WHERE photo=?");
        $supprimer->execute([$supprim_id]);

        if ($supprimer->rowCount() == 1) {
            @unlink("./../media/images_part/" . $supprim_id);
        }
    }

    header("Location:?pages=partenaire");
    exit;
}

$reqpartenaire = $bdd->query("SELECT * FROM partenaire ORDER BY id_part DESC");
?>

<div class="container-fluid py-3">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold text-primary">
                <i class="bi bi-handshake"></i>
                Partenaires
            </h3>
            <small class="text-muted">Logos affichés sur le site public</small>
        </div>

        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPartenaireModal">
            <i class="bi bi-plus-circle"></i>
            Ajouter un partenaire
        </button>

    </div>

    <?php if (isset($sms)): ?>
        <div class="alert alert-success"><?= e($sms) ?></div>
    <?php endif; ?>

    <?php if (isset($erreur)): ?>
        <div class="alert alert-danger"><?= e($erreur) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4">

        <div class="card-header bg-dark text-white">
            <i class="bi bi-table"></i>
            Liste des partenaires
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0 datatable-auto">

                    <thead class="table-light">
                        <tr>
                            <th>Logo</th>
                            <th>Nom</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php while ($part = $reqpartenaire->fetch()): ?>

                        <tr>
                            <td>
                                <img src="./../media/images_part/<?= e($part['photo']) ?>"
                                     width="60" height="40"
                                     style="object-fit:contain"
                                     alt="">
                            </td>
                            <td class="fw-semibold"><?= e($part['nom_part']) ?></td>
                            <td class="text-end">
                                <?php if ((int) ($_SESSION['niveau'] ?? 0) === 7): ?>
                                    <a href="?pages=partenaire&supprimer=<?= urlencode($part['photo']) ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirmAction(event, 'Supprimer ce partenaire ? Cette action est irréversible.', 'Oui, supprimer')">
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
<div class="modal fade" id="addPartenaireModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Ajouter un partenaire</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form method="post" enctype="multipart/form-data">

                <?= csrf_field() ?>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Nom *</label>
                        <input type="text" name="nom_part" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Photo *</label>
                        <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" name="partenaire" class="btn btn-success">
                        <i class="bi bi-check-circle"></i>
                        Enregistrer
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
