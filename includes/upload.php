<?php
/**
 * Validation centralisée des images envoyées (admin et formulaires publics).
 *  - erreur d'envoi, taille maximale, type MIME réel (finfo), image décodable (getimagesize)
 *  - l'extension est DÉDUITE du contenu réel, jamais du nom envoyé par le navigateur
 *  - le nom final est aléatoire : aucun texte saisi par l'utilisateur n'entre dans un chemin
 */
require_once __DIR__ . '/helpers.php';

if (!function_exists('upload_image_valide')) {
    /** @return string|null extension ('.jpg', '.png', '.webp') si l'image est valide, sinon null */
    function upload_image_valide($file, int $maxOctets = 5242880): ?string
    {
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp) || (int) ($file['size'] ?? 0) <= 0 || (int) $file['size'] > $maxOctets) {
            return null;
        }
        $mime = mime_reel($tmp);
        $map  = ['image/jpeg' => '.jpg', 'image/png' => '.png', 'image/webp' => '.webp'];
        if (!isset($map[$mime]) || @getimagesize($tmp) === false) {
            return null;
        }
        return $map[$mime];
    }
}

if (!function_exists('upload_nom_aleatoire')) {
    function upload_nom_aleatoire(string $prefixe, string $extension): string
    {
        return preg_replace('/[^a-z0-9_]/i', '', $prefixe) . '_' . bin2hex(random_bytes(10)) . $extension;
    }
}

if (!function_exists('upload_enregistrer_image')) {
    /**
     * Valide puis enregistre une image envoyée dans $dossier (chemin absolu, créé au besoin, protégé contre
     * l'exécution de scripts). Retourne le nom de fichier généré, ou null si l'image est refusée / non enregistrée.
     */
    function upload_enregistrer_image($file, string $dossier, string $prefixe, int $maxOctets = 5242880): ?string
    {
        $ext = upload_image_valide($file, $maxOctets);
        if ($ext === null) {
            return null;
        }
        $dossier = rtrim($dossier, '/\\');
        if (!is_dir($dossier) && !@mkdir($dossier, 0755, true) && !is_dir($dossier)) {
            error_log('[upload] dossier impossible à créer : ' . basename($dossier));
            return null;
        }
        $nom = upload_nom_aleatoire($prefixe, $ext);
        if (!move_uploaded_file((string) $file['tmp_name'], $dossier . DIRECTORY_SEPARATOR . $nom)) {
            error_log('[upload] déplacement impossible vers ' . basename($dossier));
            return null;
        }
        return $nom;
    }
}

if (!function_exists('upload_supprimer')) {
    /** Supprime un fichier envoyé (nom simple uniquement : aucun chemin accepté). */
    function upload_supprimer(string $dossier, ?string $nom): void
    {
        $nom = basename((string) $nom);
        if ($nom !== '' && $nom !== '.' && is_file(rtrim($dossier, '/\\') . DIRECTORY_SEPARATOR . $nom)) {
            @unlink(rtrim($dossier, '/\\') . DIRECTORY_SEPARATOR . $nom);
        }
    }
}

/** Dossiers d'images publiques (chemins absolus). */
if (!defined('MEDIA_ACTIVITES')) { define('MEDIA_ACTIVITES', dirname(__DIR__) . '/media/images_activ'); }
if (!defined('MEDIA_PARTENAIRES')) { define('MEDIA_PARTENAIRES', dirname(__DIR__) . '/media/images_part'); }
if (!defined('MEDIA_EQUIPE')) { define('MEDIA_EQUIPE', dirname(__DIR__) . '/admin/media/img_equipe'); }
