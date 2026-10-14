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

/* Photo du Maître + nom sous la photo */
#about .maitre-figure{margin: 0;}
#about .maitre-figure img{width: 100%;}
#about .maitre-figure figcaption{
    margin-top: 16px;
    text-align: center;
    font-family: var(--serif);
    font-weight: 600;
    font-size: 1.3rem;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--ink);
}
#about .maitre-figure figcaption::before{
    content: "";
    display: block;
    width: 52px;
    height: 2px;
    background: var(--gold);
    margin: 0 auto 12px;
}

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

/* ===== Boutons d'action du bandeau (J'ADHÈRE / JE SOUTIENS) ===== */
.hero-cta{display:flex;flex-direction:column;gap:14px;margin-top:26px;max-width:380px}
.hero-cta .btn-hero{
    display:flex;align-items:center;justify-content:center;gap:10px;
    min-height:54px;padding:14px 28px;border-radius:999px;border:2px solid transparent;
    font-weight:800;font-size:1rem;letter-spacing:.06em;text-transform:uppercase;text-decoration:none;line-height:1.1;
    transition:transform .2s ease,box-shadow .2s ease,background-color .2s ease,color .2s ease,border-color .2s ease;
    -webkit-tap-highlight-color:transparent;
}
.hero-cta .btn-hero i{font-size:1.15rem;line-height:1}
.hero-cta .btn-hero .btn-fleche{margin-left:2px;font-size:1.05rem;transition:transform .2s ease}
.hero-cta .btn-adherer{
    background:linear-gradient(135deg,#B8923A 0%,#9C7A2E 100%);color:#fff;
    box-shadow:0 8px 22px rgba(156,122,46,.45),inset 0 1px 0 rgba(255,255,255,.25);
}
.hero-cta .btn-soutenir{
    background:#fff;color:#1B2A44;border-color:#fff;
    box-shadow:0 8px 22px rgba(0,0,0,.25);
}
.hero-cta .btn-soutenir i:first-child{color:#B4232F}
.hero-cta .btn-hero:hover,.hero-cta .btn-hero:focus-visible{transform:translateY(-2px)}
.hero-cta .btn-hero:hover .btn-fleche,.hero-cta .btn-hero:focus-visible .btn-fleche{transform:translateX(4px)}
.hero-cta .btn-adherer:hover,.hero-cta .btn-adherer:focus-visible{color:#fff;box-shadow:0 12px 28px rgba(156,122,46,.6),inset 0 1px 0 rgba(255,255,255,.3)}
.hero-cta .btn-soutenir:hover,.hero-cta .btn-soutenir:focus-visible{color:#1B2A44;background:#F1E9D8;border-color:#F1E9D8}
.hero-cta .btn-hero:focus-visible{outline:3px solid #C9A24A;outline-offset:3px}
.hero-cta .btn-hero:active{transform:translateY(0) scale(.98)}
@media (min-width:576px){
    .hero-cta{flex-direction:row;flex-wrap:wrap;max-width:none}
    .hero-cta .btn-hero{min-width:210px}
}
@media (prefers-reduced-motion:reduce){
    .hero-cta .btn-hero,.hero-cta .btn-hero .btn-fleche{transition:none}
    .hero-cta .btn-hero:hover,.hero-cta .btn-hero:focus-visible,.hero-cta .btn-hero:active{transform:none}
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
/* =========================
   NOS IDÉES FORCES
========================= */
#idees-forces{background: var(--paper) !important;}
#idees-forces .card{
    background: #FBF8F0;
    border: 1px solid var(--paper-line) !important;
    border-radius: 0 !important;
}
#idees-forces .card i{color: var(--gold) !important;}
#idees-forces h4{font-family: var(--serif); font-weight: 600; color: var(--ink);}
#idees-forces .text-muted{color: var(--text-soft) !important;}
#idees-forces .btn.disabled{
    border-color: var(--paper-line) !important;
    color: var(--text-soft) !important;
    background: none !important;
}

/* Nos Idées Forces : liste numérotée, 2 colonnes sur grand écran */
#idees-forces .idees-forces{
    text-align: left;
    padding-left: 1.5rem;
    margin: 0 auto 1.5rem;
    max-width: 1000px;
    color: var(--text-soft);
    line-height: 1.75;
}
#idees-forces .idees-forces li{margin-bottom: 1.1rem; padding-left: .4rem; break-inside: avoid;}
#idees-forces .idees-forces li::marker{color: var(--gold); font-family: var(--serif); font-weight: 700;}
#idees-forces .idees-forces strong{font-family: var(--serif); font-weight: 600; color: var(--ink);}
@media (min-width: 992px){
    #idees-forces .idees-forces{column-count: 2; column-gap: 3rem;}
}


/* =========================
   QUI SOMMES-NOUS (bandeau bleu → page « Présentation du RCR »)
========================= */
.qsn-section{
    position: relative;
    background: var(--ink);
    padding: 80px 0 84px;
    text-align: center;
    overflow: hidden;
}
.qsn-section::before{
    content:"";
    position:absolute; inset:0;
    background:
      repeating-linear-gradient(90deg, rgba(241,233,216,.05) 0 1px, transparent 1px 88px),
      radial-gradient(ellipse at 50% 120%, rgba(156,122,46,.18), transparent 60%);
    pointer-events:none;
}
.qsn-section .container{position: relative;}
.qsn-seal{
    width: 64px; height: 64px; margin: 0 auto 20px;
    border: 1.5px solid var(--gold); border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-family: var(--serif); font-size: 22px; font-weight: 600; color: var(--gold);
}
.qsn-title{
    font-family: var(--serif); font-weight: 600;
    font-size: clamp(32px, 5vw, 50px); line-height: 1.1;
    color: var(--paper); margin: 0 0 18px;
}
.qsn-sous{
    display: inline-block; margin: 0;
    padding: 10px 22px;
    background: #2F4A7A;
    border-left: 3px solid var(--gold); border-right: 3px solid var(--gold);
    color: #fff; font-family: var(--serif); font-weight: 500;
    font-size: clamp(16px, 2.6vw, 21px); line-height: 1.5;
}
.qsn-rule{width: 56px; height: 2px; background: var(--gold); margin: 26px auto 30px;}
.qsn-btn{
    display: inline-flex; align-items: center; gap: 10px;
    padding: 14px 32px; min-height: 52px;
    border: 2px solid var(--gold); color: var(--paper);
    font-weight: 700; letter-spacing: .08em; text-transform: uppercase; text-decoration: none;
    transition: background .2s ease, color .2s ease, transform .2s ease;
}
.qsn-btn:hover, .qsn-btn:focus-visible{background: var(--gold); color: #fff; transform: translateY(-2px);}
.qsn-btn:focus-visible{outline: 3px solid #C9A24A; outline-offset: 3px;}
.qsn-btn i{transition: transform .2s ease;}
.qsn-btn:hover i{transform: translateX(4px);}

/* =========================
   ÉVÉNEMENTS : onglets + rendez-vous phare
========================= */
.evt-tabs{gap: 10px; border: 0; flex-wrap: wrap;}
.evt-tabs .nav-link{
    border: 1px solid var(--ink); border-radius: 0;
    color: var(--ink); background: transparent;
    font-weight: 700; padding: 12px 26px; letter-spacing: .03em;
    display: inline-flex; align-items: center; gap: 8px;
}
.evt-tabs .nav-link:hover{background: rgba(27,42,68,.08); color: var(--ink);}
.evt-tabs .nav-link.active{background: var(--ink); color: var(--paper); border-color: var(--ink);}
.evt-tabs .nav-link:focus-visible{outline: 3px solid var(--gold); outline-offset: 2px;}

.rdv-card{
    max-width: 820px; margin: 0 auto; text-align: center;
    background: var(--ink); color: var(--paper);
    border-top: 4px solid var(--gold);
    padding: 44px 32px 40px;
    box-shadow: 0 18px 44px rgba(27,42,68,.22);
}
.rdv-badge{
    display: inline-block; border: 1px solid var(--gold); color: #D9B965;
    font-size: 12.5px; font-weight: 600; letter-spacing: .1em; text-transform: uppercase;
    padding: 6px 16px; margin-bottom: 20px;
}
.rdv-date{
    font-family: var(--serif); font-weight: 600; color: #fff;
    font-size: clamp(30px, 6vw, 52px); line-height: 1.1; margin-bottom: 16px;
}
.rdv-date i{color: var(--gold); font-size: .7em; margin-right: 10px;}
.rdv-titre{
    font-family: var(--serif); font-weight: 500; color: var(--paper);
    font-size: clamp(18px, 3vw, 25px); line-height: 1.5; margin: 0 auto 18px; max-width: 34ch;
}
.rdv-accroche{
    display: inline-block; background: #2F4A7A; color: #fff; font-weight: 700;
    padding: 10px 22px; margin: 0 0 14px; border-left: 3px solid var(--gold); border-right: 3px solid var(--gold);
}
.rdv-compte{color: #C9BFA9; margin: 10px 0 0; font-size: 15px;}
.rdv-compte strong{color: #fff; font-family: var(--serif); font-size: 1.5em;}

/* Bandeau « Événements » : reprend le style « Qui sommes-nous », en plus compact */
.evt-band{padding: 56px 0 60px;}
.evt-band .qsn-rule{margin: 24px auto 0;}

/* « Où en sommes-nous » : bientôt disponible */
.evt-soon{
    max-width: 820px; margin: 0 auto; text-align: center;
    background: var(--paper); border: 1px dashed var(--paper-line);
    padding: 56px 24px;
}
.evt-soon i{font-size: 2.4rem; color: var(--gold);}
.evt-soon-titre{
    font-family: var(--serif); font-weight: 600; color: var(--ink);
    font-size: clamp(22px, 4vw, 30px); margin: 12px 0 0;
}

/* =========================
   ALTERNANCE DES FONDS : Maître (papier) / Qui sommes-nous (crème) / Président (papier) /
   Événements (crème) / Textes (papier) / Idées Forces (crème)
========================= */
.president-section{background: var(--paper);}
#why-us{background: #FBF8F0 !important;}
#why-us .card{background: var(--paper) !important;}
#textes-fondamentaux{background: var(--paper) !important;}
#textes-fondamentaux .card{background: #FBF8F0 !important;}
#idees-forces{background: #FBF8F0 !important;}
#idees-forces .card{background: var(--paper) !important;}

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
                <div class="hero-cta">
                    <a href="./adhere/adhesion.php" class="btn-hero btn-adherer">
                        <i class="bi bi-person-plus-fill" aria-hidden="true"></i>
                        <span><?= e(reglage('btn_adherer', "J'ADHÈRE")) ?></span>
                        <i class="bi bi-arrow-right btn-fleche" aria-hidden="true"></i>
                    </a>
                    <a href="?pages=soutenir" class="btn-hero btn-soutenir">
                        <i class="bi bi-heart-fill" aria-hidden="true"></i>
                        <span><?= e(reglage('btn_soutenir', 'JE SOUTIENS')) ?></span>
                        <i class="bi bi-arrow-right btn-fleche" aria-hidden="true"></i>
                    </a>
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

                <figure class="maitre-figure">

                    <img loading="lazy" decoding="async" src="./media/mbres/JS.jpg"
                         class="img-fluid rounded"
                         alt="Jésus-Christ">

                    <figcaption>YESHUWAH MASIYAH</figcaption>

                </figure>

            </div>

            <!-- TEXT -->
            <div class="col-lg-7">

                <div class="card border-0 p-4">

                    <h4 class="mb-3">
                        Sa vie
                    </h4>

                    <p class="text-muted" style="text-align: justify;">

                        Toute la Vie de Yeshuwah accomplit les Alliances
                        et les Prophéties de Yahweh : Il est né la nuit
                        de la fête de Tabernacles, la 15ème nuit du 7ème mois
                        de l'année en -7 avant Jésus-Christ et mort le jour
                        des préparatifs de la Pâque, le 14ème jour du 1er mois
                        de l'an 33 après Jésus-Christ.

                    </p>

                    <h4 class="mb-3 mt-2">
                        Son message
                    </h4>

                    <p class="text-muted" style="text-align: justify;">

                        Son message est simple :
                        «&nbsp;Tu aimeras le Seigneur Yahweh de tout ton cœur,
                        de toute ton âme et de tout ton esprit, et, Tu aimeras
                        ton prochain comme toi-même&nbsp;».

                    </p>

                    <p class="text-muted" style="text-align: justify;">

                        Le nom de celui qui pratique et enseigne ce message
                        du Maître dans et par sa vie est inscrit dans le livre
                        de Yahweh.

                    </p>

                   

                   

                </div>

            </div>

        </div>

    </div>

</section>




<!-- =========================
        QUI SOMMES-NOUS
========================= -->
<section id="qui-sommes-nous" class="qsn-section">
    <div class="container">
        <div class="qsn-seal" aria-hidden="true">R</div>
        <h2 class="qsn-title"><?= e(reglage('qsn_titre', 'Qui sommes-nous' . "\u{00A0}?")) ?></h2>
        <p class="qsn-sous"><?= e(reglage('qsn_soustitre', 'Dirigeants et Militants Chrétiens Républicains')) ?></p>
        <div class="qsn-rule" aria-hidden="true"></div>
        <a class="qsn-btn" href="?pages=apropos">
            Nos instances <i class="bi bi-arrow-right" aria-hidden="true"></i>
        </a>
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

                <span class="section-badge">
                    Président du Parti
                </span>

                <div class="president-image">

                    <img loading="lazy" decoding="async" src="./assets/img/president.jpg"
                         alt="Président du parti">

                </div>

            </div>

            <!-- CONTENT -->
            <div class="col-lg-7">

                <div class="president-content">

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

                </div>

            </div>

        </div>

    </div>

</section>



<!-- =========================
        EVENEMENTS
========================= -->

<section id="why-us" class="pb-5">

    <!-- Bandeau bleu (même style que « Qui sommes-nous ») -->
    <div class="qsn-section evt-band">
        <div class="container">
            <h2 class="qsn-title">Événements</h2>
            <p class="qsn-sous">Suivez l'action du RCR et ne manquez aucun rendez-vous.</p>
            <div class="qsn-rule" aria-hidden="true"></div>
        </div>
    </div>

    <div class="container pt-5">

        <!-- DEUX ONGLETS -->
        <ul class="nav evt-tabs justify-content-center mb-5" id="evt-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="evt-tab-ou" data-bs-toggle="pill" data-bs-target="#evt-pane-ou"
                        type="button" role="tab" aria-controls="evt-pane-ou" aria-selected="true">
                    <i class="bi bi-compass" aria-hidden="true"></i> Où en sommes-nous
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="evt-tab-rdv" data-bs-toggle="pill" data-bs-target="#evt-pane-rdv"
                        type="button" role="tab" aria-controls="evt-pane-rdv" aria-selected="false">
                    <i class="bi bi-calendar-event" aria-hidden="true"></i> Rendez-vous
                </button>
            </li>
        </ul>

        <div class="tab-content">

            <!-- OÙ EN SOMMES-NOUS : activités récentes (inchangé) -->
            <div class="tab-pane fade show active" id="evt-pane-ou" role="tabpanel" aria-labelledby="evt-tab-ou" tabindex="0">

        <?php
                /* « Où en sommes-nous » : passer à true pour réafficher les activités récentes */
                $ouEnSommesNousPret = false;
            ?>
            <?php if (!$ouEnSommesNousPret): ?>
                <div class="evt-soon">
                    <i class="bi bi-hourglass-split" aria-hidden="true"></i>
                    <p class="evt-soon-titre">Bientôt disponible</p>
                </div>
            <?php else: ?>

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

            <?php endif; ?>

            </div>

            <!-- RENDEZ-VOUS : les rendez-vous phares -->
            <div class="tab-pane fade" id="evt-pane-rdv" role="tabpanel" aria-labelledby="evt-tab-rdv" tabindex="0">
                <?php
                    $rdvDate  = new DateTimeImmutable('2028-12-30 00:00:00');
                    $joursRdv = (int) ((new DateTimeImmutable('today'))->diff($rdvDate)->format('%r%a'));
                ?>
                <div class="rdv-card">
                    <span class="rdv-badge">Rendez-vous phare</span>
                    <div class="rdv-date">
                        <i class="bi bi-calendar-event-fill" aria-hidden="true"></i>
                        <?= e(reglage('rdv_date', '30 décembre 2028')) ?>
                    </div>
                    <h3 class="rdv-titre">
                        <?= e(reglage('rdv_titre', 'Élections présidentielles, sénatoriales, législatives, nationales, provinciales et municipales')) ?>
                    </h3>
                    <p class="rdv-accroche">
                        <?= e(reglage('rdv_accroche', 'Les élections se préparent dès maintenant !')) ?>
                    </p>
                    <?php if ($joursRdv > 0): ?>
                        <p class="rdv-compte"><strong><?= number_format($joursRdv, 0, ',', ' ') ?></strong> jours restants</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>

</section>



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
        <div class="row g-4 justify-content-center">

            <!-- CARD 1 -->
            <div class="col-lg-5 col-md-6">

                <div class="card border-0 h-100 text-center p-4">

                    <div class="mb-3">

                        <i class="bi bi-book-fill"
                           style="font-size: 55px;"></i>

                    </div>

                    <h4>
                        Les Actes Constitutifs
                    </h4>

                    <p class="text-muted">
                        Consulter les actes constitutifs du parti.
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
            <div class="col-lg-5 col-md-6">

                <div class="card border-0 h-100 text-center p-4">

                    <div class="mb-3">

                        <i class="bi bi-book-half"
                           style="font-size: 55px;"></i>

                    </div>

                    <h4>
                        La Charte des Principes
                    </h4>

                    <p class="text-muted">
                        Télécharger la charte des principes du parti.
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
        </div>

    </div>

</section>





<!-- =========================
        NOS IDÉES FORCES
========================= -->

<section id="idees-forces" class="py-5">

    <div class="container">

        <!-- TITLE -->
        <div class="text-center mb-5">

            <h2 class="fw-bold text-primary">
                <?= e(reglage('idees_titre', 'Nos idées forces')) ?>
            </h2>

            <p class="text-muted">
                Les idées directrices et les références
                qui guident l'action du Rassemblement des Chrétiens Républicains.
            </p>

        </div>

        <div class="row g-4 justify-content-center">

            <div class="col-12">

                <div class="card border-0 h-100 text-center p-4">

                    <div class="mb-3">
                        <i class="bi bi-journal-bookmark" style="font-size: 55px;" aria-hidden="true"></i>
                    </div>

                    <h4>Nos Idées Forces</h4>

                    <ol class="idees-forces">

                        <li>
                            <strong>Le Guide Constitutionnel du Nekongo</strong> :
                            c'est le guide qui permet aux Nekongo
                            (citoyens propriétaires du Kongo) de comprendre
                            le Projet de la Nouvelle Constitution de la
                            République Kongo.
                            <span class="d-block mt-2">
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary rounded-pill disabled"
                                        disabled
                                        title="Document bientôt disponible">
                                    <i class="bi bi-download"></i>
                                    Bientôt disponible
                                </button>
                            </span>
                        </li>

                        <li>
                            <strong>Le Pacte Kongo</strong> :
                            le RCR adopte le retour au Pacte ancestral Kongo,
                            d'unité et de paix. Si le Pacte Kongo fut imposé
                            à la création du Royaume Kongo, il fut adopté par
                            toutes les ethnies lors de sa renaissance et son
                            expansion de 1878 à 1880.
                        </li>

                        <li>
                            <strong>Participation citoyenne et émancipation économique</strong> :
                            le RCR initie un nouveau système en remplacement
                            du système de subordination salariale.
                        </li>

                        <li>
                            <strong>Calendrier Originel de Yahweh</strong> :
                            le RCR propose l'adoption du Calendrier Républicain
                            intelligent de Yahweh, garant de l'indépendance
                            nationale.
                        </li>

                        <li>
                            <strong>Commune-Ethnie</strong> :
                            la démocratisation de nos ethnies, signataires du
                            Pacte Kongo, et leur institutionnalisation en
                            Commune, 3ème niveau du Pouvoir.
                        </li>

                    </ol>

                    <p class="text-muted mb-2">
                        Télécharger nos idées forces.
                    </p>

                    <div>
                        <button type="button"
                                class="btn btn-outline-secondary rounded-pill disabled"
                                disabled
                                title="Document bientôt disponible">
                            <i class="bi bi-download"></i>
                            Bientôt disponible
                        </button>
                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


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
