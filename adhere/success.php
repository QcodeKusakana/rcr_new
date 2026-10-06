<?php
require_once __DIR__ . '/main_function.php';
require_once __DIR__ . '/../includes/payment_helpers.php';

// 🔧 Généralisé : cette page confirme aussi bien un paiement d'adhésion
// (type=adhesion, valeur par défaut) qu'un don public (type=don) — voir
// includes/payment_helpers.php et adhere/don_paiement.php.
$type = ($_GET['type'] ?? 'adhesion') === 'don' ? 'don' : 'adhesion';

/**
 * 🔧 CORRECTIF (point 6 du cahier des charges : "le fichier d'adhésion ne
 * doit pas dépendre d'une simple réponse immédiate du navigateur") : cette
 * page affichait "Paiement confirmé" uniquement parce que le navigateur y
 * avait été redirigé, sans jamais revérifier le statut réel en base.
 * N'importe qui connaissant/devinant un id pouvait donc voir cet écran de
 * succès pour un paiement qui n'était pas réellement "paid". On vérifie
 * maintenant systématiquement le statut réel avant d'afficher quoi que ce
 * soit, et on redirige vers la bonne page sinon.
 */
$id  = (int) ($_GET['id'] ?? 0);
$row = $id > 0 ? payment_get_row($bdd, $type, $id) : null;

if (!$row || $row['status'] !== 'paid') {
    if ($row && in_array($row['status'], ['pending', 'processing', 'expired'], true)) {
        header('Location: pending.php?id=' . $id . '&type=' . $type);
    } else {
        header('Location: failed.php?id=' . $id . '&type=' . $type);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Paiement réussi</title>

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
        rgba(25,135,84,0.25),
        transparent 60%),

        linear-gradient(
        rgba(10,20,40,0.94),
        rgba(10,20,40,0.94)),

        url('../assets/img/bg.jpg');

    background-size:cover;
    background-position:center;

    font-family:'Segoe UI',sans-serif;
}

/* CARD */
.success-card{
    width:100%;
    max-width:700px;

    border:none;
    border-radius:30px;
    overflow:hidden;

    background:#fff;

    box-shadow:
        0 25px 60px rgba(0,0,0,0.25),
        0 8px 20px rgba(0,0,0,0.1);

    animation:fadeUp .7s ease;
}

