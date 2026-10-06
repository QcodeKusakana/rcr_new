<style>
/* =========================================================
   VÉRIFICATION D'AUTHENTICITÉ — mêmes tokens que le reste du
   site (--ink / --paper / --gold / --serif / --sans), voir
   pages/soutenir.php, rcr-qui-sommes-nous.html, rcr-direction.html
========================================================= */
:root{
    --ink:#1B2A44;
    --ink-2:#233355;
    --paper:#F1E9D8;
    --paper-2:#E7DBBF;
    --paper-line:#CBBB92;
    --gold:#9C7A2E;
    --red:#7D2330;
    --green:#2F6B3A;
    --text:#241F1A;
    --text-soft:#5B5346;
    --serif:'Fraunces', Georgia, serif;
    --sans:'IBM Plex Sans', system-ui, sans-serif;
}

#verif-wrap{
    font-family: var(--sans);
    color: var(--text);
    background: var(--paper);
    min-height: 70vh;
    padding: 60px 20px;
    display: flex;
    justify-content: center;
}

.verif-card{
    width: 100%;
    max-width: 620px;
    background: #fff;
    border: 1px solid var(--paper-line);
    border-radius: 18px;
    padding: 40px;
    box-shadow: 0 12px 30px rgba(27,42,68,.08);
}

.verif-title{
    font-family: var(--serif);
    color: var(--ink);
    font-size: 26px;
    font-weight: 700;
    margin-bottom: 6px;
}

.verif-sub{
    color: var(--text-soft);
    margin-bottom: 28px;
}

.verif-badge{
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 999px;
    font-weight: 700;
    margin-bottom: 24px;
}

.verif-badge.ok{
    background: rgba(47,107,58,.12);
    color: var(--green);
}

.verif-badge.ko{
    background: rgba(125,35,48,.12);
    color: var(--red);
}

.verif-row{
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px dashed var(--paper-line);
}

.verif-row span:first-child{
    color: var(--text-soft);
}

.verif-row span:last-child{
    font-weight: 600;
    color: var(--ink);
    text-align: right;
}

.verif-form input[type="text"]{
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--paper-line);
    border-radius: 10px;
    font-size: 16px;
    margin-bottom: 14px;
}

.verif-form button{
    width: 100%;
    padding: 12px 16px;
    border: none;
    border-radius: 10px;
    background: var(--ink);
    color: #fff;
    font-weight: 700;
    cursor: pointer;
}

.verif-form button:hover{
    background: var(--ink-2);
}
</style>

<div id="verif-wrap">
    <div class="verif-card">

        <div class="verif-title">Vérification d'authenticité</div>
        <div class="verif-sub">
            Confirmez qu'une fiche d'adhésion RCR est authentique à
            partir de son code d'adhérent.
        </div>

        <form class="verif-form" method="get" action="index.php">
            <input type="hidden" name="pages" value="verifier">
            <input type="text" name="code" placeholder="Code d'adhérent (ex : RCR-2026-000123)"
                   value="<?= htmlspecialchars($codeVerif) ?>" required>
            <button type="submit">Vérifier</button>
        </form>

        <?php if ($codeVerif !== ''): ?>

            <div style="margin-top:28px;">

            <?php if (!$resultat): ?>

                <div class="verif-badge ko">✕ Code introuvable</div>
                <p style="color:var(--text-soft)">
                    Aucune fiche d'adhésion ne correspond à ce code. Si
                    vous pensez qu'il s'agit d'une erreur, contactez le
                    RCR directement.
                </p>

            <?php elseif (!$resultat['est_valide']): ?>

                <div class="verif-badge ko">✕ Adhésion non confirmée</div>
                <p style="color:var(--text-soft)">
                    Ce code existe mais son paiement d'adhésion n'a pas
                    encore été confirmé — cette fiche ne doit pas être
                    considérée comme valide pour le moment.
                </p>

            <?php else: ?>

                <div class="verif-badge ok">✓ Fiche authentique</div>

                <div class="verif-row">
                    <span>Code d'adhérent</span>
                    <span><?= htmlspecialchars($resultat['codes']) ?></span>
                </div>
                <div class="verif-row">
                    <span>Nom</span>
                    <span><?= htmlspecialchars($resultat['nom'] . ' ' . $resultat['postnom'] . ' ' . $resultat['prenom']) ?></span>
                </div>
                <div class="verif-row">
                    <span>Qualité</span>
                    <span><?= htmlspecialchars($resultat['designation']) ?></span>
                </div>
                <div class="verif-row">
                    <span>Grade</span>
                    <span><?= htmlspecialchars($resultat['nom_gd']) ?></span>
                </div>
                <div class="verif-row">
                    <span>Province</span>
                    <span><?= htmlspecialchars($resultat['nom_p']) ?></span>
                </div>
                <div class="verif-row">
                    <span>Date d'adhésion</span>
                    <span><?= htmlspecialchars(date('d/m/Y', strtotime($resultat['dat_adhesion']))) ?></span>
                </div>

            <?php endif; ?>

            </div>

        <?php endif; ?>

    </div>
</div>
