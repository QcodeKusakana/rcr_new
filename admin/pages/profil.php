<?php
/**
 * Administration → Mon compte : changement de SON mot de passe.
 * Accessible à tout administrateur connecté (aucune permission requise : la page ne touche que le compte
 * de la session). Garanties : jeton CSRF (vérifié par admin_guard_start), mot de passe actuel exigé,
 * limitation des essais (5 erreurs → verrou 15 min), 12 caractères minimum, différent de l'actuel,
 * hachage password_hash, régénération de la session, journalisation (jamais le mot de passe).
 */
require_once __DIR__ . '/../../includes/audit.php';

const PROFIL_MDP_MIN = 12;
const PROFIL_ESSAIS_MAX = 5;
const PROFIL_VERROU_SEC = 900;

$idAdm = (int) ($_SESSION['id_adm'] ?? 0);
$st = $bdd->prepare('SELECT id_adm, pseudo, mail, password, role FROM admin WHERE id_adm = ? LIMIT 1');
$st->execute([$idAdm]);
$moi = $st->fetch(PDO::FETCH_ASSOC);
if (!$moi) {
    http_response_code(403);
    exit('Compte introuvable.');
}

$erreurs = [];
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $actuel = (string) ($_POST['actuel'] ?? '');
    $nouveau = (string) ($_POST['nouveau'] ?? '');
    $confirm = (string) ($_POST['confirmation'] ?? '');
    $verrou = (int) ($_SESSION['profil_verrou'] ?? 0);

    if ($verrou > time()) {
        $erreurs[] = 'Trop de tentatives. Réessayez dans ' . (int) ceil(($verrou - time()) / 60) . ' minute(s).';
    } elseif (!password_verify($actuel, (string) $moi['password'])) {
        $_SESSION['profil_echecs'] = (int) ($_SESSION['profil_echecs'] ?? 0) + 1;
        audit_log($bdd, 'admin.mdp_echec', 'admin', (string) $idAdm, 'mot de passe actuel incorrect');
        if ($_SESSION['profil_echecs'] >= PROFIL_ESSAIS_MAX) {
            $_SESSION['profil_verrou'] = time() + PROFIL_VERROU_SEC;
            $_SESSION['profil_echecs'] = 0;
            $erreurs[] = 'Trop de tentatives. Changement de mot de passe verrouillé pendant 15 minutes.';
        } else {
            $erreurs[] = 'Le mot de passe actuel est incorrect.';
        }
    } else {
        if (mb_strlen($nouveau) < PROFIL_MDP_MIN) {
            $erreurs[] = 'Le nouveau mot de passe doit contenir au moins ' . PROFIL_MDP_MIN . ' caractères.';
        }
        if (strlen($nouveau) > 72) {
            $erreurs[] = 'Le nouveau mot de passe ne doit pas dépasser 72 caractères.';
        }
        if (!preg_match('/[a-z]/', $nouveau) || !preg_match('/[A-Z]/', $nouveau) || !preg_match('/\d/', $nouveau)) {
            $erreurs[] = 'Utilisez au moins une minuscule, une majuscule et un chiffre.';
        }
        if (stripos($nouveau, (string) $moi['pseudo']) !== false) {
            $erreurs[] = 'Le mot de passe ne doit pas contenir votre pseudo.';
        }
        if (hash_equals($actuel, $nouveau)) {
            $erreurs[] = 'Le nouveau mot de passe doit être différent de l\'actuel.';
        }
        if (!hash_equals($nouveau, $confirm)) {
            $erreurs[] = 'La confirmation ne correspond pas au nouveau mot de passe.';
        }
        if (!$erreurs) {
            $bdd->prepare('UPDATE admin SET password = ? WHERE id_adm = ?')
                ->execute([password_hash($nouveau, PASSWORD_DEFAULT), $idAdm]);
            session_regenerate_id(true);
            unset($_SESSION['profil_echecs'], $_SESSION['profil_verrou']);
            audit_log($bdd, 'admin.mot_de_passe_modifie', 'admin', (string) $idAdm, 'par le titulaire');
            admin_flash('Votre mot de passe a été modifié.');
            admin_redirect('pages=profil');
        }
    }
}
?>
<div class="container-fluid p-3 p-lg-4">
    <div class="row g-4">
        <div class="col-lg-5 col-xl-4">
            <div class="card adm-card h-100">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">Mon compte</h2>
                    <dl class="mb-0">
                        <dt class="small text-muted">Pseudo</dt><dd><?= e((string) $moi['pseudo']) ?></dd>
                        <dt class="small text-muted">E-mail</dt><dd><?= e((string) $moi['mail']) ?></dd>
                        <dt class="small text-muted">Rôle</dt><dd class="mb-0"><?= e((string) $moi['role']) ?></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-7 col-xl-6">
            <div class="card adm-card">
                <div class="card-body">
                    <h2 class="h5 mb-1"><i class="bi bi-key me-1"></i> Modifier mon mot de passe</h2>
                    <p class="text-muted small">Minimum <?= PROFIL_MDP_MIN ?> caractères, avec une minuscule, une majuscule et un chiffre.</p>
                    <?php foreach ($erreurs as $m): ?>
                        <div class="alert alert-danger py-2 mb-2" data-no-toast><?= e($m) ?></div>
                    <?php endforeach; ?>
                    <form method="post" autocomplete="off" novalidate id="formMdp">
                        <div class="mb-3">
                            <label class="form-label" for="actuel">Mot de passe actuel</label>
                            <input type="password" class="form-control" id="actuel" name="actuel" required autocomplete="current-password">
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="nouveau">Nouveau mot de passe</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="nouveau" name="nouveau" required minlength="<?= PROFIL_MDP_MIN ?>" maxlength="72" autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" id="voirMdp" aria-label="Afficher ou masquer les mots de passe"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <div class="progress mb-1" style="height:6px"><div class="progress-bar" id="forceBarre" style="width:0"></div></div>
                        <div class="small text-muted mb-3" id="forceTexte">&nbsp;</div>
                        <div class="mb-3">
                            <label class="form-label" for="confirmation">Confirmer le nouveau mot de passe</label>
                            <input type="password" class="form-control" id="confirmation" name="confirmation" required autocomplete="new-password">
                            <div class="form-text text-danger d-none" id="confNok">Les deux mots de passe ne correspondent pas.</div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var n = document.getElementById('nouveau'), c = document.getElementById('confirmation'), a = document.getElementById('actuel');
    var barre = document.getElementById('forceBarre'), txt = document.getElementById('forceTexte'), nok = document.getElementById('confNok');
    document.getElementById('voirMdp').addEventListener('click', function () {
        var t = n.type === 'password' ? 'text' : 'password';
        [a, n, c].forEach(function (i) { i.type = t; });
    });
    function force(v) {
        var s = 0;
        if (v.length >= 12) s++; if (v.length >= 16) s++;
        if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
        if (/\d/.test(v)) s++; if (/[^A-Za-z0-9]/.test(v)) s++;
        return Math.min(s, 4);
    }
    n.addEventListener('input', function () {
        var f = n.value ? force(n.value) : 0;
        var lib = ['Très faible', 'Faible', 'Moyen', 'Bon', 'Excellent'], col = ['danger', 'danger', 'warning', 'info', 'success'];
        barre.style.width = (n.value ? (f + 1) * 20 : 0) + '%';
        barre.className = 'progress-bar bg-' + col[f];
        txt.textContent = n.value ? 'Robustesse : ' + lib[f] : ' ';
    });
    c.addEventListener('input', function () { nok.classList.toggle('d-none', c.value === '' || c.value === n.value); });
});
</script>
