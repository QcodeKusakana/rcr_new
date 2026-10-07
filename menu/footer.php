
<style>

    /* =========================================================
       FOOTER RCR
    ========================================================= */

    :root {
        --footer-ink: #1B2A44;
        --footer-ink-2: #233355;
        --footer-paper: #F1E9D8;
        --footer-gold: #9C7A2E;
        --footer-text: #C7BFAE;
        --footer-white: #FFFDF8;
        --footer-serif: 'Fraunces', Georgia, serif;
        --footer-sans: 'IBM Plex Sans', system-ui, sans-serif;
    }


    /* FOOTER */

    #footer {
        background: var(--footer-ink) !important;
        color: var(--footer-paper);
        margin-top: 40px;
        font-family: var(--footer-sans);
    }


    /* PARTIE PRINCIPALE */

    .footer-top {
        padding: 64px 0 45px;
        background: var(--footer-ink) !important;
    }


    .footer-top .container {
        background: transparent !important;
    }


    /* TITRES */

    #footer h3 {
        font-family: var(--footer-serif);
        font-weight: 600;
        color: var(--footer-white);
        margin-bottom: 16px;
        letter-spacing: .04em;
    }


    #footer h4 {
        font-family: var(--footer-sans);
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--footer-gold);
        margin-bottom: 18px;
    }


    /* TEXTE */

    #footer p {
        color: var(--footer-text);
        line-height: 1.8;
        font-size: 14px;
        margin-bottom: 14px;
    }

    #footer p strong {
        color: var(--footer-paper);
    }


    /* CONTACT */

    #footer a.footer-contact {
        color: var(--footer-text);
        text-decoration: none;
        transition: color .2s ease;
    }

    #footer a.footer-contact:hover {
        color: var(--footer-gold);
    }


    /* LOGO */

    .footer-logo {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid var(--footer-gold);
        background: #fff;
        padding: 8px;
    }


    /* LIENS */

    .footer-links ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }


    .footer-links ul li {
        padding: 7px 0;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }


    .footer-links ul li svg {
        flex-shrink: 0;
        margin-top: 5px;
        color: var(--footer-gold);
    }


    .footer-links ul li a {
        color: var(--footer-text);
        text-decoration: none;
        transition: color .2s ease, padding-left .2s ease;
        font-size: 14px;
    }


    .footer-links ul li a:hover {
        color: var(--footer-gold);
        padding-left: 4px;
    }


    /* RÉSEAUX SOCIAUX */

    .social-links {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 15px;
    }


    .social-links a {
        width: 38px;
        height: 38px;
        background: rgba(241, 233, 216, .08);
        color: var(--footer-paper);
        border: 1px solid rgba(241, 233, 216, .12);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: background .2s ease,
                    color .2s ease,
                    transform .2s ease;
    }


    .social-links a:hover {
        background: var(--footer-gold);
        color: var(--footer-ink);
        transform: translateY(-2px);
    }


    /* BOUTONS APPLICATION MOBILE */
    .app-stores { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; }
    .app-store {
        display: inline-flex; align-items: center; gap: 10px; min-width: 150px;
        padding: 8px 14px; border-radius: 10px; text-decoration: none;
        background: rgba(241, 233, 216, .08); border: 1px solid rgba(241, 233, 216, .22);
        color: var(--footer-paper); transition: background .2s ease, transform .2s ease, border-color .2s ease;
    }
    .app-store svg { flex-shrink: 0; }
    .app-store small { display: block; font-size: 10.5px; letter-spacing: .04em; opacity: .75; line-height: 1.1; }
    .app-store strong { display: block; font-size: 15px; line-height: 1.2; font-weight: 600; }
    a.app-store:hover { background: var(--footer-gold); border-color: var(--footer-gold); color: var(--footer-ink); transform: translateY(-2px); }
    .app-store.is-soon { opacity: .55; cursor: default; }
    @media (max-width: 768px) { .app-stores { justify-content: center; } }
    /* MENTION LÉGALE */

    .footer-legal-link {
        color: var(--footer-text);
        text-decoration: none;
        transition: color .2s ease;
    }


    .footer-legal-link:hover {
        color: var(--footer-gold);
        text-decoration: underline;
    }


    /* COPYRIGHT */

    .footer-bottom {
        background: var(--footer-gold);
        padding: 17px 15px;
        text-align: center;
    }


    .footer-bottom .copyright {
        color: var(--footer-ink);
        font-weight: 600;
        font-size: 13.5px;
        letter-spacing: .02em;
    }


    /* RESPONSIVE */

    @media (max-width: 768px) {

        .footer-top {
            padding: 48px 20px 35px;
            text-align: center;
        }

        .footer-logo {
            margin-bottom: 20px;
        }

        .footer-links ul li {
            justify-content: center;
        }

        .social-links {
            justify-content: center;
        }

    }


    @media (max-width: 576px) {

        .footer-top {
            padding-left: 15px;
            padding-right: 15px;
        }

        #footer p {
            font-size: 13.5px;
        }

        .footer-links ul li a {
            font-size: 13.5px;
        }

        .footer-bottom .copyright {
            line-height: 1.7;
        }

    }

