<?php
/**
 * Connexion PDO unique du projet (site public, administration, callback FlexPay, outils CLI).
 *
 * - utf8mb4 + collation utf8mb4_unicode_ci imposée à la session : toutes les tables sont en
 *   utf8mb4_unicode_ci ; sans cela MySQL 8 utilise utf8mb4_0900_ai_ci pour les littéraux et
 *   les requêtes UNION / comparaisons échouent (« Illegal mix of collations »).
 * - Fuseau horaire aligné entre PHP et MySQL (Kinshasa, UTC+1, pas d'heure d'été) : les délais
 *   de paiement (3 min, 60 s…) sont calculés de façon cohérente des deux côtés.
 */
require_once __DIR__ . '/../config/database.php';

if (!defined('APP_TIMEZONE')) {
    define('APP_TIMEZONE', 'Africa/Kinshasa');
}
date_default_timezone_set(APP_TIMEZONE);

if (!function_exists('rcr_db_connect')) {
    function rcr_db_connect(): PDO
    {
        $offset = (new DateTimeImmutable('now', new DateTimeZone(APP_TIMEZONE)))->format('P'); // ex. +01:00
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_BOTH, // compatibilité avec l'existant (fetch() indexé + associatif)
                PDO::ATTR_EMULATE_PREPARES   => true,
            ]
        );
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = '" . $offset . "'");
        return $pdo;
    }
}
