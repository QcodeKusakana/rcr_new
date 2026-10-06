<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<style>
:root{
  --ink:#1B2A44;
  --ink-2:#233355;
  --paper:#F1E9D8;
  --paper-2:#E7DBBF;
  --paper-line:#CBBB92;
  --gold:#9C7A2E;
  --red:#7D2330;
  --text:#241F1A;
  --text-soft:#5B5346;
  --max:720px;
  --serif:'Fraunces', Georgia, serif;
  --sans:'IBM Plex Sans', system-ui, sans-serif;
}

*{box-sizing:border-box;}

html{scroll-behavior:smooth;}

body{
  margin:0;
  background:var(--paper);
  color:var(--text);
  font-family:var(--sans);
  -webkit-font-smoothing:antialiased;
}

::selection{
  background:var(--gold);
  color:var(--paper);
}

a{color:inherit;}

/* ---------- HEADER ---------- */

.site-head{
  background:var(--ink);
  color:var(--paper);
  padding:64px 24px 56px;
  text-align:center;
  position:relative;
  overflow:hidden;
}

.site-head::before{
  content:"";
  position:absolute;
  inset:0;
  background:
    repeating-linear-gradient(
      90deg,
      rgba(241,233,216,.05) 0 1px,
      transparent 1px 88px
    );
  pointer-events:none;
}

.seal{
  width:64px;
  height:64px;
  margin:0 auto 22px;
  border:1.5px solid var(--gold);
  border-radius:50%;
  display:flex;
  align-items:center;
  justify-content:center;
  font-family:var(--serif);
  font-size:22px;
  font-weight:600;
  color:var(--gold);
  letter-spacing:.5px;
}

.site-head .kicker{
  font-size:13px;
  letter-spacing:.14em;
  text-transform:uppercase;
  color:var(--paper-2);
  margin:0 0 14px;
}

.site-head h1{
  font-family:var(--serif);
  font-weight:600;
  font-size:clamp(32px,5vw,54px);
  line-height:1.08;
  margin:0 0 20px;
  max-width:16ch;
  margin-inline:auto;
}

.site-head p{
  max-width:56ch;
  margin:0 auto;
  color:#D8CFB8;
  font-size:16.5px;
  line-height:1.8;
}

.site-head .rule{
  width:56px;
  height:2px;
  background:var(--gold);
  margin:26px auto 0;
}

/* ---------- LAYOUT ---------- */

.charter{
  max-width:1180px;
  margin:0 auto;
  padding:56px 24px 100px;
  display:grid;
  grid-template-columns:280px 1fr;
  gap:56px;
  align-items:start;
}

/* ---------- INDEX ---------- */

.index-wrap{
  position:sticky;
  top:20px;
}

.index-label{
  font-size:12px;
  letter-spacing:.12em;
  text-transform:uppercase;
  color:var(--text-soft);
  margin:0 0 14px;
  padding-bottom:10px;
  border-bottom:1px solid var(--paper-line);
}

.index{
  list-style:none;
  margin:0 0 34px;
  padding:0;
}

.index li{
  margin:0;
}

.index button{
  width:100%;
  display:flex;
  gap:12px;
  align-items:baseline;
  background:none;
  border:none;
  text-align:left;
  font-family:var(--sans);
  font-size:14.5px;
  color:var(--text-soft);
  padding:9px 4px;
  cursor:pointer;
  border-left:2px solid transparent;
  line-height:1.4;
  transition:
    color .15s ease,
    border-color .15s ease,
    background .15s ease;
}

.index button .num{
  font-family:var(--serif);
  font-style:italic;
  font-size:13px;
  color:var(--gold);
  min-width:20px;
  flex-shrink:0;
}

.index button:hover{
  color:var(--ink);
  background:rgba(156,122,46,.08);
}

.index button[aria-selected="true"]{
  color:var(--ink);
  font-weight:600;
  border-left-color:var(--gold);
  background:rgba(156,122,46,.1);
}

.values-block{
  background:var(--paper-2);
  border:1px solid var(--paper-line);
  border-radius:2px;
  padding:20px;
}

.values-block .index-label{
  border-bottom-color:var(--paper-line);
}

.values-block .index button:hover,
.values-block .index button[aria-selected="true"]{
  background:rgba(255,255,255,.4);
}

/* ---------- MOBILE INDEX ---------- */

.index-mobile{
  display:none;
}

/* ---------- CONTENT ---------- */

.page{
  background:#FBF8F0;
  border:1px solid var(--paper-line);
  box-shadow:0 1px 0 rgba(0,0,0,.04);
  padding:52px clamp(24px,5vw,72px);
  min-height:420px;
}

.article{
  display:none;
}

.article.is-active{
  display:block;
  animation:reveal .35s ease;
}

