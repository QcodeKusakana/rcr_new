<!-- =========================
        HERO SECTION
========================= -->

<?php /* 🧹 CORRECTIF : animate.css était chargé mais aucune classe animate__*
     n'est utilisée nulle part dans cette page — retiré. bootstrap-icons
     reste chargé : les icônes bi-* sont utilisées dans presque toutes
     les sections ci-dessous. */ ?>
<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<!-- =========================
        CSS STYLE
========================= -->

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
   HERO
========================= */
.hero-saas{
    position: relative;
    padding: 130px 0;
    overflow: hidden;
    background: var(--ink);
}
/* même texture de filets verticaux que l'en-tête de "Qui sommes-nous",
   pour relier visuellement les deux pages */
.hero-saas::before{
    content:"";
    position:absolute;
    inset:0;
    background:
      repeating-linear-gradient(90deg, rgba(241,233,216,.05) 0 1px, transparent 1px 96px),
      radial-gradient(ellipse at 20% 100%, rgba(156,122,46,.16), transparent 55%);
    pointer-events:none;
}
.hero-saas .container{position:relative;}

.hero-badge{
    display: inline-block;
    padding: 7px 18px;
    border: 1px solid var(--gold);
    color: var(--gold);
    font-family: var(--sans);
    font-size: 13px;
    font-weight: 600;
    letter-spacing: .04em;
    margin-bottom: 22px;
}

.hero-title{
    font-family: var(--serif);
    font-weight: 600;
    font-size: 3.1rem;
    line-height: 1.15;
    color: var(--paper);
    margin-bottom: 20px;
}

.hero-text{
    color: #C9BFA9;
    font-size: 18px;
    line-height: 1.8;
    margin-bottom: 32px;
    max-width: 46ch;
}

