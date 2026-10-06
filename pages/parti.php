
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  /* ===== mêmes tokens que la page "Qui sommes-nous" =====
     à fusionner avec la feuille de style commune du site ===== */
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
  *{box-sizing:border-box;}
  body{
    margin:0;
    background:var(--paper);
    color:var(--text);
    font-family:var(--sans);
    -webkit-font-smoothing:antialiased;
  }

  /* ---------- en-tête léger de section ---------- */
  .leadership{
    padding:64px 24px 90px;
  }
  .leadership-head{
    max-width:1180px;
    margin:0 auto 44px;
    text-align:center;
  }
  .leadership-head .kicker{
    font-size:12.5px;
    letter-spacing:.14em;
    text-transform:uppercase;
    color:var(--gold);
    margin:0 0 12px;
  }
  .leadership-head h2{
    font-family:var(--serif);
    font-weight:600;
    font-size:clamp(28px,4vw,42px);
    color:var(--ink);
    margin:0 0 16px;
  }
  .leadership-head .rule{
    width:52px;height:2px;background:var(--gold);
    margin:0 auto;
  }

  /* ---------- liste de dossiers de direction ---------- */
  .leaders{
    max-width:1040px;
    margin:0 auto;
    list-style:none;
    padding:0;
    display:flex;
    flex-direction:column;
    gap:18px;
  }

  .leader{
    background:#FBF8F0;
    border:1px solid var(--paper-line);
  }
  .leader[open]{border-color:var(--gold);}

  .leader summary{
    list-style:none;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:18px;
    padding:22px 28px;
    font-family:var(--serif);
    user-select:none;
  }
  .leader summary::-webkit-details-marker{display:none;}

  .leader summary .role-tag{
    font-family:var(--sans);
    font-size:11.5px;
    letter-spacing:.1em;
    text-transform:uppercase;
    color:var(--gold);
    border:1px solid var(--gold);
    padding:5px 10px;
    flex-shrink:0;
  }
  .leader summary .who{
    flex:1;
    font-weight:600;
    font-size:19px;
    color:var(--ink);
  }
  .leader summary .who small{
    display:block;
    font-family:var(--sans);
    font-weight:400;
    font-size:13px;
    color:var(--text-soft);
    margin-top:3px;
  }
  .leader summary .chev{
    width:20px;height:20px;
    flex-shrink:0;
    transition:transform .25s ease;
    color:var(--gold);
  }
  .leader[open] summary .chev{transform:rotate(180deg);}

  .leader-body{
    padding:6px 28px 40px;
    border-top:1px solid var(--paper-line);
  }

  .leader-grid{
    display:grid;
    grid-template-columns:280px 1fr;
    gap:44px;
    align-items:start;
    padding-top:32px;
  }
  @media (max-width:760px){
    .leader-grid{grid-template-columns:1fr;}
  }

  .portrait-frame{
    position:relative;
    padding:10px;
  }
  .portrait-frame::before{
    content:"";
    position:absolute;
    inset:0;
    border:1px solid var(--gold);
    pointer-events:none;
  }
  .portrait-frame img{
    display:block;
    width:100%;
    aspect-ratio:4/5;
    object-fit:cover;
    filter:grayscale(.15) contrast(1.02);
  }
  .portrait-caption{
    text-align:center;
    font-size:12.5px;
    color:var(--text-soft);
    letter-spacing:.03em;
    margin-top:12px;
  }

  .leader-content h3{
    font-family:var(--serif);
    font-weight:600;
    font-size:24px;
    color:var(--ink);
    margin:0 0 18px;
  }
  .leader-content p{
    font-size:16px;
    line-height:1.85;
    color:var(--text);
    text-align:justify;
    margin:0 0 16px;
    max-width:62ch;
  }

  /* ---------- bloc citation / message ---------- */
  .proclamation{
    margin-top:30px;
    padding:30px 0 6px;
    border-top:1px solid var(--paper-line);
    position:relative;
  }
  .proclamation .mark{
    font-family:var(--serif);
    font-size:54px;
    line-height:1;
    color:var(--gold);
    opacity:.55;
    display:block;
    margin-bottom:-14px;
  }
  .proclamation h4{
    font-family:var(--sans);
    font-size:12px;
    font-weight:600;
    letter-spacing:.1em;
    text-transform:uppercase;
    color:var(--gold);
    margin:0 0 16px;
  }
  .proclamation p{
    font-family:var(--serif);
    font-style:italic;
    font-size:18px;
    line-height:1.75;
    color:var(--ink-2);
    max-width:60ch;
    text-align:left;
  }
  .proclamation .attribution{
    font-family:var(--sans);
    font-style:normal;
    font-size:13px;
    color:var(--text-soft);
    margin-top:14px;
  }

  @media (max-width:600px){
    .leader summary{padding:18px;}
    .leader-body{padding:6px 18px 30px;}
    .leader summary .who{font-size:16.5px;}
  }

  :focus-visible{outline:2px solid var(--gold); outline-offset:2px;}
</style>

<section id="direction" class="leadership">
  <div class="leadership-head">
    <p class="kicker">Rassemblement des Chrétiens Républicains</p>
    <h2>Notre Direction</h2>
    <div class="rule"></div>
  </div>

  <ul class="leaders">

    <!-- ==========================================================
         Un <details> par dirigeant. Pour ajouter un Vice-Président
         ou un autre membre de l'Exécutif, dupliquer ce bloc <li>.
    =========================================================== -->
    <li class="leader" open>
      <details open>
        <summary>
          <span class="role-tag">Président</span>
          <span class="who">
            Benjamin Jean de Dieu BONIOMA ISELONGE
            <small>Président Fondateur — Chef du Parti</small>
          </span>
          <svg class="chev" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </summary>

        <div class="leader-body">
          <div class="leader-grid">

            <div>
              <div class="portrait-frame">
                <img loading="lazy" decoding="async" src="./images/president-fond.jpg" alt="Portrait du Président Benjamin Jean de Dieu BONIOMA ISELONGE">
              </div>
              <p class="portrait-caption">Président Fondateur du R.C.R</p>
            </div>

            <div class="leader-content">
              <h3>Président du Parti</h3>
              <p>Initiateur-Visionnaire du Rassemblement des Chrétiens Républicains, il est, en dignité et en notoriété, le premier et plus grand serviteur du mouvement.</p>
              <p>Monsieur Benjamin Jean de Dieu BONIOMA ISELONGE porte le titre de Président Fondateur. Il est le garant de la vision, de la discipline, et le dépositaire général de la doctrine chrétienne-républicaine.</p>
              <p>Il est le Chef du Parti et veille à l'orientation stratégique ainsi qu'à l'application des valeurs fondamentales du RCR.</p>

              <div class="proclamation">
                <span class="mark">&ldquo;</span>
                <h4>Message du Chef du Parti</h4>
                <p>Nous constatons malheureusement que les Congolais adoptent souvent une attitude d'employé ou d'étranger, au lieu d'agir comme propriétaires et responsables du Congo. Le Congo est une immense richesse : ses revenus doivent bénéficier à ses véritables propriétaires, le peuple congolais.</p>
                <p class="attribution">— Benjamin Jean de Dieu BONIOMA ISELONGE, Président Fondateur</p>
              </div>
            </div>

          </div>
        </div>
      </details>
    </li>

  </ul>
</section>

