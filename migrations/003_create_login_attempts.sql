-- =============================================================
-- Migration 003 : table `login_attempts`
-- =============================================================
-- Contexte : la connexion "Espace Membre" (pages/login.php) passe
-- désormais par le code d'adhésion SEUL (colonne `adhesion.codes`),
-- à la demande explicite du client. Ce code suit un format
-- prévisible (1 lettre + id_ad sur 6 chiffres + 1 caractère + 1
-- lettre — voir adhere/adhesion.funct.php), donc sans protection
-- anti-brute-force, un script pourrait tenter de faire défiler des
-- codes séquentiels jusqu'à tomber sur un compte valide.
--
-- Cette table enregistre les tentatives de connexion échouées par
-- adresse IP et déclenche un verrouillage temporaire après
-- plusieurs échecs rapprochés (voir includes/login_attempts.php).
--
-- À exécuter manuellement (phpMyAdmin / HeidiSQL), comme les
-- migrations précédentes (voir migrations/002_create_dons_table.sql).
-- =============================================================

CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id_att` INT(10) NOT NULL AUTO_INCREMENT,
    `ip_adresse` VARCHAR(45) NOT NULL COMMENT 'IPv4 ou IPv6 du poste ayant échoué',
    `tentatives` INT(10) NOT NULL DEFAULT 1,
    `derniere_tentative` DATETIME NOT NULL,
    `bloque_jusqu_a` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id_att`),
    UNIQUE KEY `uniq_ip_adresse` (`ip_adresse`),
    INDEX `idx_bloque_jusqu_a` (`bloque_jusqu_a`)
) COLLATE='utf8mb3_general_ci' ENGINE=InnoDB;
