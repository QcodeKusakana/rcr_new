<?php
/**
 * Page « Soutenir le RCR » (don ponctuel ou régulier).
 * Le formulaire envoie vers adhere/don_paiement.php (CSRF, validation serveur, FlexPay) — inchangé.
 * Textes : réglage « soutenir_titre » (Admin → Contenus). Messages d'erreur renvoyés par ?don_error=…
 */
$donErreurs = [
    'session'   => "Votre session a expiré, merci de réessayer.",
    'montant'   => "Montant invalide : saisissez un montant supérieur à 0 (100 000 USD maximum).",
    'telephone' => "Numéro de téléphone Mobile Money invalide.",
    'email'     => "L'adresse e-mail saisie n'est pas valide.",
    'champs'    => "Merci de renseigner tous les champs obligatoires.",
    'curl'      => "Impossible de contacter FlexPay pour le moment. Merci de réessayer.",
    'reponse'   => "Réponse inattendue de FlexPay. Merci de réessayer.",
    'flexpay'   => "FlexPay a refusé la demande de paiement. Vérifiez le numéro saisi.",
];
$donErreurCode = (string) ($_GET['don_error'] ?? '');
$provincesDon  = $bdd->query('SELECT id_p, nom_p FROM provinces ORDER BY nom_p')->fetchAll(PDO::FETCH_ASSOC);
$titreSoutien  = reglage('soutenir_titre', 'Soutenir le RCR');
$montantsRapides = [5, 10, 25, 50, 100];
?>
<style>
:root{--ink:#1B2A44;--ink-2:#233355;--paper:#F1E9D8;--paper-2:#E7DBBF;--paper-line:#CBBB92;--gold:#9C7A2E;--gold-2:#C9A24A;--red:#7D2330;--text:#241F1A;--text-soft:#5B5346;--serif:'Fraunces',Georgia,serif;--sans:'IBM Plex Sans',system-ui,sans-serif}
.sout{font-family:var(--sans);color:var(--text)}
.sout *{box-sizing:border-box}

/* bandeau */
.sout-hero{background:var(--ink);color:var(--paper);padding:54px 0 96px;position:relative;overflow:hidden}
.sout-hero::after{content:"";position:absolute;inset:auto 0 0 0;height:4px;background:linear-gradient(90deg,var(--gold),var(--gold-2),var(--gold))}
.sout-hero::before{content:"";position:absolute;right:-120px;top:-120px;width:380px;height:380px;border-radius:50%;border:60px solid rgba(201,162,74,.08)}
.sout-crumb{display:flex;gap:8px;list-style:none;margin:0 0 18px;padding:0;font-size:13px;color:#C8B98F}
.sout-crumb li+li::before{content:"/";margin-right:8px;color:#6E7A93}
.sout-crumb a{color:#E4DAC2;text-decoration:none}.sout-crumb a:hover{text-decoration:underline}
.sout-crumb .on{color:var(--gold-2);font-weight:600}
.sout-hero h1{font-family:var(--serif);font-weight:600;font-size:clamp(30px,4.6vw,46px);line-height:1.1;margin:0 0 12px;max-width:16em}
.sout-hero p{margin:0;max-width:40em;color:#D9CFB5;font-size:16.5px;line-height:1.65}

/* mise en page */
.sout-body{background:var(--paper);padding:0 0 80px}
.sout-grid{display:grid;grid-template-columns:minmax(0,5fr) minmax(0,7fr);gap:28px;margin-top:-60px;align-items:start;position:relative}
@media(max-width:900px){.sout-grid{grid-template-columns:1fr;margin-top:-48px}}

/* colonne d'information */
.sout-aside{background:var(--ink-2);color:var(--paper);padding:30px 28px;border-radius:14px;box-shadow:0 18px 40px rgba(27,42,68,.22);position:sticky;top:90px}
@media(max-width:900px){.sout-aside{position:static;order:2}.sout-card{order:1}}
.sout-aside h2{font-family:var(--serif);font-weight:600;font-size:22px;margin:0 0 6px;color:#fff}
.sout-aside>p{margin:0 0 20px;color:#CFC6AE;font-size:14.5px;line-height:1.6}
.sout-pts{list-style:none;margin:0;padding:0;display:grid;gap:16px}
.sout-pts li{display:flex;gap:14px;align-items:flex-start}
.sout-ico{flex:0 0 40px;height:40px;border-radius:10px;background:rgba(201,162,74,.16);color:var(--gold-2);display:grid;place-items:center}
.sout-pts b{display:block;font-size:14.5px;color:#fff;margin-bottom:2px}
.sout-pts span{font-size:13.5px;color:#CFC6AE;line-height:1.5}
.sout-aside hr{border:0;border-top:1px solid rgba(255,255,255,.12);margin:22px 0 16px}
.sout-pay{display:flex;flex-wrap:wrap;gap:8px}
.sout-chip{font-size:12px;font-weight:600;letter-spacing:.03em;padding:6px 11px;border-radius:99px;background:rgba(255,255,255,.1);color:#EFE7D2}

/* carte formulaire */
.sout-card{background:#FFFDF8;border:1px solid var(--paper-line);border-radius:14px;padding:clamp(22px,3.4vw,40px);box-shadow:0 18px 40px rgba(27,42,68,.12)}
.sout-card h2{font-family:var(--serif);font-weight:600;font-size:clamp(22px,2.8vw,28px);color:var(--ink);margin:0 0 4px}
.sout-card .lead{color:var(--text-soft);margin:0 0 22px;font-size:15px}
.note{display:flex;gap:10px;padding:13px 15px;border:1px solid var(--red);background:rgba(125,35,48,.06);color:var(--red);border-radius:10px;font-size:14.5px;line-height:1.55;margin-bottom:20px}
.note svg{flex-shrink:0;margin-top:2px}

.step{display:flex;align-items:center;gap:10px;margin:26px 0 14px;font-weight:600;color:var(--ink);font-size:15.5px}
.step:first-of-type{margin-top:6px}
.step i{font-style:normal;width:26px;height:26px;border-radius:50%;background:var(--ink);color:var(--paper);display:grid;place-items:center;font-size:13px}

.f-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px 16px}
.f-grid .full{grid-column:1/-1}
@media(max-width:560px){.f-grid{grid-template-columns:1fr}}
.fld label{display:block;font-weight:600;font-size:13px;color:var(--ink);margin-bottom:5px}
.fld label em{color:var(--red);font-style:normal}
.fld input[type=text],.fld input[type=email],.fld input[type=tel],.fld select{width:100%;font:inherit;font-size:15px;color:var(--text);background:#fff;border:1px solid var(--paper-line);border-radius:9px;padding:11px 13px;transition:border-color .15s,box-shadow .15s}
.fld input:focus,.fld select:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(156,122,46,.18)}
.fld small{display:block;margin-top:6px;color:var(--text-soft);font-size:12.5px;line-height:1.5}

/* choix segmentés */
.seg{display:grid;grid-template-columns:1fr 1fr;gap:10px}
@media(max-width:480px){.seg{grid-template-columns:1fr}}
.seg label{position:relative;cursor:pointer;margin:0}
.seg input{position:absolute;opacity:0;inset:0;cursor:pointer}
.seg span{display:flex;align-items:center;gap:10px;padding:13px 14px;border:1.5px solid var(--paper-line);border-radius:10px;background:#fff;font-weight:600;font-size:14.5px;color:var(--ink);transition:all .15s}
.seg span small{display:block;font-weight:400;color:var(--text-soft);font-size:12px}
.seg input:checked+span{border-color:var(--ink);background:var(--ink);color:var(--paper)}
.seg input:checked+span small{color:#CFC6AE}
.seg input:focus-visible+span{outline:3px solid rgba(156,122,46,.5);outline-offset:2px}

/* montants */
.amounts{display:flex;flex-wrap:wrap;gap:9px;margin-bottom:12px}
.amt{font:inherit;font-weight:600;font-size:15px;color:var(--ink);background:var(--paper-2);border:1.5px solid var(--paper-line);border-radius:10px;padding:10px 18px;cursor:pointer;transition:all .15s}
.amt:hover{border-color:var(--gold)}
.amt.on{background:var(--ink);border-color:var(--ink);color:var(--paper)}
.amt-input{position:relative}
.amt-input input{padding-right:64px!important;font-size:20px!important;font-weight:600;text-align:left}
.amt-input b{position:absolute;right:14px;top:50%;transform:translateY(-50%);color:var(--text-soft);font-size:14px;pointer-events:none}
.fld.err input{border-color:var(--red)}
.msg-err{display:none;color:var(--red);font-size:13px;margin-top:6px}
.fld.err .msg-err{display:block}

.don-submit{width:100%;margin-top:26px;display:flex;align-items:center;justify-content:center;gap:10px;font:inherit;font-weight:600;font-size:16.5px;color:#fff;background:linear-gradient(180deg,var(--gold-2),var(--gold));border:none;border-radius:11px;padding:16px 20px;cursor:pointer;box-shadow:0 8px 18px rgba(156,122,46,.32);transition:transform .12s,box-shadow .12s}
.don-submit:hover{transform:translateY(-1px);box-shadow:0 12px 22px rgba(156,122,46,.38)}
.don-submit:disabled{opacity:.7;cursor:wait;transform:none}
.sout-secure{display:flex;align-items:center;justify-content:center;gap:7px;text-align:center;font-size:12.5px;color:var(--text-soft);margin:14px 0 0}
.sout-member{text-align:center;margin-top:18px;font-size:14px;color:var(--text-soft)}
.sout-member a{color:var(--ink);font-weight:600}
</style>

<main class="sout">
    <section class="sout-hero">
        <div class="container">
            <ol class="sout-crumb">
                <li><a href="index.php">Accueil</a></li>
                <li class="on">Soutenir</li>
            </ol>
            <h1><?= e($titreSoutien) ?></h1>
            <p>Votre don finance l'action du parti sur le terrain. Choisissez librement le montant et la fréquence de votre soutien.</p>
        </div>
    </section>

    <section class="sout-body">
        <div class="container">
            <div class="sout-grid">

                <aside class="sout-aside">
                    <h2>Défendez vos idées</h2>
                    <p>Chaque contribution, petite ou grande, compte pour faire vivre le projet du RCR.</p>
                    <ul class="sout-pts">
                        <li><span class="sout-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v6c0 4.5 3.2 7.8 8 9 4.8-1.2 8-4.5 8-9V6l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg></span><div><b>Paiement sécurisé</b><span>Traité par FlexPay. Aucune donnée bancaire n'est conservée sur ce site.</span></div></li>
                        <li><span class="sout-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><div><b>Don ponctuel ou régulier</b><span>Un seul don, ou un soutien mensuel, trimestriel, semestriel ou annuel.</span></div></li>
                        <li><span class="sout-ico"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span><div><b>Confirmation automatique</b><span>Votre don est enregistré dès que le paiement est confirmé par FlexPay.</span></div></li>
                    </ul>
                    <hr>
                    <div class="sout-pay"><span class="sout-chip">Mobile Money</span><span class="sout-chip">Visa</span><span class="sout-chip">MasterCard</span></div>
                </aside>

                <div class="sout-card">
                    <h2>Faire un don</h2>
                    <p class="lead">Quelques informations suffisent. Les champs marqués d'une étoile sont obligatoires.</p>

                    <?php if ($donErreurCode !== '' && isset($donErreurs[$donErreurCode])): ?>
                        <div class="note" role="alert">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span><?= e($donErreurs[$donErreurCode]) ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="./adhere/don_paiement.php" id="donForm" novalidate>
                        <?= csrf_field() ?>

                        <div class="step"><i>1</i> Votre don</div>
                        <div class="fld" style="margin-bottom:16px">
                            <div class="seg">
                                <label><input type="radio" name="type_don" value="ponctuel" checked><span><div>Ponctuel<small>Un seul don</small></div></span></label>
                                <label><input type="radio" name="type_don" value="regulier"><span><div>Régulier<small>Rappel à chaque échéance</small></div></span></label>
                            </div>
                        </div>
                        <div class="fld" id="blocFrequence" style="display:none;margin-bottom:16px">
                            <label for="frequence">Fréquence</label>
                            <select name="frequence" id="frequence">
                                <option value="mensuel">Mensuel</option>
                                <option value="trimestriel">Trimestriel</option>
                                <option value="semestriel">Semestriel</option>
                                <option value="annuel">Annuel</option>
                            </select>
                            <small>Le premier don est réglé maintenant ; les suivants vous sont rappelés à chaque échéance.</small>
                        </div>
                        <div class="fld" id="fldMontant">
                            <label for="montant">Montant (USD) <em>*</em></label>
                            <div class="amounts" role="group" aria-label="Montants suggérés">
                                <?php foreach ($montantsRapides as $m): ?>
                                    <button type="button" class="amt" data-v="<?= (int) $m ?>"><?= (int) $m ?> $</button>
                                <?php endforeach; ?>
                            </div>
                            <div class="amt-input">
                                <input type="text" name="montant" id="montant" inputmode="decimal" autocomplete="off" placeholder="Autre montant" required>
                                <b>USD</b>
                            </div>
                            <div class="msg-err" id="errMontant">Saisissez un montant valide, supérieur à 0 (100 000 USD maximum).</div>
                        </div>

                        <div class="step"><i>2</i> Vos informations</div>
                        <div class="f-grid">
                            <div class="fld"><label for="nom_donateur">Nom <em>*</em></label><input type="text" id="nom_donateur" name="nom_donateur" maxlength="100" required autocomplete="family-name"></div>
                            <div class="fld"><label for="postnom">Post-nom</label><input type="text" id="postnom" name="postnom" maxlength="100"></div>
                            <div class="fld full"><label for="prenom">Prénom <em>*</em></label><input type="text" id="prenom" name="prenom" maxlength="100" required autocomplete="given-name"></div>
                            <div class="fld"><label for="email">E-mail <em>*</em></label><input type="email" id="email" name="email" maxlength="150" placeholder="vous@exemple.com" required autocomplete="email"></div>
                            <div class="fld"><label for="telephone">Téléphone <em>*</em></label><input type="tel" id="telephone" name="telephone" placeholder="0812345678" inputmode="tel" required autocomplete="tel"></div>
                            <div class="fld"><label for="id_province">Province</label>
                                <select name="id_province" id="id_province"><option value="">Sélectionner</option>
                                <?php foreach ($provincesDon as $pv): ?><option value="<?= (int) $pv['id_p'] ?>"><?= e($pv['nom_p']) ?></option><?php endforeach; ?>
                                </select></div>
                            <div class="fld"><label for="ville">Ville</label><input type="text" id="ville" name="ville" maxlength="120"></div>
                            <div class="fld full"><label for="adresse">Adresse</label><input type="text" id="adresse" name="adresse" maxlength="255"></div>
                            <div class="fld full"><label for="code_membre">Code membre (si vous êtes membre)</label><input type="text" id="code_membre" name="code_membre" maxlength="30" placeholder="Facultatif"></div>
                        </div>

                        <div class="step"><i>3</i> Moyen de paiement</div>
                        <div class="fld">
                            <div class="seg">
                                <label><input type="radio" name="canal" value="mobile_money" checked><span><div>Mobile Money<small>Confirmation sur votre téléphone</small></div></span></label>
                                <label><input type="radio" name="canal" value="carte"><span><div>Carte bancaire<small>Visa / MasterCard</small></div></span></label>
                            </div>
                            <small id="donMmNote">Vous recevrez une demande de confirmation sur le téléphone indiqué ci-dessus.</small>
                        </div>

                        <button type="submit" class="don-submit" id="donBtn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8Z"/></svg>
                            Faire mon don maintenant
                        </button>
                        <p class="sout-secure"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg> Paiement sécurisé par FlexPay</p>
                    </form>

                    <p class="sout-member">Déjà membre ? <a href="./adhere/adhesion.php">Adhérer</a> ou accéder à votre espace depuis le menu.</p>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
(function(){
    var f = document.getElementById('donForm'); if (!f) return;
    var m = document.getElementById('montant'), fld = document.getElementById('fldMontant'), btns = f.querySelectorAll('.amt');
    function sync(){
        var reg = f.querySelector('input[name=type_don]:checked').value === 'regulier';
        document.getElementById('blocFrequence').style.display = reg ? 'block' : 'none';
        document.getElementById('donMmNote').style.display = f.querySelector('input[name=canal]:checked').value === 'mobile_money' ? 'block' : 'none';
    }
    function marquer(){
        var v = m.value.replace(',', '.').trim();
        btns.forEach(function(b){ b.classList.toggle('on', b.dataset.v === v); });
    }
    f.addEventListener('change', sync); sync();
    btns.forEach(function(b){ b.addEventListener('click', function(){ m.value = b.dataset.v; fld.classList.remove('err'); marquer(); }); });
    m.addEventListener('input', function(){ fld.classList.remove('err'); marquer(); });
    f.addEventListener('submit', function(e){
        var v = m.value.replace(',', '.').trim(), n = Number(v);
        if (!/^\d+(\.\d{1,2})?$/.test(v) || !(n > 0) || n > 100000) {
            e.preventDefault(); fld.classList.add('err'); m.focus(); m.scrollIntoView({behavior:'smooth', block:'center'}); return;
        }
        if (!f.checkValidity()) { e.preventDefault(); f.reportValidity(); return; }
        var b = document.getElementById('donBtn'); b.disabled = true; b.lastChild.textContent = ' Redirection vers le paiement…';
    });
})();
</script>
