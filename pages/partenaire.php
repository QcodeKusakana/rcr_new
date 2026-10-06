<br><br>

<section id="partenaires" class="py-5 bg-light">
    <div class="container">

        <!-- TITLE -->
        <div class="section-title text-center mb-5">

            <span class="badge bg-primary px-4 py-2 mb-3 fs-6">
                R.C.R
            </span>

            <h2 class="fw-bold text-uppercase text-dark">
                NOS PARTENAIRES
            </h2>

            <div class="mx-auto mt-3"
                 style="width: 90px; height: 4px; background: #0d6efd; border-radius: 10px;">
            </div>

        </div>

        <?php
        $reqpartenaire = $bdd->query("SELECT * FROM partenaire ORDER BY id_part DESC");
        ?>

        <!-- PARTENAIRES -->
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 g-4 justify-content-center align-items-center">

            <?php while ($part = $reqpartenaire->fetch()): ?>

                <div class="col text-center">

                    <div class="card border-0 shadow-sm rounded-4 h-100 d-flex align-items-center justify-content-center p-3">

                        <img loading="lazy" decoding="async" src="./media/images_part/<?= e($part['photo']) ?>"
                             alt="<?= e($part['nom_part']) ?>"
                             title="<?= e($part['nom_part']) ?>"
                             class="img-fluid"
                             style="max-height: 90px; object-fit: contain;">

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    </div>
</section>
