<?php
/** Aides "membres" : statuts d'adhésion, échéances. */
require_once __DIR__ . '/notifications.php';

if (!function_exists('membres_marquer_expires')) {
    /** Passe en "expire" les membres actifs dont l'échéance est dépassée. Retourne le nombre de lignes modifiées. */
    function membres_marquer_expires(PDO $bdd): int
    {
        try {
            $s = $bdd->prepare("UPDATE adhesion SET statut = 'expire' WHERE statut = 'actif' AND date_echeance IS NOT NULL AND date_echeance < CURDATE()");
            $s->execute();
            return $s->rowCount();
        } catch (Throwable $e) {
            error_log('[membres_marquer_expires] ' . $e->getMessage());
            return 0;
        }
    }
}

if (!function_exists('membre_statut_libelle')) {
    function membre_statut_libelle(string $statut): array
    {
        return [
            'actif'      => ['Actif', 'success'],
            'expire'     => ['Expiré', 'danger'],
            'en_attente' => ['En attente de paiement', 'warning'],
            'suspendu'   => ['Suspendu', 'secondary'],
        ][$statut] ?? [$statut, 'light'];
    }
}
