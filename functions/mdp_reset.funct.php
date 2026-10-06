<?php
/**
 * Choix d'un nouveau mot de passe à partir du lien reçu par e-mail (jeton à usage unique, 1 heure).
 */
require_once __DIR__ . '/../includes/login_attempts.php';
require_once __DIR__ . '/../includes/audit.php';

$erreur = null;
$info = null;
$jetonValide = false;
$jeton = (string) ($_GET['t'] ?? '');
$ligne = null;

if (preg_match('/^[a-f0-9]{64}$/', $jeton)) {
    try {
        $q = $bdd->prepare('SELECT id, id_ad FROM password_resets WHERE token_hash = ? AND utilise_le IS NULL AND expire_le > NOW() AND id_ad > 0 LIMIT 1');
        $q->execute([hash('sha256', $jeton)]);
        $ligne = $q->fetch(PDO::FETCH_ASSOC) ?: null;
        $jetonValide = $ligne !== null;
    } catch (Throwable $e) {
        error_log('[mdp_reset] ' . $e->getMessage());
    }
}

if ($jetonValide && isset($_POST['reset']) && !csrf_verify()) {
    $erreur = 'Session expirée, merci de recharger la page et de réessayer.';
} elseif ($jetonValide && isset($_POST['reset'])) {
    $m1 = (string) ($_POST['mot_de_passe'] ?? '');
    $m2 = (string) ($_POST['mot_de_passe2'] ?? '');
    if (strlen($m1) < 8 || $m1 !== $m2) {
        $erreur = 'Mot de passe : 8 caractères minimum, les deux saisies doivent être identiques.';
    } else {
        try {
            $bdd->beginTransaction();
            // L'UPDATE conditionnel garantit l'usage unique même si le lien est ouvert deux fois en même temps
            $u = $bdd->prepare('UPDATE password_resets SET utilise_le = NOW() WHERE id = ? AND utilise_le IS NULL AND expire_le > NOW()');
            $u->execute([(int) $ligne['id']]);
            if ($u->rowCount() !== 1) {
                $bdd->rollBack();
                $jetonValide = false;
            } else {
                $bdd->prepare('UPDATE adhesion SET password_hash = ? WHERE id_ad = ?')->execute([password_hash($m1, PASSWORD_DEFAULT), (int) $ligne['id_ad']]);
                $bdd->prepare('UPDATE password_resets SET utilise_le = NOW() WHERE id_ad = ? AND utilise_le IS NULL')->execute([(int) $ligne['id_ad']]);
                $bdd->commit();
                login_attempts_reset($bdd, login_attempts_client_ip());
                audit_log($bdd, 'membre.mdp_reinitialise', 'adhesion', (string) $ligne['id_ad']);
                $info = 'Votre mot de passe a été modifié. Vous pouvez maintenant vous connecter.';
                $jetonValide = false; // masque le formulaire
            }
        } catch (Throwable $e) {
            if ($bdd->inTransaction()) { $bdd->rollBack(); }
            error_log('[mdp_reset] ' . $e->getMessage());
            $erreur = 'Une erreur est survenue. Merci de réessayer.';
        }
    }
}
