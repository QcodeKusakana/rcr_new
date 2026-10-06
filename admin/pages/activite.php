<?php

// 🔐 DELETE SECURE
if (isset($_GET['supprimer'])) {

    $photo = htmlspecialchars($_GET['supprimer']);

    $stmt = $bdd->prepare("DELETE FROM activite WHERE photo=?");
    $stmt->execute([$photo]);

    if ($stmt->rowCount() == 1) {
        @unlink("./../media/images_activ/" . $photo);
    }

    header("Location:?pages=activite");
    exit;
}

?>

<div class="container-fluid">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold text-primary">
                <i class="bi bi-newspaper"></i>
                Gestion des articles
            </h3>
            <small class="text-muted">
                Publier et gérer les contenus
            </small>
        </div>

        <!-- BUTTON OPEN MODAL -->
        <button class="btn btn-success"
                data-bs-toggle="modal"
                data-bs-target="#addArticleModal">

            <i class="bi bi-plus-circle"></i>
            Nouvel article
        </button>

    </div>

    <!-- ALERT -->
    <?php if(isset($sms)): ?>
        <div class="alert alert-success"><?= e((string) $sms) ?></div>
    <?php endif; ?>

    <?php if(isset($erreur)): ?>
        <div class="alert alert-danger"><?= e((string) $erreur) ?></div>
    <?php endif; ?>

    <!-- TABLE -->
    <div class="card shadow-sm border-0 rounded-4">

        <div class="card-header bg-dark text-white">
            <i class="bi bi-table"></i>
            Liste des articles
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>Titre</th>
                            <th>Catégorie</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php while($activ = $les_activite->fetch()): ?>

                        <tr>

                            <td class="fw-semibold">
                                <?= e(mb_strimwidth(html_entity_decode((string) $activ['titre'], ENT_QUOTES, "UTF-8"), 0, 50, "…")) ?>
                            </td>

                            <td>
                                <span class="badge bg-primary">
                                    <?= e($activ["categorie"]) ?>
                                </span>
                            </td>

                            <td>
                                <?php $date = new DateTime($activ['date_pub']); ?>
                                <?= $date->format('d-m-Y H:i') ?>
                            </td>

                            <td>

                                <?php if($activ['etat_modifier'] == 1): ?>
                                    <span class="badge bg-success">
                                        Publié
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">
                                        Brouillon
                                    </span>
                                <?php endif; ?>

                            </td>

                            <td class="text-end">

                                <?php if($activ['etat_modifier'] == 0): ?>

                                    <a href="?pages=activite&confirmer=<?= $activ['id_act'] ?>"
                                       class="btn btn-sm btn-success">

                                        <i class="bi bi-check-circle"></i>
                                    </a>

                                <?php else: ?>

                                    <a href="?pages=activite&deconfirmer=<?= $activ['id_act'] ?>"
                                       class="btn btn-sm btn-warning">

                                        <i class="bi bi-eye-slash"></i>
                                    </a>

                                <?php endif; ?>

                                <a href="?pages=activite&supprimer=<?= $activ['photo'] ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirmAction(event, 'Supprimer cet article ? Cette action est irréversible.', 'Oui, supprimer')">

                                    <i class="bi bi-trash"></i>
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- PAGINATION -->
    <div class="d-flex justify-content-center mt-4">

        <nav>
            <ul class="pagination">

                <li class="page-item <?= ($current==1)?'disabled':'' ?>">
                    <a class="page-link"
                       href="?pages=activite&pag=<?= max(1,$current-1) ?>">
                        &laquo;
                    </a>
                </li>

                <?php for($i=1;$i<=$nbPage;$i++): ?>

                    <li class="page-item <?= ($i==$current)?'active':'' ?>">
                        <a class="page-link"
                           href="?pages=activite&pag=<?= $i ?>">
                            <?= $i ?>
                        </a>
                    </li>

                <?php endfor; ?>

                <li class="page-item <?= ($current==$nbPage)?'disabled':'' ?>">
                    <a class="page-link"
                       href="?pages=activite&pag=<?= min($nbPage,$current+1) ?>">
                        &raquo;
                    </a>
                </li>

            </ul>
        </nav>

    </div>

</div>

<!-- MODAL FORM -->
<div class="modal fade" id="addArticleModal" tabindex="-1">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <div class="modal-header bg-primary text-white">

                <h5 class="modal-title">
                    <i class="bi bi-plus-circle"></i>
                    Ajouter un article
                </h5>

                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>

            </div>

            <form method="post" enctype="multipart/form-data">

                <?= csrf_field() ?>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Titre</label>
                        <input type="text" name="titre"
                               class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Photo</label>
                        <input type="file" name="photo"
                               class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catégorie</label>
                        <select class="form-select" name="categorie">

                            <?php while ($d=$id_categ->fetch()): ?>

                                <option><?= $d['nom_cat'] ?></option>

                            <?php endwhile; ?>

                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description"
                                  class="form-control"
                                  rows="4"></textarea>
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="submit"
                            name="activite"
                            class="btn btn-success">

                        <i class="bi bi-check-circle"></i>
                        Publier
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>