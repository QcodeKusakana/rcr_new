<style>

/* =========================================================
   NOS ÉVÉNEMENTS — mêmes tokens que le reste du site
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

#why-us.why-us{
    background:var(--paper) !important;
    padding:70px 0 80px;
    font-family:var(--sans);
    color:var(--text);
    
}

/* ---------- titre ---------- */
.events-head{text-align:center; margin-bottom:52px;}
.events-head .kicker{
    display:inline-block;
    font-size:12.5px;
    letter-spacing:.14em;
    text-transform:uppercase;
    color:var(--gold);
    border:1px solid var(--gold);
    padding:6px 16px;
    margin-bottom:18px;
}
.events-head h2{
    font-family:var(--serif);
    font-weight:600;
    font-size:clamp(28px,4vw,40px);
    color:var(--ink);
    margin:0 0 14px;
    text-transform:none;
}
.events-head .rule{width:52px; height:2px; background:var(--gold); margin:0 auto 18px;}
.events-head p{color:var(--text-soft); max-width:46ch; margin:0 auto; font-size:15.5px;}

/* ---------- grille ---------- */
.events-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:28px;
}
@media (max-width:992px){ .events-grid{grid-template-columns:repeat(2,1fr);} }
@media (max-width:640px){ .events-grid{grid-template-columns:1fr;} }

.event-card{
    background:#FBF8F0;
    border:1px solid var(--paper-line);
    display:flex;
    flex-direction:column;
    height:100%;
    transition:border-color .15s ease;
}
.event-card:hover{border-color:var(--gold);}

.event-media{position:relative;}
.event-media img{
    display:block;
    width:100%;
    height:230px;
    object-fit:cover;
}
.event-tag{
    position:absolute;
    top:14px; left:14px;
    background:var(--ink);
    color:var(--paper);
    font-size:11.5px;
    font-weight:600;
    letter-spacing:.06em;
    text-transform:uppercase;
    padding:6px 12px;
}

.event-body{
    padding:22px 22px 24px;
    display:flex;
    flex-direction:column;
    flex:1;
}
.event-date{
    display:flex;
    align-items:center;
    gap:6px;
    font-size:12.5px;
    color:var(--text-soft);
    margin:0 0 12px;
}
.event-date svg{color:var(--gold); flex-shrink:0;}

.event-title{
    font-family:var(--serif);
    font-weight:600;
    font-size:18.5px;
    line-height:1.4;
    color:var(--ink);
    margin:0 0 20px;
}
.event-title a{color:inherit; text-decoration:none;}
.event-title a:hover{color:var(--gold);}

.event-more{
    margin-top:auto;
    display:inline-flex;
    align-items:center;
    gap:8px;
    align-self:flex-start;
    font-family:var(--sans);
    font-weight:600;
    font-size:14px;
    color:var(--ink);
    border:1px solid var(--ink);
    padding:9px 20px;
    text-decoration:none;
    transition:background .15s ease, color .15s ease;
}
.event-more:hover{background:var(--ink); color:var(--paper);}
.event-more svg{transition:transform .15s ease;}
.event-more:hover svg{transform:translateX(3px);}

/* ---------- pagination ---------- */
.events-pagination{
    display:flex;
    justify-content:center;
    align-items:center;
    flex-wrap:wrap;
    gap:8px;
    margin-top:56px;
}
.page-btn{
    min-width:38px;
    height:38px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    font-family:var(--serif);
    font-size:14.5px;
    color:var(--ink);
    background:#FBF8F0;
    border:1px solid var(--paper-line);
    text-decoration:none;
    padding:0 10px;
    transition:background .15s ease, color .15s ease, border-color .15s ease;
}
.page-btn:hover{border-color:var(--gold);}
.page-btn.is-current{
    background:var(--ink);
    border-color:var(--ink);
    color:var(--paper);
    font-weight:600;
}
.page-btn.is-disabled{
    color:#B3A683;
    pointer-events:none;
    opacity:.6;
}
a.event-tag{text-decoration:none;}
a.event-tag:hover{color:#fff;opacity:.9;}
</style>



<section id="why-us" class="why-us">

    <div class="container">

        <!-- TITLE -->
        <div class="events-head">

            <span class="kicker">R.C.R</span>

            <h2><?= $actuCategorie !== null ? e($actuCategorie) : e(reglage('evenements_titre', 'Nos événements')) ?></h2>
            <?php if ($actuCategorie !== null): ?><p style="margin-bottom:10px"><a href="?pages=publication" style="color:var(--gold)">← Toutes les actualités</a></p><?php endif; ?>

            <div class="rule"></div>

            <p><?= e(reglage('evenements_intro', 'Découvrez les dernières activités, conférences et événements organisés par le RCR.')) ?></p>

        </div>

        <!-- EVENTS -->
        <div class="events-grid">

            <?php if (empty($actu['items'])): ?>
                <p class="text-center" style="grid-column:1/-1;color:var(--text-soft)">Aucune actualité publiée pour le moment.</p>
            <?php endif; ?>
            <?php foreach ($actu['items'] as $ar): ?>

                <div class="event-card">

                    <div class="event-media">
                        <img loading="lazy" decoding="async" src="./media/images_activ/<?= e(rawurlencode((string) $ar['photo'])) ?>" alt="<?= e(actu_extrait($ar['titre'], 80)) ?>">
                        <a class="event-tag" href="?pages=publication&amp;cat=<?= e(rawurlencode((string) $ar['categorie'])) ?>"><?= e($ar['categorie']) ?></a>
                    </div>

                    <div class="event-body">

                        <div class="event-date">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="1.5" stroke="currentColor" stroke-width="1.6"/><path d="M3 9.5h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                            Posté le <?= e(actu_date($ar['date_pub'], 'd/m/Y à H:i')) ?>
                        </div>

                        <h3 class="event-title">
                            <a href="<?= e(actu_url($ar)) ?>"><?= e(actu_extrait($ar['titre'], 70)) ?></a>
                        </h3>

                        <a href="<?= e(actu_url($ar)) ?>" class="event-more">
                            Lire la suite
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

        <!-- PAGINATION -->
        <?php if ($nbPage > 1):
            $qsCat = $actuCategorie !== null ? '&amp;cat=' . e(rawurlencode($actuCategorie)) : ''; ?>
        <nav class="events-pagination" aria-label="Pagination">
            <a class="page-btn <?= $current <= 1 ? 'is-disabled' : '' ?>" href="?pages=publication<?= $qsCat ?>&amp;pag=<?= max(1, $current - 1) ?>" aria-label="Page précédente">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <?php for ($i = 1; $i <= $nbPage; $i++): ?>
                <a href="?pages=publication<?= $qsCat ?>&amp;pag=<?= $i ?>" class="page-btn <?= $i === $current ? 'is-current' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <a class="page-btn <?= $current >= $nbPage ? 'is-disabled' : '' ?>" href="?pages=publication<?= $qsCat ?>&amp;pag=<?= min($nbPage, $current + 1) ?>" aria-label="Page suivante">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </nav>
        <?php endif; ?>

    </div>

</section>