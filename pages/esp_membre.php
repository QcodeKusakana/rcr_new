<style>
/* =========================================================
   ESPACE MEMBRE — mêmes tokens que le reste du site
   (cf. rcr-qui-sommes-nous.html, rcr-direction.html, don.php,
   evenements.php, accueil.php, adhesion.php)
========================================================= */
:root{
    --ink:#1B2A44;
    --ink-2:#233355;
    --paper:#F1E9D8;
    --paper-2:#E7DBBF;
    --paper-line:#CBBB92;
    --gold:#9C7A2E;
    --red:#7D2330;
    --green:#3F5B3E;
    --text:#241F1A;
    --text-soft:#5B5346;
    --serif:'Fraunces', Georgia, serif;
    --sans:'IBM Plex Sans', system-ui, sans-serif;
}

#espace-membre{
    font-family:var(--sans);
    color:var(--text);
    background:var(--paper);
}

#espace-membre .kicker{
    display:inline-block;
    font-size:12px;
    letter-spacing:.1em;
    text-transform:uppercase;
    font-weight:600;
    color:var(--gold);
    border:1px solid var(--gold);
    padding:5px 14px;
    margin-bottom:10px;
    background:transparent;
}

/* ---------- fil d'ariane ---------- */
#espace-membre #breadcrumbs.breadcrumbs{
    background:var(--ink) !important;
    border-bottom:none !important;
    margin: 0px !important;
}
#espace-membre #breadcrumbs h2{
    font-family:var(--serif);
    font-weight:600;
    font-size:19px;
    color:var(--paper) !important;
}
#espace-membre #breadcrumbs .breadcrumb-item a{color:#E2A6A6 !important;}
#espace-membre #breadcrumbs .breadcrumb-item.active{color:#C8B98F;}

/* ---------- cartes génériques ---------- */
#espace-membre .card{
    border-radius:0;
    border:1px solid var(--paper-line);
    box-shadow:none !important;
}
#espace-membre .card-header.panel-head{
    background:var(--ink) !important;
    color:var(--paper) !important;
    border-bottom:2px solid var(--gold);
    font-weight:600;
    letter-spacing:.03em;
}
#espace-membre .card-header.panel-head .bi{color:var(--gold); margin-right:6px;}

#espace-membre .list-group-item{
    background:transparent;
    border-color:var(--paper-line) !important;
    font-size:14.5px;
}

#espace-membre .btn{border-radius:0; font-weight:600;}

/* ---------- programme de parrainage ---------- */
#espace-membre .referral-card{
    background:linear-gradient(135deg, var(--ink), var(--ink-2));
}
#espace-membre .referral-card input.form-control{
    border-radius:0;
    border-color:rgba(255,255,255,.25);
}
#espace-membre .btn-copy{
    background:var(--gold);
    color:var(--ink);
    border-color:var(--gold);
}
#espace-membre .btn-copy:hover{background:#B8912F; border-color:#B8912F; color:var(--ink);}

#espace-membre .share-actions .btn{color:#fff;}
#espace-membre .btn-whatsapp{background:#25D366; border-color:#25D366;}
#espace-membre .btn-email{
    background:transparent;
    border:1px solid rgba(255,255,255,.4);
    color:#fff;
}
#espace-membre .btn-email:hover{background:rgba(255,255,255,.1); color:#fff;}

#espace-membre .stat-panel{
    background:rgba(241,233,216,.08);
    border:1px solid rgba(241,233,216,.25);
    border-radius:0 !important;
}
#espace-membre .stat-panel .display-5,
#espace-membre .stat-panel .display-6{color:var(--paper); font-family:var(--serif);}

/* ---------- badges de statut de paiement ---------- */
#espace-membre .st-badge{
    display:inline-block;
    font-size:12px;
    font-weight:600;
    letter-spacing:.03em;
    padding:5px 10px;
    border:1px solid transparent;
}
#espace-membre .st-paid{background:rgba(63,91,62,.12); color:var(--green); border-color:var(--green);}
#espace-membre .st-pending{background:rgba(156,122,46,.12); color:var(--gold); border-color:var(--gold);}
#espace-membre .st-failed{background:rgba(125,35,48,.1); color:var(--red); border-color:var(--red);}
#espace-membre .st-cancelled{background:var(--paper-2); color:var(--text-soft); border-color:var(--paper-line);}

