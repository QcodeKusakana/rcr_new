<?php
/**
 * Architecture de notifications (e-mail, SMS, interne) — PRÊTE à recevoir les API, sans les imposer.
 *
 * notify() n'envoie rien : il met un message en file (table `notifications`, statut "pending").
 * Un futur "transport" (SMTP, API SMS…) lira cette file et marquera les lignes "sent" ou "failed".
 * Tant qu'aucun transport n'est branché, la file sert d'historique et de notifications internes.
 *
 * Événements prévus : adhesion_confirmee, paiement_confirme, echeance_proche, cotisation_expiree, don_recu.
 * La clé de déduplication (cle_unique) évite d'empiler deux fois le même message.
 */
if (!function_exists('notify')) {
    /**
     * @param string $canal 'interne' | 'email' | 'sms'
     * @return bool true si mis en file, false si déjà présent ou erreur
     */
    function notify(PDO $bdd, string $evenement, string $canal, ?int $idAd, string $destinataire, array $donnees = [], ?string $cleUnique = null): bool
    {
        if (!in_array($canal, ['interne', 'email', 'sms'], true)) {
            return false;
        }
        try {
            $s = $bdd->prepare("INSERT IGNORE INTO notifications (evenement, canal, id_ad, destinataire, donnees, cle_unique, statut)
                                VALUES (?, ?, ?, ?, ?, ?, 'pending')");
            $s->execute([
                mb_substr($evenement, 0, 40), $canal, $idAd, mb_substr($destinataire, 0, 200),
                json_encode($donnees, JSON_UNESCAPED_UNICODE), $cleUnique ? mb_substr($cleUnique, 0, 120) : null,
            ]);
            return $s->rowCount() > 0;
        } catch (Throwable $e) {
            error_log('[notify] ' . $e->getMessage()); // ne bloque jamais le paiement
            return false;
        }
    }
}

if (!function_exists('notify_member')) {
    /** Met en file le message pour les canaux disponibles du membre (interne toujours, e-mail et SMS si renseignés). */
    function notify_member(PDO $bdd, int $idAd, string $evenement, array $donnees = [], ?string $cleUnique = null): void
    {
        $s = $bdd->prepare('SELECT mail, telephone FROM adhesion WHERE id_ad = ?');
        $s->execute([$idAd]);
        $m = $s->fetch(PDO::FETCH_ASSOC);
        if (!$m) {
            return;
        }
        notify($bdd, $evenement, 'interne', $idAd, (string) $idAd, $donnees, $cleUnique ? $cleUnique . ':i' : null);
        if (!empty($m['mail'])) {
            notify($bdd, $evenement, 'email', $idAd, $m['mail'], $donnees, $cleUnique ? $cleUnique . ':e' : null);
        }
        if (!empty($m['telephone'])) {
            notify($bdd, $evenement, 'sms', $idAd, $m['telephone'], $donnees, $cleUnique ? $cleUnique . ':s' : null);
        }
    }
}
