<?php
/**
 * Fonctions utilitaires partagées (public + admin).
 *
 * 🔐 CORRECTIF (audit) : plusieurs formulaires du back-office
 * (admin/functions/equipe.funct.php, partenaire.funct.php,
 * activite.funct.php...) appliquent déjà htmlspecialchars() sur les
 * champs texte AVANT de les enregistrer en base — ce qui est une bonne
 * pratique en soi, mais rend risqué le fait d'appeler à nouveau
 * htmlspecialchars() lors de l'affichage : PHP ré-échappe alors les
 * entités déjà présentes ("&#039;" devient "&amp;#039;"), ce qui casse
 * l'affichage de tout nom contenant une apostrophe ("N'Sele", "M'Bala"...
 * très courants) ou un "&". C'est ce qui s'est produit dans plusieurs
 * pages du projet (admin/pages/equipe.php, partenaire.php, adhesions.php,
 * demandes.php avaient déjà ce défaut avant cet audit ; il a aussi été
 * introduit par erreur lors de la modernisation de pages/equipe.php,
 * pages/partenaire.php et pages/home.php avant d'être repéré ici).
 *
 * e() résout ce problème une bonne fois pour toutes : elle échappe bien
 * les caractères dangereux (<, >, ", ', &) pour tout affichage HTML,
 * mais sans jamais ré-échapper une entité déjà présente
 * (double_encode = false). Résultat : sûr contre le XSS, que la valeur
 * ait déjà été échappée à l'enregistrement ou non — et l'affichage des
 * noms avec apostrophe redevient correct.
 *
 * À utiliser pour tout affichage de valeur venant de la base de données
 * ou d'une entrée utilisateur, à la place d'un htmlspecialchars() nu.
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8', false);
}

if (!function_exists('mime_reel')) {
    /**
     * Type MIME RÉEL d'un fichier (jamais celui annoncé par le navigateur). Fonctionne même si l'extension PHP
     * « fileinfo » est absente de l'hébergement : repli sur mime_content_type(), puis sur la lecture de l'en-tête
     * (images via getimagesize(), PDF via la signature « %PDF- »). Retourne application/octet-stream si inconnu.
     */
    function mime_reel(string $chemin): string
    {
        if (class_exists('finfo')) {
            $m = (new finfo(FILEINFO_MIME_TYPE))->file($chemin);
            if (is_string($m) && $m !== '') { return $m; }
        }
        if (function_exists('mime_content_type')) {
            $m = @mime_content_type($chemin);
            if (is_string($m) && $m !== '') { return $m; }
        }
        $info = @getimagesize($chemin);
        if (is_array($info) && !empty($info['mime'])) { return (string) $info['mime']; }
        $h = @fopen($chemin, 'rb');
        $debut = $h ? (string) fread($h, 5) : '';
        if ($h) { fclose($h); }
        return $debut === '%PDF-' ? 'application/pdf' : 'application/octet-stream';
    }
}