@keyframes reveal{
  from{
    opacity:0;
    transform:translateY(6px);
  }
  to{
    opacity:1;
    transform:none;
  }
}

.article-num{
  font-family:var(--serif);
  font-style:italic;
  color:var(--gold);
  font-size:15px;
  margin:0 0 6px;
}

.article h2{
  font-family:var(--serif);
  font-weight:600;
  font-size:clamp(26px,3.4vw,36px);
  margin:0 0 22px;
  color:var(--ink);
  max-width:20ch;
}

.article .rule{
  width:44px;
  height:2px;
  background:var(--gold);
  margin:0 0 28px;
}

.article p{
  max-width:var(--max);
  font-size:16px;
  line-height:1.85;
  color:var(--text);
  margin:0 0 20px;
  text-align:justify;
  hyphens:auto;
}

.article ol,
.article ul{
  max-width:var(--max);
  padding-left:22px;
  margin:0 0 22px;
}

.article li{
  font-size:16px;
  line-height:1.75;
  margin-bottom:9px;
}

.article h3{
  font-family:var(--serif);
  font-weight:600;
  font-size:19px;
  margin:30px 0 12px;
  color:var(--ink-2);
}

.article h5{
  font-family:var(--sans);
  font-weight:600;
  font-size:13px;
  letter-spacing:.06em;
  color:var(--gold);
  margin:26px 0 10px;
}

/* ---------- FONDATEURS ---------- */

.founders{
  max-width:var(--max);
  padding:0;
  margin:0 0 24px;
  list-style:none;
  counter-reset:founder;
  border-top:1px solid var(--paper-line);
}

.founders li{
  counter-increment:founder;
  display:flex;
  gap:16px;
  padding:13px 0;
  border-bottom:1px solid var(--paper-line);
  font-size:15.5px;
}

.founders li::before{
  content:counter(founder);
  font-family:var(--serif);
  font-style:italic;
  color:var(--gold);
  width:28px;
  flex-shrink:0;
}

/* ---------- PROFILE PRESIDENT ---------- */

.president-card{
  max-width:var(--max);
  background:var(--paper-2);
  border:1px solid var(--paper-line);
  padding:28px;
  margin-bottom:28px;
}

.president-card .role{
  font-size:12px;
  text-transform:uppercase;
  letter-spacing:.12em;
  color:var(--gold);
  margin-bottom:10px;
}

.president-card h3{
  margin:0 0 8px;
  color:var(--ink);
  font-family:var(--serif);
  font-size:24px;
}

.president-card p{
  margin-bottom:0;
}

/* ---------- TEAMS ---------- */

.team-grid{
  max-width:var(--max);
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:14px;
  margin-bottom:28px;
}

.team-card{
  border:1px solid var(--paper-line);
  background:#F7F2E7;
  padding:18px;
}

.team-card .team-number{
  color:var(--gold);
  font-family:var(--serif);
  font-style:italic;
  font-size:14px;
  margin-bottom:6px;
}

.team-card h3{
  margin:0;
  font-family:var(--serif);
  font-size:18px;
  color:var(--ink);
}

.team-card p{
  margin:8px 0 0;
  font-size:14px;
  line-height:1.6;
}

/* ---------- MILITANTS ---------- */

.militants-box{
  max-width:var(--max);
  border-left:3px solid var(--gold);
  background:var(--paper-2);
  padding:24px;
  margin-bottom:25px;
}

.militants-box strong{
  color:var(--ink);
}

/* ---------- EXECUTIF ---------- */

.exec-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:40px;
  max-width:900px;
}

@media (max-width:760px){
  .exec-grid{
    grid-template-columns:1fr;
  }

  .team-grid{
    grid-template-columns:1fr;
  }
}

/* ---------- VALUES ---------- */

.value-mark{
  width:46px;
  height:46px;
  margin-bottom:18px;
}

/* ---------- FOOTER ---------- */

footer{
  text-align:center;
  padding:40px 24px 56px;
  color:var(--text-soft);
  font-size:13px;
  letter-spacing:.03em;
}

/* ---------- RESPONSIVE ---------- */

@media (max-width:920px){

  .charter{
    grid-template-columns:1fr;
    padding:32px 16px 72px;
  }

  .index-wrap{
    display:none;
  }

  .index-mobile{
    display:flex;
    gap:8px;
    overflow-x:auto;
    padding:4px 2px 14px;
    margin-bottom:8px;
    position:sticky;
    top:0;
    background:var(--paper);
    z-index:5;
    -webkit-overflow-scrolling:touch;
  }

  .index-mobile button{
    flex:0 0 auto;
    font-family:var(--sans);
    font-size:13px;
    white-space:nowrap;
    padding:8px 14px;
    border:1px solid var(--paper-line);
    border-radius:999px;
    background:#FBF8F0;
    color:var(--text-soft);
  }

  .index-mobile button[aria-selected="true"]{
    background:var(--ink);
    border-color:var(--ink);
    color:var(--paper);
  }

  .page{
    padding:34px 20px;
  }
}

