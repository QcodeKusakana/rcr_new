<?php
/**
 * 🔧 NOUVEAU FICHIER (correctif du bug "paiement débité mais affiché
 * comme refusé") : point d'arrivée de la phase de vérification prolongée
 * de adhere/loading.php quand, après 60s + 2 minutes supplémentaires de
 * vérification active, FlexPay n'a toujours pas donné de réponse
 * définitive. Contrairement à l'ancien comportement, on n'affiche JAMAIS
 * "échec" dans ce cas — le statut réel en base reste "expired"
 * (ou "pending"), toujours ouvert à une confirmation tardive via le
 * webhook (adhere/webhook_flexpay.php) ou une vérification manuelle
 * ultérieure (espace membre, back-office).
 */

require_once __DIR__ . '/main_function.php';
require_once __DIR__ . '/../includes/payment_helpers.php';

$id   = (int) ($_GET['id'] ?? 0);
$type = ($_GET['type'] ?? 'adhesion') === 'don' ? 'don' : 'adhesion';

$row    = payment_get_row($bdd, $type, $id);
$status = $row['status'] ?? null;

/**
 * 🔧 AJOUT (demande explicite) : à chaque arrivée/rafraîchissement de
 * cette page, on retente une vérification ACTIVE auprès de FlexPay
 * (payment_flexpay_check() — même fonction que le webhook et que
 * adhere/check_payment.php), tant qu'un transaction_id existe pour ce
 * paiement. C'est une nouvelle tentative de résolution, pas une simple
 * lecture : on redonne sa chance à la confirmation à chaque visite,
 * utile si le webhook n'est jamais arrivé ou si la vérification
 * précédente a échoué transitoirement (réseau, timeout FlexPay...).
 *
 * ⚠️ Important : la seule PRÉSENCE d'un transaction_id ne suffit jamais
 * à elle seule à valider le paiement — un transaction_id est déjà généré
 * dès que FlexPay accepte d'ENVOYER la demande de confirmation au
 * téléphone, que le client valide ensuite ou non. C'est justement le
 * rôle de payment_flexpay_check() : interroger réellement FlexPay sur le
 * statut final de CE transaction_id (et vérifier le montant, voir
 * includes/payment_helpers.php) avant de considérer quoi que ce soit
 * comme payé. Sans ce filtre, n'importe quel visiteur ayant initié un
 * paiement sans jamais le confirmer sur son téléphone pourrait obtenir
 * sa fiche officielle simplement en revenant sur cette page.
 */
if (in_array($status, ['pending', 'processing', 'expired'], true) && (!empty($row['order_number']) || !empty($row['transaction_id']))) {
    require_once __DIR__ . '/../includes/flexpay_client.php';

    $resolved = payment_flexpay_check($bdd, $type, $id);

    if ($resolved !== null) {
        $status = $resolved;
    }
}

// Si le statut a finalement basculé (à l'instant, ou entre-temps via un
// webhook reçu pendant que l'utilisateur lisait cette page), on ne le
// laisse jamais coincé sur un message "en attente" obsolète.
if ($status === 'paid') {
    header('Location: success.php?id=' . $id . '&type=' . $type);
    exit;
}
if (in_array($status, ['failed', 'cancelled'], true)) {
    header('Location: failed.php?id=' . $id . '&type=' . $type);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Paiement en attente de confirmation</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

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
        rgba(253,126,20,0.25),
        transparent 60%),

        linear-gradient(
        rgba(15,23,42,0.94),
        rgba(15,23,42,0.94)),

        url('../assets/img/bg.jpg');

    background-size:cover;
    background-position:center;

    font-family:'Segoe UI',sans-serif;
}

.pending-card{
    width:100%;
    max-width:650px;
    border:none;
    border-radius:30px;
    overflow:hidden;
    background:#fff;

    box-shadow:
        0 25px 60px rgba(0,0,0,0.25),
        0 8px 20px rgba(0,0,0,0.1);
}

.pending-header{
    background:linear-gradient(135deg,#fd7e14,#ffc107);
    color:#fff;
    text-align:center;
    padding:45px 30px;
}

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
    font-size:52px;
    color:#fd7e14;
}

.pending-title{
    font-size:30px;
    font-weight:800;
    margin-top:20px;
}

.pending-body{
    padding:45px;
    text-align:center;
}

.pending-message{
    font-size:17px;
    color:#555;
    line-height:1.8;
}

.warning-box{
    background:#fff8e6;
    border:1px solid #ffe6a8;
    border-left:5px solid #fd7e14;
    border-radius:16px;
    padding:18px;
    margin-top:25px;
    color:#7a4b00;
    text-align:left;
}

.btn-custom{
    height:58px;
    border-radius:16px;
    font-weight:700;
    font-size:16px;
}

