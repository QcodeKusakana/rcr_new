<?php
/**
 * « Mot de passe oublié » : envoie un lien à usage unique (valable 1 heure) à l'adresse du compte.
 *  - Réponse IDENTIQUE que le compte existe ou non (pas d'énumération des comptes).
 *  - Le jeton n'est jamais stocké en clair : seule son empreinte SHA-256 est en base.
 *  - Limites : 3 demandes / heure / compte et 8 / heure / adresse IP.
 */
require_once __DIR__ . '/../includes/login_attempts.php';
require_once __DIR__ . '/../includes/notif_sender.php';
require_once __DIR__ . '/../includes/audit.php';

$erreur = null;
$info = null;

if (isset($_POST['reset']) && !csrf_verify()) {
    $erreur = 'Session expirée, merci de recharger la page et de réessayer.';
} elseif (isset($_POST['reset'])) {
    $generique = "Si ce compte existe, un e-mail contenant un lien de réinitialisation vient d'être envoyé. Le lien est valable 1 heure. Pensez à vérifier vos courriers indésirables.";
    $ip = login_attempts_client_ip();
    $ident = trim((string) ($_POST['identifiant'] ?? ''));
    try {
        $s = $bdd->prepare('SELECT COUNT(*) FROM password_resets WHERE ip = ? AND cree_le > (NOW() - INTERVAL 1 HOUR)');
        $s->execute([$ip]);
        $tropIp = (int) $s->fetchColumn() >= 8;

        $membre = null;
        if ($ident !== '' && !$tropIp) {
            $q = $bdd->prepare('SELECT id_ad, mail, prenom FROM adhesion WHERE codes = ? OR mail = ? LIMIT 1');
            $q->execute([strtoupper($ident), mb_strtolower($ident)]);
            $membre = $q->fetch(PDO::FETCH_ASSOC) ?: null;
        }
        if ($membre && filter_var($membre['mail'], FILTER_VALIDATE_EMAIL)) {
            $c = $bdd->prepare('SELECT COUNT(*) FROM password_resets WHERE id_ad = ? AND cree_le > (NOW() - INTERVAL 1 HOUR)');
            $c->execute([(int) $membre['id_ad']]);
            if ((int) $c->fetchColumn() < 3) {
                $jeton = bin2hex(random_bytes(32));
                $bdd->prepare('INSERT INTO password_resets (id_ad, token_hash, expire_le, ip) VALUES (?, ?, (NOW() + INTERVAL 1 HOUR), ?)')
                    ->execute([(int) $membre['id_ad'], hash('sha256', $jeton), $ip]);
                $base = defined('SITE_URL') ? rtrim((string) SITE_URL, '/') : 'https://rcr.cd';
                $lien = $base . '/?pages=mdp_reset&t=' . $jeton;
                $nom = htmlspecialchars((string) $membre['prenom'], ENT_QUOTES, 'UTF-8');
                $html = notif_gabarit('Réinitialisation de votre mot de passe',
                    '<p>Bonjour ' . $nom . ',</p><p>Une demande de réinitialisation de mot de passe a été faite pour votre compte. '
                    . 'Ce lien est valable <b>1 heure</b> et ne peut servir qu\'une fois.</p>'
                    . '<p style="margin:22px 0"><a href="' . htmlspecialchars($lien, ENT_QUOTES, 'UTF-8') . '" style="background:#f2c230;color:#1b2a44;text-decoration:none;font-weight:bold;padding:12px 20px;border-radius:6px;display:inline-block">Choisir un nouveau mot de passe</a></p>'
                    . '<p style="color:#666;font-size:13px">Si vous n\'êtes pas à l\'origine de cette demande, ignorez ce message : votre mot de passe actuel reste valable.</p>');
                [$ok, $err] = mail_envoyer($membre['mail'], 'RCR — réinitialisation de votre mot de passe', $html);
                if (!$ok) { error_log('[mdp_oublie] envoi impossible : ' . $err); }
                audit_log($bdd, 'membre.mdp_demande', 'adhesion', (string) $membre['id_ad'], $ok ? 'e-mail envoyé' : 'envoi échoué');
            }
        } else {
            usleep(random_int(400000, 1200000)); // brouille la différence de durée avec le cas « compte trouvé »
            if ($ident !== '' && !$tropIp) {
                // trace sans lien vers un compte : permet de limiter les demandes par IP
                $bdd->prepare('INSERT INTO password_resets (id_ad, token_hash, expire_le, utilise_le, ip) VALUES (0, ?, NOW(), NOW(), ?)')
                    ->execute([hash('sha256', bin2hex(random_bytes(16))), $ip]);
            }
        }
        $info = $generique;
    } catch (Throwable $e) {
        error_log('[mdp_oublie] ' . $e->getMessage());
        $erreur = "Service momentanément indisponible. Merci de réessayer plus tard.";
    }
}
