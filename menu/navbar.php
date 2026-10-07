<?php
/* =========================================================
   CONTEXTE
========================================================= */

$page = $page ?? 'home';

$isLoggedIn = !empty($_SESSION['id_ad']);
$memberId   = $isLoggedIn ? (int) $_SESSION['id_ad'] : null;

$memberPhoto = '';

if ($isLoggedIn && !empty($_SESSION['passeport'])) {
    $memberPhoto = './media/passeport/' .
        rawurlencode(basename($_SESSION['passeport']));
}

/* Pages actives */
$isAboutActive = in_array($page, ['parti', 'apropos'], true);

$isProgressActive = in_array(
    $page,
    ['publication'],
    true
);
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;1,9..144,500&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<style>
/* =========================================================
   VARIABLES — mêmes tokens que le reste du site
   (cf. rcr-qui-sommes-nous.html, accueil.php, don.php...)

   🎨 CORRECTIF DESIGN : le dégradé violet/vert "SaaS" d'origine
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
   🔧 CORRECTIF DESIGN (demande) : le menu s'étalait sur toute
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


/* Icônes application mobile (Android / iPhone) */
.member-app { display: flex; align-items: center; gap: 2px; margin-left: 4px; }
.member-app > a, .member-app > .app-soon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 36px; height: 36px; padding: 0 !important; font-size: 18px;
    color: var(--white) !important; border: 1px solid rgba(241, 233, 216, .18); background: rgba(241, 233, 216, .04);
}
.member-app > .app-soon { opacity: .45; cursor: default; }
.member-app > a:hover { background: rgba(241, 233, 216, .12) !important; }

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

    .member-app { margin: 8px 0 0; justify-content: center; }
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


<!-- =====================================================
     HEADER
====================================================== -->
<header id="header">

    <div class="header-container">

        <!-- LOGO -->
        <div class="header-logo">

            <a
                href="?pages=home"
                aria-label="Retour à l'accueil"
            >
                <img
                    src="./media/lo/logo.png"
                    alt="Logo"
                >
            </a>

        </div>


        <!-- NAVIGATION -->
        <nav
            id="navbar"
            class="main-navbar"
            aria-label="Navigation principale"
        >

            <ul>
<?php
$menuHtml = menu_rendu($page, '', $isLoggedIn);
if ($menuHtml !== ''):
    echo $menuHtml;
else: /* secours : menu historique si la table site_menu est vide ou absente */ ?>

                <!-- ACCUEIL -->
                <li>

                    <a
                        href="?pages=home"
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
                            <a href="?pages=apropos">
                                Presentation
                            </a>
                        </li>

                        <li>
                            <a href="?pages=parti">
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
                            <a href="?pages=publication">
                                Nos evenements
                            </a>
                        </li>

                    </ul>

                </li>

                <!-- CONTACT -->
                <li>

                    <a
                        href="?pages=contact"
                        class="<?= $page === 'contact' ? 'active' : '' ?>"
                    >
                        Contact
                    </a>

                </li>

                <!-- SOUTIEN -->
                <li class="support-item">

                    <a
                        href="?pages=soutenir"
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

                            <a href="./adhere/adhesion.php">
                                <i class="bi bi-person-plus me-2"></i>

                                Adherer
                            </a>

                        </li>


                        <li>

                            <a
                                href="<?=
                                    $isLoggedIn
                                    ? '?pages=esp_membre'
                                    : '?pages=login'
                                ?>"
                            >

                                <i class="bi bi-arrow-repeat me-2"></i>

                                Renouveler mon adhesion

                            </a>

                        </li>

                    </ul>

                </li>

<?php endif; ?>
<?php $nApp = app_mobile_liens(); ?>
                <li class="member-app">
                    <?php foreach ([['android', 'Android', 'bi-android2'], ['ios', 'iPhone', 'bi-apple']] as [$k, $lib, $ico]): ?>
                    <?php if ($nApp[$k] !== ''): ?>
                    <a href="<?= htmlspecialchars($nApp[$k], ENT_QUOTES, 'UTF-8') ?>" title="Télécharger l'application <?= $lib ?>" aria-label="Application <?= $lib ?>" <?= preg_match('/\.apk(\?|$)/i', $nApp[$k]) ? 'download' : 'target="_blank" rel="noopener"' ?>><i class="bi <?= $ico ?>"></i></a>
                    <?php else: ?>
                    <span class="app-soon" title="Application <?= $lib ?> : bientôt disponible" aria-label="Application <?= $lib ?> bientôt disponible"><i class="bi <?= $ico ?>"></i></span>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </li>
                <!-- CONNEXION / MEMBRE -->

                <?php if ($isLoggedIn): ?>

                    <li class="member-connected">

                        <a
                            href="?pages=esp_membre"
                            title="Accéder à mon espace membre"
                        >

                            <?php if ($memberPhoto): ?>

                                <img
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
                            href="?pages=login"
                            class="<?= $page === 'login' ? 'active' : '' ?>"
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

    </div>

</header>


<!-- Espace sous le header -->
<div class="header-spacer"></div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const navbar =
        document.getElementById('navbar');

    const mobileToggle =
        document.getElementById('mobileNavToggle');


    /* ==========================================
       MENU MOBILE
    ========================================== */

    if (mobileToggle && navbar) {

        mobileToggle.addEventListener(
            'click',
            function () {

                navbar.classList.toggle(
                    'mobile-open'
                );

                this.classList.toggle(
                    'bi-list'
                );

                this.classList.toggle(
                    'bi-x'
                );

            }
        );
    }


    /* ==========================================
       DROPDOWNS MOBILE
    ========================================== */

    const dropdownLinks =
        document.querySelectorAll(
            '.main-navbar .dropdown > a'
        );


    dropdownLinks.forEach(function (link) {

        link.addEventListener(
            'click',
            function (e) {

                if (
                    window.innerWidth <= 991
                ) {

                    e.preventDefault();

                    const parent =
                        this.parentElement;

                    parent.classList.toggle(
                        'open'
                    );

                }

            }
        );

    });


    /* ==========================================
       FERMER LE MENU APRÈS CLIC
    ========================================== */

    const normalLinks =
        document.querySelectorAll(
            '.main-navbar a[href]:not([href="#"])'
        );


    normalLinks.forEach(function (link) {

        link.addEventListener(
            'click',
            function () {

                if (
                    window.innerWidth <= 991
                ) {

                    navbar.classList.remove(
                        'mobile-open'
                    );

                    mobileToggle.classList.add(
                        'bi-list'
                    );

                    mobileToggle.classList.remove(
                        'bi-x'
                    );

                }

            }
        );

    });

});
</script>