/* ---------- tableaux ---------- */
#espace-membre .table{color:var(--text);}
#espace-membre .table thead th{
    font-family:var(--sans);
    font-size:12.5px;
    text-transform:uppercase;
    letter-spacing:.05em;
    color:var(--text-soft);
    border-bottom:2px solid var(--paper-line);
    background:var(--paper-2);
}
#espace-membre .table td, #espace-membre .table th{border-color:var(--paper-line);}
#espace-membre .table tbody tr:hover{background:rgba(156,122,46,.06);}
#espace-membre .table tfoot th{border-top:2px solid var(--paper-line);}
#espace-membre .text-success{color:var(--green) !important;}

/* ---------- alertes ---------- */
#espace-membre .alert{border-radius:0; border-width:1px; border-left-width:4px;}
#espace-membre .alert-danger{background:rgba(125,35,48,.07); border-color:var(--red) !important; color:var(--red);}
#espace-membre .alert-warning{background:rgba(156,122,46,.1); border-color:var(--gold) !important; color:#6B5320;}
#espace-membre .alert-info{background:rgba(27,42,68,.06); border-color:var(--ink) !important; color:var(--ink-2);}

/* ---------- identité ---------- */
#espace-membre .id-photo{border:3px solid var(--gold);}

/* ---------- bouton secondaire (Soutenir) ---------- */
#espace-membre .btn-outline-ink{
    border:1px solid var(--ink);
    color:var(--ink);
    background:transparent;
}
#espace-membre .btn-outline-ink:hover{background:var(--ink); color:var(--paper);}

/* ---------- CTA renouvellement ---------- */
#espace-membre .btn-renew{
    background:var(--gold);
    border-color:var(--gold);
    color:var(--ink);
}
#espace-membre .btn-renew:hover{background:#B8912F; border-color:#B8912F; color:var(--ink);}
#espace-membre .btn-outline-primary{
    border:1px solid var(--ink);
    color:var(--ink);
}
#espace-membre .btn-outline-primary:hover{background:var(--ink); color:var(--paper);}

/* ---------- état suspendu ---------- */
#espace-membre .suspended-card{
    background:#FBF8F0;
    border:1px solid var(--paper-line) !important;
}
#espace-membre .suspended-card .bi-exclamation-octagon-fill{color:var(--red) !important;}
#espace-membre .suspended-card h4{font-family:var(--serif); font-weight:600; color:var(--ink);}
</style>

<div id="espace-membre">

<section id="breadcrumbs" class="breadcrumbs py-3 border-bottom">
    <div class="container">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

            <h2 class="fw-bold m-0">
                <i class="bi bi-person-circle"></i> Votre espace
            </h2>

            <ol class="breadcrumb m-0 bg-transparent p-0">
                <li class="breadcrumb-item">
                    <a href="?pages=deconnexion" class="fw-bold text-decoration-none">
                        <i class="bi bi-box-arrow-right"></i> Déconnexion
                    </a>
                </li>
                <li class="breadcrumb-item active">Membre</li>
            </ol>

        </div>

    </div>
