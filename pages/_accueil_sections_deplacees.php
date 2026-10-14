<?php
/**
 * Sections retirées de la page d'accueil (réorganisation) — conservées ici pour être réutilisées ailleurs
 * (par ex. pages/apropos.php ou pages/parti.php) sans rien perdre :
 *   1. Équipe (« Les membres du RCR »)   2. Nos partenaires   3. Galerie
 * (la section « Président du Parti » est de retour sur l'accueil)
 *
 * Utilisation : dans la page de destination, ajouter  include __DIR__ . '/_accueil_sections_deplacees.php';
 * Ces sections ont besoin des variables $equipeHome, $partenairesHome et $reqGalerieHome, qui sont
 * préparées dans functions/home.funct.php : pour les afficher sur une autre page, recopier ces requêtes dans
 * le fichier functions/<nom-de-la-page>.funct.php correspondant.
 * Ce fichier n'est PAS une page publique (le routeur de index.php ne l'appelle jamais).
 * Prérequis déjà présents sur le site : Bootstrap, bootstrap-icons, polices Fraunces / IBM Plex Sans.
 */
?>
<style>
/* =========================================================
   TOKENS — identiques à rcr-qui-sommes-nous.html,
   rcr-direction.html, don.php et evenements.php
========================================================= */
:root{
    --ink:#1B2A44;
    --ink-2:#233355;
    --paper:#F1E9D8;
    --paper-2:#E7DBBF;
    --paper-line:#CBBB92;
    --gold:#9C7A2E;
    --text:#241F1A;
    --text-soft:#5B5346;
    --serif:'Fraunces', Georgia, serif;
    --sans:'IBM Plex Sans', system-ui, sans-serif;
}

body{
    overflow-x: hidden;
    font-family: var(--sans);
    color: var(--text);
}

/* Bandeau kicker réutilisé sur toutes les sections */
.section-badge{
    display: inline-block;
    font-family: var(--sans);
    font-size: 12.5px;
    font-weight: 600;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--gold);
    border: 1px solid var(--gold);
    padding: 6px 16px;
    margin-bottom: 16px;
    background: none;
    border-radius: 0;
}

/* =========================
   TEAM SECTION — « Notre équipe »
   Grille responsive (sans dépendance JS) ; défilement horizontal
   avec aimantation sur mobile.
========================= */
.team-section{
    background:
        radial-gradient(circle at 12% 0%, rgba(156,122,46,.10), transparent 42%),
        var(--paper);
    padding: 80px 0 90px;
}
.team-head{max-width: 640px; margin: 0 auto 46px; text-align: center;}
.team-head h2{
    font-family: var(--serif);
    font-weight: 600;
    font-size: clamp(28px, 4vw, 40px);
    color: var(--ink);
    margin: 0 0 14px;
}
.team-head .rule{width: 52px; height: 2px; background: var(--gold); margin: 0 auto 16px;}
.team-head p{color: var(--text-soft); font-size: 16px; line-height: 1.7; margin: 0;}

.team-grid{
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 26px;
    align-items: start; /* « Lire la suite » n'agrandit que la carte concernée */
}
.team-body{min-height: 168px;}
.team-grid.is-few{justify-content: center; grid-template-columns: repeat(auto-fit, minmax(240px, 280px));}

.team-card{
    position: relative;
    display: flex;
    flex-direction: column;
    background: #FBF8F0;
    border: 1px solid var(--paper-line);
    overflow: hidden;
    transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
}
.team-card:hover,
.team-card:focus-within{
    transform: translateY(-4px);
    border-color: var(--gold);
    box-shadow: 0 18px 40px rgba(27,42,68,.14);
}

