<?php
/**
 * Modèle de configuration e-mail (SMTP).
 * Copiez ce fichier en « mail.php » (même dossier) et renseignez les valeurs fournies par votre hébergeur
 * (cPanel → Comptes de messagerie → « Configurer le client de messagerie »).
 * Sans ce fichier, aucun e-mail n'est envoyé : les messages restent en file (table notifications) et le site fonctionne.
 * Ne jamais publier ce fichier une fois rempli.
 */
if (!defined('MAIL_HOST')) {
    define('MAIL_HOST', 'mail.rcr.cd');          // serveur SMTP
    define('MAIL_PORT', 465);                    // 465 (SSL) ou 587 (STARTTLS)
    define('MAIL_SECURE', 'ssl');                // 'ssl' | 'tls' | '' (déconseillé)
    define('MAIL_USER', 'no-reply@rcr.cd');
    define('MAIL_PASS', 'MOT_DE_PASSE_DU_COMPTE');
    define('MAIL_FROM', 'no-reply@rcr.cd');
    define('MAIL_FROM_NAME', 'Rassemblement des Chrétiens Républicains');
}