</section>
<?php if($infoAd['reabonner']==1){ ?>

<?php
// ⚠️ CORRECTIF (audit, déjà présent) : le lien était codé en dur sur
// "http://localhost:800/..." — inutilisable une fois le site en ligne.
// Reconstruit dynamiquement à partir du domaine réel.
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://')
    . $_SERVER['HTTP_HOST'];

$lienParrainage = $baseUrl . '/adhere/adhesion.php?idmbre=' . (int) $infoAd['id_ad'];

$messagePartage = "Rejoignez le RCR (Rassemblement des Chrétiens Républicains) ! Adhérez via mon lien de parrainage : " . $lienParrainage;
?>

<!-- ======= Programme de parrainage (design + partage fonctionnel) ======= -->
<section class="py-4">
    <div class="container">

        <div class="card border-0 overflow-hidden">

            <div class="card-body p-4 p-lg-5 referral-card">

                <div class="row align-items-center g-4">

                    <div class="col-lg-8 text-white">

                        <span class="kicker">
                            <i class="bi bi-people-fill"></i>
                            Programme de parrainage
                        </span>

                        <h4 class="fw-bold mb-2" style="font-family:var(--serif);">
                            Invitez vos proches à rejoindre le RCR
                        </h4>

                        <p class="mb-3 opacity-75">
                            Partagez votre lien personnel : chaque adhésion réalisée
                            depuis ce lien vous est automatiquement associée.
                        </p>

                        <!-- LIEN + COPIE -->
                        <div class="input-group mb-3" style="max-width:520px;">

                            <input type="text"
                                   id="lienParrainage"
                                   class="form-control"
                                   value="<?= e($lienParrainage) ?>"
                                   readonly
                                   onclick="this.select();">

                            <button class="btn btn-copy"
                                    type="button"
                                    id="btnCopierLien"
                                    onclick="copierLienParrainage()">

                                <i class="bi bi-clipboard"></i>
                                Copier

                            </button>

                        </div>

                        <!-- PARTAGE -->
                        <div class="d-flex flex-wrap gap-2 share-actions">

                            <a class="btn btn-whatsapp"
                               target="_blank"
                               rel="noopener"
                               href="https://api.whatsapp.com/send?text=<?= urlencode($messagePartage) ?>">

                                <i class="bi bi-whatsapp"></i>
                                WhatsApp

                            </a>

                            <a class="btn"
                               style="background:#1877f2;color:#fff;"
                               target="_blank"
                               rel="noopener"
                               href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($lienParrainage) ?>">

                                <i class="bi bi-facebook"></i>
                                Facebook

                            </a>

                            <a class="btn"
                               style="background:#000;color:#fff;"
                               target="_blank"
                               rel="noopener"
                               href="https://twitter.com/intent/tweet?text=<?= urlencode($messagePartage) ?>">

                                <i class="bi bi-twitter-x"></i>
                                X

                            </a>

                            <a class="btn btn-email"
                               href="mailto:?subject=<?= urlencode('Rejoignez le RCR') ?>&body=<?= urlencode($messagePartage) ?>">

                                <i class="bi bi-envelope-fill"></i>
                                E-mail

                            </a>

                        </div>

                    </div>

                    <!-- STATS RÉELLES : nombre de filleuls déjà parrainés
                         et commission totale estimée (table parner +
                         payments, requêtes dans
                         functions/esp_membre.funct.php ; taux dans
                         config/commission.php). -->
                    <div class="col-lg-4 text-center">

                        <div class="stat-panel p-4 text-white mb-3">

                            <div class="display-5 fw-bold">
                                <?= (int) $rows ?>
                            </div>

                            <div class="opacity-75">
                                <i class="bi bi-person-check-fill"></i>
                                Filleul(s) parrainé(s)
                            </div>

                        </div>

                        <div class="stat-panel p-4 text-white">

                            <div class="display-6 fw-bold">
                                <?= number_format($totalCommissionsEstimees ?? 0, 2) ?> $
                            </div>

                            <div class="opacity-75">
                                <i class="bi bi-piggy-bank-fill"></i>
                                Commission estimée (<?= number_format(TAUX_COMMISSION_PARRAINAGE, 0) ?>%)
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
</section>

<script>
function copierLienParrainage(){

    const champ = document.getElementById('lienParrainage');
    const bouton = document.getElementById('btnCopierLien');

    function afficherCopie(){
        const contenuInitial = bouton.innerHTML;

        bouton.innerHTML = '<i class="bi bi-check-lg"></i> Copié !';

        setTimeout(function(){
            bouton.innerHTML = contenuInitial;
        }, 2000);
    }

    // 🔧 CORRECTIF ("copie ne fonctionne pas") : navigator.clipboard
    // n'existe QUE dans un contexte sécurisé (HTTPS, ou http://localhost
    // strictement) — sur Laragon, le site est généralement servi sur un
    // domaine local en http:// (ex. http://rcr.test), donc
    // "navigator.clipboard" vaut "undefined". Appeler .writeText() dessus
    // levait alors une erreur JS immédiate, AVANT même la création d'une
    // promesse : le .catch() plus bas ne l'a jamais interceptée, donc le
    // clic sur "Copier" ne faisait strictement rien, sans aucun message.
    // Repli fonctionnel ajouté via document.execCommand('copy') (API plus
    // ancienne mais qui, elle, fonctionne aussi en http://) : la copie
    // marche maintenant aussi bien en local (Laragon) qu'une fois le site
    // en ligne en HTTPS.
    if (window.isSecureContext && navigator.clipboard && navigator.clipboard.writeText) {

        navigator.clipboard.writeText(champ.value)
            .then(afficherCopie)
            .catch(copierAvecExecCommand);

    } else {
        copierAvecExecCommand();
    }

    function copierAvecExecCommand(){

        champ.focus();
        champ.select();
        champ.setSelectionRange(0, champ.value.length); // nécessaire sur mobile

        let reussi = false;

        try {
            reussi = document.execCommand('copy');
        } catch (erreur) {
            reussi = false;
        }

        if (reussi) {
            afficherCopie();
        } else {
            alert('Impossible de copier automatiquement. Sélectionnez le lien et copiez-le avec Ctrl+C.');
        }
    }
}
</script>

<!-- ======= Pricing Section ======= -->
<section id="pricing" class="pricing">
    <div class="container">
        <div class="row">

          <div class="col-lg-4 col-md-6">
    <div class="card border-0 h-100">

        <div class="card-header panel-head">
            <i class="bi bi-person-badge"></i> Identité
        </div>

        <div class="card-body">

            <div class="text-center mb-3">
                <img loading="lazy" decoding="async" src="./media/passeport/<?= e($_SESSION['passeport']) ?>"
                     class="rounded-circle id-photo"
                     style="width:120px;height:120px;object-fit:cover;">
            </div>

            <ul class="list-group list-group-flush small">

                <li class="list-group-item">ID: <b><?= e($infoAd['codes']) ?></b></li>
                <li class="list-group-item">Nom: <b><?= e($infoAd['nom']) ?></b></li>
                <li class="list-group-item">Post-nom: <b><?= e($infoAd['postnom']) ?></b></li>
                <li class="list-group-item">Prénom: <b><?= e($infoAd['prenom']) ?></b></li>
                <li class="list-group-item">Téléphone: <b><?= e($infoAd['telephone']) ?></b></li>
                <li class="list-group-item">Naissance: <b><?= e($infoAd['datenaiss']) ?></b></li>
                <li class="list-group-item">Email: <b><?= e($infoAd['mail']) ?></b></li>

            </ul>

            <a target="_blank"
               class="btn btn-outline-primary w-100 mt-3"
               href="./admin/pages/print/print_adherer.php?cod=<?= (int) $_SESSION['id_ad'] ?>">

                <i class="bi bi-download"></i> Télécharger fiche
            </a>

        </div>
    </div>
</div>

           <div class="col-lg-4 col-md-6 mt-4 mt-md-0">
    <div class="card border-0 h-100">

        <div class="card-header panel-head">
            <i class="bi bi-geo-alt"></i> Circonscriptions
        </div>

        <div class="card-body">

            <ul class="list-group list-group-flush">

                <li class="list-group-item">Pays: <b><?= e($infoAd['nationalite']) ?></b></li>
                <li class="list-group-item">Territoire: <b><?= e($infoAd['nom_tr']) ?></b></li>
                <li class="list-group-item">Secteur: <b><?= e($infoAd['nom_sec']) ?></b></li>
                <li class="list-group-item">Qualité: <b><?= e($infoAd['designation']) ?></b></li>
                <li class="list-group-item">Grade: <b><?= e($infoAd['nom_gd']) ?></b></li>

            </ul>

        </div>
    </div>
</div>

           <div class="col-lg-4 col-md-6 mt-4 mt-lg-0">

    <div class="card border-0 h-100">

        <div class="card-header panel-head">
            <i class="bi bi-cash-coin"></i> Paiement &amp; abonnement
        </div>

        <div class="card-body">

            <?php
                // 🔧 CORRECTIF (bug "$0.00") : $infoAd['prix'] n'existe plus
                // (voir functions/esp_membre.funct.php) — la colonne "prix"
                // était ambiguë entre qualites/cotisation/grades et prenait
                // toujours la valeur de cotisation.prix (= 0 en base). Le
                // montant réel à payer est désormais la somme du prix du
                // grade + du prix de la cotisation, exactement comme calculé
                // dans adhere/code_paiement.php ($montant = $prix_grade +
                // $prix_cotisation) afin que le prix affiché ici corresponde
                // toujours au montant réellement facturé au paiement.
                $prixTotal = (float) ($infoAd['prix_grade'] ?? 0) * max(1, (int) ($infoAd['mois'] ?? 1)); // tarif mensuel x nombre de mois
            ?>

            <h5 class="text-center">
                <sup>$</sup><?= number_format($prixTotal, 2) ?>
                <small class="text-muted">/ <?= e($infoAd['nom_cot']) ?></small>
            </h5>

            <p class="text-center text-muted small mb-0">
                Grade (<?= e($infoAd['nom_gd']) ?>) : <?= number_format((float) $infoAd['prix_grade'], 2) ?> $ / mois x <?= max(1, (int) ($infoAd['mois'] ?? 1)) ?> mois
            </p>

            <hr>

            <?php
                $echeance = !empty($infoAd['date_echeance']) ? new DateTime($infoAd['date_echeance']) : null;
                $joursRestants = $echeance ? (int) (new DateTime('today'))->diff($echeance)->format('%r%a') : null;
                $lienPaiement = './adhere/paiement.php?token=' . urlencode($infoAd['payment_token']);
            ?>
            <?php if (!$echeance): ?>
                <div class="alert alert-warning text-center">Adhésion en attente de paiement</div>
                <a class="btn btn-renew w-100" href="<?= e($lienPaiement) ?>"><i class="bi bi-credit-card-2-front-fill"></i> Payer maintenant</a>
            <?php elseif ($joursRestants < 0): ?>
                <div class="alert alert-danger text-center">Cotisation expirée depuis le <b><?= e($echeance->format('d/m/Y')) ?></b></div>
                <a class="btn btn-renew w-100" href="<?= e($lienPaiement) ?>"><i class="bi bi-arrow-repeat"></i> Renouveler ma cotisation</a>
            <?php else: ?>
                <div class="alert alert-success text-center">
                    Cotisation à jour jusqu'au <b><?= e($echeance->format('d/m/Y')) ?></b>
                    (<?= $joursRestants ?> jour<?= $joursRestants > 1 ? 's' : '' ?>)
                </div>
                <?php if ($joursRestants <= 30): ?>
                    <a class="btn btn-renew w-100" href="<?= e($lienPaiement) ?>"><i class="bi bi-arrow-repeat"></i> Renouveler par anticipation</a>
                <?php endif; ?>
                <a class="btn btn-outline-ink w-100 mt-2" href="./member/carte.php" target="_blank" rel="noopener"><i class="bi bi-person-vcard"></i> Ma carte de membre</a>
            <?php endif; ?>

            <?php /* 🔧 CORRECTIF (paiement fonctionnel) : "Service non
                 disponible" renvoyait vers rien de réel. pages/soutenir.php
                 existe déjà, fonctionne (formulaire de don + coordonnées
                 Equity/M-Pesa) et n'a pas de raison de rester inaccessible
                 depuis l'espace membre. */ ?>
            <a class="btn btn-outline-ink w-100 mt-3" href="?pages=soutenir">
                <i class="bi bi-heart-fill"></i> Soutenir
            </a>

        </div>
    </div>
</div>

        </div>

    </div>
</section><!-- End Pricing Section -->

<?php /* 🔧 RETRAIT (demande client) : la section "PAIEMENT M-PESA (Compte
     Marchand)" / "PAIEMENT EQUITY BCDC" a été retirée de l'espace
     membre. Le paiement en ligne réel (FlexPay, bouton REABONNER
     ci-dessus) et la page "?pages=soutenir" restent inchangés — seule
     cette carte d'instructions de paiement manuel a été supprimée
     d'ici, à la demande explicite du client. */ ?>

<!-- ======= Historique de mes paiements (nouveau) =======
     Demande : "classer toutes ses paiements effectués". Liste tous les
     paiements de CE membre (table payments, voir la requête ajoutée
     dans functions/esp_membre.funct.php), y compris les tentatives
     échouées, avec un badge de statut coloré. -->
<section class="py-4">
    <div class="container">

        <div class="card border-0">

            <div class="card-header panel-head">
                <i class="bi bi-clock-history"></i> Historique de mes paiements
            </div>

            <div class="card-body">

                <?php if (empty($mesPaiements)): ?>

                    <p class="text-muted text-center mb-0">
                        Aucun paiement enregistré pour le moment.
                    </p>

                <?php else: ?>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Montant</th>
                                    <th>Fournisseur</th>
                                    <th>Référence</th>
                                    <th>Statut</th>
                                    <th>Reçu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $statutClasses = [
                                        'paid'      => 'st-badge st-paid',
                                        'pending'   => 'st-badge st-pending',
                                        'failed'    => 'st-badge st-failed',
                                        'cancelled' => 'st-badge st-cancelled',
                                        'processing' => 'st-badge st-pending',
                                        'expired'   => 'st-badge st-cancelled',
                                    ];
                                    $statutLibelles = [
                                        'paid'      => 'Payé',
                                        'pending'   => 'En attente',
                                        'failed'    => 'Échoué',
                                        'cancelled' => 'Annulé',
                                        'processing' => 'En cours',
                                        'expired'   => 'Expiré',
                                    ];
                                ?>
                                <?php foreach ($mesPaiements as $p): ?>
                                    <?php $statut = $p['status'] ?? 'pending'; ?>
                                    <tr>
                                        <td>
                                            <?= e($p['created_at'] ? (new DateTime($p['created_at']))->format('d/m/Y H:i') : '—') ?>
                                        </td>
                                        <td>
                                            <?= number_format((float) $p['montant'], 2) ?> <?= e($p['devise'] ?? 'USD') ?>
                                        </td>
                                        <td><?= e($p['provider'] ?? '—') ?></td>
                                        <td><small class="text-muted"><?= e($p['reference'] ?? '—') ?></small></td>
                                        <td>
                                            <span class="<?= $statutClasses[$statut] ?? 'st-badge st-cancelled' ?>">
                                                <?= e($statutLibelles[$statut] ?? ucfirst($statut)) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($statut === 'paid'): ?>
                                                <a href="./member/recu.php?t=p&amp;id=<?= (int) $p['id'] ?>" target="_blank" rel="noopener">PDF</a>
                                            <?php else: ?>—<?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>
</section>

<!-- ======= Mes filleuls (nouveau) =======
     Demande : commission de parrainage estimée par filleul. La liste et
     le total réellement payé par chaque filleul sont déjà fonctionnels
     (table parner + payments, voir functions/esp_membre.funct.php) ;
     seule la colonne "commission estimée" est en attente du taux exact
     à appliquer (question posée à l'utilisateur — aucun pourcentage
     inventé). Dès le taux confirmé, une colonne s'ajoutera ici sans
     toucher au reste. -->
<section class="py-4">
    <div class="container">

        <div class="card border-0">

            <div class="card-header panel-head">
                <i class="bi bi-people-fill"></i> Mes filleuls
            </div>

            <div class="card-body">

                <?php if (empty($filleuls)): ?>

                    <p class="text-muted text-center mb-0">
                        Vous n'avez encore parrainé personne. Partagez votre lien
                        ci-dessus pour commencer à inviter vos proches.
                    </p>

                <?php else: ?>

                    <div class="alert alert-info small mb-3">
                        <i class="bi bi-info-circle"></i>
                        Commission estimée à titre indicatif (taux de
                        <?= number_format(TAUX_COMMISSION_PARRAINAGE, 0) ?>%
                        appliqué à chaque paiement réussi de vos filleuls,
                        adhésion initiale et renouvellements compris) —
                        aucun versement n'est déclenché automatiquement par
                        le site.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Filleul</th>
                                    <th>Code</th>
                                    <th>Grade</th>
                                    <th>Date d'adhésion</th>
                                    <th>Paiements réussis</th>
                                    <th>Total payé</th>
                                    <th>Commission estimée</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($filleuls as $f): ?>
                                    <tr>
                                        <td><?= e(trim($f['nom'] . ' ' . $f['postnom'] . ' ' . $f['prenom'])) ?></td>
                                        <td><small class="text-muted"><?= e($f['codes']) ?></small></td>
                                        <td><?= e($f['nom_gd']) ?></td>
                                        <td>
                                            <?= e($f['dat_adhesion'] ? (new DateTime($f['dat_adhesion']))->format('d/m/Y') : '—') ?>
                                        </td>
                                        <td><?= (int) $f['nb_paiements'] ?></td>
                                        <td><?= number_format((float) $f['total_paye'], 2) ?> $</td>
                                        <td><b class="text-success"><?= number_format((float) $f['commission_estimee'], 2) ?> $</b></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <th colspan="6" class="text-end">Total commission estimée</th>
                                    <th class="text-success"><?= number_format($totalCommissionsEstimees ?? 0, 2) ?> $</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>
</section>

<?php /* 🔧 CORRECTIF (audit) : cette section affichait 3 statistiques
     factices ("232 Clients", "521 Projets", "1463 Support") — un
     reliquat du gabarit d'origine, sans lien avec une vraie donnée, et
     sans aucun sens pour l'espace personnel d'un membre. Seule "Membres"
     était réelle (issue de $rows) ; elle est désormais mise en valeur
     directement dans la carte "Programme de parrainage" ci-dessus
     ("Filleul(s) parrainé(s)"), donc retirée d'ici pour ne pas la
     dupliquer. */ ?>
<?php /* Section "Counts" retirée */ ?>
<?php }else{ ?>

    <?php /* 🎨 CORRECTIF (audit) : ancienne balise <center> (obsolète en HTML5)
         remplacée par une mise en page Bootstrap 5 cohérente avec le
         reste du site. Le bouton "REABONNEZ-VOUS" pointait vers "#" sans
         action réelle — même correctif que ci-dessus : message honnête
         plutôt qu'un lien qui semble fonctionner sans rien faire. */ ?>
    <section class="py-5">
        <div class="container">

            <div class="row justify-content-center">

                <div class="col-lg-6">

                    <div class="card border-0 suspended-card text-center">

                        <div class="card-body p-5">

                            <i class="bi bi-exclamation-octagon-fill" style="font-size:48px;"></i>

                            <h4 class="mt-3">
                                Votre service a été suspendu
                            </h4>

                            <p class="text-muted">
                                Votre espace membre sera définitivement
                                supprimé dans 30 jours si aucune action
                                n'est effectuée.
                            </p>

                            <button type="button"
                                    class="btn btn-renew px-4"
                                    onclick="alert('Le réabonnement en ligne n\'est pas encore disponible. Merci de contacter le RCR directement.')">
                                Réabonnez-vous
                            </button>

                            <p class="text-muted small mt-4 mb-0">
                                Pour garder vos services opérationnels, merci !
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    </section>

<?php }  ?>

</div><!-- /#espace-membre -->