.team-photo{
    position: relative;
    aspect-ratio: 4 / 5;
    overflow: hidden;
    background: linear-gradient(160deg, var(--ink) 0%, var(--ink-2) 100%);
}
.team-photo img{
    width: 100%; height: 100%;
    object-fit: cover;
    object-position: center 20%;
    transition: transform .6s ease;
    display: block;
}
.team-card:hover .team-photo img{transform: scale(1.05);}
.team-photo::after{
    content: "";
    position: absolute; inset: auto 0 0 0; height: 45%;
    background: linear-gradient(to top, rgba(15,26,46,.78), transparent);
    pointer-events: none;
}
.team-initiales{
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    font-family: var(--serif);
    font-size: 64px;
    color: rgba(241,233,216,.85);
    letter-spacing: .04em;
}
.team-role{
    position: absolute; left: 16px; right: 16px; bottom: 14px; z-index: 1;
    display: inline-block;
    align-self: flex-start;
    color: var(--paper);
    font-family: var(--sans);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
    border-left: 3px solid var(--gold);
    padding-left: 10px;
    line-height: 1.35;
}

.team-body{padding: 20px 20px 22px; display: flex; flex-direction: column; flex: 1;}
.team-body h3{
    font-family: var(--serif);
    font-weight: 600;
    font-size: 1.18rem;
    line-height: 1.3;
    color: var(--ink);
    margin: 0 0 10px;
}
.team-bio{
    color: var(--text-soft);
    font-size: 14.5px;
    line-height: 1.7;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.team-card.is-open .team-bio{-webkit-line-clamp: unset; display: block;}
.team-more{
    align-self: flex-start;
    margin-top: 10px;
    padding: 0;
    border: 0;
    background: none;
    color: var(--gold);
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
}
.team-more:hover{color: var(--ink); text-decoration: underline;}
.team-more:focus-visible{outline: 2px solid var(--gold); outline-offset: 3px;}

.team-contact{margin-top: auto; padding-top: 16px; display: flex; gap: 8px;}
.team-contact a{
    width: 36px; height: 36px;
    border: 1px solid var(--paper-line);
    display: inline-flex; align-items: center; justify-content: center;
    color: var(--ink);
    text-decoration: none;
    transition: background .2s ease, color .2s ease, border-color .2s ease;
}
.team-contact a:hover,
.team-contact a:focus-visible{background: var(--ink); border-color: var(--ink); color: var(--paper);}

.team-cta{text-align: center; margin-top: 42px;}
.team-cta a{
    display: inline-flex; align-items: center; gap: 8px;
    border: 1px solid var(--ink);
    color: var(--ink);
    padding: 12px 26px;
    font-weight: 600;
    text-decoration: none;
    transition: background .2s ease, color .2s ease;
}
.team-cta a:hover{background: var(--ink); color: var(--paper);}
.team-empty{text-align: center; color: var(--text-soft);}

@media (max-width: 1199px){ .team-grid{grid-template-columns: repeat(3, minmax(0, 1fr));} }
@media (max-width: 991px){ .team-grid{grid-template-columns: repeat(2, minmax(0, 1fr));} }
/* Téléphone : carrousel natif (défilement horizontal aimanté), sans bibliothèque */
@media (max-width: 575px){
    .team-section{padding: 60px 0 70px;}
    .team-grid, .team-grid.is-few{
        display: flex;
        gap: 16px;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        padding: 4px 4px 14px;
        margin: 0 -4px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    .team-card{flex: 0 0 82%; scroll-snap-align: center;}
    .team-card:hover{transform: none;}
}
@media (prefers-reduced-motion: reduce){
    .team-card, .team-photo img{transition: none;}
    .team-card:hover{transform: none;}
}

/* =========================
   NOS PARTENAIRES (accueil)
========================= */
.partners-section{
    background: #FBF8F0;
    border-top: 1px solid var(--paper-line);
    border-bottom: 1px solid var(--paper-line);
    padding: 76px 0 80px;
}
.partners-head{max-width: 620px; margin: 0 auto 42px; text-align: center;}
.partners-head h2{font-family: var(--serif); font-weight: 600; font-size: clamp(28px, 4vw, 40px); color: var(--ink); margin: 0 0 14px;}
.partners-head .rule{width: 52px; height: 2px; background: var(--gold); margin: 0 auto 16px;}
.partners-head p{color: var(--text-soft); font-size: 16px; line-height: 1.7; margin: 0;}

.partners-grid, .partners-track{list-style: none; margin: 0; padding: 0;}
.partners-grid{display: flex; flex-wrap: wrap; justify-content: center; gap: 22px;}
.partners-grid .partner-item{flex: 0 1 200px;}

.partner-logo{
    margin: 0;
    height: 132px;
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px;
    background: #fff;
    border: 1px solid var(--paper-line);
    padding: 18px 20px 14px;
    transition: border-color .25s ease, box-shadow .25s ease, transform .25s ease;
}
.partner-logo img{
    max-width: 100%; max-height: 64px; object-fit: contain;
    filter: grayscale(1); opacity: .72;
    transition: filter .3s ease, opacity .3s ease;
}
.partner-logo figcaption{
    font-size: 12px; font-weight: 600; letter-spacing: .04em; color: var(--text-soft);
    text-align: center; line-height: 1.3;
    max-width: 100%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.partner-logo:hover{border-color: var(--gold); box-shadow: 0 12px 28px rgba(27,42,68,.10); transform: translateY(-3px);}
.partner-logo:hover img{filter: none; opacity: 1;}
.partner-logo:hover figcaption{color: var(--ink);}

/* Bandeau défilant continu (pause au survol / au focus) */
.partners-marquee{
    overflow: hidden;
    -webkit-mask-image: linear-gradient(to right, transparent, #000 8%, #000 92%, transparent);
            mask-image: linear-gradient(to right, transparent, #000 8%, #000 92%, transparent);
}
.partners-track{display: flex; gap: 22px; width: max-content; animation: rcr-defile 38s linear infinite;}
.partners-track .partner-item{flex: 0 0 200px;}
.partners-marquee:hover .partners-track,
.partners-marquee:focus-within .partners-track{animation-play-state: paused;}
@keyframes rcr-defile{ to { transform: translateX(calc(-50% - 11px)); } }

.partners-cta{margin-top: 40px; display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 14px 26px;}
.partners-btn{
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--ink); color: var(--paper);
    padding: 12px 26px; font-weight: 600; text-decoration: none;
    transition: background .2s ease;
}
.partners-btn:hover{background: var(--ink-2); color: var(--paper);}
.partners-link{color: var(--gold); font-weight: 600; text-decoration: none; border-bottom: 1px solid transparent;}
.partners-link:hover{color: var(--ink); border-bottom-color: var(--ink);}

@media (max-width: 575px){
    .partners-section{padding: 56px 0 60px;}
    .partners-grid{gap: 12px;}
    .partners-grid .partner-item{flex: 0 1 calc(50% - 6px);}
    .partners-track .partner-item{flex-basis: 150px;}
    .partner-logo{height: 112px; padding: 14px 12px 10px;}
    .partner-logo img{max-height: 52px;}
}
/* Mouvement réduit : pas d'animation, logos en grille */
@media (prefers-reduced-motion: reduce){
    .partners-marquee{-webkit-mask-image: none; mask-image: none;}
    .partners-track{animation: none; width: auto; flex-wrap: wrap; justify-content: center;}
    .partners-track [aria-hidden="true"]{display: none;}
    .partner-logo, .partner-logo img{transition: none;}
}

#partenaires{background: #FBF8F0 !important;}
#partenaires .card{
    background: var(--paper);
    border: 1px solid var(--paper-line) !important;
    border-radius: 0 !important;
}
#partenaires .btn-outline-primary{
    color: var(--ink) !important;
    border-color: var(--ink) !important;
    border-radius: 0 !important;
}
#partenaires .btn-outline-primary:hover{background: var(--ink) !important; color: var(--paper) !important;}

#portfolio{background: #FBF8F0 !important;}
#portfolio .card{
    background: var(--paper);
    border: 1px solid var(--paper-line) !important;
    border-radius: 0 !important;
}
#portfolio h5{font-family: var(--serif); font-weight: 600; color: var(--ink);}
#portfolio .btn-outline-primary{
    color: var(--ink) !important;
    border-color: var(--ink) !important;
    border-radius: 0 !important;
}
#portfolio .btn-outline-primary:hover{background: var(--ink) !important; color: var(--paper) !important;}

