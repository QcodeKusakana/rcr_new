<?php
/**
 * Protection CSRF minimaliste, sans dépendance externe (ajoutée suite à
 * l'audit de sécurité — aucun des formulaires du projet n'avait de jeton).
 *
 * Usage dans un formulaire :
 *     <form method="post">
 *         <?php echo csrf_field(); ?>
 *         ...
 *     </form>
 *
 * Usage à la réception du POST, avant tout traitement :
 *     if (!csrf_verify()) {
 *         die('Session expirée, merci de recharger la page et réessayer.');
 *     }
 *
 * Nécessite que session_start() ait déjà été appelé (c'est le cas dans
 * tous les main_function.php du projet).
 */

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify(): bool
    {
        return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
            && is_string($_POST['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }
}
