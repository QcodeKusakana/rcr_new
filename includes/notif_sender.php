<?php
/**
 * Transport e-mail pour la file `notifications` : modèles de messages en français + envoi par lots.
 * Les canaux « sms » et « interne » ne sont pas traités ici (aucune API SMS disponible).
 */
require_once __DIR__ . '/mailer.php';

if (!function_exists('notif_gabarit')) {
    /** Habillage commun des e-mails (sobre, compatible clients de messagerie). */
    function notif_gabarit(string $titre, string $corpsHtml): string
    {
        $t = htmlspecialchars($titre, ENT_QUOTES, 'UTF-8');
        return '<!DOCTYPE html><html lang="fr"><body style="margin:0;background:#f4f5f7;font-family:Arial,Helvetica,sans-serif;color:#1b2a44">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:24px 12px">'
            . '<table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width:560px;background:#fff;border-radius:8px;overflow:hidden">'
            . '<tr><td style="background:#1b2a44;color:#f2c230;padding:18px 24px;font-size:18px;font-weight:bold">Rassemblement des Chrétiens Républicains</td></tr>'
            . '<tr><td style="padding:24px"><h2 style="margin:0 0 14px;font-size:20px">' . $t . '</h2>' . $corpsHtml . '</td></tr>'
            . '<tr><td style="padding:14px 24px;background:#fafafa;color:#777;font-size:12px">Message automatique, merci de ne pas y répondre. rcr.cd</td></tr>'
            . '</table></td></tr></table></body></html>';
    }
}

if (!function_exists('notif_rendre')) {
    /** @return array{0:string,1:string}|null [sujet, html] ; null si l'événement n'a pas de modèle e-mail */
    function notif_rendre(string $evenement, array $d): ?array
    {
        $e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $m = isset($d['montant']) ? number_format((float) $d['montant'], 2, ',', ' ') . ' ' . $e($d['devise'] ?? 'USD') : '';
        $ref = isset($d['reference']) ? '<p style="color:#666;font-size:13px">Référence : <b>' . $e($d['reference']) . '</b></p>' : '';
        $ech = isset($d['echeance']) ? date('d/m/Y', strtotime((string) $d['echeance'])) : '';
        $site = defined('SITE_URL') ? rtrim((string) SITE_URL, '/') : 'https://rcr.cd';
        $btn = fn(string $url, string $lib) => '<p style="margin:22px 0"><a href="' . $e($url) . '" style="background:#f2c230;color:#1b2a44;text-decoration:none;font-weight:bold;padding:12px 20px;border-radius:6px;display:inline-block">' . $e($lib) . '</a></p>';
        switch ($evenement) {
            case 'adhesion_confirmee':
                return ['Bienvenue au RCR — adhésion confirmée', notif_gabarit('Adhésion confirmée', '<p>Merci, votre paiement de <b>' . $m . '</b> est confirmé et votre adhésion est active.</p>' . $ref . '<p>Vous pouvez télécharger votre carte de membre et vos reçus depuis votre espace membre.</p>' . $btn($site . '/?pages=esp_membre', 'Mon espace membre'))];
            case 'paiement_confirme':
                return ['RCR — paiement confirmé', notif_gabarit('Paiement confirmé', '<p>Nous avons bien reçu votre paiement de <b>' . $m . '</b>. Votre cotisation est à jour.</p>' . $ref . $btn($site . '/?pages=esp_membre', 'Voir mes paiements'))];
            case 'echeance_proche':
                $j = (int) ($d['jours'] ?? 0);
                return ['RCR — votre cotisation arrive à échéance', notif_gabarit('Votre cotisation arrive à échéance', '<p>Votre cotisation expire le <b>' . $e($ech) . '</b>' . ($j > 0 ? ' (dans ' . $j . ' jour' . ($j > 1 ? 's' : '') . ')' : '') . '. Pensez à la renouveler pour rester membre actif.</p>' . $btn($site . '/?pages=esp_membre', 'Renouveler ma cotisation'))];
            case 'cotisation_expiree':
                return ['RCR — votre cotisation a expiré', notif_gabarit('Cotisation expirée', '<p>Votre cotisation a expiré le <b>' . $e($ech) . '</b>. Renouvelez-la pour retrouver tous les avantages de votre adhésion.</p>' . $btn($site . '/?pages=esp_membre', 'Renouveler ma cotisation'))];
            case 'don_recu':
                return ['RCR — merci pour votre don', notif_gabarit('Merci pour votre soutien', '<p>Votre don de <b>' . $m . '</b> a bien été reçu. Au nom du Rassemblement, merci.</p>' . $ref)];
            case 'don_rappel':
                return ['RCR — rappel de votre don régulier', notif_gabarit('Votre don régulier', '<p>Votre prochain versement de <b>' . $m . '</b> est prévu le <b>' . $e($ech) . '</b>.</p>' . $btn($site . '/?pages=soutenir', 'Faire mon don'))];
        }
        return null;
    }
}

if (!function_exists('notifications_envoyer_emails')) {
    /**
     * Envoie jusqu'à $limite e-mails en attente. Un échec définitif (adresse invalide) passe en « failed » ;
     * une panne SMTP laisse le message « pending » pour le prochain passage (maximum 24 h, ensuite « failed »).
     * @return array{envoyes:int, echecs:int, restants:int, config:bool}
     */
    function notifications_envoyer_emails(PDO $bdd, int $limite = 30): array
    {
        $res = ['envoyes' => 0, 'echecs' => 0, 'restants' => 0, 'config' => mail_configure()];
        if (!$res['config']) {
            $res['restants'] = (int) $bdd->query("SELECT COUNT(*) FROM notifications WHERE canal = 'email' AND statut = 'pending'")->fetchColumn();
            return $res;
        }
        $s = $bdd->prepare("SELECT id, evenement, destinataire, donnees, cree_le FROM notifications WHERE canal = 'email' AND statut = 'pending' ORDER BY id LIMIT " . max(1, min(200, $limite)));
        $s->execute();
        $ok = $bdd->prepare("UPDATE notifications SET statut = 'sent', envoye_le = NOW() WHERE id = ? AND statut = 'pending'");
        $ko = $bdd->prepare("UPDATE notifications SET statut = 'failed' WHERE id = ? AND statut = 'pending'");
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $n) {
            $rendu = notif_rendre($n['evenement'], json_decode((string) $n['donnees'], true) ?: []);
            if ($rendu === null) { $ko->execute([$n['id']]); $res['echecs']++; continue; }
            [$envoye, $err] = mail_envoyer($n['destinataire'], $rendu[0], $rendu[1]);
            if ($envoye) { $ok->execute([$n['id']]); $res['envoyes']++; continue; }
            $definitif = strpos($err, 'invalide') !== false || strpos($err, 'destinataire refusé') !== false || (time() - strtotime((string) $n['cree_le'])) > 86400;
            if ($definitif) { $ko->execute([$n['id']]); $res['echecs']++; }
            else { break; } // panne SMTP : on s'arrête, le prochain passage réessaiera
        }
        $res['restants'] = (int) $bdd->query("SELECT COUNT(*) FROM notifications WHERE canal = 'email' AND statut = 'pending'")->fetchColumn();
        return $res;
    }
}
