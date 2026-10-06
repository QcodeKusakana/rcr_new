<?php require_once __DIR__ . '/main_function.php'; ?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Vérification paiement</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

body{
    margin:0;
    padding:0;
    min-height:100vh;
    font-family:'Segoe UI',sans-serif;

    background:
        radial-gradient(circle at top,
        rgba(25,135,84,.18),
        transparent 45%),

        linear-gradient(
        rgba(10,20,40,.94),
        rgba(10,20,40,.94)
        ),

        url('../assets/img/bg.jpg');

    background-size:cover;
    background-position:center;
}

/* CONTAINER */
.loading-wrapper{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:25px;
}

/* CARD */
.loading-card{
    width:100%;
    max-width:650px;
    background:#fff;
    border:none;
    border-radius:30px;
    overflow:hidden;

    box-shadow:
        0 25px 70px rgba(0,0,0,.25),
        0 10px 20px rgba(0,0,0,.08);
}

/* HEADER */
.loading-header{
    background:linear-gradient(135deg,#198754,#20c997);
    color:#fff;
    padding:45px 30px;
    text-align:center;
    transition:background .4s ease;
}

.loading-header.phase-extended{
    background:linear-gradient(135deg,#fd7e14,#ffc107);
}

/* LOGO */
.logo{
    width:95px;
    height:95px;
    border-radius:50%;
    background:#fff;
    padding:6px;
    object-fit:cover;
}

/* TITLE */
.loading-title{
    font-size:30px;
    font-weight:800;
    margin-top:15px;
}

/* BODY */
.loading-body{
    padding:45px 30px;
    text-align:center;
}

/* SPINNER */
.spinner-custom{
    width:90px;
    height:90px;
    border:8px solid #e9ecef;
    border-top:8px solid #198754;
    border-radius:50%;
    margin:auto;

    animation:spin 1s linear infinite;
}

.phase-extended .spinner-custom{
    border-top-color:#fd7e14;
}

/* TEXT */
.status-title{
    font-size:28px;
    font-weight:800;
    margin-top:30px;
}

.status-text{
    color:#666;
    margin-top:12px;
    font-size:17px;
}

.status-warning{
    display:none;
    margin-top:18px;
    background:#fff8e6;
    border:1px solid #ffe6a8;
    border-left:5px solid #fd7e14;
    border-radius:14px;
    padding:16px 18px;
    color:#7a4b00;
    font-size:15px;
    text-align:left;
}

/* TIMER */
.timer-box{
    margin-top:25px;
    background:#f8f9fa;
    border-radius:18px;
    padding:18px;
    border-left:5px solid #198754;
}

.timer{
    font-size:35px;
    font-weight:900;
    color:#198754;
}

/* DOTS */
.dot-loader{
    display:flex;
    justify-content:center;
    gap:10px;
    margin-top:25px;
}

.dot-loader span{
    width:12px;
    height:12px;
    background:#198754;
    border-radius:50%;
    animation:bounce 1.3s infinite ease-in-out;
}

.phase-extended .dot-loader span{
    background:#fd7e14;
}

.dot-loader span:nth-child(2){
    animation-delay:.2s;
}

.dot-loader span:nth-child(3){
    animation-delay:.4s;
}

/* ANIMATION */
@keyframes spin{
    100%{
        transform:rotate(360deg);
    }
}

@keyframes bounce{

    0%,80%,100%{
        transform:scale(0);
        opacity:.3;
    }

    40%{
        transform:scale(1);
        opacity:1;
    }
}

</style>

</head>

<body>

<div class="loading-wrapper">

    <div class="loading-card">

        <!-- HEADER -->
        <div class="loading-header" id="loadingHeader">

            <img loading="lazy" decoding="async" src="../media/lo/logorcr.png"
                 class="logo"
                 alt="Logo">

            <div class="loading-title" id="loadingTitle">
                Paiement en cours
            </div>

            <div class="mt-2" id="loadingSubtitle">
                Vérification sécurisée FlexPay
            </div>

        </div>

        <!-- BODY -->
        <div class="loading-body" id="loadingBody">

            <!-- SPINNER -->
            <div class="spinner-custom"></div>

            <!-- TITLE -->
            <div class="status-title" id="statusTitle">
                Confirmation Mobile Money
            </div>

            <!-- TEXT -->
            <div class="status-text" id="statusText">

                Veuillez confirmer le paiement
                sur votre téléphone.

            </div>

            <?php /*
                🔧 AJOUT (correctif du bug "paiement débité mais affiché
                comme refusé") : ce bandeau n'apparaît qu'au-delà des 60
                premières secondes (phase de vérification prolongée). Il
                explique explicitement à l'utilisateur de ne pas payer une
                seconde fois pendant que le système continue de vérifier
                la confirmation FlexPay en arrière-plan, y compris de
                façon active (voir adhere/check_payment.php).
            */ ?>
            <div class="status-warning" id="statusWarning">
                <i class="fa-solid fa-circle-info me-2"></i>
                Cela prend parfois plus de temps que prévu pour les
                paiements Mobile Money en RDC. Si vous avez confirmé sur
                votre téléphone, <strong>ne payez surtout pas une deuxième
                fois</strong> : nous continuons à vérifier automatiquement
                votre transaction auprès de FlexPay.
            </div>

            <!-- DOTS -->
            <div class="dot-loader">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <!-- TIMER (uniquement pendant les 60 premières secondes) -->
            <div class="timer-box" id="timerBox">

                <div class="mb-2">
                    Temps restant
                </div>

                <div class="timer" id="timer">
                    60
                </div>

                <small class="text-muted">
                    Passé ce délai, nous continuons la vérification sans
                    vous redemander de payer
                </small>

            </div>

        </div>

    </div>

</div>

<script>

/**
 * PAYMENT ID / TYPE
 */
const paymentId =
    <?= (int)($_GET['payment_id'] ?? $_GET['id'] ?? 0) ?>;

const paymentType =
    <?= json_encode(($_GET['type'] ?? 'adhesion') === 'don' ? 'don' : 'adhesion') ?>;

/**
 * ÉLÉMENTS
 */
const timerElement       = document.getElementById('timer');
const timerBox           = document.getElementById('timerBox');
const statusWarning      = document.getElementById('statusWarning');
const statusTitle        = document.getElementById('statusTitle');
const statusText         = document.getElementById('statusText');
const loadingHeader      = document.getElementById('loadingHeader');
const loadingTitle       = document.getElementById('loadingTitle');
const loadingSubtitle    = document.getElementById('loadingSubtitle');
const loadingBody        = document.getElementById('loadingBody');

let polling   = null;
let countdown = null;
let extendedPollsDone = 0;

// 🔧 Phase 2 (vérification prolongée) : 20 tentatives espacées de 6s,
// soit 2 minutes supplémentaires, avant d'orienter vers la page
// "vérification en cours" (adhere/pending.php) plutôt que vers un échec.
const EXTENDED_MAX_POLLS   = 20;
const EXTENDED_INTERVAL_MS = 6000;

function handleStatus(status){

    if(status === "paid"){
        stopAll();
        window.location.href =
            "success.php?id=" + paymentId + "&type=" + paymentType;
        return true;
    }

    if(status === "failed" || status === "cancelled"){
        stopAll();
        window.location.href =
            "failed.php?id=" + paymentId + "&type=" + paymentType;
        return true;
    }

    return false;
}

function stopAll(){
    if(polling)   clearInterval(polling);
    if(countdown) clearInterval(countdown);
}

/**
 * PHASE 1 — POLLING NORMAL (0-60s)
 */
polling = setInterval(() => {

    fetch("check_payment.php?id=" + paymentId + "&type=" + paymentType)
        .then(response => response.json())
        .then(data => { handleStatus(data.status); })
        .catch(error => { console.log(error); });

}, 3000);

/**
 * PHASE 1 — COMPTE À REBOURS
 */
let timeLeft = 60;

countdown = setInterval(() => {

    timeLeft--;
    timerElement.innerHTML = timeLeft;

    if(timeLeft <= 0){

        clearInterval(countdown);
        clearInterval(polling);

        /**
         * 🔧 CORRECTIF (cause racine du bug signalé) : on n'affiche plus
         * automatiquement "échec" ici. On tente d'abord une résolution
         * immédiate côté serveur (adhere/expire_payment.php, qui lui-même
         * retente une vérification active FlexPay avant de conclure) ;
         * seul un statut réellement final (paid/failed/cancelled) déclenche
         * encore une redirection à ce stade. Sinon, on bascule en phase de
         * vérification prolongée au lieu de déclarer l'échec.
         */
        fetch("expire_payment.php?id=" + paymentId + "&type=" + paymentType)
            .then(response => response.json())
            .then(data => {

                if(handleStatus(data.status)){
                    return;
                }

                startExtendedPhase();

            })
            .catch(() => {
                // Même en cas d'erreur réseau sur cet appel, on ne
                // déclare jamais l'échec : on bascule en vérification
                // prolongée, qui retentera par elle-même.
                startExtendedPhase();
            });

    }

}, 1000);

/**
 * PHASE 2 — VÉRIFICATION PROLONGÉE (au-delà de 60s)
 */
function startExtendedPhase(){

    timerBox.style.display   = 'none';
    statusWarning.style.display = 'block';

    loadingHeader.classList.add('phase-extended');
    loadingBody.classList.add('phase-extended');

    loadingTitle.textContent    = 'Vérification en cours';
    loadingSubtitle.textContent = 'Merci de patienter quelques instants';
    statusTitle.textContent     = 'Vérification approfondie';
    statusText.textContent      =
        'Nous vérifions votre transaction directement auprès de FlexPay.';

    polling = setInterval(() => {

        extendedPollsDone++;

        fetch(
            "check_payment.php?id=" + paymentId +
            "&type=" + paymentType +
            "&active=1"
        )
            .then(response => response.json())
            .then(data => {

                if(handleStatus(data.status)){
                    return;
                }

                if(extendedPollsDone >= EXTENDED_MAX_POLLS){
                    clearInterval(polling);
                    window.location.href =
                        "pending.php?id=" + paymentId + "&type=" + paymentType;
                }

            })
            .catch(error => { console.log(error); });

    }, EXTENDED_INTERVAL_MS);

}

</script>

</body>
</html>