.hero-buttons{
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.btn-primary-saas{
    background: var(--gold);
    color: var(--ink);
    padding: 14px 26px;
    text-decoration: none;
    font-weight: 600;
    font-family: var(--sans);
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: transform .2s ease, background .2s ease;
}
.btn-primary-saas:hover{
    transform: translateY(-2px);
    background:#B08F3C;
    color: var(--ink);
}

.btn-secondary-saas{
    border: 1px solid rgba(241,233,216,.35);
    color: var(--paper);
    padding: 14px 26px;
    text-decoration: none;
    font-weight: 600;
    font-family: var(--sans);
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: border-color .2s ease;
}
.btn-secondary-saas:hover{border-color: var(--gold); color: var(--paper);}

.hero-card{
    background: rgba(241,233,216,.05);
    border: 1px solid rgba(241,233,216,.18);
    backdrop-filter: blur(8px);
    padding: 30px;
}
.hero-card h5{
    font-family: var(--serif);
    font-weight: 600;
    color: var(--paper) !important;
}
.hero-card h5 i{color: var(--gold) !important;}

.hero-quicklink{
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(241,233,216,.06);
    color: var(--paper);
    padding: 16px 18px;
    margin-bottom: 12px;
    text-decoration: none;
    font-weight: 600;
    font-family: var(--sans);
    border-left: 2px solid transparent;
    transition: background .2s ease, border-color .2s ease, transform .2s ease;
}
.hero-quicklink span{display: flex; align-items: center; gap: 10px;}
.hero-quicklink i{color: var(--gold);}
.hero-quicklink:hover{
    background: rgba(241,233,216,.12);
    border-left-color: var(--gold);
    transform: translateX(4px);
    color: var(--paper);
}

/* =========================
   ABOUT SAAS
========================= */
.about-saas{background: var(--paper);}

.about-title{
    font-family: var(--serif);
    font-style: italic;
    font-weight: 500;
    font-size: 2rem;
    line-height: 1.4;
    color: var(--ink);
    margin-bottom: 20px;
}

.about-text{
    color: var(--text-soft);
    line-height: 1.85;
}

.about-stats{
    display: flex;
    gap: 1px;
    margin-top: 30px;
    flex-wrap: wrap;
    background: var(--paper-line);
    border: 1px solid var(--paper-line);
}

.stat-card{
    flex: 1;
    min-width: 150px;
    background: #FBF8F0;
    padding: 20px;
    text-align: center;
}
.stat-card h3{
    font-family: var(--serif);
    font-weight: 600;
    color: var(--ink);
    font-size: 1.7rem;
    margin-bottom: 4px;
}
.stat-card span{
    font-size: 12.5px;
    color: var(--text-soft);
    text-transform: uppercase;
    letter-spacing: .04em;
}

.about-visual{
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.visual-box{
    display: flex;
    align-items: center;
    gap: 18px;
    background: var(--paper-2);
    border-left: 3px solid var(--gold);
    padding: 18px 20px;
}
.visual-box i{
    font-size: 1.6rem;
    color: var(--gold);
    flex-shrink: 0;
}
.visual-box h4{
    font-family: var(--serif);
    font-weight: 600;
    font-size: 1.05rem;
    margin: 0 0 4px;
    color: var(--ink);
}
.visual-box p{
    font-size: 0.9rem;
    color: var(--text-soft);
    margin: 0;
}

/* =========================
   PRESIDENT SECTION
========================= */
.president-section{background: #FBF8F0;}

.president-image{position: relative; padding: 10px;}
.president-image::before{
    content:"";
    position:absolute;
    inset:0;
    border:1px solid var(--gold);
    pointer-events:none;
}
.president-image img{
    width: 100%;
    display:block;
    object-fit:cover;
}

.president-content h2{
    font-family: var(--serif);
    font-weight: 600;
    font-size: 2.1rem;
    color: var(--ink);
    margin-bottom: 20px;
}

.president-content p{
    line-height: 1.9;
    color: var(--text);
    text-align: justify;
}

.idea-title{
    margin-top: 25px;
    font-family: var(--sans);
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--gold);
}

.modern-btn{
    border: none;
    background: var(--ink);
    color: var(--paper);
    padding: 13px 28px;
    font-weight: 600;
    font-family: var(--sans);
    margin-top: 20px;
    transition: background .2s ease;
}
.modern-btn:hover{background: var(--ink-2); color: var(--paper);}

.more-content{
    background: var(--paper);
    border: 1px solid var(--paper-line);
    padding: 25px;
}
.more-content h4{
    font-family: var(--serif);
    font-weight:600;
    color: var(--ink);
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

/* =========================
   TEXTES FONDAMENTAUX
========================= */
#textes-fondamentaux{background: #FBF8F0 !important;}
#textes-fondamentaux .card{
    background: var(--paper);
    border: 1px solid var(--paper-line) !important;
    border-radius: 0 !important;
}
#textes-fondamentaux .card i{color: var(--gold) !important;}
#textes-fondamentaux h4{
    font-family: var(--serif);
    font-weight: 600;
    color: var(--ink);
}
#textes-fondamentaux .text-muted{color: var(--text-soft) !important;}
#textes-fondamentaux .btn.disabled{
    border-color: var(--paper-line) !important;
    color: var(--text-soft) !important;
    background: none !important;
}

/* =========================
   NOTRE MAÎTRE
========================= */
#about{background: var(--paper) !important;}
#about h2.text-primary{
    font-family: var(--serif) !important;
    font-weight: 600 !important;
    color: var(--ink) !important;
}
#about .card{
    background: #FBF8F0;
    border: 1px solid var(--paper-line) !important;
    border-radius: 0 !important;
}
#about .card h4{
    font-family: var(--serif);
    font-weight: 600;
    color: var(--ink) !important;
}
#about img.rounded{border-radius: 0 !important; border: 1px solid var(--paper-line);}
#about .btn-primary{
    background: var(--ink) !important;
    border: none !important;
    font-family: var(--sans);
}
#about .border-top{border-color: var(--paper-line) !important;}

/* section titles génériques réutilisés par plusieurs sections
   ("Notre équipe", "Notre Maître", "Nos textes fondamentaux") */
.text-primary{color: var(--ink) !important;}
h2.fw-bold.text-primary{
    font-family: var(--serif);
    font-weight: 600 !important;
    text-transform: none;
}

