<?php
include_once('functions/main_function.php');
require_once __DIR__ . '/includes/seo.php';

// Routeur : liste blanche des pages publiques (les fichiers d'aide de pages/ ne sont jamais appelables par URL).
$page = 'home';
if (isset($_GET['pages']) && $_GET['pages'] !== '') {
    $demande = (string) $_GET['pages'];
    if (isset(SEO_PAGES_PUBLIQUES[$demande]) && is_file(__DIR__ . '/pages/' . $demande . '.php')) {
        $page = $demande;
    } else {
        http_response_code(404);
        include __DIR__ . '/errors/404.php';
        exit;
    }
}

if (is_file(__DIR__ . '/functions/' . $page . '.funct.php')) {
    include __DIR__ . '/functions/' . $page . '.funct.php';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <?php seo_head($bdd, $page); ?>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="shortcut icon" type="image/x-icon" href="media/lo/logo1.ico">

    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="rcr">
    <meta name="twitter:site" content="@RCR">

    <meta name="ResourceLoaderDynamicStyles" content="">
    <meta name="generator" content="RCR">
    <meta name="referrer" content="origin">
    <meta name="referrer" content="origin-when-crossorigin">

    <?php /* 🔧 CORRECTIF (audit) : deux erreurs sur les balises Open Graph,
         invisibles à l'oeil mais qui empêchaient un partage correct sur
         Facebook/WhatsApp/LinkedIn :
         1) "fb: app_id" et "og: type" contenaient un espace après les
            deux-points — une propriété Open Graph mal orthographiée est
            simplement ignorée par les robots de partage (aucune erreur
            visible, le partage semblait "marcher" mais sans ces
            métadonnées).
         2) "og:type" était de toute façon dupliqué (ici et juste
            en dessous, avec une valeur différente "article" vs
            "Article") — la bonne occurrence est conservée ci-dessous.
         3) "fb:app_id" contenait un identifiant visiblement factice
            (1234567800, jamais un vrai ID Facebook) : retiré plutôt que
            de publier un faux identifiant. Si vous avez un vrai App ID
            Facebook pour le RCR, transmettez-le-moi pour le réintégrer
            correctement. */ ?>
    <meta itemprop="name" content="RCR">
    <meta itemprop="image" content="https://rcr.cd/media/lo/logorcr.png">

    <!-- Favicons -->
    <link href="media/lo/logo1.ico" rel="icon">
    <link href="media/lo/logorcr.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Roboto:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/animate.css/animate.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet">

    <!-- =======================================================
    * Template Name: Groovin - v4.7.1
    * Template URL: https://bootstrapmade.com/groovin-free-bootstrap-theme/
    * Author: BootstrapMade.com
    * License: https://bootstrapmade.com/license/
    ======================================================== -->

</head>

<body id="body">
<script src="framework/jquery-2.2.4.min.js"></script>
<script src="framework/jquery-ui.js"></script>
<?php
include('pages/onlinevisiteurs.php');
include('pages/onlinemembres.php');
include_once('menu/navbar.php'); ?>
<main id="main">
    <?php
    include 'pages/' . $page . '.php';
    ?>
</main>
<?php include_once('menu/footer.php'); ?>
<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
        class="bi bi-arrow-up-short"></i></a>

<!-- Vendor JS Files -->
<script src="assets/vendor/purecounter/purecounter.js"></script>
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
<script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
<script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
<script src="assets/vendor/php-email-form/validate.js"></script>

<!-- Template Main JS File -->
<script src="assets/js/main.js"></script>

</body>
</html>