</style>
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

<div class="pending-card mx-auto">

    <!-- HEADER -->
    <div class="success-header" style="background:linear-gradient(135deg,#b45309,#f59e0b)">

        <div class="icon-box">
            <i class="fa-solid fa-hourglass-half"></i>
        </div>

        <div class="success-title">
            Paiement en attente de confirmation
        </div>

        <p class="mt-3 mb-0 opacity-75">
            FlexPay n'a pas encore confirmé la réception de l'argent
        </p>

    </div>

    <!-- BODY -->
    <div class="success-body">

        <div class="success-message">

            <?php if ($type === 'don'): ?>

                Votre don n'est <strong>pas encore confirmé</strong>.

                <br><br>

                Si vous avez validé la demande sur votre téléphone, patientez quelques instants
                puis cliquez sur « Vérifier à nouveau ». Aucun reçu n'est délivré tant que
                FlexPay n'a pas confirmé le paiement.

            <?php else: ?>

                Votre demande d'adhésion est enregistrée, mais votre paiement n'est
                <strong>pas encore confirmé</strong> par FlexPay.

                <br><br>

                Tant que l'argent n'est pas reçu, votre adhésion reste <strong>non validée</strong>.
                Elle sera validée automatiquement dès que FlexPay confirmera le paiement.

            <?php endif; ?>

        </div>

        <!-- BADGE -->
        <div class="badge-success-custom" style="background:#fff3e0;color:#9a5b00;border:1px solid #f5c26b">

            <i class="fa-solid fa-clock me-2"></i>
            Vérification en cours auprès de FlexPay

        </div>

        <!-- INFO -->
        <div class="info-box">

            <h5 class="fw-bold mb-3">
                <i class="fa-solid fa-circle-info me-2 text-warning"></i>
                Que faire maintenant ?
            </h5>

            <ul>

                <li>
                    Confirmez la demande de paiement reçue sur votre téléphone (code PIN Mobile Money).
                </li>

                <li>
                    Puis cliquez sur « Vérifier à nouveau » : la page interroge FlexPay.
                </li>

                <?php if ($type !== 'don'): ?>
                <li>
                    Vous pouvez télécharger dès maintenant votre fiche d'adhésion, marquée
                    <strong>« NON VALIDÉE »</strong>. Elle deviendra <strong>validée</strong>
                    (à retélécharger) une fois le paiement confirmé.
                </li>
                <?php endif; ?>

                <li>
                    Si votre compte a été débité mais que cette page reste en attente, ne payez pas
                    une seconde fois : la confirmation arrive en général sous quelques minutes.
                    Conservez votre référence : <strong><?= e((string) ($row['reference'] ?? '')) ?></strong>
                </li>

            </ul>

        </div>

        <!-- BUTTON -->
        <div class="d-grid gap-3 mt-4">

            <a href="pending.php?id=<?= (int) $id ?>&amp;type=<?= e($type) ?>" class="btn btn-warning btn-custom">
                <i class="fa-solid fa-rotate me-2"></i>
                Vérifier à nouveau
            </a>

            <?php if ($type !== 'don'): ?>

            <?php /*
                AJOUT (point 6 du cahier des charges : "rediriger
                automatiquement l'utilisateur vers la page de
                téléchargement... ne pas demander de revenir
                manuellement dans une autre partie du site") : lien
                direct vers la fiche officielle. Il fonctionne sans
                étape de connexion supplémentaire, car
                adhere/adhesion.funct.php pose déjà $_SESSION['id_ad']
                dès la soumission du formulaire (avant même le
                paiement) — voir le contrôle d'accès de
                admin/pages/print/print_adherer.php, qui exige que
                cette session corresponde exactement à l’adhésion
                (`cod`), et que le paiement soit bien "paid".
            */ ?>

            <a href="../admin/pages/print/print_adherer.php?cod=<?= (int) ($row['id_ad'] ?? 0) ?>"
               class="btn btn-outline-warning btn-custom"
               target="_blank" rel="noopener">

                <i class="fa-solid fa-file-arrow-down me-2"></i>
                Télécharger ma fiche (NON VALIDÉE)

            </a>

            <?php endif; ?>

            <a href="../index.php"
               class="btn <?= $type === 'don' ? 'btn-success' : 'btn-outline-success' ?> btn-custom">

                <i class="fa-solid fa-house me-2"></i>
                Retour à l’accueil

            </a>

            <?php if ($type !== 'don'): ?>

            <?php /*
                CORRECTIF (lien mort) : pointait vers "login.php",
                inexistant dans le dossier adhere/. La page de
                connexion réelle est pages/login.php, servie par le
                routeur du site via ?pages=login.
            */ ?>

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