/* =========================
   CONTACT BAR
========================= */
.contact-bar{
    background: var(--ink) !important;
    padding: 22px 0;
}
.contact-bar a, .contact-bar span{
    color: var(--paper) !important;
    font-family: var(--sans);
}
.contact-bar a:hover{color: var(--gold) !important;}
.contact-bar i{color: var(--gold);}
.contact-bar .opacity-50{color: #8A93A8 !important;}
.contact-bar .opacity-50 i{color: #8A93A8;}

/* =========================
   PARTENAIRES / ÉVÉNEMENTS / GALERIE
========================= */
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

#why-us{background: var(--paper) !important;}
#why-us .card{
    background: #FBF8F0;
    border: 1px solid var(--paper-line) !important;
    border-radius: 0 !important;
}
#why-us .card a.text-primary{color: var(--ink) !important; font-family: var(--serif);}
#why-us .btn-dark{
    background: var(--ink) !important;
    border: none !important;
    border-radius: 0 !important;
}

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

/* =========================
   RESPONSIVE
========================= */
@media(max-width: 992px){
    .hero-title, .president-content h2{font-size: 2rem;}
}
@media(max-width: 768px){
    .hero-saas{padding: 90px 0;}
}

.hero-saas{
    position: relative;
    padding: 130px 0;
    overflow: hidden;
    background: var(--ink);
    isolation: isolate;
}

/* =========================
   SLIDER ARRI�RE-PLAN
========================= */

.hero-slider{
    position: absolute;
    inset: 0;
    z-index: -3;
    overflow: hidden;
}

.hero-slide{
    position: absolute;
    inset: 0;

    /* Toutes les images occupent exactement la m�me zone */
    width: 100%;
    height: 100%;

    /* L'image remplit toute la zone sans d�formation */
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;

    opacity: 0;

    transform: scale(1.05);

    transition:
        opacity 1.2s ease-in-out,
        transform 7s ease;
}

.hero-slide.active{
    opacity: 1;
    transform: scale(1);
}

/* =========================
   VOILE SUR LES IMAGES
========================= */

.hero-overlay{
    position: absolute;
    inset: 0;
    z-index: -2;

    background:
        linear-gradient(
            90deg,
            rgba(27,42,68,.94) 0%,
            rgba(27,42,68,.82) 42%,
            rgba(27,42,68,.60) 72%,
            rgba(27,42,68,.72) 100%
        );

    pointer-events: none;
}

/* Texture conserv�e par-dessus le slider */

.hero-saas::before{
    content:"";
    position:absolute;
    inset:0;

    z-index:-1;

    background:
        repeating-linear-gradient(
            90deg,
            rgba(241,233,216,.05) 0 1px,
            transparent 1px 96px
        ),
        radial-gradient(
            ellipse at 20% 100%,
            rgba(156,122,46,.16),
            transparent 55%
        );

    pointer-events:none;
}

/* Le contenu reste au-dessus */

.hero-saas .container{
    position:relative;
    z-index:2;
}

.hero-badge{
    display: inline-block;
    padding: 7px 18px;
    border: 1px solid var(--gold);
    color: var(--gold);
    font-family: var(--sans);
    font-size: 13px;
    font-weight: 600;
    letter-spacing: .04em;
    margin-bottom: 22px;
    background: rgba(27,42,68,.25);
}

.hero-title{
    font-family: var(--serif);
    font-weight: 600;
    font-size: 3.1rem;
    line-height: 1.15;
    color: var(--paper);
    margin-bottom: 20px;

    text-shadow:
        0 2px 15px rgba(0,0,0,.30);
}

.hero-text{
    color: #C9BFA9;
    font-size: 18px;
    line-height: 1.8;
    margin-bottom: 32px;
    max-width: 46ch;

    text-shadow:
        0 2px 10px rgba(0,0,0,.35);
}

/* =========================
   BOUTONS
========================= */

