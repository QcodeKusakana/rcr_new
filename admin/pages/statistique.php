<?php
// ✅ UNE SEULE REQUETE (OPTIMISATION IMPORTANTE)
$sql = $bdd->query("
    SELECT p.id_p, p.nom_p, COUNT(a.id_ad) AS total_membres
    FROM provinces p
    LEFT JOIN adhesion a ON a.province = p.id_p
    GROUP BY p.id_p, p.nom_p
    ORDER BY total_membres DESC
");
?>

<div class="container-fluid" style="margin: 20PX;">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="fw-bold text-primary">
                <i class="bi bi-bar-chart-fill"></i>
                Statistiques des membres
            </h3>
            <small class="text-muted">
                Répartition des adhérents par province
            </small>
        </div>

    </div>

    <!-- CARD TABLE -->
    <div class="card border-0 shadow-sm rounded-4">

        <div class="card-header bg-dark text-white">
            <i class="bi bi-geo-alt-fill"></i>
            Statistiques par province
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0 datatable-auto">

                    <thead class="table-light">
                        <tr>
                            <th>
                                <i class="bi bi-geo-alt"></i>
                                Province
                            </th>

                            <th>
                                <i class="bi bi-people-fill"></i>
                                Nombre de membres
                            </th>

                            <th class="text-end">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php while($cat = $sql->fetch()): ?>

                        <tr>

                            <!-- PROVINCE -->
                            <td class="fw-semibold">

                                <i class="bi bi-pin-map-fill text-primary"></i>

                                <?= htmlspecialchars($cat['nom_p']) ?>

                            </td>

                            <!-- COUNT -->
                            <td>

                                <?php if($cat['total_membres'] > 0): ?>

                                    <span class="badge bg-success fs-6">
                                        <?= $cat['total_membres'] ?>
                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-secondary">
                                        0
                                    </span>

                                <?php endif; ?>

                            </td>

                            <!-- ACTION -->
                            <td class="text-end">

                                <?php if($_SESSION['niveau'] == 7): ?>

                                    <a target="_blank"
                                       href="pages/print/printListeProvinces.php?IdProv=<?= $cat['id_p'] ?>"
                                       class="btn btn-sm btn-primary">

                                        <i class="bi bi-printer"></i>
                                        Imprimer
                                    </a>

                                <?php else: ?>

                                    <span class="text-muted small">
                                        Accès restreint
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