</style>


<!-- =========================================================
     FOOTER RCR
========================================================= -->

<?php
/* ---------------------------------------------------------------------------------------------
   Pied de page piloté par la base : coordonnées et réseaux sociaux (site_reglages, groupe contact),
   liens (site_menu, zone footer). Valeurs de secours uniquement si la base est vide.
   $navBase : préfixe des liens ('' à la racine, '../' depuis adhere/).
--------------------------------------------------------------------------------------------- */
$navBase   = $navBase ?? '';
$fConnecte = !empty($_SESSION['id_ad']);
$fNom      = reglage('parti_nom', 'Rassemblement des Chrétiens Républicains');
$fSigle    = reglage('parti_sigle', 'RCR');
$fAdresse  = reglage('contact_adresse_complete', reglage('contact_adresse', 'Ngaliema - Kinshasa'));
$fEmail    = reglage('contact_email', 'contact@rcr.cd');
$fTels     = array_values(array_filter([reglage('contact_telephone'), reglage('contact_telephone2')]));
$fIntro    = reglage('footer_contact_intro', "Une question, une suggestion ou besoin d'informations ?");
$fIcones   = [
    'facebook' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"> <path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5 3.66 9.15 8.44 9.94v-7.03H7.9v-2.91h2.54V9.85c0-2.5 1.49-3.89 3.77-3.89 1.09 0 2.23.2 2.23.2v2.45h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.78l-.44 2.91h-2.34V22c4.78-.79 8.44-4.94 8.44-9.94Z"/> </svg>',
    'instagram' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"> <path d="M12 2c2.7 0 3.06.01 4.12.06 1.06.05 1.79.22 2.43.47.66.26 1.22.6 1.77 1.15.55.55.9 1.11 1.15 1.77.25.64.42 1.37.47 2.43C22 8.94 22 9.3 22 12s-.01 3.06-.06 4.12c-.05 1.06-.22 1.79-.47 2.43a4.9 4.9 0 0 1-1.15 1.77 4.9 4.9 0 0 1-1.77 1.15c-.64.25-1.37.42-2.43.47C15.06 22 14.7 22 12 22s-3.06-.01-4.12-.06c-1.06-.05-1.79-.22-2.43-.47a4.9 4.9 0 0 1-1.77-1.15 4.9 4.9 0 0 1-1.15-1.77c-.25-.64-.42-1.37-.47-2.43C2 15.06 2 14.7 2 12s.01-3.06.06-4.12c.05-1.06.22-1.79.47-2.43.26-.66.6-1.22 1.15-1.77A4.9 4.9 0 0 1 5.45 2.53c.64-.25 1.37-.42 2.43-.47C8.94 2 9.3 2 12 2Zm0 5a5 5 0 1 0 0 10 5 5 0 0 0 0-10Zm0 8.2a3.2 3.2 0 1 1 0-6.4 3.2 3.2 0 0 1 0-6.4Zm5.2-8.4a1.17 1.17 0 1 1-2.34 0 1.17 1.17 0 0 1 2.34 0Z"/> </svg>',
    'x' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"> <path d="M18.9 2H22l-7.2 8.2L23.3 22h-6.7l-5.2-6.8L5.4 22H2.3l7.7-8.8L1 2h6.9l4.7 6.2L18.9 2Zm-1.2 18h1.9L7.4 4h-2l12.3 16Z"/> </svg>',
    'youtube' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"> <path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.6A3 3 0 0 0 .5 6.2 31.5 31.5 0 0 0 0 12a31.5 31.5 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.6 9.4.6 9.4.6s7.5 0 9.4-.6a3 3 0 0 0 2.1-2.1A31.5 31.5 0 0 0 24 12a31.5 31.5 0 0 0-.5-5.8ZM9.6 15.5v-7l6 3.5-6 3.5Z"/> </svg>',
    'tiktok' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"> <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.1h-3.06v13.7a2.9 2.9 0 1 1-2-2.74V10.4a6 6 0 1 0 5.99 6V9.98a7.8 7.8 0 0 0 4.84 1.67V8.59a4.9 4.9 0 0 1-2-.9Z"/> </svg>',
    'linkedin' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"> <path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5ZM3 9h4v12H3V9Zm7 0h3.8v1.7h.05c.53-1 1.83-2.05 3.77-2.05 4.03 0 4.78 2.65 4.78 6.1V21h-4v-5.6c0-1.34-.02-3.06-1.87-3.06-1.87 0-2.16 1.46-2.16 2.96V21h-4V9Z"/> </svg>',
    'whatsapp' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38a9.9 9.9 0 0 0 4.74 1.21h.01c5.46 0 9.91-4.45 9.91-9.91A9.85 9.85 0 0 0 12.04 2Zm5.8 14.03c-.24.68-1.42 1.3-1.95 1.34-.5.05-.97.23-3.28-.68-2.78-1.1-4.55-3.95-4.69-4.13-.13-.18-1.12-1.49-1.12-2.84s.71-2.02.96-2.29c.25-.27.55-.34.73-.34l.52.01c.17 0 .39-.06.61.47.24.56.79 1.94.86 2.08.07.14.11.3.02.48-.09.18-.13.29-.27.45l-.4.47c-.13.13-.27.28-.12.55.15.27.68 1.12 1.46 1.81 1 .89 1.85 1.17 2.12 1.3.27.13.42.11.58-.07.16-.18.67-.78.85-1.05.18-.27.36-.22.6-.13.25.09 1.57.74 1.84.88.27.13.45.2.52.31.07.11.07.65-.17 1.33Z"/></svg>',
];
$fReseaux  = [];
foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X', 'youtube' => 'YouTube', 'tiktok' => 'TikTok', 'linkedin' => 'LinkedIn', 'whatsapp' => 'WhatsApp'] as $k => $lib) {
    $u = trim(reglage('reseau_' . $k));
    if ($u !== '' && preg_match('#^https?://#i', $u)) { $fReseaux[] = [$k, $lib, $u]; }
}
// Colonnes de liens : site_menu zone « footer » (parent = titre de colonne), sinon liens par défaut
$fColonnes = [];
try {
    $fRows = menu_items('footer');
    $fPar = [];
    foreach ($fRows as $r) { $fPar[(int) ($r['parent_id'] ?? 0)][] = $r; }
    foreach ($fPar[0] ?? [] as $col) {
        $fColonnes[] = ['titre' => $col['libelle'], 'liens' => array_map(fn($l) => [$l['libelle'], $l['url']], $fPar[(int) $col['id']] ?? [])];
    }
} catch (Throwable $e) { $fColonnes = []; }
if (!$fColonnes) {
    $fColonnes = [
        ['titre' => 'Menu', 'liens' => [['Accueil', '?pages=home'], ['Qui sommes-nous', '?pages=apropos'], ['Nos événements', '?pages=publication'], ['Mot du Président', '?pages=parti'], ['Nos Idées Forces', '?pages=home#textes-fondamentaux'], ['Soutenir', '?pages=soutenir']]],
        ['titre' => 'Nous rejoindre', 'liens' => [["J'adhère", './adhere/adhesion.php'], ['Je renouvelle', '@renouveler'], ['Je soutiens', '?pages=soutenir']]],
    ];
}
$fApp = app_mobile_liens($navBase);
$fChevron = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>
<footer id="footer">
    <div class="footer-top">
        <div class="container">
            <div class="row gy-5">

                <div class="col-lg-3 col-md-6 text-center text-md-start">
                    <img src="<?= e($navBase) ?>media/lo/logorcr.png" class="footer-logo" alt="Logo <?= e($fSigle) ?>">
                    <h3 class="mt-3">
                        <img src="<?= e($navBase) ?>media/lo/footer.png" class="img" alt="<?= e($fSigle) ?>" style="width:130px;height:auto;">
                    </h3>
                    <p><strong><?= e($fNom) ?></strong></p>
                    <p><?= nl2br(e($fAdresse)) ?></p>
                    <?php if ($fTels): ?>
                    <p>
                        <strong>Téléphone :</strong><br>
                        <?php foreach ($fTels as $i => $t): ?>
                            <?= $i ? '<br>' : '' ?><a class="footer-contact" href="tel:<?= e(preg_replace('/[^0-9+]/', '', $t)) ?>"><?= e($t) ?></a>
                        <?php endforeach; ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($fEmail !== ''): ?>
                    <p>
                        <strong>Email :</strong><br>
                        <a class="footer-contact" href="mailto:<?= e($fEmail) ?>"><?= e($fEmail) ?></a>
                    </p>
                    <?php endif; ?>
                </div>

                <?php foreach (array_slice($fColonnes, 0, 2) as $col): ?>
                <div class="col-lg-3 col-md-6 footer-links">
                    <h4><?= e($col['titre']) ?></h4>
                    <ul>
                        <?php foreach ($col['liens'] as [$lib, $url]): ?>
                        <li><?= $fChevron ?> <a href="<?= e(menu_url($url, $navBase, $fConnecte)) ?>"><?= e($lib) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>

                <div class="col-lg-3 col-md-6 footer-links">
                    <h4>Contactez-nous</h4>
                    <p><?= e($fIntro) ?></p>
                    <ul>
                        <li><?= $fChevron ?> <a href="<?= e(menu_url('?pages=contact', $navBase)) ?>">Nous contacter</a></li>
                    </ul>
                    <h4 class="mt-4">Application mobile</h4>
                    <div class="app-stores">
                        <?php foreach ([['android', 'Android', 'Android', '<svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.6 9.48l1.84-3.18a.38.38 0 0 0-.66-.38l-1.86 3.22a11.5 11.5 0 0 0-9.84 0L5.22 5.92a.38.38 0 0 0-.66.38l1.84 3.18A10.9 10.9 0 0 0 1 18h22a10.9 10.9 0 0 0-5.4-8.52ZM7 15.25a1.25 1.25 0 1 1 0-2.5 1.25 1.25 0 0 1 0 2.5Zm10 0a1.25 1.25 0 1 1 0-2.5 1.25 1.25 0 0 1 0 2.5Z"/></svg>'], ['ios', 'iPhone', 'iPhone', '<svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16.37 12.6c-.02-2.2 1.8-3.26 1.88-3.31-1.02-1.5-2.62-1.7-3.19-1.73-1.36-.14-2.65.8-3.34.8-.69 0-1.75-.78-2.88-.76-1.48.02-2.85.86-3.61 2.19-1.54 2.67-.39 6.62 1.1 8.79.73 1.06 1.6 2.25 2.74 2.21 1.1-.04 1.52-.71 2.85-.71 1.33 0 1.7.71 2.87.69 1.18-.02 1.93-1.08 2.65-2.15.84-1.23 1.18-2.42 1.2-2.48-.03-.01-2.3-.88-2.27-3.54ZM14.2 6.1c.6-.73 1.01-1.75.9-2.76-.87.04-1.92.58-2.54 1.31-.56.65-1.05 1.68-.92 2.67.97.07 1.96-.49 2.56-1.22Z"/></svg>']] as [$k, $lib, $nom, $svg]): ?>
                        <?php if ($fApp[$k] !== ''): ?>
                        <a class="app-store" href="<?= e($fApp[$k]) ?>" rel="noopener" <?= preg_match('/\.apk(\?|$)/i', $fApp[$k]) ? 'download' : 'target="_blank"' ?> aria-label="Télécharger l'application RCR pour <?= e($lib) ?>">
                            <?= $svg ?>
                            <span><small>Télécharger pour</small><strong><?= e($nom) ?></strong></span>
                        </a>
                        <?php else: ?>
                        <span class="app-store is-soon" aria-label="Application <?= e($lib) ?> bientôt disponible" title="Bientôt disponible">
                            <?= $svg ?>
                            <span><small><?= e($lib) ?></small><strong>Bientôt</strong></span>
                        </span>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($fReseaux): ?>
                    <h4 class="mt-4">Suivez-nous sur les réseaux sociaux</h4>
                    <div class="social-links mt-3">
                        <?php foreach ($fReseaux as [$k, $lib, $u]): ?>
                        <a href="<?= e($u) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($lib) ?>" title="<?= e($lib) ?>"><?= $fIcones[$k] ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <div class="copyright">
                <a href="<?= e(menu_url('?pages=mention', $navBase)) ?>" class="footer-legal-link">Mentions légales</a>
                <a href="<?= e(menu_url('?pages=politique-confidentialite', $navBase)) ?>" class="footer-legal-link">Politique de confidentialité</a>
                <span>— © <?= date('Y') ?> <?= e($fSigle) ?> - Tous droits réservés</span>
            </div>
        </div>
    </div>
</footer>
