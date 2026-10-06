<br><br>

<section id="team" class="py-5" style="background-color: #ffffff">
    <div class="container">

        <!-- TITLE -->
        <div class="section-title text-center mb-5">

            <span class="badge bg-primary px-4 py-2 mb-3 fs-6">
                R.C.R
            </span>

            <h2 class="fw-bold text-uppercase text-dark">
                LES CADRES DU RCR
            </h2>

            <div class="mx-auto mt-3"
                 style="width: 90px; height: 4px; background: #0d6efd; border-radius: 10px;">
            </div>

        </div>

        <!-- TEAM -->
        <div class="row g-4">

            <?php while ($m = $reqequipe->fetch()): ?>

                <div class="col-lg-4 col-md-6">

                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 text-center">

                        <img loading="lazy" decoding="async" src="./admin/media/img_equipe/<?= e($m['photo']) ?>"
                             class="card-img-top"
                             style="height: 320px; object-fit: cover;"
                             alt="<?= e($m['name']) ?>">

                        <div class="card-body">

                            <h5 class="fw-bold mb-1">
                                <?= e($m['name']) ?>
                            </h5>

                            <span class="badge bg-primary mb-2">
                                <?= e($m['function']) ?>
                            </span>

                            <p class="text-muted small mb-1">
                                <i class="bi bi-telephone"></i>
                                <?= e($m['telephone']) ?>
                            </p>

                            <?php if (!empty($m['resume'])): ?>
                                <p class="text-muted small mb-0">
                                    <?= e($m['resume']) ?>
                                </p>
                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    </div>
</section>
