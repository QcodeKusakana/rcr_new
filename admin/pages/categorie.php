<?php

// 🔐 DELETE SECURE
if (isset($_GET['supprimer'])) {

    $id = (int) $_GET['supprimer'];

    if ($_SESSION['niveau'] == 7) {

        $stmt = $bdd->prepare("DELETE FROM categorie WHERE id_cat=?");
        $stmt->execute([$id]);

    }

    header("Location:?pages=categorie");
    exit;
}

?>

<div class="container-fluid">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold text-primary">
                <i class="bi bi-tags-fill"></i>
                Gestion des catégories
            </h3>

            <small class="text-muted">
                Créer et organiser les contenus
            </small>

        </div>

        <!-- OPEN MODAL -->
        <button class="btn btn-success"
                data-bs-toggle="modal"
                data-bs-target="#categoryModal">

            <i class="bi bi-plus-circle"></i>
            Ajouter catégorie

        </button>

    </div>

    <!-- ALERTS -->
    <?php if(isset($smss)): ?>
        <div class="alert alert-success"><?= e((string) $smss) ?></div>
    <?php endif; ?>

    <?php if(isset($erreurs)): ?>
        <div class="alert alert-danger"><?= e((string) $erreurs) ?></div>
    <?php endif; ?>

    <div class="row">

        <div class="col-lg-12">

            <div class="card border-0 shadow-sm rounded-4">

                <div class="card-header bg-dark text-white">
                    <i class="bi bi-list-ul"></i>
                    Liste des catégories
                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0 datatable-auto">

                            <thead class="table-light">

                                <tr>
                                    <th>#ID</th>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th class="text-end">Actions</th>
                                </tr>

                            </thead>

                            <tbody>

                            <?php
                            $reqcategorie = $bdd->query("SELECT * FROM categorie ORDER BY id_cat DESC");

                            while($cat = $reqcategorie->fetch()):
                            ?>

                                <tr>

                                    <td class="fw-bold">
                                        <?= $cat['id_cat'] ?>
                                    </td>

                                    <td>
                                        <i class="bi bi-tag text-primary me-1"></i>
                                        <?= htmlspecialchars($cat['nom_cat']) ?>
                                    </td>

                                    <td class="text-muted">
                                        <?= e(mb_strimwidth(html_entity_decode((string) $cat['resume'], ENT_QUOTES, "UTF-8"), 0, 60, "…")) ?>
                                    </td>

                                    <td class="text-end">

                                        <?php if($_SESSION['niveau'] == 7): ?>

                                            <!-- EDIT -->
                                            <a href="?pages=modify_cat&edit=<?= $cat['id_cat'] ?>"
                                               class="btn btn-sm btn-warning">

                                                <i class="bi bi-pencil-square"></i>

                                            </a>

                                            <!-- DELETE -->
                                            <a href="?pages=categorie&supprimer=<?= $cat['id_cat'] ?>"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirmAction(event, 'Supprimer cette catégorie ? Cette action est irréversible.', 'Oui, supprimer')">

                                                <i class="bi bi-trash"></i>

                                            </a>

                                        <?php else: ?>

                                            <span class="text-muted small">
                                                Lecture seule
                                            </span>

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

    </div>

</div>

<!-- MODAL FORM -->
<div class="modal fade" id="categoryModal" tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title">
                    <i class="bi bi-plus-circle"></i>
                    Nouvelle catégorie
                </h5>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>

            </div>

            <form method="post">

                <?= csrf_field() ?>

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">
                            Nom catégorie
                        </label>

                        <input type="text"
                               name="nom_cat"
                               class="form-control">

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Description
                        </label>

                        <textarea name="resume"
                                  class="form-control"
                                  rows="4"></textarea>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="submit"
                            name="categorie"
                            class="btn btn-success">

                        <i class="bi bi-check-circle"></i>
                        Enregistrer

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>