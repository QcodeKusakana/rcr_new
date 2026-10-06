<?php
$mode_edition = 0;
$edit_pub = ['nom_cat' => '', 'resume' => ''];

// Chargement de la catégorie à modifier
if (isset($_GET['edit']) && !empty($_GET['edit'])) {

    $mode_edition = 1;
    $edit_id = (int) $_GET['edit'];

    $edit_pub_req = $bdd->prepare("SELECT * FROM categorie WHERE id_cat=?");
    $edit_pub_req->execute([$edit_id]);

    if ($edit_pub_req->rowCount() == 1) {
        $edit_pub = $edit_pub_req->fetch(PDO::FETCH_ASSOC);
    } else {
        $erreurs = "Catégorie introuvable.";
    }
}

// Mise à jour
if (isset($_POST['modify']) && !csrf_verify()) {

    $erreurs = "Session expirée, merci de recharger la page et réessayer.";

} elseif (isset($_POST['modify'])) {

    $nom_cat = htmlspecialchars($_POST['nom_cat']);
    $resume  = htmlspecialchars($_POST['resume']);

    if ($mode_edition == 1) {
        $update_cat = $bdd->prepare('UPDATE categorie SET nom_cat=?, resume=? WHERE id_cat=?');
        $update_cat->execute([$nom_cat, $resume, $edit_id]);
        $smss = "Catégorie modifiée avec succès.";
        $edit_pub['nom_cat'] = $nom_cat;
        $edit_pub['resume'] = $resume;
    }
}
?>

<div class="container-fluid py-3">

    <div class="mb-4">
        <h3 class="fw-bold text-primary">
            <i class="bi bi-pencil-square"></i>
            Modifier la catégorie
        </h3>
    </div>

    <?php if (isset($smss)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($smss) ?></div>
    <?php endif; ?>

    <?php if (isset($erreurs)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erreurs) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 col-lg-6">

        <div class="card-body">

            <form method="post">

                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nom de la catégorie</label>
                    <input type="text" name="nom_cat" class="form-control"
                           value="<?= htmlspecialchars($edit_pub['nom_cat']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="resume" class="form-control" rows="6"><?= htmlspecialchars($edit_pub['resume']) ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="modify" class="btn btn-success">
                        <i class="bi bi-check-circle"></i>
                        Enregistrer
                    </button>
                    <a href="?pages=categorie" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left-circle"></i>
                        Retour aux catégories
                    </a>
                </div>

            </form>

        </div>

    </div>

</div>
