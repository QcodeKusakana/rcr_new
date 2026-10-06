<?php

/*
|--------------------------------------------------------------------------
| NOMBRE D'ARTICLES
|--------------------------------------------------------------------------
*/

$nbrdocument = $bdd->query('SELECT * FROM activite');
$nbrFichier  = $nbrdocument->rowCount();

/*
|--------------------------------------------------------------------------
| MODE EDITION
|--------------------------------------------------------------------------
*/

$mode_edition = 0;
$edit_pub = [];

/*
|--------------------------------------------------------------------------
| MODIFICATION ARTICLE
|--------------------------------------------------------------------------
*/

if(isset($_GET['edit']) && !empty($_GET['edit'])){

    $mode_edition = 1;

    $edit_id = (int) $_GET['edit'];

    $edit_pub_req = $bdd->prepare("
        SELECT * FROM activite
        WHERE id_act = ?
    ");

    $edit_pub_req->execute([$edit_id]);

    if($edit_pub_req->rowCount() == 1){

        $edit_pub = $edit_pub_req->fetch(PDO::FETCH_ASSOC);

    }else{

        echo '
        <div class="alert alert-danger shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Article introuvable.
        </div>';
    }
}

/*
|--------------------------------------------------------------------------
| MISE A JOUR
|--------------------------------------------------------------------------
*/

if(isset($_POST['modifier']) && !csrf_verify()){

    $erreur = "Session expirée, merci de recharger la page et réessayer.";

} elseif(isset($_POST['modifier'])){

    $titre       = htmlspecialchars(trim($_POST['titre']));
    $description = htmlspecialchars(trim($_POST['description']));
    $categorie   = htmlspecialchars(trim($_POST['categorie']));

    if(empty($titre) || empty($description) || empty($categorie)){

        $erreur = "Veuillez remplir tous les champs.";

    }else{

        if($mode_edition == 1){

            // ⚠️ CORRECTIF FONCTIONNEL (audit) : le formulaire propose de
            // changer la photo de l'article (champ "image"), mais rien ne
            // traitait $_FILES ici — la nouvelle image était donc
            // silencieusement ignorée. Ajouté ci-dessous : upload optionnel
            // (mêmes règles de validation d'extension que la création d'un
            // article dans admin/functions/activite.funct.php), l'ancienne
            // photo reste inchangée si aucun nouveau fichier n'est fourni.
            $finalcateg = $_POST['photoName'] ?? null;

            if (!empty($_FILES['image']['name'])) {

                require_once __DIR__ . '/../../includes/upload.php';
                $file_extension = upload_image_valide($_FILES['image']);

                if ($file_extension !== null) {

                    $nouveauNom = upload_nom_aleatoire('activ', $file_extension);
                    $file_dest = './../media/images_activ/' . $nouveauNom;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $file_dest)) {

                        $ancienNom = $finalcateg;
                        $finalcateg = $nouveauNom;

                        if (!empty($ancienNom)) {
                            @unlink('./../media/images_activ/' . $ancienNom);
                        }

                    } else {
                        $erreur = "Une erreur est survenue lors de l'envoi de la nouvelle image.";
                    }

                } else {
                    $erreur = "Seuls les fichiers jpg/png/webp sont autorisés pour l'image.";
                }
            }

            if (empty($erreur)) {

                $update = $bdd->prepare("
                    UPDATE activite
                    SET titre = ?, description = ?, categorie = ?, photo = ?
                    WHERE id_act = ?
                ");

                $update->execute([
                    $titre,
                    $description,
                    $categorie,
                    $finalcateg,
                    $edit_id
                ]);

                $sms = "Article modifié avec succès.";
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| CATEGORIES
|--------------------------------------------------------------------------
*/

$id_categ = $bdd->query("
    SELECT * FROM categorie
    ORDER BY nom_cat ASC
");

?>

<!-- ===================================================== -->
<!-- Bootstrap + Bootstrap Icons sont déjà chargés une fois -->
<!-- pour toutes par le gabarit admin/index.php : le lien   -->
<?php /* dupliqué ici (audit) forçait un double téléchargement  */ ?>
<!-- de ces feuilles de style sur cette page. Retiré.        -->
<!-- ===================================================== -->

<!-- ===================================================== -->
<!-- CONTAINER -->
<!-- ===================================================== -->

<div class="container-fluid py-4">

    <div class="row justify-content-center">

        <div class="col-xl-11">

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">

                <!-- HEADER -->

                <div class="card-header bg-dark text-white p-4">

                    <div class="d-flex justify-content-between align-items-center flex-wrap">

                        <div>

                            <h3 class="fw-bold mb-1">

                                <i class="bi bi-pencil-square me-2"></i>
                                Modifier un article

                            </h3>

                            <small class="text-light opacity-75">

                                Gestion et modification des publications

                            </small>

                        </div>

                        <div class="badge bg-success fs-6 px-3 py-2">

                            <?= $nbrFichier ?> article(s)

                        </div>

                    </div>

                </div>

                <!-- BODY -->

                <div class="card-body p-4 p-lg-5">

                    <!-- ALERTES -->

                    <?php if(isset($sms)): ?>

                        <div class="alert alert-success alert-dismissible fade show shadow-sm">

                            <i class="bi bi-check-circle-fill me-2"></i>

                            <?= e((string) $sms) ?>

                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="alert">
                            </button>

                        </div>

                    <?php endif; ?>

                    <?php if(isset($erreur)): ?>

                        <div class="alert alert-danger alert-dismissible fade show shadow-sm">

                            <i class="bi bi-exclamation-circle-fill me-2"></i>

                            <?= e((string) $erreur) ?>

                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="alert">
                            </button>

                        </div>

                    <?php endif; ?>

                    <!-- FORM -->

                    <form method="POST"
                          enctype="multipart/form-data">

                        <?= csrf_field() ?>

                        <div class="row g-4">

                            <!-- LEFT -->

                            <div class="col-lg-8">

                                <!-- TITRE -->

                                <div class="mb-4">

                                    <label class="form-label fw-semibold">

                                        <i class="bi bi-type me-2 text-primary"></i>
                                        Titre de l'article

                                    </label>

                                    <input type="text"
                                           name="titre"
                                           class="form-control form-control-lg rounded-3"
                                           value="<?= htmlspecialchars($edit_pub['titre'] ?? '') ?>"
                                           placeholder="Entrer le titre">

                                </div>

                                <!-- CATEGORIE -->

                                <div class="mb-4">

                                    <label class="form-label fw-semibold">

                                        <i class="bi bi-tags-fill me-2 text-success"></i>
                                        Catégorie

                                    </label>

                                    <select class="form-select form-select-lg rounded-3"
                                            name="categorie">

                                        <option value="">
                                            -- Sélectionner --
                                        </option>

                                        <?php while($d = $id_categ->fetch()): ?>

                                            <option
                                                value="<?= $d['nom_cat'] ?>"

                                                <?=
                                                (($edit_pub['categorie'] ?? '') == $d['nom_cat'])
                                                ? 'selected'
                                                : ''
                                                ?>
                                            >

                                                <?= $d['nom_cat'] ?>

                                            </option>

                                        <?php endwhile; ?>

                                    </select>

                                </div>

                                <!-- DESCRIPTION -->

                                <div class="mb-4">

                                    <label class="form-label fw-semibold">

                                        <i class="bi bi-card-text me-2 text-danger"></i>
                                        Description

                                    </label>

                                    <textarea name="description"
                                              rows="10"
                                              class="form-control rounded-3"
                                              placeholder="Description de l'article..."><?= htmlspecialchars($edit_pub['description'] ?? '') ?></textarea>

                                </div>

                            </div>

                            <!-- RIGHT -->

                            <div class="col-lg-4">

                                <div class="card border-0 shadow-sm rounded-4">

                                    <div class="card-header bg-light fw-bold">

                                        <i class="bi bi-image-fill me-2"></i>
                                        Image de l'article

                                    </div>

                                    <div class="card-body text-center">

                                        <img src="./../media/images_activ/<?= $edit_pub['photo'] ?? 'default.png' ?>"
                                             class="img-fluid rounded-4 shadow-sm mb-3"
                                             style="height:250px;width:100%;object-fit:cover;">

                                        <input type="file"
                                               name="image"
                                               class="form-control rounded-3">

                                        <input type="hidden"
                                               name="photoName"
                                               value="<?= $edit_pub['photo'] ?? '' ?>">

                                        <small class="text-muted d-block mt-3">

                                            JPG, PNG ou WEBP recommandé

                                        </small>

                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- BUTTONS -->

                        <div class="d-flex flex-wrap gap-3 mt-5">

                            <button type="submit"
                                    name="modifier"
                                    class="btn btn-success btn-lg px-5 rounded-3 shadow-sm">

                                <i class="bi bi-check-circle-fill me-2"></i>
                                Modifier maintenant

                            </button>

                            <a href="?pages=home"
                               class="btn btn-outline-dark btn-lg px-5 rounded-3">

                                <i class="bi bi-arrow-left-circle me-2"></i>
                                Retour

                            </a>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- ===================================================== -->
<!-- STYLE -->
<!-- ===================================================== -->

<style>

body{
    background: #f4f6f9;
}

.card{
    border-radius: 22px;
}

.form-control,
.form-select{
    border: 1px solid #dbe0e6;
    padding: 14px 16px;
    box-shadow: none !important;
}

.form-control:focus,
.form-select:focus{
    border-color: #198754;
}

textarea{
    resize: none;
}

.btn{
    transition: .3s ease;
}

.btn:hover{
    transform: translateY(-2px);
}

.card-header{
    border-bottom: 1px solid #ececec;
}

</style>

<!-- ===================================================== -->
<!-- BOOTSTRAP JS -->
<!-- ===================================================== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- ===================================================== -->
<!-- UPLOAD JS -->
<!-- ===================================================== -->

<script src="uploadFile.js"></script>