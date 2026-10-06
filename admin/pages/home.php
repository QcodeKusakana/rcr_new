<?php
if (!isset($_SESSION['id_adm'])) {
    header("Location:?pages=login");
    exit;
}
?>

<div class="container-fluid" style="margin: 20PX;">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold text-primary">
                <i class="bi bi-journal-richtext"></i>
                Gestion des articles
            </h3>
            <small class="text-muted">
                Publications et contenus
            </small>
        </div>

    </div>

    <!-- ARTICLES -->
    <div class="row g-4">

        <?php while($pub = $les_articles->fetch()):

            $isPublished = ($pub['etat_modifier'] == 1);

            // ⚠️ idéalement remplacer par JOIN (optimisation future)
            $comments = $bdd->prepare("SELECT COUNT(*) FROM commentaire WHERE id_act=?");
            $comments->execute([$pub['id_act']]);
            $nb_comments = $comments->fetchColumn();
        ?>

        <div class="col-xl-4 col-lg-6 col-md-6">

            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">

                <!-- IMAGE -->
                <div class="position-relative">

                    <img src="./../media/images_activ/<?= $pub['photo'] ?>"
                         class="w-100"
                         style="height:240px; object-fit:cover;">

                    <!-- STATUS -->
                    <span class="badge position-absolute top-0 end-0 m-3
                        <?= $isPublished ? 'bg-success' : 'bg-danger' ?>">

                        <i class="bi bi-circle-fill"></i>
                        <?= $isPublished ? 'Publié' : 'Brouillon' ?>

                    </span>

                </div>

                <!-- BODY -->
                <div class="card-body">

                    <h5 class="fw-bold">
                        <?= e(mb_strimwidth(html_entity_decode((string) $pub['titre'], ENT_QUOTES, "UTF-8"), 0, 60, "…")) ?>
                    </h5>

                    <div class="text-muted small mt-2">

                        <i class="bi bi-chat-dots"></i>
                        <?= $nb_comments ?> commentaires

                    </div>

                </div>

                <!-- FOOTER -->
                <div class="card-footer bg-white border-0">

                    <div class="d-flex flex-wrap gap-2">

                        <!-- EDIT -->
                        <a href="?pages=modif_article&edit=<?= $pub['id_act'] ?>"
                           class="btn btn-primary btn-sm rounded-pill">

                            <i class="bi bi-pencil-square"></i>
                            Modifier
                        </a>

                        <?php if(!$isPublished): ?>

                            <?php if($_SESSION['niveau'] == 7): ?>

                                <!-- DELETE -->
                                <a href="?pages=home&supprimer=<?= $pub['id_act'] ?>"
                                   class="btn btn-danger btn-sm rounded-pill"
                                   onclick="return confirmAction(event, 'Supprimer cet article ? Cette action est irréversible.', 'Oui, supprimer')">

                                    <i class="bi bi-trash"></i>
                                    Supprimer
                                </a>

                                <!-- PUBLISH -->
                                <a href="?pages=home&confirmer=<?= $pub['id_act'] ?>"
                                   class="btn btn-success btn-sm rounded-pill">

                                    <i class="bi bi-check-circle"></i>
                                    Publier
                                </a>

                            <?php endif; ?>

                        <?php else: ?>

                            <!-- RETIRER -->
                            <a href="?pages=home&deconfirmer=<?= $pub['id_act'] ?>"
                               class="btn btn-warning btn-sm rounded-pill">

                                <i class="bi bi-eye-slash"></i>
                                Retirer
                            </a>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

        <?php endwhile; ?>

    </div>

    <!-- PAGINATION -->
    <div class="d-flex justify-content-center mt-5">

        <nav>

            <ul class="pagination shadow-sm">

                <li class="page-item <?= ($current == 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?pages=home&pag=<?= max(1,$current-1) ?>">
                        &laquo;
                    </a>
                </li>

                <?php for($i=1;$i<=$nbPage;$i++): ?>

                    <li class="page-item <?= ($i==$current)?'active':'' ?>">
                        <a class="page-link" href="?pages=home&pag=<?= $i ?>">
                            <?= $i ?>
                        </a>
                    </li>

                <?php endfor; ?>

                <li class="page-item <?= ($current==$nbPage)?'disabled':'' ?>">
                    <a class="page-link" href="?pages=home&pag=<?= min($nbPage,$current+1) ?>">
                        &raquo;
                    </a>
                </li>

            </ul>

        </nav>

    </div>

</div>