.hero-buttons{
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.btn-primary-saas{
    background: var(--gold);
    color: var(--ink);
    padding: 14px 26px;
    text-decoration: none;
    font-weight: 600;
    font-family: var(--sans);
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition:
        transform .2s ease,
        background .2s ease,
        box-shadow .2s ease;
}

.btn-primary-saas:hover{
    transform: translateY(-2px);
    background:#B08F3C;
    color: var(--ink);

    box-shadow:
        0 8px 25px rgba(0,0,0,.25);
}

.btn-secondary-saas{
    border: 1px solid rgba(241,233,216,.45);
    color: var(--paper);
    padding: 14px 26px;
    text-decoration: none;
    font-weight: 600;
    font-family: var(--sans);
    display: inline-flex;
    align-items: center;
    gap: 10px;

    background: rgba(27,42,68,.20);

    transition:
        border-color .2s ease,
        background .2s ease,
        transform .2s ease;
}

.btn-secondary-saas:hover{
    border-color: var(--gold);
    color: var(--paper);

    background: rgba(27,42,68,.40);

    transform: translateY(-2px);
}

/* =========================
   CARTE � DROITE
========================= */

.hero-card{
    background: rgba(27,42,68,.55);
    border: 1px solid rgba(241,233,216,.25);

    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);

    padding: 30px;

    box-shadow:
        0 15px 45px rgba(0,0,0,.18);
}

.hero-card h5{
    font-family: var(--serif);
    font-weight: 600;
    color: var(--paper) !important;
}

.hero-card h5 i{
    color: var(--gold) !important;
}

.hero-quicklink{
    display: flex;
    align-items: center;
    justify-content: space-between;

    background: rgba(241,233,216,.07);

    color: var(--paper);

    padding: 16px 18px;
    margin-bottom: 12px;

    text-decoration: none;
    font-weight: 600;
    font-family: var(--sans);

    border-left: 2px solid transparent;

    transition:
        background .2s ease,
        border-color .2s ease,
        transform .2s ease;
}

.hero-quicklink span{
    display: flex;
    align-items: center;
    gap: 10px;
}

.hero-quicklink i{
    color: var(--gold);
}

.hero-quicklink:hover{
    background: rgba(241,233,216,.14);

    border-left-color: var(--gold);

    transform: translateX(4px);

    color: var(--paper);
}

/* =========================
   MOBILE
========================= */

@media(max-width: 992px){

    .hero-title{
        font-size: 2rem;
    }

}

@media(max-width: 768px){

    .hero-saas{
        padding: 90px 0;
    }

    .hero-overlay{
        background:
            linear-gradient(
                90deg,
                rgba(27,42,68,.91),
                rgba(27,42,68,.78)
            );
    }

}
</style>

<!-- =========================
        HERO SAAS MODERNE
========================= -->
<section class="hero-saas">

    <!-- =========================
         SLIDER ARRI�RE-PLAN
    ========================== -->
    <div class="hero-slider">

        <div class="hero-slide active"
             style="background-image:url('./media/hero/hero-1.jpg');"></div>

        <div class="hero-slide"
             style="background-image:url('./media/hero/hero-2.jpg');"></div>

        <div class="hero-slide"
             style="background-image:url('./media/hero/hero-3.jpg');"></div>

        <div class="hero-slide"
             style="background-image:url('./media/hero/hero-4.jpg');"></div>

        <div class="hero-slide"
             style="background-image:url('./media/hero/hero-5.jpg');"></div>

    </div>

    <!-- Voile sombre au-dessus des images -->
    <div class="hero-overlay"></div>

    <div class="container">

        <div class="row align-items-center">

            <!-- LEFT CONTENT -->
            <div class="col-lg-7">

                <span class="hero-badge">
                    <?= e(reglage('home_surtitre', 'Rassembler pour le changement')) ?>
                </span>

                <h1 class="hero-title">
                    <?= e(reglage('home_titre', 'RASSEMBLER POUR BÂTIR UN PAYS PLUS BEAU QU\'AVANT')) ?>
                </h1>

                <p class="hero-text">
                    <?= e(reglage('home_soustitre', 'Un engagement citoyen pour construire un avenir meilleur et durable.')) ?>
                </p>
                <div class="hero-cta" style="display:flex;flex-wrap:wrap;gap:12px;margin-top:20px">
                    <a href="./adhere/adhesion.php" class="btn btn-lg" style="background:#9C7A2E;color:#fff;font-weight:700;padding:12px 28px;border-radius:999px"><?= e(reglage('btn_adherer', "J'ADHÈRE")) ?></a>
                    <a href="?pages=soutenir" class="btn btn-lg" style="background:#fff;color:#1B2A44;font-weight:700;padding:12px 28px;border-radius:999px"><?= e(reglage('btn_soutenir', 'JE SOUTIENS')) ?></a>
                </div>

                

            </div>

            <!-- RIGHT VISUAL -->
            <div class="col-lg-5 d-none d-lg-block">

                <div class="hero-card">

                    <h5 class="text-white fw-bold mb-4">
                        <i class="bi bi-lightning-charge-fill text-warning"></i>
                        Accès rapide
                    </h5>

                    <a href="./adhere/adhesion.php" class="hero-quicklink">
                        <span>
                            <i class="bi bi-person-plus-fill"></i>
                            Devenir membre
                        </span>
                        <i class="bi bi-chevron-right"></i>
                    </a>

                    <a href="?pages=soutenir" class="hero-quicklink">
                        <span>
                            <i class="bi bi-heart-fill"></i>
                            Faire un don
                        </span>
                        <i class="bi bi-chevron-right"></i>
                    </a>

                    <a href="?pages=contact" class="hero-quicklink">
                        <span>
                            <i class="bi bi-envelope-fill"></i>
                            Nous contacter
                        </span>
                        <i class="bi bi-chevron-right"></i>
                    </a>

                </div>

            </div>

        </div>

    </div>

