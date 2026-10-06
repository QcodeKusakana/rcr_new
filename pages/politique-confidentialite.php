<?php
/**
 * mention.php
 *
 * Politique de confidentialité et conditions particulières
 * d'adhésion et de contribution en ligne du site RCR.
 *
 * Rassemblement des Chrétiens Républicains
 */
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap');

    :root {
        --legal-ink: #1b2a44;
        --legal-ink-light: #233355;
        --legal-paper: #f1e9d8;
        --legal-paper-soft: #e7dbbf;
        --legal-line: #cbbb92;
        --legal-gold: #9c7a2e;
        --legal-red: #7d2330;
        --legal-text: #241f1a;
        --legal-text-soft: #5b5346;
        --legal-white: #fffdf8;
        --legal-max: 1180px;
        --legal-serif: "Fraunces", Georgia, serif;
        --legal-sans: "IBM Plex Sans", Arial, sans-serif;
    }

    .legal-page,
    .legal-page * {
        box-sizing: border-box;
    }

    .legal-page {
        margin: 0;
        padding: 0;
        color: var(--legal-text);
        background: var(--legal-paper);
        font-family: var(--legal-sans);
        line-height: 1.75;
    }

    .legal-hero {
        position: relative;
        overflow: hidden;
        padding: 70px 24px 62px;
        color: #fffdf8;
        text-align: center;
        background:
            linear-gradient(
                135deg,
                rgba(27, 42, 68, .98),
                rgba(35, 51, 85, .96)
            );
    }

    .legal-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        opacity: .12;
        background-image:
            linear-gradient(45deg, transparent 48%, #ffffff 49%, transparent 51%),
            linear-gradient(-45deg, transparent 48%, #ffffff 49%, transparent 51%);
        background-size: 46px 46px;
        pointer-events: none;
    }

    .legal-hero-content {
        position: relative;
        z-index: 1;
        max-width: 850px;
        margin: 0 auto;
    }

    .legal-seal {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 68px;
        height: 68px;
        margin: 0 auto 22px;
        color: var(--legal-ink);
        background: var(--legal-gold);
        border: 4px solid rgba(255, 253, 248, .35);
        border-radius: 50%;
        font-family: var(--legal-serif);
        font-size: 32px;
        font-weight: 700;
    }

    .legal-kicker {
        margin: 0 0 10px;
        color: #e8d6a7;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .legal-title {
        margin: 0;
        font-family: var(--legal-serif);
        font-size: clamp(35px, 5vw, 64px);
        font-weight: 600;
        line-height: 1.1;
    }

    .legal-intro {
        max-width: 720px;
        margin: 24px auto 0;
        color: rgba(255, 253, 248, .82);
        font-size: 16px;
    }

    .legal-gold-line {
        width: 90px;
        height: 4px;
        margin: 30px auto 0;
        background: var(--legal-gold);
        border-radius: 30px;
    }

    .legal-layout {
        display: grid;
        grid-template-columns: 270px minmax(0, 1fr);
        gap: 55px;
        max-width: var(--legal-max);
        margin: 0 auto;
        padding: 58px 24px 80px;
    }

    .legal-sidebar {
        align-self: start;
        position: sticky;
        top: 25px;
    }

    .legal-sidebar-title {
        margin: 0 0 18px;
        color: var(--legal-ink);
        font-family: var(--legal-serif);
        font-size: 24px;
        line-height: 1.3;
    }

    .legal-sidebar-list {
        margin: 0;
        padding: 0;
        list-style: none;
        border-left: 2px solid var(--legal-line);
    }

    .legal-sidebar-list li {
        margin: 0;
        padding: 0;
    }

    .legal-sidebar-list a {
        display: block;
        padding: 8px 0 8px 18px;
        color: var(--legal-text-soft);
        font-size: 14px;
        text-decoration: none;
        transition: .2s ease;
    }

    .legal-sidebar-list a:hover {
        padding-left: 24px;
        color: var(--legal-red);
    }

    .legal-note {
        margin-top: 30px;
        padding: 18px;
        color: var(--legal-text-soft);
        background: rgba(255, 253, 248, .6);
        border: 1px solid var(--legal-line);
        border-radius: 8px;
        font-size: 13px;
    }

    .legal-note strong {
        display: block;
        margin-bottom: 5px;
        color: var(--legal-ink);
    }

    .legal-content {
        min-width: 0;
        padding: 42px clamp(22px, 4vw, 56px);
        background: var(--legal-white);
        border: 1px solid var(--legal-line);
        box-shadow: 0 14px 35px rgba(27, 42, 68, .08);
    }

    .legal-section {
        scroll-margin-top: 30px;
    }

    .legal-section + .legal-section {
        margin-top: 46px;
        padding-top: 42px;
        border-top: 1px solid var(--legal-line);
    }

    .legal-section h2 {
        position: relative;
        margin: 0 0 22px;
        padding-bottom: 17px;
        color: var(--legal-ink);
        font-family: var(--legal-serif);
        font-size: clamp(25px, 3vw, 35px);
        font-weight: 600;
        line-height: 1.25;
    }

    .legal-section h2::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 65px;
        height: 3px;
        background: var(--legal-gold);
    }

    .legal-section h3 {
        margin: 25px 0 10px;
        color: var(--legal-red);
        font-family: var(--legal-serif);
        font-size: 21px;
        font-weight: 600;
    }

    .legal-section p {
        margin: 0 0 16px;
        color: var(--legal-text);
        font-size: 15px;
        text-align: justify;
    }

    .legal-section ul {
        margin: 12px 0 20px;
        padding-left: 22px;
    }

    .legal-section li {
        margin-bottom: 7px;
        color: var(--legal-text);
        font-size: 15px;
    }

    .legal-info {
        padding: 22px 24px;
        margin-bottom: 22px;
        background: var(--legal-paper);
        border-left: 4px solid var(--legal-gold);
    }

    .legal-info p {
        margin-bottom: 8px;
        text-align: left;
    }

    .legal-info p:last-child {
        margin-bottom: 0;
    }

    .legal-info strong {
        color: var(--legal-ink);
    }

    .legal-info a {
        color: var(--legal-red);
        text-decoration: none;
        font-weight: 600;
    }

    .legal-info a:hover {
        text-decoration: underline;
    }

    .legal-warning {
        padding: 18px 20px;
        margin-bottom: 35px;
        color: #614c16;
        background: #fff4d6;
        border: 1px solid #e2c978;
        border-radius: 6px;
        font-size: 14px;
    }

    .legal-footer {
        padding: 30px 24px;
        color: rgba(255, 253, 248, .78);
        text-align: center;
        background: var(--legal-ink);
        font-size: 13px;
    }

    .legal-footer strong {
        color: #fffdf8;
    }

    .legal-footer-link {
        color: #fffdf8;
        text-decoration: none;
        font-weight: 600;
        transition: .2s ease;
    }

    .legal-footer-link:hover {
        color: #e8d6a7;
        text-decoration: underline;
    }

    @media (max-width: 920px) {

        .legal-layout {
            grid-template-columns: 1fr;
            gap: 28px;
            padding-top: 35px;
        }

        .legal-sidebar {
            position: static;
        }

        .legal-sidebar-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            border-left: 0;
        }

        .legal-sidebar-list a {
            padding: 7px 12px;
            background: rgba(255, 253, 248, .7);
            border: 1px solid var(--legal-line);
            border-radius: 30px;
        }

        .legal-sidebar-list a:hover {
            padding-left: 12px;
        }

        .legal-note {
            margin-top: 20px;
        }
    }

    @media (max-width: 600px) {

        .legal-hero {
            padding: 48px 18px 42px;
        }

        .legal-layout {
            padding: 25px 14px 45px;
        }

        .legal-content {
            padding: 28px 20px;
        }

        .legal-section p,
        .legal-section li {
            font-size: 14px;
            text-align: left;
        }

        .legal-info {
            padding: 18px;
        }

        .legal-footer {
            padding: 25px 16px;
        }
    }
