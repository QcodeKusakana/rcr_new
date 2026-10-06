<?php
include 'adhesion.funct.php';

/* Contexte de l'en-tête (menu commun piloté par la base, liens relatifs à adhere/) */
$page             = 'adhesion';
$isLoggedIn       = !empty($_SESSION['id_ad']);
$memberId         = $isLoggedIn ? (int) $_SESSION['id_ad'] : null;
$memberPhoto      = ($isLoggedIn && !empty($_SESSION['passeport'])) ? '../media/passeport/' . rawurlencode(basename((string) $_SESSION['passeport'])) : '';
$isAboutActive    = false;
$isProgressActive = false;
$menuHtml         = menu_rendu($page, '../', $isLoggedIn);

/*
|--------------------------------------------------------------------------
| CHARGEMENT DES PROVINCES
|--------------------------------------------------------------------------
*/
function load_province($bdd)
{
    $output = "<option value=''>Sélectionner une province</option>";

    $req = $bdd->query("
        SELECT id_p, nom_p 
        FROM provinces 
        ORDER BY nom_p ASC
    ");

    while ($row = $req->fetch(PDO::FETCH_ASSOC)) {

        $output .= '
            <option value="' . htmlspecialchars($row['id_p']) . '">
                ' . htmlspecialchars($row['nom_p']) . '
            </option>
        ';
    }

    return $output;
}

/*
|--------------------------------------------------------------------------
| CHARGEMENT DES QUALITES
|--------------------------------------------------------------------------
*/
function load_qualite($bdd, $idadm = null)
{
    $output = "<option value=''>Sélectionner la qualité</option>";

    if (!empty($idadm)) {

        $sql = "
            SELECT id_qt, designation 
            FROM qualites 
        ";

    } else {

        $sql = "
            SELECT id_qt, designation 
            FROM qualites 
        ";
    }

    $req = $bdd->query($sql);

    while ($row = $req->fetch(PDO::FETCH_ASSOC)) {

        $output .= '
            <option value="' . htmlspecialchars($row['id_qt']) . '">
               Membre  ' . htmlspecialchars($row['designation']) . '
            </option>
        ';
    }

    return $output;
}

$cotisations = $bdd->prepare("
    SELECT id_cot, nom_cot
    FROM cotisation
    ORDER BY nom_cot ASC
");
$cotisations->execute();
$cotisations = $cotisations->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;1,9..144,500&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<style>
/* =========================================================
   VARIABLES — mêmes tokens que le reste du site
   (cf. rcr-qui-sommes-nous.html, accueil.php, don.php...)

   CORRECTIF DESIGN : le dégradé violet/vert "SaaS" d'origine
   (--primary, --green, --dropdown-bg...) ne correspondait à
   aucune autre page du site — recentré sur la charte bleu
   encre / doré déjà utilisée partout ailleurs.
========================================================= */
:root {
    --header-bg: #1B2A44;
    --ink: #1B2A44;
    --ink-2: #233355;
    --gold: #9C7A2E;
    --gold-light: #B8912F;
    --paper: #F1E9D8;

    --white: #ffffff;

    --text-light: #F1E9D8;
    --text-muted: #A9B2C4;

    --border-dark: rgba(241, 233, 216, 0.1);

    --dropdown-bg: #17233A;
    --dropdown-hover: #223350;

    --shadow: 0 12px 35px rgba(0, 0, 0, 0.20);

    --radius: 0;
    --radius-btn: 0;

    --header-height: 78px;

    --serif: 'Fraunces', Georgia, serif;
    --sans: 'IBM Plex Sans', system-ui, sans-serif;

    --transition: all .2s ease;
}


/* =========================================================
   RESET HEADER
========================================================= */

#header *,
#header *::before,
#header *::after {
    box-sizing: border-box;
}

#header ul {
    margin: 0;
    padding: 0;
    list-style: none;
}

#header a {
    text-decoration: none;
}


/* =========================================================
   HEADER
========================================================= */

#header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;

    z-index: 9999;

    min-height: var(--header-height);

    display: flex;
    align-items: center;

    background: var(--header-bg);

    border-bottom: 2px solid var(--gold);

    box-shadow: 0 5px 30px rgba(0, 0, 0, 0.12);
}


/* =========================================================
   CONTAINER
========================================================= */

#header .header-container {
    width: min(100% - 40px, 1400px);

    margin: 0 auto;

    min-height: var(--header-height);

    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 24px;
}


/* =========================================================
   LOGO
========================================================= */

.header-logo {
    flex-shrink: 0;

    display: flex;
    align-items: center;
}

.header-logo a {
    display: flex;
    align-items: center;
}

.header-logo img {
    display: block;

    width: auto;
    height: 52px;

    max-width: 190px;

    object-fit: contain;

    transition: transform .25s ease;
}

.header-logo a:hover img {
    transform: scale(1.02);
}


/* =========================================================
   NAVIGATION
   CORRECTIF DESIGN (demande) : le menu s'étalait sur toute
   la largeur de la barre (gap et padding trop généreux pour le
   nombre d'items). Resserré ci-dessous — le menu forme
   maintenant un bloc compact aligné à droite, avec un fin
   séparateur pour distinguer navigation et actions (Soutien /
   Rejoindre / Connexion) plutôt que des espaces égaux partout.
========================================================= */

.main-navbar {
    display: flex;
    align-items: center;
}

.main-navbar > ul {
    display: flex;
    align-items: center;

    gap: 2px;
}


/* =========================================================
   LIENS
========================================================= */

.main-navbar > ul > li {
    position: relative;

    display: flex;
    align-items: center;
}

.main-navbar > ul > li > a {
    position: relative;

    display: flex;
    align-items: center;

    gap: 6px;

    padding: 10px 12px;

    color: var(--text-muted);

    font-family: var(--sans);

    font-size: 14px;
    font-weight: 600;

    white-space: nowrap;

    border-radius: 6px;

    transition: var(--transition);
}

