<style>

/* =========================
    LOGIN — ESPACE MEMBRE
========================= */

#login-section{
    background: linear-gradient(180deg, #f8f9fa 0%, #f1f3f5 100%);
    min-height: 70vh;
    display: flex;
    align-items: center;
}

.login-card{
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(17,24,39,0.12);
}

.login-card-header{
    background: linear-gradient(135deg, #111827 0%, #1f2937 60%, #7f1d1d 140%);
    padding: 40px 32px 32px;
    text-align: center;
    color: #fff;
}

.login-card-header .login-logo{
    width: 76px;
    height: 76px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid rgba(255,255,255,0.25);
    background: #fff;
    padding: 6px;
    margin-bottom: 16px;
}

.login-card-header h2{
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 6px;
}

.login-card-header p{
    color: rgba(255,255,255,0.7);
    font-size: 14px;
    margin: 0;
}

.login-card-body{
    padding: 32px;
    background: #fff;
}

.login-input-group .input-group-text{
    background: #fff;
    border-right: 0;
}

.login-input-group .form-control{
    border-left: 0;
}

.login-input-group .form-control:focus{
    box-shadow: none;
    border-color: #ced4da;
}

.login-input-group:focus-within{
    box-shadow: 0 0 0 3px rgba(220,53,69,0.12);
    border-radius: 10px;
}

#toggle-code-visibility{
    cursor: pointer;
    background: #fff;
}

.btn-login-membre{
    background: #FF0000;
    border: 1px solid #FF0000;
    color: #fff;
    font-weight: 700;
    border-radius: 12px;
    padding: 12px;
    transition: 0.25s ease;
}

.btn-login-membre:hover{
    background: #fff;
    color: #FF0000;
}

.login-footer-links{
    text-align: center;
    font-size: 14px;
    margin-top: 22px;
}

.login-footer-links a{
    color: #495057;
    text-decoration: none;
    font-weight: 600;
}

.login-footer-links a:hover{
    color: #FF0000;
}

.login-footer-links .divider{
    color: #ced4da;
    margin: 0 8px;
}

</style>

<br/>

<!-- ======= Breadcrumbs ======= -->

<!-- ======= LOGIN SECTION ======= -->
<section id="login-section" class="py-5">
    <div class="container">

        <div class="row justify-content-center">

            <div class="col-lg-5 col-md-7">

                <div class="card login-card border-0">

                    <!-- HEADER -->
                    <div class="login-card-header">

                        <img loading="lazy" decoding="async" src="./media/lo/logorcr.png" class="login-logo" alt="Logo RCR">

                        <h2>Espace Membre</h2>
                        <p>Connectez-vous avec votre code d'adhésion</p>

                    </div>

                    <!-- BODY -->
                    <div class="login-card-body">

                        <!-- ERREUR -->
                        <?php if (!empty($erreur)) { ?>
                            <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 shadow-sm mb-4">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <span><?= e($erreur) ?></span>
                            </div>
                        <?php } ?>

                        <!-- FORMULAIRE -->
                        <form action="" method="post" autocomplete="off">

                            <?= csrf_field() ?>

                            <div class="mb-3">
                        <label class="form-label fw-semibold">Code d'adhésion ou e-mail</label>
                        <div class="input-group login-input-group">
                            <span class="input-group-text"><i class="bi bi-person-badge text-danger"></i></span>
                            <input type="text" id="identifiant" class="form-control form-control-lg" name="identifiant"
                                   placeholder="Ex : M0000120185K ou vous@exemple.com" autocomplete="username" autofocus required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Mot de passe</label>
                        <div class="input-group login-input-group">
                            <span class="input-group-text"><i class="bi bi-lock text-danger"></i></span>
                            <input type="password" id="codes" class="form-control form-control-lg" name="mot_de_passe" autocomplete="current-password" required>
                            <span class="input-group-text" id="toggle-code-visibility" title="Afficher / masquer"><i class="bi bi-eye" id="toggle-code-icon"></i></span>
                        </div>
                        <div class="form-text">Le mot de passe a été choisi lors de votre adhésion. Votre code d'adhésion figure sur votre carte et votre reçu. <a href="?pages=mdp_oublie">Mot de passe oublié ?</a></div>
                    </div>

                            <div class="d-grid">
                                <button type="submit"
                                        class="btn btn-login-membre btn-lg"
                                        name="login">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Se connecter
                                </button>
                            </div>

                        </form>

                        <div class="login-footer-links">
                            <a href="./adhere/adhesion.php">Pas encore membre ?</a>
                            <span class="divider">|</span>
                            <a href="?pages=contact">Code introuvable ?</a>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
</section>

<script>
(function(){
    var input = document.getElementById('codes');
    var toggle = document.getElementById('toggle-code-visibility');
    var icon = document.getElementById('toggle-code-icon');

    if (!input || !toggle || !icon) {
        return;
    }

    toggle.addEventListener('click', function(){
        var visible = input.getAttribute('type') === 'text';
        input.setAttribute('type', visible ? 'password' : 'text');
        icon.classList.toggle('bi-eye', visible);
        icon.classList.toggle('bi-eye-slash', !visible);
    });
})();
</script>
