<?php /** Vue de connexion admin (traitement : admin/functions/login.funct.php). */ ?>
<div class="adm-auth">
    <div class="adm-auth-card">
        <div class="adm-auth-head">
            <img src="../media/lo/logorcr.png" alt="Logo RCR" width="64" height="64">
            <h1>Administration</h1>
            <p>Rassemblement des Chrétiens Républicains</p>
        </div>
        <div class="adm-auth-body">
            <?php if ($message !== ''): ?>
                <div class="alert alert-<?= e($type) ?>" role="alert" data-no-toast><?= e($message) ?></div>
            <?php endif; ?>
            <form method="post" action="?pages=login" autocomplete="on">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label" for="adm_pseudo">Pseudo</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person" aria-hidden="true"></i></span>
                        <input type="text" id="adm_pseudo" name="pseudo" class="form-control" required autofocus autocomplete="username" maxlength="50" value="<?= e($_POST['pseudo'] ?? '') ?>">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="adm_mdp">Mot de passe</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock" aria-hidden="true"></i></span>
                        <input type="password" id="adm_mdp" name="password" class="form-control" required autocomplete="current-password">
                        <button class="btn btn-outline-secondary" type="button" onclick="var i=document.getElementById('adm_mdp');i.type=i.type==='password'?'text':'password'" aria-label="Afficher ou masquer le mot de passe"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <button type="submit" name="login" value="1" class="btn btn-primary w-100 py-2"><i class="bi bi-box-arrow-in-right me-1"></i> Se connecter</button>
            </form>
        </div>
        <div class="adm-auth-foot">
            <a href="?pages=register">Demander un compte administrateur</a> · <a href="../index.php">Retour au site</a>
        </div>
    </div>
</div>
