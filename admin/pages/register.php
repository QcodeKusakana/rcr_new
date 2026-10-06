<?php
$message = "";
$type = "";

if (isset($_POST['compte']) && !csrf_verify()) {

    $message = "Session expirée, merci de recharger la page et réessayer.";
    $type = "danger";

} elseif (isset($_POST['compte'])) {

    $pseudo   = mb_substr(trim((string) ($_POST['pseudo'] ?? '')), 0, 50);
    $mail     = mb_substr(trim((string) ($_POST['mail'] ?? '')), 0, 120);
    $password = (string) ($_POST['password'] ?? '');
    $password2 = (string) ($_POST['password2'] ?? '');

    // Vérification champs
    if (empty($pseudo) || empty($mail) || empty($password) || empty($password2)) {

        $message = "Tous les champs sont obligatoires.";
        $type = "danger";

    }
    // Vérification email
    elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {

        $message = "Adresse email invalide.";
        $type = "danger";

    }
    // Vérification mot de passe
    elseif ($password !== $password2) {

        $message = "Les mots de passe ne correspondent pas.";
        $type = "danger";

    }
    // Vérification longueur mot de passe
    elseif (strlen($password) < 10) {

        $message = "Le mot de passe doit contenir au moins 10 caractères.";
        $type = "danger";

    }
    // Vérification email existant
    elseif (pseudo_taken($pseudo) > 0) {

        $message = "Ce pseudo est déjà utilisé.";
        $type = "warning";

    }
    elseif (mail_taken($mail) > 0) {

        $message = "Cette adresse email existe déjà.";
        $type = "warning";

    }
    else {

        if (insert_register($pseudo, $mail, $password)) {

            $message = "Inscription effectuée avec succès.";
            $type = "success";

        } else {

            $message = "Erreur lors de l'inscription.";
            $type = "danger";
        }
    }
}
?>

<div class="container-fluid py-5" style="background: linear-gradient(135deg,#0f172a,#1e293b);">

    <div class="row justify-content-center">

        <div class="col-lg-6 col-md-8 col-sm-12">

            <!-- CARD -->
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">

                <!-- HEADER -->
                <div class="card-header text-white text-center py-4"
                     style="background: linear-gradient(135deg,#1f2937,#111827);">

                    <h2 class="fw-bold mb-1">
                        <i class="bi bi-person-plus-fill me-2"></i>
                        Création de compte
                    </h2>

                    <small class="opacity-75">
                        Administrateur du système
                    </small>

                </div>

                <!-- BODY -->
                <div class="card-body p-4 p-md-5 bg-light">

                    <!-- MESSAGE -->
                    <?php if (!empty($message)) : ?>
                        <div class="alert alert-<?= e($type) ?> shadow-sm rounded-3">
                            <?= e($message) ?>
                        </div>
                    <?php endif; ?>

                    <!-- FORM -->
                    <form method="POST">

                        <?= csrf_field() ?>

                        <!-- PSEUDO -->
                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Pseudo
                            </label>

                            <div class="input-group">

                                <span class="input-group-text bg-white">
                                    <i class="bi bi-person text-primary"></i>
                                </span>

                                <input type="text"
                                       name="pseudo"
                                       class="form-control form-control-lg"
                                       placeholder="Entrez votre pseudo"
                                       required>

                            </div>

                        </div>

                        <!-- EMAIL -->
                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Email
                            </label>

                            <div class="input-group">

                                <span class="input-group-text bg-white">
                                    <i class="bi bi-envelope text-success"></i>
                                </span>

                                <input type="email"
                                       name="mail"
                                       class="form-control form-control-lg"
                                       placeholder="exemple@mail.com"
                                       required>

                            </div>

                        </div>

                        <!-- PASSWORD -->
                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Mot de passe
                            </label>

                            <div class="input-group">

                                <span class="input-group-text bg-white">
                                    <i class="bi bi-lock text-danger"></i>
                                </span>

                                <input type="password"
                                       name="password"
                                       class="form-control form-control-lg"
                                       placeholder="••••••••"
                                       required>

                            </div>

                        </div>

                        <!-- CONFIRM -->
                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Confirmation
                            </label>

                            <div class="input-group">

                                <span class="input-group-text bg-white">
                                    <i class="bi bi-shield-lock text-warning"></i>
                                </span>

                                <input type="password"
                                       name="password2"
                                       class="form-control form-control-lg"
                                       placeholder="Confirmez le mot de passe"
                                       required>

                            </div>

                        </div>

                        <!-- BUTTON -->
                        <div class="d-grid">

                            <button type="submit"
                                    name="compte"
                                    class="btn btn-primary btn-lg rounded-3 shadow-sm">

                                <i class="bi bi-check-circle me-2"></i>
                                Créer le compte

                            </button>

                        </div>

                    </form>

                    <!-- LOGIN LINK -->
                    <?php if (!isset($_SESSION['id_adm'])) : ?>

                        <div class="text-center mt-4">

                            <small class="text-muted">
                                Vous avez déjà un compte ?
                            </small>

                            <br>

                            <a href="?pages=login"
                               class="fw-semibold text-decoration-none text-primary">

                                Se connecter

                            </a>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</div>