.main-navbar > ul > li > a:hover {
    color: var(--white);

    background: rgba(241, 233, 216, 0.05);
}


/* actif */

.main-navbar > ul > li > a.active,
.main-navbar > ul > li.active > a {
    color: var(--white);
}

.main-navbar > ul > li > a.active::after,
.main-navbar > ul > li.active > a::after {
    content: "";

    position: absolute;

    left: 12px;
    right: 12px;
    bottom: 2px;

    height: 2px;

    background: var(--gold);
}


/* séparateur avant le groupe d'actions (Soutien / Rejoindre / Connexion) */
.support-item {
    margin-left: 10px;
    padding-left: 12px;
    border-left: 1px solid var(--border-dark);
}


/* =========================================================
   CHEVRON
========================================================= */

.main-navbar .dropdown > a .bi-chevron-down {
    font-size: 10px;

    transition: transform .2s ease;
}

.main-navbar .dropdown:hover > a .bi-chevron-down {
    transform: rotate(180deg);
}


/* =========================================================
   DROPDOWN
========================================================= */

.main-navbar .dropdown > ul {
    position: absolute;

    top: calc(100% + 10px);
    left: 0;

    min-width: 210px;

    padding: 7px;

    opacity: 0;
    visibility: hidden;

    transform: translateY(8px);

    background: var(--dropdown-bg);

    border: 1px solid var(--border-dark);

    box-shadow: 0 18px 45px rgba(0, 0, 0, .35);

    transition:
        opacity .2s ease,
        transform .2s ease,
        visibility .2s ease;

    z-index: 1000;
}

.main-navbar .dropdown:hover > ul {
    opacity: 1;
    visibility: visible;

    transform: translateY(0);
}

.main-navbar .dropdown > ul::before {
    content: "";

    position: absolute;

    top: -6px;
    left: 22px;

    width: 12px;
    height: 12px;

    background: var(--dropdown-bg);

    transform: rotate(45deg);

    border-top: 1px solid var(--border-dark);
    border-left: 1px solid var(--border-dark);
}

.main-navbar .dropdown > ul li {
    width: 100%;
}

.main-navbar .dropdown > ul li a {
    display: block;

    width: 100%;

    padding: 10px 12px;

    color: var(--text-light);

    font-family: var(--sans);

    font-size: 13.5px;
    font-weight: 500;

    border-radius: 4px;

    transition: var(--transition);
}

.main-navbar .dropdown > ul li a:hover {
    color: var(--white);

    background: var(--dropdown-hover);

    padding-left: 16px;
}


/* =========================================================
   BOUTON NOUS REJOINDRE
========================================================= */

.main-navbar .join-item {
    margin-left: 4px;
}

.main-navbar .join-item > a {
    padding: 10px 18px !important;

    color: var(--ink) !important;

    background: var(--gold);

    box-shadow: 0 6px 18px rgba(156, 122, 46, .3);
}

.main-navbar .join-item > a:hover {
    background: var(--gold-light);
}

.main-navbar .join-item > a i{color: var(--ink);}

/* Supprimer trait actif du bouton */

.main-navbar .join-item > a::after {
    display: none !important;
}


/* =========================================================
   SOUTIEN
========================================================= */

.main-navbar .support-item > a {
    color: var(--gold);
}

.main-navbar .support-item > a:hover {
    color: var(--gold-light);
}


/* =========================================================
   CONNEXION / MEMBRE
========================================================= */

.member-login > a {
    padding: 10px 16px !important;

    border: 1px solid rgba(241, 233, 216, .18);

    color: var(--white) !important;

    margin-left: 4px;
}

.member-login > a:hover {
    border-color: rgba(241, 233, 216, .35);

    background: rgba(241, 233, 216, .05) !important;
}


/* =========================================================
   MEMBRE CONNECTÉ
========================================================= */

.member-connected > a {
    padding: 5px 12px 5px 5px !important;

    border: 1px solid rgba(241, 233, 216, .18);

    background: rgba(241, 233, 216, .04);

    color: var(--white) !important;

    margin-left: 4px;
}

.member-connected > a:hover {
    background: rgba(241, 233, 216, .08) !important;
}

.member-avatar {
    width: 32px;
    height: 32px;

    object-fit: cover;

    border-radius: 50%;

    border: 2px solid var(--gold);

    background: #fff;
}


/* =========================================================
   MOBILE BUTTON
========================================================= */

.mobile-nav-toggle {
    display: none;

    color: var(--paper);

    font-size: 27px;

    cursor: pointer;
}


/* =========================================================
   RESPONSIVE TABLETTE
========================================================= */

@media (max-width: 1250px) {

    #header .header-container {
        width: min(100% - 28px, 1200px);
    }

    .main-navbar > ul > li > a {
        padding: 10px 9px;

        font-size: 13px;
    }

    .header-logo img {
        max-width: 160px;
        height: 46px;
    }
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 991px) {

    :root {
        --header-height: 70px;
    }

    #header .header-container {
        width: calc(100% - 28px);
    }

    .header-logo img {
        max-width: 150px;
        height: 42px;
    }

    .mobile-nav-toggle {
        display: block;
    }

    .main-navbar > ul {
        position: fixed;

        top: 82px;
        left: 14px;
        right: 14px;

        max-height: calc(100vh - 100px);

        overflow-y: auto;

        display: none;

        padding: 12px;

        background: var(--dropdown-bg);

        border: 1px solid var(--border-dark);

        box-shadow: 0 25px 55px rgba(0, 0, 0, .4);
    }

    .main-navbar.mobile-open > ul {
        display: block;
    }

    .main-navbar > ul > li {
        width: 100%;

        display: block;

        margin: 2px 0;
    }

    .main-navbar > ul > li > a {
        width: 100%;

        padding: 13px 14px;

        font-size: 14px;
    }

    .support-item {
        margin: 6px 0 0;
        padding: 10px 0 0;
        border-left: none;
        border-top: 1px solid var(--border-dark);
    }

    .main-navbar .join-item {
        margin: 8px 0 0;
    }

    .main-navbar .join-item > a {
        justify-content: center;
    }

    .member-login > a,
    .member-connected > a {
        margin-left: 0;
        margin-top: 8px;
    }

    .main-navbar .dropdown > ul {
        position: static;

        display: none;

        min-width: 100%;

        margin: 4px 0 6px;

        opacity: 1;
        visibility: visible;

        transform: none;

        box-shadow: none;

        background: rgba(241, 233, 216, .03);
    }

    .main-navbar .dropdown.open > ul {
        display: block;
    }

    .main-navbar .dropdown > ul::before {
        display: none;
    }
}


