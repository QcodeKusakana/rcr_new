<?php
/**
 * Envoi des e-mails en attente (CLI). À planifier toutes les 5 minutes :
 *   *\/5 * * * *  php /chemin/site/tools/send_notifications.php >> /chemin/site/storage/logs/mail.log 2>&1
 * Option : --test=adresse@exemple.com  envoie un message de test pour vérifier config/mail.php.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Accès interdit.'); }
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/includes/notif_sender.php';

foreach ($argv ?? [] as $a) {
    if (strpos($a, '--test=') === 0) {
        [$ok, $err] = mail_envoyer(substr($a, 7), 'RCR — test d\'envoi', notif_gabarit('Test d\'envoi', '<p>Si vous lisez ce message, la configuration e-mail du site RCR fonctionne.</p>'));
        echo $ok ? "Envoyé.\n" : "ÉCHEC : $err\n";
        exit($ok ? 0 : 1);
    }
}
$r = notifications_envoyer_emails($bdd, 50);
echo '[' . date('Y-m-d H:i:s') . '] e-mails : ' . $r['envoyes'] . ' envoyés, ' . $r['echecs'] . ' échecs, ' . $r['restants'] . ' en attente'
   . ($r['config'] ? '' : ' (config/mail.php absent : rien envoyé)') . "\n";
