<?php
require_once __DIR__ . '/main_function.php';
require_once __DIR__ . '/../includes/payment_helpers.php';

// 🔧 Généralisé : cette page couvre aussi bien l'échec d'un paiement
// d'adhésion (type=adhesion, par défaut) que celui d'un don public
// (type=don) — voir includes/payment_helpers.php et
// adhere/don_paiement.php.
$type = ($_GET['type'] ?? 'adhesion') === 'don' ? 'don' : 'adhesion';

/**
 * 🔧 CORRECTIF (cause racine du bug "paiement débité mais affiché comme
 * refusé") : cette page affichait jusqu'ici un message d'échec générique
 * et STATIQUE, quel que soit le statut réel en base — elle ne lisait même
 * pas $_GET['id']. Un lien vers failed.php (ancien, en cache, partagé...)
 * affichait donc "refusé" même pour un paiement en réalité "paid" ou
 * encore en vérification. On lit maintenant le statut réel, et on
 * n'affiche ce message d'échec que s'il est réellement "failed" ou
 * "cancelled" ; tout autre cas redirige vers la bonne page.
 */
$id  = (int) ($_GET['id'] ?? 0);
$row = $id > 0 ? payment_get_row($bdd, $type, $id) : null;

if ($row) {
    if ($row['status'] === 'paid') {
        header('Location: success.php?id=' . $id . '&type=' . $type);
        exit;
    }
    if (in_array($row['status'], ['pending', 'expired'], true)) {
        header('Location: pending.php?id=' . $id . '&type=' . $type);
        exit;
    }
}

// Statut réel ('failed' ou 'cancelled') pour nuancer le message affiché
// plus bas — reste 'failed' par défaut si l'identifiant est absent/invalide
// (lien direct sans contexte), comportement inchangé dans ce cas précis.
$statutReel = $row['status'] ?? 'failed';
?>
<!DOCTYPE html>
<html lang="fr">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Paiement échoué</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

body{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;

    background:
        radial-gradient(circle at top,
        rgba(220,53,69,0.25),
        transparent 60%),

        linear-gradient(
        rgba(15,23,42,0.94),
        rgba(15,23,42,0.94)),

        url('../assets/img/bg.jpg');

    background-size:cover;
    background-position:center;

    font-family:'Segoe UI',sans-serif;
}

/* CARD */
.failed-card{
    width:100%;
    max-width:650px;
    border:none;
    border-radius:30px;
    overflow:hidden;
    background:#fff;

    box-shadow:
        0 25px 60px rgba(0,0,0,0.25),
        0 8px 20px rgba(0,0,0,0.1);

    animation:fadeUp .6s ease;
}

/* HEADER */
.failed-header{
    background:linear-gradient(135deg,#dc3545,#b02a37);
    color:#fff;
    text-align:center;
    padding:45px 30px;
    position:relative;
}

.failed-header::after{
    content:'';
    position:absolute;
    top:-40%;
    left:-40%;
    width:180%;
    height:180%;
    background:rgba(255,255,255,0.05);
    transform:rotate(25deg);
}

/* ICON */
.icon-box{
    width:110px;
    height:110px;
    margin:auto;

    background:#fff;
    border-radius:50%;

    display:flex;
    align-items:center;
    justify-content:center;

    box-shadow:0 10px 25px rgba(0,0,0,0.2);
}

.icon-box i{
    font-size:55px;
    color:#dc3545;
}

/* TITLE */
.failed-title{
    font-size:32px;
    font-weight:800;
    margin-top:20px;
}

/* BODY */
.failed-body{
    padding:45px;
    text-align:center;
}

.failed-message{
    font-size:17px;
    color:#555;
    line-height:1.8;
}

/* ALERT BOX */
.reason-box{
    background:#fff5f5;
    border:1px solid #ffd6d6;
    border-left:5px solid #dc3545;
    border-radius:16px;
    padding:18px;
    margin-top:25px;
    color:#842029;
}

/* BUTTONS */
.btn-custom{
    height:58px;
    border-radius:16px;
    font-weight:700;
    font-size:16px;
    transition:.3s;
}

.btn-custom:hover{
    transform:translateY(-2px);
}

/* ANIMATION */
@keyframes fadeUp{

    from{
        opacity:0;
        transform:translateY(20px);
    }

    to{
        opacity:1;
        transform:translateY(0);
    }
}

</style>

</head>

<body>

<div class="container">

    <div class="failed-card mx-auto">

        <!-- HEADER -->
        <div class="failed-header">

            <div class="icon-box">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>

            <div class="failed-title">
                Paiement échoué
            </div>

            <p class="mt-3 mb-0 opacity-75">
                La transaction n’a pas pu être finalisée
            </p>

        </div>

        <!-- BODY -->
        <div class="failed-body">

            <div class="failed-message">

                <?php if ($statutReel === 'cancelled'): ?>

                    La transaction a été annulée.

                <?php else: ?>

                    Le paiement n'a pas été confirmé par FlexPay.
                    Vous pouvez réessayer.

                <?php endif; ?>

                <br><br>

                Vérifiez votre solde Mobile Money puis
                réessayez la transaction.

                <?php if ($type !== 'don'): ?>
                    <br><br>
                    <strong>Votre adhésion reste non validée</strong> tant que FlexPay n'a pas reçu l'argent.
                    Vous pouvez télécharger votre fiche, marquée « NON VALIDÉE », puis la retélécharger une fois le paiement confirmé.
                <?php endif; ?>

            </div>

            <!-- RAISON -->
            <div class="reason-box">

                <i class="fa-solid fa-triangle-exclamation me-2"></i>

                Causes possibles :
                <ul class="text-start mt-3 mb-0">

                    <li>Solde insuffisant</li>

                    <li>Confirmation non effectuée</li>

                    <li>Numéro incorrect</li>

                    <li>Connexion opérateur indisponible</li>

                    <li>Transaction expirée</li>

                </ul>

            </div>

            <!-- BUTTONS -->
            <div class="d-grid gap-3 mt-4">

                <?php if ($type === 'don'): ?>

                    <a href="../index.php?pages=soutenir"
                        class="btn btn-danger btn-custom">

                            <i class="fa-solid fa-rotate-left me-2"></i>
                            Refaire un don

                        </a>

                <?php else: ?>

                    <a href="paiement.php?token=<?= urlencode($_SESSION['payment_token'] ?? '') ?>"
                        class="btn btn-danger btn-custom">

                                <i class="fa-solid fa-rotate-left me-2"></i>
                                Réessayer le paiement

                            </a>

                <?php endif; ?>

                <?php if ($type !== 'don' && !empty($row['id_ad'])): ?>
                    <a href="../admin/pages/print/print_adherer.php?cod=<?= (int) $row['id_ad'] ?>"
                       class="btn btn-outline-warning btn-custom" target="_blank" rel="noopener">
                        <i class="fa-solid fa-file-arrow-down me-2"></i>
                        Télécharger ma fiche (NON VALIDÉE)
                    </a>
                <?php endif; ?>

                <a href="../index.php"
                   class="btn btn-outline-secondary btn-custom">

                    <i class="fa-solid fa-house me-2"></i>
                    Retour à l’accueil

                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>