:focus-visible{
  outline:2px solid var(--gold);
  outline-offset:2px;
}

@media (prefers-reduced-motion:reduce){

  html{
    scroll-behavior:auto;
  }

  .article.is-active{
    animation:none;
  }
}
</style>

<header class="site-head">

  <div class="seal">R</div>

  <p style="text-align:center!important;">
    Rassemblement des Chrétiens Républicains
  </p>

  <h1>Qui sommes-nous ?</h1>

  <p>
    Nous sommes un Rassemblement. Le rassemblement de toutes les personnes
    qui expriment leur ferme volonté de résister à toutes les forces négatives
    développées par ceux qui cherchent à écraser notre peuple, réduire à néant
    nos rêves, nos espoirs, notre avenir ainsi que celui des générations futures.
  </p>

  <div class="rule"></div>

</header>

<section class="charter">

  <!-- MOBILE -->

  <nav
    class="index-mobile"
    id="index-mobile"
    aria-label="Sommaire">
  </nav>

  <!-- DESKTOP -->

  <aside class="index-wrap">

<p class="index-label">Sommaire</p>

<ul class="index" id="index-main"></ul>

<div class="values-block">

  <p class="index-label">
    Nos valeurs fondamentales
  </p>

  <ul class="index" id="index-values"></ul>

</div>

  </aside>

  <!-- CONTENU -->

  <div class="page" id="page-content"></div>

</section>

<footer>
  Rassemblement des Chrétiens Républicains — R.C.R
</footer>

<script>

/* =========================================================
   CONTENU
   ========================================================= */

<?php
$aproposJs = array_map(function ($b) {
    $meta = json_decode((string) ($b['meta'] ?? ''), true) ?: [];
    return ['id' => $b['cle'], 'roman' => $meta['roman'] ?? '', 'label' => $b['titre'], 'group' => $meta['groupe'] ?? 'main', 'html' => bloc_rendu($b)];
}, blocs('apropos'));
?>
const sections = <?= json_encode($aproposJs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;


/* =========================================================
   RENDU DES ARTICLES
   ========================================================= */

const pageEl =
  document.getElementById('page-content');

const idxMain =
  document.getElementById('index-main');

const idxValues =
  document.getElementById('index-values');

const idxMobile =
  document.getElementById('index-mobile');


/* Création des articles */

sections.forEach(s => {

  const art =
    document.createElement('article');

  art.className = 'article';

  art.id = 'content-' + s.id;

  art.innerHTML = s.html;

  pageEl.appendChild(art);

});


/* =========================================================
   BOUTONS DE NAVIGATION
   ========================================================= */

function makeButton(s, mobile){

  const li =
    mobile ? null : document.createElement('li');

  const btn =
    document.createElement('button');

  btn.type = 'button';

  btn.dataset.target = s.id;

  btn.setAttribute(
    'aria-selected',
    'false'
  );

  if(mobile){

    btn.textContent = s.label;

  }else{

    btn.innerHTML =
      `<span class="num">${s.roman}</span>
       <span>${s.label}</span>`;

  }

  btn.addEventListener(
    'click',
    () => activate(s.id)
  );

  if(li){

    li.appendChild(btn);

    return li;

  }

  return btn;
}


/* Création des menus */

sections.forEach(s => {

  if(s.group === 'main'){

    idxMain.appendChild(
      makeButton(s,false)
    );

  }else{

    idxValues.appendChild(
      makeButton(s,false)
    );

  }

  idxMobile.appendChild(
    makeButton(s,true)
  );

});


/* =========================================================
   ACTIVATION D'UNE RUBRIQUE
   ========================================================= */

function activate(id){

  sections.forEach(s => {

    const isMatch =
      s.id === id;

    document
      .getElementById(
        'content-' + s.id
      )
      .classList
      .toggle(
        'is-active',
        isMatch
      );

  });


  /* Activation des boutons */

  document
    .querySelectorAll(
      'button[data-target]'
    )
    .forEach(b => {

      b.setAttribute(
        'aria-selected',
        b.dataset.target === id
          ? 'true'
          : 'false'
      );

    });


  /* Défilement mobile */

  const activeBtn =
    idxMobile.querySelector(
      `button[data-target="${id}"]`
    );

  if(activeBtn){

    activeBtn.scrollIntoView({
      behavior:'smooth',
      inline:'center',
      block:'nearest'
    });

  }

}


/* =========================================================
   PREMIÈRE RUBRIQUE
   ========================================================= */

activate(
  sections[0].id
);

</script>
