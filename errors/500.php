<?php
require_once __DIR__ . '/_gabarit.php';
$inc = $GLOBALS['rcr_incident'] ?? null;
$msg = "Notre équipe a été informée. Merci de réessayer dans quelques instants.";
if (is_array($inc) && !empty($inc['ref'])) {
    $msg .= ' Référence de l\'incident : ' . $inc['ref'] . '.';
    // Détail technique : uniquement si APP_DEBUG est activé, OU si la requête vient de la machine locale elle-même
    // (adresse source loopback ET hôte local). Un visiteur distant ne peut jamais le voir : REMOTE_ADDR n'est pas falsifiable par un en-tête.
    $addr  = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $hote  = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
    $local = in_array($addr, ['127.0.0.1', '::1'], true)
          && (in_array($hote, ['localhost', '127.0.0.1', '[::1]'], true) || preg_match('/\.(test|local|localhost)$/', $hote));
    if (((defined('APP_DEBUG') && APP_DEBUG) || $local) && !empty($inc['detail'])) {
        $msg .= ' [DÉTAIL TECHNIQUE LOCAL] ' . $inc['detail'];
    }
}
rcr_page_erreur(500, 'Une erreur est survenue', $msg);