</section>




<!-- =========================
        ABOUT SECTION
========================= -->
<section class="about-saas py-5">
    <div class="container">

        <div class="row align-items-center g-4">

            <!-- TEXTE -->
            <div class="col-lg-7">

                <span class="section-badge">
                    Présentation Officielle
                </span>

                <h2 class="about-title">
                    <?= e(reglage('home_citation', '« Sans Rassemblement des Chrétiens Républicains, la démocratie Congolaise serait affaiblie »')) ?>
                </h2>

                <p class="about-text">
                    Monsieur <strong>Benjamin Jean de Dieu BONIOMA ISELONGE</strong>
                    est en dignité et notoriété le Premier Plus Grand Serviteur
                    et le Président Fondateur du mouvement.
                </p>

                <!-- STATS -->
                <div class="about-stats">

                    <div class="stat-card">
                        <h3>2006</h3>
                        <span>Année de création</span>
                    </div>

                    <div class="stat-card">
                        <h3>RCR</h3>
                        <span>Parti politique</span>
                    </div>

                    <div class="stat-card">
                        <h3>RDC</h3>
                        <span>Vision nationale</span>
                    </div>

                </div>

            </div>

            <!-- VISUEL -->
            <div class="col-lg-5">

                <div class="about-visual">

                    <div class="visual-box">
                        <i class="bi bi-heart-fill"></i>
                        <div>
                            <h4><?= e(reglage('valeur_amour_titre', 'Amour')) ?></h4>
                            <p><?= e(reglage('valeur_amour_texte')) ?></p>
                        </div>
                    </div>

                    <div class="visual-box">
                        <i class="bi bi-people-fill"></i>
                        <div>
                            <h4><?= e(reglage('valeur_democratie_titre', 'Démocratie')) ?></h4>
                            <p><?= e(reglage('valeur_democratie_texte')) ?></p>
                        </div>
                    </div>

                    <div class="visual-box">
                        <i class="bi bi-balance-scale"></i>
                        <div>
                            <h4><?= e(reglage('valeur_justice_titre', 'Justice')) ?></h4>
                            <p><?= e(reglage('valeur_justice_texte')) ?></p>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>
</section>
<!-- =========================
        PRESIDENT SECTION
========================= -->

