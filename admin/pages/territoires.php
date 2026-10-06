<?php

// 🔐 SUPPRESSION D'UN TERRITOIRE
// CORRECTIF : ce bloc supprimait par erreur une ACTIVITÉ (table activite) portant le même identifiant
// (copier-coller), au lieu du territoire. Un territoire n'est supprimé que s'il n'est plus utilisé
// (aucun secteur, aucun membre, aucun don rattaché) : l'intégrité des fiches membres est préservée.
if (isset($_GET['sup'])) {
    $id = (int) $_GET['sup'];
    $usage = $bdd->prepare("SELECT (SELECT COUNT(*) FROM secteurs WHERE id_tr = ?) + (SELECT COUNT(*) FROM adhesion WHERE territoire = ?)");
    $usage->execute([$id, $id]);
    if ((int) $usage->fetchColumn() > 0) {
        admin_flash('Ce territoire est utilisé (secteurs ou membres rattachés) : il ne peut pas être supprimé.', 'warning');
    } else {
        $supprimer = $bdd->prepare("DELETE FROM territoires WHERE id_tr = ?");
        $supprimer->execute([$id]);
        if ($supprimer->rowCount() === 1) {
            audit_log($bdd, 'territoire.supprimer', 'territoires', (string) $id);
            admin_flash('Territoire supprimé.');
        }
    }
    header("Location:?pages=territoires");
    exit;
}
?>

<div class="container-fluid" style="margin: 20PX;">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold text-primary">
                <i class="bi bi-map-fill"></i>
                Gestion des territoires
            </h3>
            <small class="text-muted">
                Provinces, territoires et secteurs
            </small>
        </div>

    </div>

    <div class="row g-4">

        <!-- FORM -->
        <div class="col-lg-4">

            <div class="card shadow-sm border-0 rounded-4">

                <div class="card-header bg-primary text-white">
                    <i class="bi bi-plus-circle"></i>
                    Ajouter territoire
                </div>

                <div class="card-body">

                    <?php if(isset($sms)): ?>
                        <div class="alert alert-success py-2">
                            <?= e((string) $sms) ?>
                        </div>
                    <?php endif; ?>

                    <?php if(isset($erreur)): ?>
                        <div class="alert alert-danger py-2">
                            <?= e((string) $erreur) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data">

                        <?= csrf_field() ?>

                        <div class="mb-3">

                            <label class="form-label">
                                Nom du territoire
                            </label>

                            <input type="text"
                                   name="nom_tr"
                                   class="form-control"
                                   placeholder="Ex: Masina">

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Province
                            </label>

                            <select class="form-select" name="id_p">

                                <option disabled selected>
                                    Choisir province
                                </option>

                                <?php while ($d = $id_prov->fetch()): ?>

                                    <option value="<?= $d['id_p'] ?>">
                                        <?= htmlspecialchars($d['nom_p']) ?>
                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>

                        <button type="submit"
                                class="btn btn-success w-100"
                                name="activite">

                            <i class="bi bi-check-circle"></i>
                            Ajouter

                        </button>

                    </form>

                </div>

            </div>

        </div>

        <!-- LIST -->
        <div class="col-lg-8">

            <div class="card shadow-sm border-0 rounded-4">

                <div class="card-header bg-dark text-white">
                    <i class="bi bi-list-ul"></i>
                    Liste des provinces et territoires
                </div>

                <div class="card-body">

                    <?php while($activ = $les_activite->fetch()):

                        $territ = $bdd->prepare("SELECT * FROM territoires WHERE id_p=?");
                        $territ->execute([$activ['id_p']]);

                        $nbr = $territ->rowCount();
                    ?>

                    <!-- PROVINCE HEADER -->
                    <div class="bg-light p-2 rounded mb-2 fw-bold">

                        <i class="bi bi-geo-alt-fill text-primary"></i>

                        <?= htmlspecialchars($activ['nom_p']) ?>

                        <span class="badge bg-primary">
                            <?= $nbr ?> territoires
                        </span>

                    </div>

                    <!-- TERRITOIRES -->
                    <div class="ps-3 mb-3">

                        <?php while($t = $territ->fetch()):

                            $secteur = $bdd->prepare("SELECT * FROM secteurs WHERE id_tr=?");
                            $secteur->execute([$t['id_tr']]);

                            $nbrsect = $secteur->rowCount();
                        ?>

                        <div class="border-start ps-3 mb-2">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <i class="bi bi-pin-map text-success"></i>

                                    <a href="?pages=secteurs&tr_id=<?= $t['id_tr'] ?>"
                                       class="text-decoration-none fw-semibold">

                                        <?= htmlspecialchars($t['nom_tr']) ?>

                                    </a>

                                    <span class="text-muted small">
                                        (<?= $nbrsect ?> secteurs)
                                    </span>

                                </div>

                                <!-- DELETE -->
                                <a href="?pages=territoires&sup=<?= $t['id_tr'] ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirmAction(event, 'Supprimer ce territoire ? Cette action est irréversible.', 'Oui, supprimer')">

                                    <i class="bi bi-trash"></i>

                                </a>

                            </div>

                            <!-- SECTEURS -->
                            <div class="text-muted small mt-1">

                                <?php while($s = $secteur->fetch()): ?>

                                    <span class="badge bg-secondary me-1">
                                        <?= htmlspecialchars($s['nom_sec']) ?>
                                    </span>

                                <?php endwhile; ?>

                            </div>

                        </div>

                        <?php endwhile; ?>

                    <?php endwhile; ?>

                </div>

            </div>

        </div>

    </div>

    <!-- PAGINATION -->
    <div class="d-flex justify-content-center mt-4">

        <nav>

            <ul class="pagination">

                <li class="page-item <?= ($current == 1) ? 'disabled' : '' ?>">
                    <a class="page-link"
                       href="?pages=territoires&pag=<?= max(1, $current-1) ?>">
                        &laquo;
                    </a>
                </li>

                <?php for($i=1;$i<=$nbPage;$i++): ?>

                    <li class="page-item <?= ($i==$current)?'active':'' ?>">
                        <a class="page-link"
                           href="?pages=territoires&pag=<?= $i ?>">
                            <?= $i ?>
                        </a>
                    </li>

                <?php endfor; ?>

                <li class="page-item <?= ($current==$nbPage)?'disabled':'' ?>">
                    <a class="page-link"
                       href="?pages=territoires&pag=<?= min($nbPage, $current+1) ?>">
                        &raquo;
                    </a>
                </li>

            </ul>

        </nav>

    </div>

</div>