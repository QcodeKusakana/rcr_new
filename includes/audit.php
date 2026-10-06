<?php
/**
 * Journal d'audit des actions importantes (connexion, modification de tarif,
 * validation de paiement, modification de contenu, etc.).
 * Ne lève jamais d'exception : un échec de journalisation ne doit pas bloquer l'action.
 */
if (!function_exists('audit_log')) {
    function audit_log(PDO $bdd, string $action, string $cible = '', string $cibleId = '', $detail = null): void
    {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $s = $bdd->prepare('INSERT INTO audit_logs (id_adm, action, cible, cible_id, detail, ip) VALUES (?,?,?,?,?,?)');
            $s->execute([
                isset($_SESSION['id_adm']) ? (int) $_SESSION['id_adm'] : null,
                mb_substr($action, 0, 60), mb_substr($cible, 0, 80), mb_substr($cibleId, 0, 60),
                $detail === null ? null : mb_substr(is_string($detail) ? $detail : json_encode($detail, JSON_UNESCAPED_UNICODE), 0, 4000),
                mb_substr($ip, 0, 45),
            ]);
        } catch (Throwable $e) {
            error_log('[audit_log] ' . $e->getMessage());
        }
    }
}