/* =========================================================
   PETITS ÉCRANS
========================================================= */

@media (max-width: 480px) {

    #header .header-container {
        width: calc(100% - 20px);
    }

    .header-logo img {
        max-width: 130px;
        height: 38px;
    }
}


/* =========================================================
   ESPACE APRÈS HEADER FIXE
========================================================= */

.header-spacer {
    height: var(--header-height);
}
</style>
<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>FORMULAIRE D'ADHÉSION - RCR</title>

    <!-- Bootstrap (grille et composants de formulaire — conservé,
         seule l'habillage visuel est redéfini ci-dessous) -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons — conservé : les icônes "bi-*" sont aussi
         utilisées par le script de l'assistant pas-à-pas (data-step-icon) -->
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- JQuery -->
    <script src="jquery.min.js"></script>

<style>

/* =========================================================
   ADHÉSION — mêmes tokens que le reste du site
   (cf. rcr-qui-sommes-nous.html, rcr-direction.html, don.php,
   evenements.php, accueil.php)
========================================================= */
:root{
    --ink:#1B2A44;
    --ink-2:#233355;
    --paper:#F1E9D8;
    --paper-2:#E7DBBF;
    --paper-line:#CBBB92;
    --gold:#9C7A2E;
    --red:#7D2330;
    --green:#3F5B3E;
    --text:#241F1A;
    --text-soft:#5B5346;
    --serif:'Fraunces', Georgia, serif;
    --sans:'IBM Plex Sans', system-ui, sans-serif;
}

body{
    background:var(--paper);
    font-family:var(--sans);
    color:var(--text);
    padding-top:96px;
}

/* ---------- en-tête ---------- */
#header{
    background:var(--ink) !important;
    height:80px;
    box-shadow:none;
    border-bottom:1px solid rgba(241,233,216,.12);
}

/* ---------- conteneurs de formulaire ---------- */
.form-container{
    background:#FBF8F0;
    border:1px solid var(--paper-line);
    border-radius:0;
    padding:30px clamp(18px,3vw,34px);
    box-shadow:none;
}

.form-container > h2{
    font-family:var(--serif);
    font-weight:600;
    font-size:26px;
    color:var(--ink);
    text-transform:none;
}

.form-container h5{
    font-family:var(--serif);
    font-weight:600;
    color:var(--ink);
}

/* ---------- champs ---------- */
.form-control,
.form-select{
    height:48px;
    border-radius:0;
    border:1px solid var(--paper-line);
    background:#fff;
    margin-bottom:15px;
}
.form-control:focus,
.form-select:focus{
    border-color:var(--gold);
    box-shadow:0 0 0 3px rgba(156,122,46,.15);
}

textarea.form-control{height:auto;}

label{
    font-weight:600;
    margin-bottom:6px;
    color:var(--ink);
    font-size:14.5px;
}

.obl,
.text-danger{color:var(--red) !important;}

/* ---------- cartes de section (fond uni, plus de bandeaux arc-en-ciel) ---------- */
.card{
    border-radius:0;
    border:1px solid var(--paper-line);
}
.card-header{
    background:var(--ink) !important;
    color:var(--paper) !important;
    border-radius:0 !important;
    border-bottom:2px solid var(--gold);
}
.card-header h5{color:var(--paper);}
.card-header .bi{color:var(--gold);}

/* ---------- alertes ---------- */
.alert{border-radius:0; border-width:1px; border-left-width:4px;}
.alert-danger{
    background:rgba(125,35,48,.07);
    border-color:var(--red) !important;
    color:var(--red);
}
.alert-success{
    background:rgba(63,91,62,.08);
    border-color:var(--green) !important;
    color:var(--green);
}

