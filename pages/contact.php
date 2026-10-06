<style>

/* =========================================================
   CONTACT — mêmes tokens que le reste du site
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

#contact.contact{
    background:var(--paper);
    padding:70px 0 80px;
    font-family:var(--sans);
    color:var(--text);
}

.contact .section-title{text-align:center; margin-bottom:44px;}
.contact .section-title h2{
    font-family:var(--serif);
    font-weight:600;
    font-size:clamp(26px,3.6vw,36px);
    color:var(--ink);
    margin:0 0 14px;
}
.contact .section-title h2::after{
    content:"";
    display:block;
    width:52px; height:2px;
    background:var(--gold);
    margin:16px auto 0;
}
.contact .section-title p{
    color:var(--text-soft);
    max-width:56ch;
    margin:0 auto;
    font-size:15px;
}

.contact-info{
    background:#FBF8F0;
    border:1px solid var(--paper-line);
    box-shadow:none;
    padding:40px clamp(20px,3vw,44px) !important;
}

/* ---------- coordonnées ---------- */
.contact-address{margin-bottom:30px;}
.contact-address i,
.icon-box .icon i{
    color:var(--gold) !important;
    font-size:26px;
}
.contact-address h3,
.icon-box h3{
    font-family:var(--serif);
    font-weight:600;
    font-size:17px;
    color:var(--ink);
    margin:10px 0 8px;
}
.contact-address address{
    font-style:normal;
    color:var(--text-soft);
    font-size:14.5px;
    line-height:1.7;
}
.icon-box{margin-top:26px;}
.icon-box p{margin:0;}
.icon-box a{
    color:var(--text-soft);
    text-decoration:none;
    font-size:14.5px;
}
.icon-box a:hover{color:var(--gold);}

/* ---------- formulaire ---------- */
.contact .form h3{
    font-family:var(--serif);
    font-weight:600;
    color:var(--ink);
    font-size:19px;
}
.contact .form hr{border-color:var(--paper-line); opacity:1;}

.contact .form-control{
    border-radius:0;
    border:1px solid var(--paper-line);
    background:#fff;
    height:48px;
}
.contact .form-control:focus{
    border-color:var(--gold);
    box-shadow:0 0 0 3px rgba(156,122,46,.15);
}
.contact textarea.form-control{height:auto;}

.contact .alert{border-radius:0; border-width:1px; border-left-width:4px;}
.contact .alert-success{
    background:rgba(63,91,62,.08);
    border-color:var(--green);
    color:var(--green);
}
.contact .alert-success span{color:var(--green); font-weight:600;}
.contact .alert-danger{
    background:rgba(125,35,48,.07);
    border-color:var(--red);
    color:var(--red);
}

.contact .btn-danger{
    border-radius:0;
    background:var(--ink);
    border-color:var(--ink);
    color:var(--paper);
    font-weight:600;
    padding:13px 34px;
}
.contact .btn-danger:hover{
    background:var(--ink-2);
    border-color:var(--ink-2);
}

@media(max-width:768px){
    .contact-info{padding:26px !important;}
}

</style>

<!-- ======= Contact Section ======= -->
<section id="contact" class="contact">
    <div class="container">

        <div class="section-title">
            <h2><?= e(reglage('contact_titre', 'Contactez-nous')) ?></h2>
            <p><?= e(reglage('contact_intro', 'Retrouvez ici nos coordonnées et un formulaire pour nous écrire directement.')) ?></p>
        </div>

        <div class="row contact-info">
            <div class="col-lg-6">
                <div class="col-md-12">
                    <div class="contact-address">
                        <i class="bi bi-geo-alt"></i>
                        <h3>Adresse</h3>
                        <address><?= nl2br(e(reglage('contact_adresse_complete', reglage('contact_adresse', 'Kinshasa')))) ?></address>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-12 icon-box">
                        <div class="icon"><i class="bi bi-phone"></i></div>
                        <h3>Service Membres</h3>
                        <?php foreach (array_filter([reglage('contact_telephone'), reglage('contact_telephone2'), reglage('contact_telephone3')]) as $tel): ?>
                            <p><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $tel)) ?>"><?= e($tel) ?></a></p>
                        <?php endforeach; ?>
                    </div>
                    <div class="col-lg-6 col-md-12 icon-box">
                        <div class="icon"><i class="bi bi-envelope"></i></div>
                        <h3>Email</h3>
                        <?php $ce = reglage('contact_email', 'contact@rcr.cd'); ?><p><a href="mailto:<?= e($ce) ?>"><?= e($ce) ?></a></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="form">
                    <h3>Contacter-nous par email</h3>
                    <hr/>

                    <?php if(!empty($sms)): ?>
                        <div class="alert alert-success"><?= e($sms) ?></div>
                    <?php endif; ?>

                    <?php if(!empty($errors)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($errors) ?></div>
                    <?php endif; ?>

                    <form method="post" action="?pages=contact#contact">
                        <?= csrf_field() ?>
                        <div style="position:absolute;left:-9999px" aria-hidden="true"><input type="text" name="site_web" tabindex="-1" autocomplete="off"></div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <input type="text" name="name" class="form-control" placeholder="Votre nom" aria-label="Votre nom" maxlength="120" required value="<?= e($contactSaisie['name']) ?>">
                            </div>
                            <div class="col-md-6 form-group mt-3 mt-md-0">
                                <input type="email" class="form-control" name="mail" placeholder="Votre email" aria-label="Votre email" maxlength="150" required value="<?= e($contactSaisie['mail']) ?>">
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <input type="text" class="form-control" name="objet" placeholder="Objet" aria-label="Objet" maxlength="200" required value="<?= e($contactSaisie['objet']) ?>">
                        </div>
                        <div class="form-group mt-3">
                            <textarea class="form-control" name="message" rows="5" placeholder="Message" aria-label="Message" maxlength="5000" required><?= e($contactSaisie['message']) ?></textarea>
                        </div>
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-danger" name="send">Envoyer maintenant</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section><!-- End Contact Section -->
