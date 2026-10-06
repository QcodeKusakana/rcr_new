<form method="post">

<div class="container-fluid py-3">

    <!-- HEADER -->
    <div class="mb-3">

        <h4 class="fw-bold">
            <i class="bi bi-geo-alt-fill text-primary"></i>
            TERRITOIRE :
            <span class="text-danger">
                <?= htmlspecialchars($infoter['nom_tr']) ?>
            </span>
        </h4>

        <small class="text-muted">
            Gestion des secteurs du territoire
        </small>

    </div>

    <!-- ALERTS -->
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

    <!-- CARD -->
    <div class="card border-0 shadow-sm rounded-4">

        <div class="card-body">

            <?= csrf_field() ?>

            <!-- ID hidden propre -->
            <input type="hidden" name="id_tr" value="<?= (int)$infoter['id_tr'] ?>">

            <?php if($infoter['changer'] == 1): ?>

                <!-- MODE BLOQUÉ -->
                <div class="alert alert-warning d-flex align-items-center">

                    <i class="bi bi-lock-fill me-2"></i>

                    Ce territoire est bloqué, ajout impossible.

                </div>

                <div class="d-flex justify-content-end gap-2">

                    <a href="?pages=territoires"
                       class="btn btn-danger">

                        <i class="bi bi-x-circle"></i>
                        Quitter
                    </a>

                </div>

            <?php else: ?>

                <!-- INPUT -->
                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        Nom du secteur
                    </label>

                    <div class="input-group">

                        <span class="input-group-text">
                            <i class="bi bi-pin-map"></i>
                        </span>

                        <input type="text"
                               name="nom_sec"
                               class="form-control"
                               placeholder="Ex: Secteur de Ngaliema"
                               required>

                    </div>

                </div>

                <!-- ACTIONS -->
                <div class="d-flex justify-content-between flex-wrap gap-2">

                    <div class="d-flex gap-2">

                        <button type="submit"
                                name="btnsect"
                                class="btn btn-info">

                            <i class="bi bi-plus-circle"></i>
                            Ajouter
                        </button>

                        <a href="?pages=territoires"
                           class="btn btn-danger">

                            <i class="bi bi-x-circle"></i>
                            Quitter
                        </a>

                    </div>

                    <a href="?pages=secteurs&tr_id=<?= (int)$infoter['id_tr'] ?>&blq=1"
                       class="btn btn-warning"
                       onclick="return confirmAction(event, 'Bloquer ce territoire ? Aucun nouveau secteur ne pourra plus y être ajouté.', 'Oui, bloquer')">

                        <i class="bi bi-shield-lock"></i>
                        Bloquer

                    </a>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

</form>