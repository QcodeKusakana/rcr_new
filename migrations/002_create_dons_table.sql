-- Migration : création de la table `dons` (paiements publics hors adhésion)
--
-- Contexte : la table `payments` existante est réservée aux paiements liés
-- à une adhésion (`adhesion_id` NOT NULL + clé étrangère vers `adhesion`,
-- voir rcr.sql). Un don public (visiteur non-adhérent, éventuellement
-- anonyme) ne peut pas y être inséré sans violer cette contrainte —
-- d'où cette table dédiée, qui reprend les mêmes conventions que
-- `payments` (status, provider, reference, transaction_id) pour rester
-- cohérente avec le reste du projet.
--
-- À exécuter UNE SEULE FOIS sur la base `rcr` (phpMyAdmin, HeidiSQL, ou
-- ligne de commande MySQL). Sans effet si la table existe déjà
-- (IF NOT EXISTS) : peut être relancée sans risque.

CREATE TABLE IF NOT EXISTS `dons` (
  `id_don` int NOT NULL AUTO_INCREMENT,
  `nom_donateur` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `montant` decimal(10,2) NOT NULL,
  `devise` varchar(10) DEFAULT 'USD',
  `provider` varchar(50) DEFAULT 'FLEXPAY',
  `reference` varchar(100) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `status` enum('pending','paid','failed','cancelled') DEFAULT 'pending',
  `ip_donateur` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_don`),
  KEY `idx_dons_reference` (`reference`),
  KEY `idx_dons_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