/* HEADER */
.success-header{
    background:linear-gradient(135deg,#198754,#20c997);
    color:#fff;

    text-align:center;

    padding:50px 30px;

    position:relative;
    overflow:hidden;
}

.success-header::after{
    content:'';
    position:absolute;

    top:-50%;
    left:-50%;

    width:200%;
    height:200%;

    background:rgba(255,255,255,0.05);

    transform:rotate(25deg);
}

/* ICON */
.icon-box{
    width:120px;
    height:120px;

    margin:auto;

    background:#fff;
    border-radius:50%;

    display:flex;
    align-items:center;
    justify-content:center;

    box-shadow:0 10px 25px rgba(0,0,0,0.2);
}

.icon-box i{
    font-size:60px;
    color:#198754;
}

/* TITRE */
.success-title{
    font-size:34px;
    font-weight:800;
    margin-top:22px;
}

/* BODY */
.success-body{
    padding:45px;
    text-align:center;
}

/* TEXT */
.success-message{
    font-size:17px;
    line-height:1.9;
    color:#555;
}

/* INFO BOX */
.info-box{
    margin-top:25px;

    background:#f8f9fa;

    border-radius:18px;

    padding:22px;

    border-left:5px solid #198754;

    text-align:left;
}

.info-box ul{
    margin:0;
    padding-left:18px;
}

.info-box li{
    margin-bottom:10px;
}

/* BADGE */
.badge-success-custom{
    background:rgba(25,135,84,0.1);
    color:#198754;

    border-radius:50px;

    padding:10px 18px;

    display:inline-block;

    font-weight:700;

    margin-top:20px;
}

/* BUTTON */
.btn-custom{
    height:60px;
    border-radius:16px;

    font-size:17px;
    font-weight:700;

    transition:.3s;
}

.btn-custom:hover{
    transform:translateY(-3px);
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

    <div class="success-card mx-auto">

        <!-- HEADER -->
        <div class="success-header">

            <div class="icon-box">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <div class="success-title">
                Paiement confirmé
            </div>

            <p class="mt-3 mb-0 opacity-75">
                <?= $type === 'don'
                    ? 'Merci infiniment pour votre soutien'
                    : 'Votre adhésion a été validée avec succès' ?>
            </p>

        </div>

        <!-- BODY -->
        <div class="success-body">

            <div class="success-message">

                <?php if ($type === 'don'): ?>

                    Votre don a été confirmé avec succès par FlexPay.

                    <br><br>

                    Grâce à votre soutien, le RCR peut continuer à agir.
                    Merci de votre confiance.

                <?php else: ?>

                    Merci. Votre paiement Mobile Money
                    a été confirmé avec succès par FlexPay.

                    <br><br>

                    Votre demande d’adhésion est maintenant
                    enregistrée dans le système.

                <?php endif; ?>

            </div>

            <!-- BADGE -->
            <div class="badge-success-custom">

                <i class="fa-solid fa-shield-check me-2"></i>
                Transaction sécurisée et validée

            </div>

            <!-- INFO -->
            <div class="info-box">

                <h5 class="fw-bold mb-3">
                    <i class="fa-solid fa-circle-info me-2 text-success"></i>
                    Informations importantes
                </h5>

                <ul>

                    <li>
                        Votre paiement a été traité avec succès.
                    </li>

                    <?php if ($type !== 'don'): ?>
                    <li>
                        Votre fiche d'adhésion officielle est prête au téléchargement ci-dessous.
                    </li>
                    <?php endif; ?>

                    <li>
                        Une confirmation peut être envoyée sur votre téléphone.
                    </li>

                    <li>
                        Conservez votre référence de transaction.
                    </li>

                </ul>

            </div>

            <!-- BUTTON -->
            <div class="d-grid gap-3 mt-4">

                <?php if ($type !== 'don'): ?>
                <?php /*
                    🔧 AJOUT (point 6 du cahier des charges : "rediriger
                    automatiquement l'utilisateur vers la page de
                    téléchargement... ne pas demander de revenir
                    manuellement dans une autre partie du site") : lien
                    direct vers la fiche officielle. Il fonctionne sans
                    étape de connexion supplémentaire, car
                    adhere/adhesion.funct.php pose déjà $_SESSION['id_ad']
                    dès la soumission du formulaire (avant même le
                    paiement) — voir le contrôle d'accès de
                    admin/pages/print/print_adherer.php, qui exige que
                    cette session corresponde exactement à l'adhésion
                    (`cod`), et que le paiement soit bien "paid".
                */ ?>
                <a href="../admin/pages/print/print_adherer.php?cod=<?= (int) ($row['id_ad'] ?? 0) ?>"
                   class="btn btn-success btn-custom"
                   target="_blank" rel="noopener">

                    <i class="fa-solid fa-file-arrow-down me-2"></i>
                    Télécharger ma fiche d'adhésion

                </a>
                <?php endif; ?>

                <a href="../index.php"
                   class="btn <?= $type === 'don' ? 'btn-success' : 'btn-outline-success' ?> btn-custom">

                    <i class="fa-solid fa-house me-2"></i>
                    Retour à l’accueil

                </a>

                <?php if ($type !== 'don'): ?>
                <?php /* 🔧 CORRECTIF (lien mort) : pointait vers "login.php",
                     inexistant dans le dossier adhere/. La page de
                     connexion réelle est pages/login.php, servie par le
                     routeur du site via ?pages=login. */ ?>
                <a href="../index.php?pages=login"
                   class="btn btn-outline-success btn-custom">

                    <i class="fa-solid fa-user-lock me-2"></i>
                    Accéder à mon espace

                </a>
                <?php endif; ?>

            </div>

        </div>

    </div>

</div>

</body>
</html>