/* ---------- boutons ---------- */
.btn{border-radius:0; font-weight:600;}
.btn-primary{
    background:var(--ink);
    border-color:var(--ink);
}
.btn-primary:hover{background:var(--ink-2); border-color:var(--ink-2);}
.btn-outline-secondary{
    color:var(--ink);
    border-color:var(--ink);
}
.btn-outline-secondary:hover{background:var(--ink); border-color:var(--ink); color:var(--paper);}
.btn-danger{
    height:50px;
    font-weight:bold;
    background:var(--gold);
    border-color:var(--gold);
    color:var(--ink);
}
.btn-danger:hover{background:#B8912F; border-color:#B8912F; color:var(--ink);}

/* =========================================================
   ASSISTANT PAS-À-PAS (comportement JS inchangé — habillage
   uniquement : cf. #stepperNav / .form-step / #btn* plus bas)
========================================================= */

.stepper{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    list-style:none;
    margin:0 0 30px;
    padding:0;
    gap:4px;
}

.stepper li{
    flex:1;
    text-align:center;
    position:relative;
}

.stepper li:not(:last-child)::after{
    content:"";
    position:absolute;
    top:18px;
    left:calc(50% + 24px);
    right:calc(-50% + 24px);
    height:2px;
    background:var(--paper-line);
    z-index:0;
}

.stepper li.done:not(:last-child)::after{
    background:var(--gold);
}

.stepper .step-circle{
    width:36px;
    height:36px;
    border-radius:50%;
    background:var(--paper-2);
    color:var(--text-soft);
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:700;
    margin:0 auto 8px;
    position:relative;
    z-index:1;
    transition:.25s ease;
    border:3px solid #FBF8F0;
    box-shadow:0 0 0 1px var(--paper-line);
}

.stepper li.active .step-circle{
    background:var(--ink);
    color:var(--paper);
    box-shadow:0 0 0 1px var(--ink);
}

.stepper li.done .step-circle{
    background:var(--gold);
    color:#fff;
    box-shadow:0 0 0 1px var(--gold);
}

.stepper .step-label{
    font-size:12.5px;
    font-weight:600;
    color:var(--text-soft);
    display:block;
    line-height:1.2;
}

.stepper li.active .step-label{
    color:var(--ink);
}

@media(max-width:768px){

    .stepper .step-label{
        display:none;
    }

    .stepper li:not(:last-child)::after{
        top:16px;
    }

    .stepper .step-circle{
        width:32px;
        height:32px;
    }
}

.step-progress-text{
    text-align:center;
    color:var(--text-soft);
    font-size:14px;
    margin-bottom:25px;
    font-weight:600;
}

.form-step{
    animation:fadeStepIn .3s ease;
}

@keyframes fadeStepIn{

    from{
        opacity:0;
        transform:translateY(8px);
    }

    to{
        opacity:1;
        transform:translateY(0);
    }
}

.wizard-nav{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-top:10px;
    margin-bottom:40px;
}

.wizard-nav .btn{
    border-radius:0;
    font-weight:600;
    padding:12px 28px;
}

/* ---------- engagement (colonne latérale) ---------- */
.engagement-list p{
    display:flex;
    gap:10px;
    font-size:14.5px;
    line-height:1.7;
    color:var(--text);
    margin:0 0 14px;
    padding-bottom:14px;
    border-bottom:1px solid var(--paper-line);
}
.engagement-list p:last-child{border-bottom:none; margin-bottom:0; padding-bottom:0;}

</style>

</head>

<body>

<!-- HEADER -->
<header id="header" class="fixed-top d-flex align-items-center shadow-sm">

    <div class="container d-flex justify-content-between align-items-center">

        <!-- LOGO -->
        <a href="../index.php?pages=home" class="d-flex align-items-center">
            <img loading="lazy" decoding="async" src="./../media/lo/logo.png"
                 style="width:190px; height:68px; object-fit:contain;"
                 alt="Logo">
        </a>

    </div>
     <!-- NAVIGATION -->
        <nav
            id="navbar"
            class="main-navbar"
            aria-label="Navigation principale"
        >

            <ul>
<?php if ($menuHtml !== ''): echo $menuHtml; else: ?>


                <!-- ACCUEIL -->
                <li>

                    <a
                        href="../index.php?pages=home"
                        class="<?= $page === 'home' ? 'active' : '' ?>"
                    >
                        Accueil
                    </a>

                </li>


                <!-- QUI SOMMES-NOUS -->
                <li
                    class="dropdown
                    <?= $isAboutActive ? 'active' : '' ?>"
                >

                    <a href="#">

                        <span>
                            Qui sommes-nous ?
                        </span>

                        <i class="bi bi-chevron-down"></i>

                    </a>


                    <ul>

                        <li>
                            <a href="../index.php?pages=apropos">
                                Presentation
                            </a>
                        </li>

                        <li>
                            <a href="../index.php?pages=parti">
                                Chef du parti
                            </a>
                        </li>

                    </ul>

                </li>


                <!-- OÙ EN SOMMES-NOUS -->
                <li
                    class="dropdown
                    <?= $isProgressActive ? 'active' : '' ?>"
                >

                    <a href="#">

                        <span>
                            Ou en sommes-nous ?
                        </span>

                        <i class="bi bi-chevron-down"></i>

                    </a>


                    <ul>

                        <li>
                            <a href="#">
                                Ou en sommes-nous ?
                            </a>
                        </li>

                        <li>
                            <a href="../index.php?pages=publication">
                                Nos evenements
                            </a>
                        </li>

                    </ul>

                </li>


                <!-- SOUTIEN -->
                <li class="support-item">

                    <a
                        href="../index.php?pages=soutenir"
                        class="<?= $page === 'soutenir' ? 'active' : '' ?>"
                    >
                        Votre soutien
                    </a>

                </li>
                <!-- NOUS REJOINDRE -->
                <li class="dropdown join-item">

                    <a href="#">

                        <span>
                            Nous rejoindre
                        </span>

                        <i class="bi bi-chevron-down"></i>

                    </a>


                    <ul>

                        <li>

                            <a href="./adhesion.php">
                                <i class="bi bi-person-plus me-2"></i>

                                Adherer
                            </a>

                        </li>


                        <li>

                            <a
                                href="<?=
                                    $isLoggedIn
                                    ? '../index.php?pages=esp_membre'
                                    : '../index.php?pages=login'
                                ?>"
                            >

                                <i class="bi bi-arrow-repeat me-2"></i>

                                Renouveler mon adhesion

                            </a>

                        </li>

                    </ul>

                </li>

                <?php endif; ?>
                <!-- CONNEXION / MEMBRE -->

                <?php if ($isLoggedIn): ?>

                    <li class="member-connected">

                        <a
                            href="../index.php?pages=esp_membre"
                            title="Accéder à mon espace membre"
                        >

                            <?php if ($memberPhoto): ?>

                                <img loading="lazy" decoding="async"
                                    class="member-avatar"
                                    src="<?= htmlspecialchars(
                                        $memberPhoto,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    alt="Profil"
                                >

                            <?php else: ?>

                                <i class="bi bi-person-circle"></i>

                            <?php endif; ?>

                            <span>
                                Mon espace
                            </span>

                        </a>

                    </li>

                <?php else: ?>

                    <li class="member-login">

                        <a
                            href="../index.php?pages=login"
                        >

                            Connexion

                        </a>

                    </li>

                <?php endif; ?>

            </ul>


            <!-- BOUTON MOBILE -->
            <i
                class="bi bi-list mobile-nav-toggle"
                id="mobileNavToggle"
                aria-label="Ouvrir le menu"
            ></i>

        </nav>

</header>

<section class="container-fluid my-5">

    <div class="row">
 <!-- ENGAGEMENT -->
        <div class="col-lg-4">

            <div class="form-container">

                <h5 class="mb-4">
                    Acte de mon engagement
                </h5>

                <div class="engagement-list">

                    <p>1. Je déclare rester fidèle aux idéaux du RCR.</p>
                    <p>2. J'accepte les communications politiques et administratives.</p>
                    <p>3. Je soutiens le mouvement en tout lieu.</p>
                    <p>4. Je contribuerai aux nouvelles adhésions.</p>
                    <p>5. J'accepte les conditions de cotisation et de traitement des données.</p>

                </div>

            </div>

        </div>
        <!-- FORMULAIRE -->
<div class="col-lg-8">

            <div class="form-container">

                <h2 class="mb-4 text-center">
                    Formulaire d'adhésion
                </h2>

                <!-- MESSAGE ERREUR -->
                <?php if(isset($errors)): ?>

                    <div class="alert alert-danger">
                        <?= htmlspecialchars($errors) ?>
                    </div>

                <?php endif; ?>

                <!-- MESSAGE SUCCESS -->
                <?php if(isset($sms)): ?>

                    <div class="alert alert-success">
                        <?= htmlspecialchars($sms) ?>
                    </div>

                <?php endif; ?>


                <?php if (!empty($_SESSION['adhesion_erreur'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['adhesion_erreur']) ?></div>
                    <?php unset($_SESSION['adhesion_erreur']); ?>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data" class="php-email-form" id="adhesionForm" novalidate>

                <?= csrf_field() ?>

    <!-- ======================= -->
    <!-- ASSISTANT PAS-À-PAS : généré automatiquement en JS à partir des
         sections .form-step ci-dessous (voir <script> en bas de page) —
         s'adapte tout seul si la section "Sponsor" est masquée. -->
    <!-- ======================= -->
    <ol class="stepper" id="stepperNav"></ol>
    <p class="step-progress-text" id="stepProgressText"></p>

    <!-- ======================= -->
    <!-- INFORMATIONS PERSONNELLES -->
    <!-- ======================= -->
    <div class="card shadow-sm border-0 mb-4 form-step" data-step-label="Catégorie" data-step-icon="bi-award-fill">
        <div class="card-header"><h5 class="mb-0"><i class="bi bi-award-fill"></i> Choisissez votre catégorie de membre</h5></div>
        <div class="card-body"><div class="rcr-opts" id="catList"></div></div>
    </div>
    <div class="card shadow-sm border-0 mb-4 form-step" data-step-label="Grade" data-step-icon="bi-gem">
        <div class="card-header"><h5 class="mb-0"><i class="bi bi-gem"></i> Choisissez votre grade</h5></div>
        <div class="card-body"><div class="rcr-opts" id="gradeList"></div></div>
    </div>
    <div class="card shadow-sm border-0 mb-4 form-step" data-step-label="Cotisation" data-step-icon="bi-calendar-check">
        <div class="card-header"><h5 class="mb-0"><i class="bi bi-calendar-check"></i> Mode de cotisation</h5></div>
        <div class="card-body"><div class="rcr-opts" id="periodeList"></div></div>
    </div>
    <div class="card shadow-sm border-0 mb-4 form-step" data-step-label="Vos informations" data-step-icon="bi-person-fill">

        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-person-fill"></i>
                Informations personnelles
            </h5>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        Nom <span class="obl">*</span>
                    </label>

                    <input type="text"
                           name="nom"
                           class="form-control"
                           required
                           value="<?= htmlspecialchars($nom ?? '') ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        Postnom <span class="obl">*</span>
                    </label>

                    <input type="text"
                           name="postnom"
                           class="form-control"
                           required
                           value="<?= htmlspecialchars($postnom ?? '') ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        Prénom <span class="obl">*</span>
                    </label>

                    <input type="text"
                           name="prenom"
                           class="form-control"
                           required
                           value="<?= htmlspecialchars($prenom ?? '') ?>">
                </div>

            </div>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Civilité <span class="obl">*</span>
                    </label>

                    <select name="civilite"
                            class="form-select"
                            required>

                        <option value="">Sélectionner</option>
                        <option value="Mr">Mr</option>
                        <option value="Mme">Mme</option>
                        <option value="Mlle">Mlle</option>

                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Nationalité <span class="obl">*</span>
                    </label>

                    <select name="nationalite"
                            class="form-select"
                            required>

                        <option value="CD">Congo-Kinshasa</option>
                        <option value="FR">France</option>
                        <option value="BE">Belgique</option>
                        <option value="US">États-Unis</option>

                    </select>
                </div>

            </div>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        E-mail <span class="obl">*</span>
                    </label>

                    <input type="email"
                           name="mail"
                           class="form-control"
                           required
                           value="<?= htmlspecialchars($mail ?? '') ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Téléphone <span class="obl">*</span>
                    </label>

                    <input type="tel"
                           name="telephone"
                           class="form-control"
                           required
                           value="<?= htmlspecialchars($telephone ?? '') ?>">
                </div>

            </div>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Date de naissance <span class="obl">*</span>
                    </label>

                    <input type="date"
                           name="datenaiss"
                           class="form-control"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Code postal <span class="obl">*</span>
                    </label>

                    <input type="text"
                           name="codepostal"
                           class="form-control"
                           required>
                </div>

            </div>

        </div>

    </div>


    <!-- ======================= -->
    <!-- INFORMATIONS ADMINISTRATIVES -->
    <!-- ======================= -->
    <div class="card shadow-sm border-0 mb-4 form-step" data-step-label="Infos administratives" data-step-icon="bi-briefcase-fill">
        <div class="card-header"><h5 class="mb-0"><i class="bi bi-briefcase-fill"></i> Informations administratives et compte</h5></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Catégorie socioprofessionnelle</label>
                    <input type="text" name="categorie" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Diplôme / Profession</label>
                    <input type="text" name="diplome" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Ville</label>
                    <input type="text" name="ville" class="form-control">
                </div>
            </div>
            <hr>
            <p class="text-muted mb-2">Votre compte membre sera créé automatiquement. Vous vous connecterez avec votre <strong>code d'adhésion</strong> (affiché après paiement) ou votre e-mail, et ce mot de passe.</p>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Mot de passe <span class="obl">*</span></label>
                    <input type="password" name="mot_de_passe" id="mot_de_passe" class="form-control" minlength="8" autocomplete="new-password" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Confirmer le mot de passe <span class="obl">*</span></label>
                    <input type="password" name="mot_de_passe2" id="mot_de_passe2" class="form-control" minlength="8" autocomplete="new-password" required>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0 mb-4 form-step" data-step-label="Localisation" data-step-icon="bi-geo-alt-fill">

        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-geo-alt-fill"></i>
                Adresse et localisation
            </h5>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        Province <span class="obl">*</span>
                    </label>

                    <select name="province"
                            id="province"
                            class="form-select"
                            required>

                        <?= load_province($bdd); ?>

                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        Territoire <span class="obl">*</span>
                    </label>

                    <select name="territoire"
                            id="territoire"
                            class="form-select"
                            required>

                        <option value="">Sélectionner</option>

                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        Secteur <span class="obl">*</span>
                    </label>

                    <select name="secteur"
                            id="secteur"
                            class="form-select"
                            required>

                        <option value="">Sélectionner</option>

                    </select>
                </div>

            </div>

            <div class="row">

                <div class="col-md-12 mb-3">
                    <label class="form-label">
                        Adresse complète <span class="obl">*</span>
                    </label>

                    <textarea name="adresse"
                              class="form-control"
                              rows="4"
                              required><?= htmlspecialchars($adresse ?? '') ?></textarea>
                </div>

            </div>

        </div>

    </div>


    <!-- ======================= -->
    <!-- DOCUMENTS -->
    <!-- ======================= -->
    <div class="card shadow-sm border-0 mb-4 form-step" data-step-label="Documents" data-step-icon="bi-file-earmark-arrow-up-fill">

        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-file-earmark-arrow-up-fill"></i>
                Documents requis
            </h5>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Photo passeport <span class="obl">*</span>
                    </label>

                    <input type="file"
                           name="passeport"
                           class="form-control"
                           accept=".jpg,.jpeg,.png"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        CV PDF
                    </label>

                    <input type="file"
                           name="cv"
                           class="form-control"
                           accept=".pdf">

                </div>

            </div>

        </div>

    </div>


    <!-- ======================= -->
    <!-- SPONSOR -->
    <!-- ======================= -->
    <?php if(empty($_GET['idmbre'])): ?>

    <div class="card shadow-sm border-0 mb-4 form-step" data-step-label="Sponsor" data-step-icon="bi-people-fill">

        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-people-fill"></i>
                Membre sponsor
            </h5>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        ID Sponsor
                    </label>

                    <input type="text"
                           id="search_text"
                           class="form-control"
                           placeholder="Entrer ID sponsor">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Nom Sponsor
                    </label>

                    <select name="id_dest"
                            id="result"
                            class="form-select">

                        <option value="">Sélectionner</option>

                    </select>
                </div>

            </div>

        </div>

    </div>

    <?php endif; ?>


    <!-- ======================= -->
    <!-- ENCADREUR -->
    <!-- ======================= -->
    <div class="card shadow-sm border-0 mb-4 form-step" data-step-label="Encadreur" data-step-icon="bi-diagram-3-fill">

        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-diagram-3-fill"></i>
                Encadreur et placement
            </h5>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        ID Encadreur
                    </label>

                    <input type="text"
                           id="search_text2"
                           class="form-control"
                           placeholder="Entrer ID encadreur">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        Nom Encadreur
                    </label>

                    <select name="id_dest2"
                            id="result2"
                            class="form-select">

                        <option value="">Sélectionner</option>

                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">
                        Placement
                    </label>

                    <select name="placement"
                            class="form-select">

                        <option value="">Choix</option>
                        <option value="A">PIED A</option>
                        <option value="B">PIED B</option>

                    </select>
                </div>

            </div>

            <!-- ======================= -->
            <!-- ACCEPTATION OBLIGATOIRE -->
            <!-- ======================= -->
            

        </div>

    </div>


    <!-- ======================= -->
    <!-- NAVIGATION DE L'ASSISTANT -->
    <!-- Une seule barre de navigation, partagée par toutes les étapes :
         le script en bas de page affiche/masque "Précédent" et bascule
         entre "Suivant" et le bouton d'envoi final selon l'étape
         active. -->
    <div class="card shadow-sm border-0 mb-4 form-step" data-step-label="Récapitulatif" data-step-icon="bi-check2-circle">
        <div class="card-header"><h5 class="mb-0"><i class="bi bi-check2-circle"></i> Récapitulatif et validation</h5></div>
        <div class="card-body">
            <table class="table table-sm" id="recapTable"></table>
            <p class="text-muted small">Le montant définitif est recalculé par le serveur à partir du barème en vigueur. Le paiement s'effectue à l'étape suivante (Mobile Money ou carte bancaire).</p>
            <div class="form-check mt-3">
                <input class="form-check-input"
                       type="checkbox"
                       name="acceptation_legale"
                       id="acceptation_legale"
                       value="1"
                       required>

                <label class="form-check-label" for="acceptation_legale">
                    J’ai pris connaissance et j’accepte
                    <a href="../index.php?pages=mention" target="_blank" rel="noopener">
                        les mentions légales
                    </a>
                    et
                    <a href="../index.php?pages=politique-confidentialite" target="_blank" rel="noopener">
                        la politique de confidentialité
                    </a>
                    <span class="obl">*</span>
                </label>

                <div class="invalid-feedback">
                    Vous devez accepter les mentions légales et la politique de confidentialité avant d’envoyer votre demande.
                </div>
            </div>
        </div>
    </div>
    <!-- ======================= -->
    <div class="wizard-nav">

        <button type="button"
                id="btnPrev"
                class="btn btn-outline-secondary">

            <i class="bi bi-arrow-left"></i>
            Précédent

        </button>

        <button type="button"
                id="btnNext"
                class="btn btn-primary ms-auto">

            Suivant
            <i class="bi bi-arrow-right"></i>

        </button>

        <button type="submit"
                name="btnenvoyer"
                id="btnSubmit"
                class="btn btn-danger btn-lg px-5 ms-auto d-none">

            <i class="bi bi-send-fill"></i>
            Valider et passer au paiement

        </button>

    </div>

</form>




            </div>

        </div>



       

    </div>

</section>

<script>

$(document).ready(function(){

    /*
    |--------------------------------------------------------------------------
    | PROVINCE -> TERRITOIRE
    |--------------------------------------------------------------------------
    */
    $('#province').change(function(){

        let province_id = $(this).val();

        $.ajax({

            url:"ajax.php",
            method:"POST",

            data:{
                provinceID:province_id
            },

            success:function(data){

                $('#territoire').html(data);
            },

            error:function(xhr){

                console.log(xhr.responseText);
            }
        });
    });

    /*
    |--------------------------------------------------------------------------
    | TERRITOIRE -> SECTEUR
    |--------------------------------------------------------------------------
    */
    $('#territoire').change(function(){

        let territoire_id = $(this).val();

        $.ajax({

            url:"ajax2.php",
            method:"POST",

            data:{
                territoireID:territoire_id
            },

            success:function(data){

                $('#secteur').html(data);
            },

            error:function(xhr){

                console.log(xhr.responseText);
            }
        });
    });

    /*
    |--------------------------------------------------------------------------
    | QUALITE -> GRADE
    |--------------------------------------------------------------------------
    */
    /* qualité/grade/cotisation : voir script « tarifs » plus bas */

    /*
    |--------------------------------------------------------------------------
    | ASSISTANT PAS-À-PAS (audit / design)
    |--------------------------------------------------------------------------
    | Design uniquement : le formulaire reste UN SEUL <form>, envoyé en
    | un seul POST comme avant (adhesion.funct.php n'a pas changé). On se
    | contente d'afficher une section (.form-step) à la fois, avec une
    | validation native du navigateur avant de passer à la suivante.
    |
    | Le nombre d'étapes n'est pas figé en dur : il est déduit du nombre
    | réel de .form-step présents dans la page — la section "Sponsor"
    | étant masquée côté serveur quand ?idmbre est fourni (parrainage),
    | l'assistant s'adapte tout seul sans rien à changer ici.
    */
    const $steps        = $('.form-step');
    const $stepperNav   = $('#stepperNav');
    const $progressText = $('#stepProgressText');
    const $btnPrev       = $('#btnPrev');
    const $btnNext       = $('#btnNext');
    const $btnSubmit     = $('#btnSubmit');

    let currentStep = 0;

    // Construction de la barre d'étapes à partir des data-step-* de
    // chaque section — un seul endroit à modifier si une étape change.
    $steps.each(function(i){

        $stepperNav.append(
            '<li data-index="' + i + '">' +
                '<div class="step-circle"><i class="bi ' + $(this).data('step-icon') + '"></i></div>' +
                '<span class="step-label">' + $(this).data('step-label') + '</span>' +
            '</li>'
        );
    });

    const $stepperItems = $stepperNav.find('li');

    function updateStepper(){

        $stepperItems.each(function(i){

            $(this).removeClass('active done');

            if (i < currentStep) {
                $(this).addClass('done');
            } else if (i === currentStep) {
                $(this).addClass('active');
            }
        });

        $progressText.text(
            'Étape ' + (currentStep + 1) + ' sur ' + $steps.length +
            ' — ' + $steps.eq(currentStep).data('step-label')
        );
    }

    function showStep(index){

        $steps.hide().eq(index).show();

        currentStep = index;

        updateStepper();

        $btnPrev.toggleClass('invisible', index === 0);

        if (index === $steps.length - 1) {

            $btnNext.addClass('d-none');
            $btnSubmit.removeClass('d-none');

        } else {

            $btnNext.removeClass('d-none');
            $btnSubmit.addClass('d-none');
        }

        $('html, body').animate({
            scrollTop: $('#adhesionForm').offset().top - 100
        }, 250);
    }

    // Valide uniquement les champs de l'étape visible, avec le message
    // natif du navigateur (reportValidity) sur le premier champ en
    // erreur — pas besoin de dupliquer une logique de validation.
  /**
 * Vérifie que tous les champs obligatoires
 * de l'étape actuelle sont remplis avant de continuer.
 */
function validateStep(index) {
    const $currentStep = $steps.eq(index);
    let firstInvalid = null;

    // Vérifier uniquement les champs marqués "required"
    $currentStep
        .find('input[required], select[required], textarea[required]')
        .each(function () {

            // Vérification HTML5
            if (!this.checkValidity()) {
                firstInvalid = this;
                return false; // arrêter la recherche
            }
        });

    // Si un champ obligatoire n'est pas rempli
    if (firstInvalid) {

        // Afficher le message de validation du navigateur
        firstInvalid.reportValidity();

        // Placer le curseur sur le champ concerné
        firstInvalid.focus();

        return false;
    }

    // Tous les champs obligatoires sont valides
    return true;
}

   $btnNext.on('click', function () {

        if (validateStep(currentStep)) {
            showStep(currentStep + 1);
        }
    
    });

    $btnPrev.on('click', function(){
        showStep(currentStep - 1);
    });

    // La touche Entrée dans un champ ne doit pas envoyer le formulaire
    // en entier depuis la 1ère étape (le bouton d'envoi existe déjà
    // dans le DOM, juste masqué) — elle avance d'une étape à la place,
    // ou envoie si on est déjà sur la dernière étape.
    $('#adhesionForm').on('keydown', 'input:not([type=file])', function(e){

        if (e.key === 'Enter') {

            e.preventDefault();

            if (currentStep === $steps.length - 1) {
                $btnSubmit.trigger('click');
            } else {
                $btnNext.trigger('click');
            }
        }
    });

    // Filet de sécurité : re-valide la dernière étape au moment de
    // l'envoi réel (au cas où un champ y aurait été modifié après coup).
    $('#adhesionForm').on('submit', function(e){

        if (!validateStep($steps.length - 1)) {
            e.preventDefault();
        }
    });

    showStep(0);

});

</script>


<style>
.rcr-opts{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px}
.rcr-opt{position:relative;border:2px solid #d9dee8;border-radius:12px;padding:16px;cursor:pointer;background:#fff;transition:border-color .15s,box-shadow .15s}
.rcr-opt:hover{border-color:#9C7A2E}
.rcr-opt input{position:absolute;opacity:0;pointer-events:none}
.rcr-opt.sel{border-color:#1B2A44;box-shadow:0 0 0 3px rgba(156,122,46,.25);background:#fffdf8}
.rcr-opt .t{font-weight:700;color:#1B2A44;font-size:1.05rem}
.rcr-opt .p{margin-top:6px;font-size:1.25rem;font-weight:700;color:#9C7A2E}
.rcr-opt small{color:#6b7280}
</style>
<script>
window.RCR_TARIFS = <?= json_encode($TARIFS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
(function(){
  var T = window.RCR_TARIFS, $f = $('#adhesionForm');
  var catId = 0, gradeId = 0, perId = 0;
  function usd(n){ return (Math.round(n*100)/100).toLocaleString('fr-FR',{minimumFractionDigits:0,maximumFractionDigits:2}) + ' USD'; }
  function cat(){ return T.categories.find(function(c){return c.id===catId;}); }
  function grade(){ var c=cat(); return c && c.grades.find(function(g){return g.id===gradeId;}); }
  function per(){ return T.periodes.find(function(p){return p.id===perId;}); }
  function opt(name,val,title,price,sub,checked){
    return '<label class="rcr-opt'+(checked?' sel':'')+'"><input type="radio" name="'+name+'" value="'+val+'" required'+(checked?' checked':'')+'>'
      +'<div class="t"></div>'+(price?'<div class="p">'+price+'</div>':'')+(sub?'<small>'+sub+'</small>':'')+'</label>';
  }
  function fillText($el,items){ $el.find('.rcr-opt').each(function(i){ $(this).find('.t').text(items[i]); }); }
  function renderCats(){
    var h='', names=[];
    T.categories.forEach(function(c){ h+=opt('id_qt',c.id,'',null,'',c.id===catId); names.push('Membre '+c.nom); });
    var $e=$('#catList').html(h); fillText($e,names);
  }
  function renderGrades(){
    var c=cat(), h='', names=[]; if(!c){ $('#gradeList').html('<p class="text-muted">Choisissez d\'abord une catégorie.</p>'); return; }
    c.grades.forEach(function(g){ h+=opt('grade',g.id,'',usd(g.prix)+' / mois','',g.id===gradeId); names.push(g.nom); });
    var $e=$('#gradeList').html(h); fillText($e,names);
  }
  function renderPeriodes(){
    var g=grade(), h='', names=[]; if(!g){ $('#periodeList').html('<p class="text-muted">Choisissez d\'abord un grade.</p>'); return; }
    T.periodes.forEach(function(p){ h+=opt('reglement',p.id,'',usd(g.prix*p.mois),p.mois===1?'par mois':'pour '+p.mois+' mois',p.id===perId); names.push(p.nom); });
    var $e=$('#periodeList').html(h); fillText($e,names);
  }
  function recap(){
    var c=cat(), g=grade(), p=per(), rows=[];
    function add(k,v){ rows.push('<tr><th style="width:38%"></th><td></td></tr>'); recapData.push([k,v]); }
    var recapData=[]; add('Catégorie', c?('Membre '+c.nom):'—'); add('Grade', g?g.nom:'—'); add('Mode de cotisation', p?p.nom:'—');
    add('Montant à payer', (g&&p)?usd(g.prix*p.mois):'—');
    add('Nom complet', [$f.find('[name=nom]').val(),$f.find('[name=postnom]').val(),$f.find('[name=prenom]').val()].join(' ').trim()||'—');
    add('E-mail', $f.find('[name=mail]').val()||'—'); add('Téléphone', $f.find('[name=telephone]').val()||'—');
    add('Province', $f.find('[name=province] option:selected').text().trim()||'—');
    var $t=$('#recapTable').html(rows.join(''));
    $t.find('tr').each(function(i){ $(this).find('th').text(recapData[i][0]); $(this).find('td').text(recapData[i][1]); });
  }
  $f.on('change','input[name=id_qt]',function(){ catId=+this.value; gradeId=0; perId=0; renderCats(); renderGrades(); renderPeriodes(); });
  $f.on('change','input[name=grade]',function(){ gradeId=+this.value; perId=0; renderGrades(); renderPeriodes(); });
  $f.on('change','input[name=reglement]',function(){ perId=+this.value; renderPeriodes(); });
  $(document).on('click','#btnNext,#btnPrev',recap);
  $f.on('submit',function(e){
    if($('#mot_de_passe').val()!==$('#mot_de_passe2').val()){ e.preventDefault(); alert('Les deux mots de passe ne sont pas identiques.'); }
  });
  var qs=new URLSearchParams(location.search), pre=+(qs.get('qt')||0);
  if(pre && cat.call(null)===undefined){ var ok=T.categories.some(function(c){return c.id===pre;}); if(ok){ catId=pre; } }
  renderCats(); renderGrades(); renderPeriodes();
})();
</script>
</body>
</html>
