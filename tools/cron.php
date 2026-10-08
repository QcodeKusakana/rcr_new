<?php
/**
 * Tâches planifiées RCR (CLI uniquement). À lancer chaque jour (ex. 02h00) :
 *   0 2 * * *  php /chemin/du/site/tools/cron.php >> /chemin/du/site/storage/logs/cron.log 2>&1
 * Et CHAQUE MINUTE pour le rattrapage des paiements (calendrier 10 s … 15 min géré dans le script) :
 *   * * * * *  php /chemin/du/site/tools/cron.php --paiements
 *
 * Options : --dry-run (n'écrit rien) ; --paiements (uniquement le rattrapage des paiements).
 * Idempotent : peut être relancé sans doublons (clés uniques sur les notifications).
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Accès interdit.'); }
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/membres.php';
require_once dirname(__DIR__) . '/includes/notifications.php';
require_once dirname(__DIR__) . '/includes/payment_helpers.php';
require_once dirname(__DIR__) . '/includes/notif_sender.php';

$dry = in_array('--dry-run', $argv ?? [], true);
$seulementPaiements = in_array('--paiements', $argv ?? [], true);
$log = function (string $m) { echo '[' . date('Y-m-d H:i:s') . '] ' . $m . "\n"; };
$log('cron RCR ' . ($dry ? '(simulation)' : '') . ($seulementPaiements ? ' paiements' : ''));

/* 1. Rattrapage des paiements restés ouverts (callback perdu, client parti) — à lancer CHAQUE MINUTE.
 *    Calendrier de re-vérification non bloquant : ≈10 s, 30 s, 1, 2, 5, 10, 15 min puis toutes les 15 min jusqu'à 3 jours.
 *    Un paiement n'est JAMAIS passé en échec ici sans règle FlexPay (status "1" + 3 min, voir payment_verify_and_confirm). */
try {
    $q = $bdd->query("SELECT 'payment' AS t, id, created_at, nb_verifs, TIMESTAMPDIFF(SECOND, COALESCE(derniere_verif, created_at), NOW()) AS depuis, TIMESTAMPDIFF(SECOND, created_at, NOW()) AS age
                      FROM payments WHERE status IN ('pending','processing','expired') AND order_number IS NOT NULL AND created_at > (NOW() - INTERVAL 3 DAY)
                      UNION ALL
                      SELECT 'don', id_don, created_at, nb_verifs, TIMESTAMPDIFF(SECOND, COALESCE(derniere_verif, created_at), NOW()), TIMESTAMPDIFF(SECOND, created_at, NOW())
                      FROM dons WHERE status IN ('pending','processing','expired') AND order_number IS NOT NULL AND created_at > (NOW() - INTERVAL 3 DAY)
                      ORDER BY created_at LIMIT 100");
    $paliers = [10, 30, 60, 120, 300, 600, 900]; // âge minimal (s) pour la N-ième vérification
    $n = 0; $payes = 0;
    foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $k = (int) $r['nb_verifs'];
        $dueAge = $k < count($paliers) ? $paliers[$k] : 900;
        $espacement = $k < count($paliers) ? 0 : 900; // après le calendrier : un passage toutes les 15 min
        if ((int) $r['age'] < $dueAge || ($espacement > 0 && (int) $r['depuis'] < $espacement)) { continue; }
        $n++;
        if ($dry) { continue; }
        if (payment_flexpay_check($bdd, $r['t'] === 'don' ? 'don' : 'adhesion', (int) $r['id']) === 'paid') { $payes++; }
    }
    $log("paiements revérifiés : $n (confirmés : $payes)");
} catch (Throwable $e) { $log('ERREUR rattrapage paiements : ' . $e->getMessage() . ' (migration phase7 appliquée ?)'); }

if ($seulementPaiements) { exit(0); }

/* 2. Membres dont l'échéance est dépassée -> expiré */
$exp = $dry ? 0 : membres_marquer_expires($bdd);
$log("membres passés à « expiré » : $exp");

/* 3. Rappels d'échéance (J-7 et J-1) — une seule notification par membre, échéance et palier */
$rap = 0;
try {
    foreach ([7, 1] as $j) {
        $s = $bdd->prepare("SELECT id_ad, date_echeance FROM adhesion WHERE statut = 'actif' AND date_echeance = DATE_ADD(CURDATE(), INTERVAL ? DAY)");
        $s->execute([$j]);
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $m) {
            if (!$dry) {
                notify_member($bdd, (int) $m['id_ad'], 'echeance_proche', ['jours' => $j, 'echeance' => $m['date_echeance']], "echeance:{$m['id_ad']}:{$m['date_echeance']}:j$j");
            }
            $rap++;
        }
    }
    // Cotisation expirée depuis hier : une seule relance
    $s = $bdd->query("SELECT id_ad, date_echeance FROM adhesion WHERE statut = 'expire' AND date_echeance = DATE_SUB(CURDATE(), INTERVAL 1 DAY)");
    foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $m) {
        if (!$dry) { notify_member($bdd, (int) $m['id_ad'], 'cotisation_expiree', ['echeance' => $m['date_echeance']], "expiree:{$m['id_ad']}:{$m['date_echeance']}"); }
        $rap++;
    }
} catch (Throwable $e) { $log('ERREUR rappels membres : ' . $e->getMessage()); }
$log("rappels membres mis en file : $rap");

/* 4. Rappels de dons réguliers (échéance dans 3 jours ou dépassée de moins de 7 jours) */
$rapD = 0;
try {
    $s = $bdd->query("SELECT id_don, id_ad, prochaine_echeance, montant, devise FROM dons
                      WHERE status = 'paid' AND type_don = 'regulier' AND id_ad IS NOT NULL
                        AND prochaine_echeance BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)");
    foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $d) {
        if (!$dry) {
            notify_member($bdd, (int) $d['id_ad'], 'don_rappel', ['montant' => $d['montant'], 'devise' => $d['devise'], 'echeance' => $d['prochaine_echeance']], "don:{$d['id_don']}:{$d['prochaine_echeance']}");
        }
        $rapD++;
    }
} catch (Throwable $e) { $log('ERREUR rappels dons : ' . $e->getMessage()); }
$log("rappels de dons mis en file : $rapD");

/* 5. Nettoyage : tentatives de connexion > 30 j, sessions PHP > 7 j, anciennes sauvegardes gérées par tools/backup.php */
if (!$dry) {
    try { $bdd->exec("DELETE FROM login_attempts WHERE derniere_tentative < (NOW() - INTERVAL 30 DAY)"); } catch (Throwable $e) {}
    $dir = dirname(__DIR__) . '/tmp_sessions';
    $supp = 0;
    foreach (glob($dir . '/sess_*') ?: [] as $f) { if (filemtime($f) < time() - 7 * 86400 && @unlink($f)) { $supp++; } }
    $log("sessions anciennes supprimées : $supp");
}
/* 6. Envoi des e-mails en attente + purge des jetons de réinitialisation anciens */
if (!$dry) {
    try {
        $r = notifications_envoyer_emails($bdd, 50);
        $log('e-mails : ' . $r['envoyes'] . ' envoyés, ' . $r['echecs'] . ' échecs, ' . $r['restants'] . ' en attente' . ($r['config'] ? '' : ' (config/mail.php absent)'));
        $bdd->exec("DELETE FROM password_resets WHERE cree_le < (NOW() - INTERVAL 7 DAY)");
    } catch (Throwable $e) { $log('ERREUR envoi e-mails : ' . $e->getMessage()); }
}
$log('terminé');