<section id="about-president" class="president-section py-5">

    <div class="container">

        <div class="row align-items-center">

            <!-- IMAGE -->
            <div class="col-lg-5 mb-4 mb-lg-0">

                <div class="president-image">

                    <img loading="lazy" decoding="async" src="./assets/img/president.jpg"
                         alt="Président du parti">

                </div>

            </div>

            <!-- CONTENT -->
            <div class="col-lg-7">

                <div class="president-content">

                    <span class="section-badge">
                        Président du Parti
                    </span>

                    <h2>
                        Benjamin Jean de Dieu BONIOMA ISELONGE
                    </h2>

                    <p>
                        Initiateur et Président du Rassemblement,
                        il est le Garant de l'orientation de la Vision
                        et de la Discipline, le Dépositaire Général
                        de la doctrine chrétienne-républicaine
                        et le Chef du Parti.
                    </p>

                    <h3 class="idea-title">
                        Mes idées
                    </h3>

                    <p>
                        Construire une nation fondée sur les valeurs
                        chrétiennes, la démocratie, la responsabilité,
                        la justice et l'unité nationale.
                    </p>

                    <!-- BUTTON -->
                    <button class="modern-btn"
                            data-bs-toggle="collapse"
                            data-bs-target="#moreContent">

                        Lire la suite

                    </button>

                    <!-- COLLAPSE -->
                    <div class="collapse mt-4" id="moreContent">

                        <div class="more-content">

                            <h4>
                                Présentation du Parti
                            </h4>

                            <p>

                                <strong>Dénomination :</strong><br>
                                Rassemblement des Chrétiens Républicains (RCR)

                            </p>

                            <p>

                                <strong>Personnalité Juridique :</strong><br>
                                Arrêté N°010/2006 du 30 janvier 2006

                            </p>

                            <p>

                                <strong>Devise :</strong>

                            </p>

                            <ul>

                                <li>
                                    <strong>Amour :</strong>
                                    Amour de Dieu et du prochain
                                </li>

                                <li>
                                    <strong>Démocratie :</strong>
                                    Liberté, responsabilité et devoir
                                </li>

                                <li>
                                    <strong>Justice :</strong>
                                    Expression du droit et de la vérité
                                </li>

                            </ul>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>



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
        OWL CAROUSEL
========================= -->

<!-- =========================
        NOS TEXTES
========================= -->

<?php /* 🔧 CORRECTIF (audit) : id="services" dupliquait celui de la section
     "Notre équipe" ci-dessus (deux id identiques = HTML invalide, cible
     ambiguë pour un lien d'ancrage ou un script). Renommé. */ ?>
<section id="textes-fondamentaux" class="py-5">

    <div class="container">

        <!-- TITLE -->
        <div class="text-center mb-5">

            <h2 class="fw-bold text-primary">
                <?= e(reglage('textes_titre', 'Nos textes fondamentaux')) ?>
            </h2>

            <p class="text-muted">
                Découvrez les documents et textes fondamentaux
                du Rassemblement des Chrétiens Républicains.
            </p>

        </div>

        <!-- CARDS -->
        <div class="row g-4">

            <!-- CARD 1 -->
            <div class="col-lg-4 col-md-6">

                <div class="card border-0 h-100 text-center p-4">

                    <div class="mb-3">

                        <i class="bi bi-book-fill"
                           style="font-size: 55px;"></i>

                    </div>

                    <h4>
                        Déclaration Constitutive
                    </h4>

                    <p class="text-muted">
                        Télécharger la déclaration constitutive du parti.
                    </p>

                    <?php /* 🔧 CORRECTIF (audit) : ce bouton pointait vers
                         "#" (aucun fichier réel derrière) — cliquer
                         semblait fonctionner mais ne téléchargeait rien.
                         Désactivé explicitement en attendant le document
                         réel plutôt que de laisser un lien trompeur. */ ?>
                    <a target="_blank" href="../media/text/Fondamentaux.pdf" type="button"
                            class="btn btn-outline-secondary rounded-pill"
                            disabled
                            title="Document bientôt disponible">

                        <i class="bi bi-download"></i>
                        Télécharger

                    </a>

                </div>

            </div>
             <!-- CARD 2-->
            <div class="col-lg-4 col-md-6">

                <div class="card border-0 h-100 text-center p-4">

                    <div class="mb-3">

                        <i class="bi bi-book-half"
                           style="font-size: 55px;"></i>

                    </div>

                    <h4>
                        Charte des pricipes
                    </h4>

                    <p class="text-muted">
                        Télécharger les statuts officiels du parti.
                    </p>

                    <?php /* 🔧 CORRECTIF (audit) : même correctif que les deux
                         cartes précédentes. */ ?>
                    <button type="button"
                            class="btn btn-outline-secondary rounded-pill disabled"
                            disabled
                            title="Document bientôt disponible">

                        <i class="bi bi-download"></i>
                        Bientôt disponible

                    </button>

                </div>

            </div>

            <!-- CARD 3 -->
            <div class="col-lg-4 col-md-6">

                <div class="card border-0 h-100 text-center p-4">

                    <div class="mb-3">

                        <i class="bi bi-journal-bookmark"
                           style="font-size: 55px;"></i>

                    </div>

                    <h4>
                        Nos Idées Forces
                    </h4>

                    <p class="text-muted">
                        Télécharger Nos idées Forces.
                    </p>

                    <?php /* 🔧 CORRECTIF (audit) : aucun lien Play Store réel
                         n'existe dans le projet — même correctif que la
                         carte précédente. */ ?>
                    <button type="button"
                            class="btn btn-outline-secondary rounded-pill disabled"
                            disabled
                            title="Lien bientôt disponible">

                        <i class="bi bi-download"></i>
                        Bientôt disponible

                    </button>
                    </br>
                     <p class="text-muted">
                       Guide Constitutionnel du Nekongo
                    </p>
                     
                    <?php /* 🔧 CORRECTIF (audit) : aucun lien Play Store réel
                         n'existe dans le projet — même correctif que la
                         carte précédente. */ ?>
                    <button type="button"
                            class="btn btn-outline-secondary rounded-pill disabled"
                            disabled
                            title="Lien bientôt disponible">

                        <i class="bi bi-download"></i>
                        Bientôt disponible

                    </button>

                </div>

            </div>

           

        </div>

    </div>