</style>

<!-- =========================
        TEAM SECTION
========================= -->

<section id="equipe" class="team-section" aria-labelledby="equipe-titre">

    <div class="container">

        <div class="team-head">
            <span class="section-badge"><?= e(reglage('equipe_surtitre', 'Notre équipe')) ?></span>
            <h2 id="equipe-titre"><?= e(reglage('equipe_titre', 'Les membres du RCR')) ?></h2>
            <div class="rule" aria-hidden="true"></div>
            <p><?= e(reglage('equipe_intro', 'Découvrez les responsables et membres du Rassemblement des Chrétiens Républicains.')) ?></p>
        </div>

        <?php if (empty($equipeHome)): ?>
            <p class="team-empty">La présentation de l'équipe sera bientôt disponible.</p>
        <?php else: ?>
        <div class="team-grid<?= count($equipeHome) < 4 ? ' is-few' : '' ?>">
            <?php foreach ($equipeHome as $i => $m):
                $nomMembre = html_entity_decode((string) $m['name'], ENT_QUOTES, 'UTF-8');
                $mots = preg_split('/\s+/u', trim($nomMembre)) ?: [];
                $initiales = mb_strtoupper(mb_substr($mots[0] ?? '', 0, 1) . mb_substr($mots[count($mots) - 1] ?? '', 0, 1));
                $bio = trim(html_entity_decode((string) $m['resume'], ENT_QUOTES, 'UTF-8'));
                $photoOk = $m['photo'] !== '' && is_file(__DIR__ . '/../admin/media/img_equipe/' . basename((string) $m['photo']));
                $mailOk = filter_var(html_entity_decode((string) $m['mail'], ENT_QUOTES, 'UTF-8'), FILTER_VALIDATE_EMAIL);
                $tel = preg_replace('/[^0-9+]/', '', html_entity_decode((string) $m['telephone'], ENT_QUOTES, 'UTF-8'));
            ?>
            <article class="team-card">
                <div class="team-photo">
                    <?php if ($photoOk): ?>
                        <img loading="lazy" decoding="async" width="400" height="500"
                             src="./admin/media/img_equipe/<?= e(rawurlencode(basename((string) $m['photo']))) ?>"
                             alt="Portrait de <?= e($nomMembre) ?>">
                    <?php else: ?>
                        <span class="team-initiales" aria-hidden="true"><?= e($initiales) ?></span>
                    <?php endif; ?>
                    <?php if (trim((string) $m['function']) !== ''): ?>
                        <span class="team-role"><?= e($m['function']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="team-body">
                    <h3><?= e($nomMembre) ?></h3>
                    <?php if ($bio !== ''): ?>
                        <p class="team-bio" id="bio-<?= (int) $m['id_eq'] ?>"><?= nl2br(e($bio)) ?></p>
                        <?php if (mb_strlen($bio) > 140): ?>
                            <button type="button" class="team-more" aria-expanded="false" aria-controls="bio-<?= (int) $m['id_eq'] ?>">Lire la suite</button>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($mailOk || strlen($tel) >= 9): ?>
                    <div class="team-contact">
                        <?php if ($mailOk): ?><a href="mailto:<?= e($mailOk) ?>" aria-label="Écrire à <?= e($nomMembre) ?>"><i class="bi bi-envelope" aria-hidden="true"></i></a><?php endif; ?>
                        <?php if (strlen($tel) >= 9): ?><a href="tel:<?= e($tel) ?>" aria-label="Appeler <?= e($nomMembre) ?>"><i class="bi bi-telephone" aria-hidden="true"></i></a><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="team-cta">
            <a href="?pages=apropos">Découvrir nos instances <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>

    </div>

</section>

<script>
/* « Lire la suite » des biographies (sans dépendance) */
document.addEventListener('click', function (e) {
    var b = e.target.closest ? e.target.closest('.team-more') : null;
    if (!b) { return; }
    var card = b.closest('.team-card');
    var open = card.classList.toggle('is-open');
    b.setAttribute('aria-expanded', open ? 'true' : 'false');
    b.textContent = open ? 'Réduire' : 'Lire la suite';
});
</script>






<!-- =========================
        NOS PARTENAIRES
========================= -->

<?php if (!empty($partenairesHome)):
    $defile = count($partenairesHome) >= 6; // bandeau défilant à partir de 6 logos, sinon grille centrée
    $rendrePartenaire = function (array $p, bool $decoratif = false): string {
        $nom = html_entity_decode((string) $p['nom_part'], ENT_QUOTES, 'UTF-8');
        return '<li class="partner-item"' . ($decoratif ? ' aria-hidden="true"' : '') . '>'
             . '<figure class="partner-logo" title="' . e($nom) . '">'
             . '<img loading="lazy" decoding="async" src="./media/images_part/' . e(rawurlencode(basename((string) $p['photo']))) . '" alt="' . ($decoratif ? '' : e($nom)) . '">'
             . '<figcaption>' . e($nom) . '</figcaption></figure></li>';
    };
?>
<section id="partenaires" class="partners-section" aria-labelledby="partenaires-titre">
    <div class="container">

        <div class="partners-head">
            <span class="section-badge"><?= e(reglage('partenaires_surtitre', 'Ils nous soutiennent')) ?></span>
            <h2 id="partenaires-titre"><?= e(reglage('partenaires_titre', 'Nos partenaires')) ?></h2>
            <div class="rule" aria-hidden="true"></div>
            <p><?= e(reglage('partenaires_intro', 'Les organisations et personnalités qui accompagnent l’action du Rassemblement des Chrétiens Républicains.')) ?></p>
        </div>

        <?php if ($defile): ?>
        <div class="partners-marquee" role="region" aria-label="Logos de nos partenaires">
            <ul class="partners-track">
                <?php foreach ($partenairesHome as $p) { echo $rendrePartenaire($p); } ?>
                <?php foreach ($partenairesHome as $p) { echo $rendrePartenaire($p, true); } /* copie pour une boucle continue */ ?>
            </ul>
        </div>
        <?php else: ?>
        <ul class="partners-grid">
            <?php foreach ($partenairesHome as $p) { echo $rendrePartenaire($p); } ?>
        </ul>
        <?php endif; ?>

        <div class="partners-cta">
            <a class="partners-btn" href="?pages=partenaire">Voir tous nos partenaires <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            <a class="partners-link" href="?pages=contact">Devenir partenaire du RCR</a>
        </div>

    </div>
</section>
<?php endif; ?>



<!-- =========================
        GALERIE
========================= -->

<?php if ($reqGalerieHome && $reqGalerieHome->rowCount() > 0): ?>

<section id="portfolio" class="py-5">

    <div class="container">

        <!-- TITLE -->
        <div class="text-center mb-5">

            <h2 class="fw-bold text-primary">
                Galerie
            </h2>

            <p class="text-muted">
                Quelques images et souvenirs
                des activités du RCR.
            </p>

        </div>

        <!-- GALLERY -->
        <div class="row g-4">

            <?php while ($photoActivite = $reqGalerieHome->fetch()): ?>

            <div class="col-lg-4 col-md-6">

                <div class="card border-0 gallery-item">

                    <img loading="lazy" decoding="async" src="./media/images_activ/<?= e($photoActivite['photo']) ?>"
                         class="card-img-top"
                         style="height: 250px; object-fit: cover;"
                         alt="<?= e($photoActivite['titre']) ?>">

                    <div class="card-body text-center">

                        <h5 class="fw-bold">
                            <?= e($photoActivite['titre']) ?>
                        </h5>

                        <a href="./media/images_activ/<?= e($photoActivite['photo']) ?>"
                           class="btn btn-outline-primary rounded-pill portfolio-lightbox"
                           data-gallery="galerie-accueil"
                           data-glightbox="title: <?= e($photoActivite['titre']) ?>">

                            <i class="bi bi-image"></i>
                            Voir l'image

                        </a>

                    </div>

                </div>

            </div>

            <?php endwhile; ?>

        </div>

    </div>

</section>

<?php endif; ?>