</style>


<div class="legal-page">

    <!-- ============================= -->
    <!-- EN-TÊTE -->
    <!-- ============================= -->

    <header class="legal-hero">

        <div class="legal-hero-content">

            <div class="legal-seal" aria-hidden="true">
                R
            </div>

            <p class="legal-kicker">
                Rassemblement des Chrétiens Républicains
            </p>

            <h1 class="legal-title">
                Politique de confidentialité
            </h1>

            <p class="legal-intro">
                Protection des données personnelles et conditions particulières
                relatives à l’adhésion et aux contributions en ligne.
            </p>

            <div class="legal-gold-line"></div>

        </div>

    </header>


    <!-- ============================= -->
    <!-- CONTENU -->
    <!-- ============================= -->

    <main class="legal-layout">


        <!-- ============================= -->
        <!-- MENU LATÉRAL -->
        <!-- ============================= -->

        <aside class="legal-sidebar">

            <h2 class="legal-sidebar-title">
                Sur cette page
            </h2>

            <ul class="legal-sidebar-list">
                <?php foreach (blocs('politique-confidentialite') as $b): ?>
                <li><a href="#<?= e($b['cle']) ?>"><?= e($b['titre']) ?></a></li>
                <?php endforeach; ?>
            </ul>


            <div class="legal-note">

                <strong>
                    Protection de vos données
                </strong>

                Le RCR accorde une importance particulière à la
                confidentialité et à la protection des données
                personnelles collectées via son site.

            </div>

        </aside>


        <!-- ============================= -->
        <!-- CONTENU JURIDIQUE -->
        <!-- ============================= -->

        <article class="legal-content">


            <!-- ============================= -->
            <!-- I. PROTECTION DES DONNÉES -->
            <!-- ============================= -->

            <?php foreach (blocs('politique-confidentialite') as $b): ?>
            <section class="legal-section" id="<?= e($b['cle']) ?>"><?= bloc_rendu($b) ?></section>
            <?php endforeach; ?>


        </article>

    </main>


    <!-- ============================= -->
    <!-- FOOTER -->
    <!-- ============================= -->

    <footer class="legal-footer">

        <strong>
            Rassemblement des Chrétiens Républicains — RCR
        </strong>

        <br>

        <a href="index.php?pages=mention" class="legal-footer-link">
            Politique de confidentialité
        </a>

        <span>
            — © <?php echo date('Y'); ?> Tous droits réservés.
        </span>

    </footer>

</div>