</section>



<!-- =========================
        NOTRE MAITRE
========================= -->

<section id="about" class="py-5">

    <div class="container">

        <!-- TITLE -->
        <div class="text-center mb-5">

            <h2 class="fw-bold text-primary">
                Notre Maître
            </h2>

            <p class="text-muted">

                Nous, Chrétiens Républicains, avons pour Maître
                le Seigneur Jésus-Christ.

            </p>

        </div>

        <!-- CONTENT -->
        <div class="row align-items-center g-4">

            <!-- IMAGE -->
            <div class="col-lg-5">

                <img loading="lazy" decoding="async" src="./media/mbres/JS.jpg"
                     class="img-fluid rounded"
                     alt="Jésus-Christ">

            </div>

            <!-- TEXT -->
            <div class="col-lg-7">

                <div class="card border-0 p-4">

                    <h4 class="mb-3">
                        Notre Doctrine
                    </h4>

                    <p class="text-muted" style="text-align: justify;">

                        Cette doctrine est unique à son genre.
                        Le Christianisme est considéré comme un mode de vie
                        capable de transformer positivement la société
                        et de réorganiser notre civilisation
                        pour le bien-être de tous.

                    </p>

                    <p class="text-muted" style="text-align: justify;">

                        Jésus-Christ a changé le cours de l'Histoire.
                        Son enseignement basé sur l'amour,
                        la justice et la sagesse continue
                        d'influencer les nations.

                    </p>
                    <h5 class="fw-bold text-dark">

                                Sa vie et son message provoquent
                                des changements

                            </h5>

                            <p class="text-muted"
                               style="text-align: justify;">

                                Là où son enseignement a été reconnu,
                                les effets ont été visibles :
                                protection des enfants,
                                fondation des écoles,
                                abolition de l'esclavage,
                                valorisation du mariage
                                et promotion des droits humains.

                            </p>

                            <p class="text-muted">

                                <strong>
                                    Deux missions importantes :
                                </strong>

                            </p>

                            <ol class="text-muted">

                                <li>
                                    Consacrer Dieu comme
                                    le seul Maître des Nations.
                                </li>

                                <li>
                                    Mettre les richesses
                                    des Nations au service de tous.
                                </li>

                            </ol>

                   

                   

                </div>

            </div>

        </div>

    </div>

</section>




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
        EVENEMENTS
========================= -->

