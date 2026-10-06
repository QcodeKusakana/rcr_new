<?php
// =====================
// DELETE PROVINCE (SECURE)
// =====================
if (isset($_GET['supprimer'])) {

    $id = (int) $_GET['supprimer'];

    if ($id > 0) {

        $stmt = $bdd->prepare("DELETE FROM provinces WHERE id_p = ?");
        $stmt->execute([$id]);
    }
}

// =====================
// FETCH PROVINCES
// =====================
$provinces = $bdd->query("SELECT * FROM provinces ORDER BY id_p DESC");
?>

<div class="container-fluid py-3" style="margin: 20PX;">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold text-primary mb-0">
                <i class="bi bi-geo-alt-fill"></i>
                Gestion des provinces
            </h3>
            <small class="text-muted">Ajouter, modifier et supprimer les provinces</small>
        </div>

    </div>

    <div class="row g-4">

        <!-- FORM -->
        <div class="col-lg-5">

            <div class="card shadow-sm border-0 rounded-4">

                <div class="card-header bg-primary text-white rounded-top-4">
                    <i class="bi bi-plus-circle me-1"></i>
                    Nouvelle province
                </div>

                <div class="card-body">

                    <?php if(isset($smss)): ?>
                        <div class="alert alert-success py-2">
                            <?= e((string) $smss) ?>
                        </div>
                    <?php endif; ?>

                    <?php if(isset($erreurs)): ?>
                        <div class="alert alert-danger py-2">
                            <?= e((string) $erreurs) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post">

                        <?= csrf_field() ?>

                        <div class="mb-3">

                            <label class="form-label">
                                Nom de la province
                            </label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-geo-alt"></i>
                                </span>

                                <input type="text"
                                       name="nom_p"
                                       class="form-control"
                                       placeholder="Ex: Kinshasa"
                                       required>
                            </div>

                        </div>

                        <button type="submit"
                                name="prov"
                                class="btn btn-success w-100">

                            <i class="bi bi-check-circle me-1"></i>
                            Enregistrer
                        </button>

                    </form>

                </div>

            </div>

        </div>

        <!-- TABLE -->
        <div class="col-lg-7">

            <div class="card shadow-sm border-0 rounded-4">

                <div class="card-header bg-dark text-white rounded-top-4">
                    <i class="bi bi-table me-1"></i>
                    Liste des provinces
                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0 datatable-auto">

                            <thead class="table-light">

                                <tr>
                                    <th>ID</th>
                                    <th>Province</th>
                                    <th class="text-end">Actions</th>
                                </tr>

                            </thead>

                            <tbody>

                            <?php while($p = $provinces->fetch()): ?>

                                <tr>

                                    <td class="fw-bold">
                                        <?= (int)$p['id_p'] ?>
                                    </td>

                                    <td>
                                        <i class="bi bi-geo text-primary me-1"></i>
                                        <?= htmlspecialchars($p['nom_p']) ?>
                                    </td>

                                    <td class="text-end">

                                        <?php if($_SESSION['niveau'] == 7): ?>

                                            <a href="?pages=modify_org&edit=<?= $p['id_p'] ?>"
                                               class="btn btn-sm btn-warning">

                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <a href="?pages=provinces&supprimer=<?= $p['id_p'] ?>"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirmAction(event, 'Supprimer cette province ? Cette action est irréversible.', 'Oui, supprimer')">

                                                <i class="bi bi-trash"></i>
                                            </a>

                                        <?php else: ?>

                                            <span class="badge bg-secondary">
                                                Lecture
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