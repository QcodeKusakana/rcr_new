<?php
/**
 * Modèle de configuration FlexPay.
 * Copiez ce fichier en « flexpay.php » (dans ce même dossier) et renseignez vos valeurs réelles.
 * Si vous avez déjà votre config/flexpay.php (celui de l'ancien site), gardez-le tel quel : il est compatible.
 * Ne jamais publier le jeton (Git, e-mail, conversation).
 */
if (!defined('FLEXPAY_TOKEN')) {
    define('FLEXPAY_TOKEN', 'COLLER_ICI_LE_JETON_BEARER_SANS_LE_MOT_Bearer');
    define('FLEXPAY_BASE_URL', 'https://backend.flexpay.cd/api/rest/v1');
    define('FLEXPAY_MERCHANT', 'RCR');
    define('FLEXPAY_CALLBACK_URL', 'https://rcr.cd/api/flexpay_callback.php');
}
// Facultatif : forcer l'URL publique du site (sinon détectée : hôte local en développement, rcr.cd en production)
// define('SITE_URL', 'https://rcr.cd');