<section id="why-us" class="py-5">

    <div class="container">

        <!-- TITLE -->
        <div class="text-center mb-5">

            <h2 class="fw-bold text-primary">
                Les événements récents
            </h2>

            <p class="text-muted">
                Découvrez les dernières activités
                et événements du RCR.
            </p>

        </div>

        <!-- ARTICLES -->
        <div class="row g-4">

            <?php while($ar = $reqArticle->fetch()){ ?>

            <div class="col-lg-4 col-md-6">

                <div class="card border-0 h-100">

                    <!-- IMAGE -->
                    <img loading="lazy" decoding="async" src="./media/images_activ/<?= e($ar['photo']) ?>"
                         class="card-img-top"
                         style="height: 250px; object-fit: cover;"
                         alt="">

                    <!-- BODY -->
                    <div class="card-body d-flex flex-column">

                        <!-- DATE -->
                        <small class="text-muted mb-2">

                            <i class="bi bi-calendar-event"></i>

                            <?php
                                $date = new DateTime($ar['date_pub']);
                            ?>

                            Posté le
                            <?= $date->format('d/m/Y à H:i'); ?>

                        </small>

                        <!-- TITLE -->
                        <h5 class="fw-bold">

                            <a href="?pages=detail&categ=<?= urlencode($ar['categorie']) ?>&id=<?= (int) $ar['id_act'] ?>"
                               class="text-decoration-none text-primary">

                                <?= e(mb_strimwidth(html_entity_decode((string) $ar['titre'], ENT_QUOTES, "UTF-8"), 0, 50, "…")) ?>

                            </a>

                        </h5>

                        <!-- DESCRIPTION -->
                        <p class="text-muted flex-grow-1">

                            <?= e(mb_strimwidth(strip_tags(html_entity_decode((string) $ar['description'], ENT_QUOTES, "UTF-8")), 0, 100, "…")) ?>

                        </p>

                        <!-- BUTTON -->
                        <a href="?pages=detail&categ=<?= urlencode($ar['categorie']) ?>&id=<?= (int) $ar['id_act'] ?>"
                           class="btn btn-dark rounded-pill">

                            Lire la suite

                        </a>

                    </div>

                </div>

            </div>

            <?php } ?>

        </div>

    </div>

</section>



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
<!-- ======= Team Section ======= -->
<script>
    $(document).ready(function(){
        $(".repons").hide();

        $(".aa").click(function(e){
            e.preventDefault() ;
            // $(this).parent().next().slideToggle().css("color","green");
            if($(this).html()=="Lire la suite"){
                $(".repons").show(200);
                $(this).html("Cacher la suite");
            }else{
                $(".repons").hide(200);
                $(this).html("Lire la suite");
            }
        });
    });
    $(document).ready(function(){
        $(".repons2").hide();

        $(".aa2").click(function(e){
            e.preventDefault() ;
            // $(this).parent().next().slideToggle().css("color","green");
            if($(this).html()=="Lire la suites"){
                $(".repons2").show(200);
                $(this).html("Cacher la suites");
            }else{
                $(".repons2").hide(200);
                $(this).html("Lire la suites");
            }
        });
    });
    $(function(){
        $(document).scrollTop(0)  ;
        $('nav a.target').removeClass('target') ;
        $('nav a.publications').addClass('target') ;
    })
    if ($.fn.owlCarousel) $(".sliderrs").owlCarousel({
        margin:10,
        loop:true,
        autoplay:true,
        autoplayTimeout:2000,
        autoplayHoverPause:true,
        responsive:{
            0:{
                items:1,
                nav:false
            },
            600:{
                items:2,
                nav:false
            },
            1000:{
                items:3,
                nav:false
            }
        }
    });
</script>
<script>

if ($.fn.owlCarousel) $('.team-slider').owlCarousel({

    loop:true,

    margin:20,

    nav:false,

    autoplay:true,

    autoplayTimeout:4000,

    responsive:{

        0:{
            items:1
        },

        768:{
            items:2
        },

        1200:{
            items:3
        }

    }

});

</script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const slides = document.querySelectorAll(".hero-slide");

    if (!slides.length) return;

    let currentSlide = 0;

    setInterval(function () {

        slides[currentSlide].classList.remove("active");

        currentSlide = (currentSlide + 1) % slides.length;

        slides[currentSlide].classList.add("active");

    }, 5000);

});
</script>
