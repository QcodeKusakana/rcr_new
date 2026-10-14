-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mer. 14 oct. 2026 à 15:04
-- Version du serveur : 11.8.9-MariaDB
-- Version de PHP : 8.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `rcrcd_rcr`
--

-- --------------------------------------------------------

--
-- Structure de la table `activite`
--

CREATE TABLE `activite` (
  `id_act` int(11) NOT NULL,
  `titre` text NOT NULL,
  `description` text NOT NULL,
  `photo` varchar(50) NOT NULL,
  `categorie` varchar(50) NOT NULL,
  `id_adm` int(11) NOT NULL,
  `date_pub` datetime NOT NULL,
  `etat_depot` int(11) NOT NULL DEFAULT 0,
  `etat_modifier` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `adhesion`
--

CREATE TABLE `adhesion` (
  `id_ad` int(11) NOT NULL,
  `codes` char(50) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `postnom` varchar(50) NOT NULL,
  `prenom` varchar(200) NOT NULL,
  `mail` varchar(200) NOT NULL,
  `nationalite` varchar(200) NOT NULL,
  `civilite` varchar(200) NOT NULL,
  `sexe` enum('M','F') DEFAULT NULL,
  `id_qt` int(11) DEFAULT NULL,
  `grade` int(11) DEFAULT NULL,
  `codepostal` varchar(200) NOT NULL,
  `secteur` int(11) NOT NULL,
  `territoire` int(11) NOT NULL,
  `province` int(11) NOT NULL,
  `ville` varchar(120) DEFAULT NULL,
  `reglement` int(11) NOT NULL,
  `telephone` varchar(200) NOT NULL,
  `datenaiss` varchar(200) NOT NULL,
  `categorie` varchar(200) NOT NULL,
  `diplome` varchar(200) NOT NULL,
  `passeport` varchar(200) NOT NULL,
  `cv` varchar(200) NOT NULL,
  `adresse` text NOT NULL,
  `dat_adhesion` datetime NOT NULL,
  `reabonner` bigint(20) NOT NULL DEFAULT 1,
  `payment_token` varchar(255) DEFAULT NULL,
  `statut` enum('en_attente','actif','expire','suspendu') NOT NULL DEFAULT 'en_attente',
  `id_cot` int(11) DEFAULT NULL,
  `date_echeance` date DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `derniere_connexion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `adhesion`
--

INSERT INTO `adhesion` (`id_ad`, `codes`, `nom`, `postnom`, `prenom`, `mail`, `nationalite`, `civilite`, `sexe`, `id_qt`, `grade`, `codepostal`, `secteur`, `territoire`, `province`, `ville`, `reglement`, `telephone`, `datenaiss`, `categorie`, `diplome`, `passeport`, `cv`, `adresse`, `dat_adhesion`, `reabonner`, `payment_token`, `statut`, `id_cot`, `date_echeance`, `password_hash`, `derniere_connexion`) VALUES
(3, 'S0000030514K', 'Kusakana', 'Sala', 'Qui-vive', 'qcodeur@gmail.com', 'CD', 'Mr', NULL, 3, 12, '00243', 514, 97, 3, 'Kinshasa', 1, '0816340682', '1992-09-05', '', 'L2', 'Photo_3c857b072e0df6a3b110.jpg', '', 'Avenue Lulonga, N°10, Quartier Ecole, Commune Lemba', '2026-10-08 08:48:49', 1, '0530b2150c7b4f4eca50a6b5ff28f1b68e9f1d6a3cacd774517ca057ba7fd40c', 'actif', 1, '2026-11-08', '$2y$12$D8jmSlwQwD3o0OBi1e2rN.e8Gb63fz5.laXDQ.tkRtI9wqSjMRUli', '2026-10-13 17:52:15');

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--

CREATE TABLE `admin` (
  `id_adm` int(11) NOT NULL,
  `pseudo` varchar(50) NOT NULL,
  `mail` varchar(120) NOT NULL,
  `password` text NOT NULL,
  `fonction` varchar(50) DEFAULT NULL,
  `niveau` int(11) NOT NULL DEFAULT 0,
  `role` varchar(50) NOT NULL DEFAULT 'user',
  `confirmer` int(11) NOT NULL DEFAULT 0,
  `id_role` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `admin`
--

INSERT INTO `admin` (`id_adm`, `pseudo`, `mail`, `password`, `fonction`, `niveau`, `role`, `confirmer`, `id_role`) VALUES
(1, 'Qui-vive', 'qcodeur@gmail.com', '$2y$12$6D8NivEk3QOqSEOKhDf1puVjWu2TSk0N0/tuMlBGBz7ooqW0UFdPC', 'admin_principal', 7, 'user', 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `api_tokens`
--

CREATE TABLE `api_tokens` (
  `id_tok` int(10) UNSIGNED NOT NULL,
  `id_ad` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `appareil` varchar(120) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `cree_le` timestamp NOT NULL DEFAULT current_timestamp(),
  `derniere_utilisation` datetime DEFAULT NULL,
  `expire_le` datetime NOT NULL,
  `revoque` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `api_tokens`
--

INSERT INTO `api_tokens` (`id_tok`, `id_ad`, `token_hash`, `appareil`, `ip`, `cree_le`, `derniere_utilisation`, `expire_le`, `revoque`) VALUES
(3, 3, 'd4734b760a81394b3bd58a3a03b51e623c6f25dcbb4c216e126e9bd8e5f0cfdb', 'android', '206.42.68.41', '2026-10-09 18:18:01', '2026-10-09 19:18:02', '2026-12-08 19:18:02', 0),
(4, 3, '8c47d4585779710430d26a0354d45e7791576fb026db1fc813e63034eda44153', 'android', '41.243.30.24', '2026-10-10 14:23:20', '2026-10-10 15:23:22', '2026-12-09 15:23:22', 1),
(5, 3, '1755b438b62619f71eb9f2459809fbe0ba40b0f9fa5ebfe9e5c81a65bcbcf2e5', 'android', '41.243.30.24', '2026-10-10 14:25:34', '2026-10-10 15:25:35', '2026-12-09 15:25:35', 0);

-- --------------------------------------------------------

--
-- Structure de la table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_adm` int(11) DEFAULT NULL,
  `action` varchar(60) NOT NULL,
  `cible` varchar(80) NOT NULL DEFAULT '',
  `cible_id` varchar(60) NOT NULL DEFAULT '',
  `detail` text DEFAULT NULL,
  `ip` varchar(45) NOT NULL DEFAULT '',
  `cree_le` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `id_adm`, `action`, `cible`, `cible_id`, `detail`, `ip`, `cree_le`) VALUES
(1, NULL, 'admin.connexion_echec', 'admin', '', 'pseudo inconnu', '169.159.220.46', '2026-10-07 18:44:32'),
(2, NULL, 'admin.connexion_echec', 'admin', '', 'pseudo inconnu', '169.159.220.46', '2026-10-07 18:44:36'),
(3, NULL, 'admin.connexion_echec', 'admin', '', 'pseudo inconnu', '169.159.220.46', '2026-10-07 18:44:39'),
(4, NULL, 'admin.connexion_echec', 'admin', '', 'pseudo inconnu', '169.159.212.46', '2026-10-07 18:45:03'),
(5, NULL, 'admin.connexion_echec', 'admin', '', 'pseudo inconnu', '169.159.212.46', '2026-10-07 18:45:13'),
(6, NULL, 'admin.connexion_echec', 'admin', '', 'pseudo inconnu', '169.159.212.46', '2026-10-07 18:45:16'),
(7, NULL, 'admin.connexion_echec', 'admin', '', 'pseudo inconnu', '169.159.212.46', '2026-10-07 18:45:20'),
(8, 1, 'admin.connexion', 'admin', '1', NULL, '169.159.212.46', '2026-10-07 23:17:39'),
(9, 1, 'admin.connexion', 'admin', '1', NULL, '169.159.212.40', '2026-10-08 07:43:16'),
(10, 1, 'admin.connexion', 'admin', '1', NULL, '169.159.212.40', '2026-10-08 09:45:29'),
(11, 1, 'contenu.modifier', 'site_blocs', 'home/notre_maitre/fr', NULL, '169.159.212.40', '2026-10-08 09:48:53'),
(12, 1, 'contenu.modifier', 'site_blocs', 'home/notre_maitre/fr', NULL, '169.159.212.40', '2026-10-08 09:49:05'),
(13, 1, 'reglage.modifier', 'site_reglages', 'home_soustitre/fr', NULL, '169.159.220.40', '2026-10-08 09:54:54'),
(14, 1, 'reglage.modifier', 'site_reglages', 'home_surtitre/fr', NULL, '169.159.220.40', '2026-10-08 09:54:54'),
(15, 1, 'reglage.modifier', 'site_reglages', 'home_titre/fr', NULL, '169.159.220.40', '2026-10-08 09:54:54'),
(16, 1, 'reglage.modifier', 'site_reglages', 'contact_adresse_complete/fr', NULL, '169.159.220.40', '2026-10-08 09:54:54'),
(17, 1, 'admin.connexion', 'admin', '1', NULL, '196.250.93.143', '2026-10-09 09:07:29'),
(18, 1, 'tarif.modifier', 'grades', '12', '{\"ancien\":{\"prix\":\"1.00\",\"actif\":1},\"nouveau\":{\"prix\":5,\"actif\":true}}', '196.250.93.143', '2026-10-09 09:07:54'),
(19, 1, 'admin.session_expiree', 'admin', '1', NULL, '185.132.178.137', '2026-10-09 10:14:41'),
(20, 1, 'admin.connexion', 'admin', '1', NULL, '185.132.178.137', '2026-10-09 10:14:51'),
(21, 1, 'admin.connexion', 'admin', '1', NULL, '41.243.252.201', '2026-10-10 19:48:57'),
(22, 1, 'territoire.bloquer', 'territoires', '83', NULL, '41.243.252.201', '2026-10-10 19:53:59'),
(23, 1, 'export.cotisations', 'payments', '', '{\"lignes\":1}', '41.243.252.201', '2026-10-10 19:55:07'),
(24, 1, 'admin.connexion', 'admin', '1', NULL, '41.243.76.158', '2026-10-13 16:53:32');

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

CREATE TABLE `categorie` (
  `id_cat` int(11) NOT NULL,
  `nom_cat` varchar(100) NOT NULL,
  `resume` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commentaire`
--

CREATE TABLE `commentaire` (
  `id_cmt` int(11) NOT NULL,
  `id_act` int(11) NOT NULL,
  `pseudo` varchar(60) NOT NULL DEFAULT '',
  `commentaire` text DEFAULT NULL,
  `date_pub` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cotisation`
--

CREATE TABLE `cotisation` (
  `id_cot` int(11) NOT NULL,
  `nom_cot` varchar(50) DEFAULT NULL,
  `jours` int(11) DEFAULT NULL,
  `prix` decimal(10,2) NOT NULL DEFAULT 0.00,
  `mois` int(11) NOT NULL DEFAULT 1,
  `actif` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `cotisation`
--

INSERT INTO `cotisation` (`id_cot`, `nom_cot`, `jours`, `prix`, `mois`, `actif`) VALUES
(1, 'Mensuelle', 30, 0.00, 1, 1),
(2, 'Trimestrielle', 90, 0.00, 3, 1),
(3, 'Semestrielle', 180, 0.00, 6, 1),
(4, 'Annuelle', 365, 0.00, 12, 1);

-- --------------------------------------------------------

--
-- Structure de la table `dislike`
--

CREATE TABLE `dislike` (
  `id_dislike` int(11) NOT NULL,
  `id_act` int(11) NOT NULL,
  `ip` varchar(45) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `dons`
--

CREATE TABLE `dons` (
  `id_don` int(11) NOT NULL,
  `nom_donateur` varchar(100) DEFAULT NULL,
  `postnom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `nationalite` varchar(100) DEFAULT NULL,
  `sexe` enum('M','F') DEFAULT NULL,
  `province` int(11) DEFAULT NULL,
  `territoire` int(11) DEFAULT NULL,
  `id_province` int(11) DEFAULT NULL,
  `ville` varchar(120) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `code_membre` varchar(50) DEFAULT NULL,
  `telephone` varchar(20) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `montant` decimal(10,2) NOT NULL,
  `devise` varchar(10) DEFAULT 'USD',
  `provider` varchar(50) DEFAULT 'FLEXPAY',
  `reference` varchar(100) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `status` enum('pending','processing','paid','failed','cancelled','expired') NOT NULL DEFAULT 'pending',
  `ip_donateur` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `type_don` enum('ponctuel','regulier') NOT NULL DEFAULT 'ponctuel',
  `frequence` enum('mensuel','trimestriel','semestriel','annuel') DEFAULT NULL,
  `id_ad` int(11) DEFAULT NULL,
  `canal` enum('mobile_money','carte') NOT NULL DEFAULT 'mobile_money',
  `order_number` varchar(80) DEFAULT NULL,
  `provider_reference` varchar(80) DEFAULT NULL,
  `verifie_le` datetime DEFAULT NULL,
  `maj_le` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `prochaine_echeance` date DEFAULT NULL,
  `flexpay_status` varchar(20) DEFAULT NULL,
  `derniere_verif` datetime DEFAULT NULL,
  `nb_verifs` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `dernier_callback` datetime DEFAULT NULL,
  `derniere_erreur` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `dons`
--

INSERT INTO `dons` (`id_don`, `nom_donateur`, `postnom`, `prenom`, `nationalite`, `sexe`, `province`, `territoire`, `id_province`, `ville`, `adresse`, `code_membre`, `telephone`, `email`, `montant`, `devise`, `provider`, `reference`, `transaction_id`, `status`, `ip_donateur`, `created_at`, `type_don`, `frequence`, `id_ad`, `canal`, `order_number`, `provider_reference`, `verifie_le`, `maj_le`, `prochaine_echeance`, `flexpay_status`, `derniere_verif`, `nb_verifs`, `dernier_callback`, `derniere_erreur`) VALUES
(2, 'Kusakana', 'Sala', 'Qui-vive', NULL, NULL, NULL, NULL, NULL, '', '', 'S0000030514K', '243816340682', 'qcodeur@gmail.com', 5.00, 'USD', 'FLEXPAY', 'RCR-D-261009-1840D4E084', 'M6hqPawi3589798192449816', 'processing', '206.42.68.41', '2026-10-09 18:19:59', 'ponctuel', NULL, 3, 'carte', 'M6hqPawi3589798192449816', NULL, '2026-10-09 19:21:17', '2026-10-09 18:21:17', NULL, '4', '2026-10-09 19:21:17', 6, '2026-10-09 19:20:46', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `encadreur`
--

CREATE TABLE `encadreur` (
  `id_en` int(11) NOT NULL,
  `id_exp` int(11) DEFAULT NULL,
  `id_dest` int(11) DEFAULT NULL,
  `placement` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `equipes`
--

CREATE TABLE `equipes` (
  `id_eq` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `telephone` varchar(30) NOT NULL DEFAULT '',
  `mail` varchar(50) NOT NULL DEFAULT '',
  `function` varchar(130) NOT NULL,
  `resume` text NOT NULL,
  `photo` text NOT NULL,
  `valid` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `grades`
--

CREATE TABLE `grades` (
  `id_gd` int(11) NOT NULL,
  `nom_gd` varchar(50) DEFAULT NULL,
  `prix` decimal(10,2) NOT NULL DEFAULT 0.00,
  `id_qt` int(11) DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `ordre` int(11) NOT NULL DEFAULT 0,
  `ancien` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `grades`
--

INSERT INTO `grades` (`id_gd`, `nom_gd`, `prix`, `id_qt`, `actif`, `ordre`, `ancien`) VALUES
(1, 'Diamant Puissant', 200.00, 1, 1, 1, 0),
(2, 'Super Diamant', 100.00, 1, 1, 2, 0),
(3, 'Diamant Force', 50.00, 1, 1, 3, 0),
(4, 'Diamant', 25.00, 1, 1, 4, 0),
(5, 'Super Diamant', 100.00, 2, 1, 1, 0),
(6, 'Diamant Force', 50.00, 2, 1, 2, 0),
(7, 'Diamant', 25.00, 2, 1, 3, 0),
(8, 'Platine', 20.00, 2, 1, 4, 0),
(9, 'Diamant', 25.00, 3, 1, 1, 0),
(10, 'Platine', 20.00, 3, 1, 2, 0),
(11, 'Or', 10.00, 3, 1, 3, 0),
(12, 'Argent', 5.00, 3, 1, 4, 0);

-- --------------------------------------------------------

--
-- Structure de la table `grades_historique`
--

CREATE TABLE `grades_historique` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_gd` int(11) NOT NULL,
  `ancien_prix` decimal(10,2) DEFAULT NULL,
  `nouveau_prix` decimal(10,2) DEFAULT NULL,
  `ancien_actif` tinyint(1) DEFAULT NULL,
  `nouveau_actif` tinyint(1) DEFAULT NULL,
  `id_adm` int(11) DEFAULT NULL,
  `cree_le` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `grades_historique`
--

INSERT INTO `grades_historique` (`id`, `id_gd`, `ancien_prix`, `nouveau_prix`, `ancien_actif`, `nouveau_actif`, `id_adm`, `cree_le`) VALUES
(1, 12, 1.00, 5.00, 1, 1, 1, '2026-10-09 09:07:54');

-- --------------------------------------------------------

--
-- Structure de la table `historiquepaiement`
--

CREATE TABLE `historiquepaiement` (
  `id_ht` int(11) NOT NULL,
  `id_ad` int(11) DEFAULT NULL,
  `id_qt` int(11) DEFAULT NULL,
  `dat` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `horsline`
--

CREATE TABLE `horsline` (
  `id_hor` int(11) NOT NULL,
  `ip_user` varchar(50) DEFAULT NULL,
  `temps` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `horsline`
--

INSERT INTO `horsline` (`id_hor`, `ip_user`, `temps`) VALUES
(541, '114.119.157.178', 1791986219);

-- --------------------------------------------------------

--
-- Structure de la table `likes`
--

CREATE TABLE `likes` (
  `id_like` int(11) NOT NULL,
  `id_act` int(11) NOT NULL,
  `ip` varchar(45) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id_att` int(11) NOT NULL,
  `ip_adresse` varchar(45) NOT NULL COMMENT 'IPv4 ou IPv6 du poste ayant échoué',
  `tentatives` int(11) NOT NULL DEFAULT 1,
  `derniere_tentative` datetime NOT NULL,
  `bloque_jusqu_a` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `login_attempts`
--

INSERT INTO `login_attempts` (`id_att`, `ip_adresse`, `tentatives`, `derniere_tentative`, `bloque_jusqu_a`) VALUES
(1, 'maa0e1a3e2485d6bd6ff6a454d139fe025a25bb73', 2, '2026-10-07 19:43:09', NULL),
(2, '169.159.220.46', 3, '2026-10-07 19:44:39', NULL),
(4, 'a5e3a8d5587b909e76e564471afea9cbd9278196f', 1, '2026-10-08 00:38:39', NULL),
(7, '196.250.93.143', 1, '2026-10-14 13:00:31', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `messages_contact`
--

CREATE TABLE `messages_contact` (
  `id` int(10) UNSIGNED NOT NULL,
  `nom` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `objet` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `ip` varchar(45) NOT NULL DEFAULT '',
  `statut` enum('nouveau','lu','traite','archive') NOT NULL DEFAULT 'nouveau',
  `traite_par` int(11) DEFAULT NULL,
  `cree_le` timestamp NOT NULL DEFAULT current_timestamp(),
  `maj_le` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `messages_contact`
--

INSERT INTO `messages_contact` (`id`, `nom`, `email`, `objet`, `message`, `ip`, `statut`, `traite_par`, `cree_le`, `maj_le`) VALUES
(1, 'RobertSkeld', 'ninadvhegmannjfc33@gmail.com', 'Hi  i am writing about   the price for reseller', 'Hi, I wanted to know your price.', '77.90.185.5', 'nouveau', NULL, '2026-10-14 12:19:16', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `evenement` varchar(40) NOT NULL,
  `canal` enum('interne','email','sms') NOT NULL,
  `id_ad` int(11) DEFAULT NULL,
  `destinataire` varchar(200) NOT NULL,
  `donnees` text DEFAULT NULL,
  `cle_unique` varchar(120) DEFAULT NULL,
  `statut` enum('pending','sent','failed','read') NOT NULL DEFAULT 'pending',
  `cree_le` timestamp NOT NULL DEFAULT current_timestamp(),
  `envoye_le` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`id`, `evenement`, `canal`, `id_ad`, `destinataire`, `donnees`, `cle_unique`, `statut`, `cree_le`, `envoye_le`) VALUES
(1, 'adhesion_confirmee', 'interne', 3, '3', '{\"reference\":\"RCR-A-261008-96845C01EA\",\"montant\":\"1.00\",\"devise\":\"USD\"}', 'pay:RCR-A-261008-96845C01EA:i', 'pending', '2026-10-08 07:50:21', NULL),
(2, 'adhesion_confirmee', 'email', 3, 'qcodeur@gmail.com', '{\"reference\":\"RCR-A-261008-96845C01EA\",\"montant\":\"1.00\",\"devise\":\"USD\"}', 'pay:RCR-A-261008-96845C01EA:e', 'pending', '2026-10-08 07:50:21', NULL),
(3, 'adhesion_confirmee', 'sms', 3, '0816340682', '{\"reference\":\"RCR-A-261008-96845C01EA\",\"montant\":\"1.00\",\"devise\":\"USD\"}', 'pay:RCR-A-261008-96845C01EA:s', 'pending', '2026-10-08 07:50:21', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `onlines`
--

CREATE TABLE `onlines` (
  `id_on` int(11) NOT NULL,
  `id_usr` int(11) NOT NULL,
  `temps` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `onlines`
--

INSERT INTO `onlines` (`id_on`, `id_usr`, `temps`) VALUES
(1, 3, 1791569023);

-- --------------------------------------------------------

--
-- Structure de la table `operateurs`
--

CREATE TABLE `operateurs` (
  `id_op` int(11) NOT NULL,
  `nom_op` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `parner`
--

CREATE TABLE `parner` (
  `id_parn` int(11) NOT NULL,
  `id_exp` int(11) DEFAULT NULL,
  `id_dest` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `partenaire`
--

CREATE TABLE `partenaire` (
  `id_part` int(11) NOT NULL,
  `nom_part` varchar(50) NOT NULL,
  `photo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_ad` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expire_le` datetime NOT NULL,
  `utilise_le` datetime DEFAULT NULL,
  `ip` varchar(45) NOT NULL DEFAULT '',
  `cree_le` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `adhesion_id` int(11) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `reference_payment` varchar(255) DEFAULT NULL,
  `telephone_py` varchar(20) DEFAULT NULL,
  `montant` decimal(10,2) NOT NULL,
  `devise` varchar(10) DEFAULT 'USD',
  `provider` varchar(50) DEFAULT 'FLEXPAY',
  `status` enum('pending','processing','paid','failed','cancelled','expired') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `id_ad` int(11) DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `codes_ad` varchar(50) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `trans_keys` tinyint(1) NOT NULL DEFAULT 0,
  `type_transaction` enum('adhesion','cotisation') NOT NULL DEFAULT 'adhesion',
  `id_cot` int(11) DEFAULT NULL,
  `canal` enum('mobile_money','carte') NOT NULL DEFAULT 'mobile_money',
  `order_number` varchar(80) DEFAULT NULL,
  `provider_reference` varchar(80) DEFAULT NULL,
  `periode_debut` date DEFAULT NULL,
  `periode_fin` date DEFAULT NULL,
  `verifie_le` datetime DEFAULT NULL,
  `maj_le` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `flexpay_status` varchar(20) DEFAULT NULL,
  `derniere_verif` datetime DEFAULT NULL,
  `nb_verifs` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `dernier_callback` datetime DEFAULT NULL,
  `derniere_erreur` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `payments`
--

INSERT INTO `payments` (`id`, `adhesion_id`, `transaction_id`, `reference_payment`, `telephone_py`, `montant`, `devise`, `provider`, `status`, `created_at`, `id_ad`, `reference`, `codes_ad`, `currency`, `trans_keys`, `type_transaction`, `id_cot`, `canal`, `order_number`, `provider_reference`, `periode_debut`, `periode_fin`, `verifie_le`, `maj_le`, `flexpay_status`, `derniere_verif`, `nb_verifs`, `dernier_callback`, `derniere_erreur`) VALUES
(4, 3, 'Uld8jnzVCsbD243812807254', 'RCR-A-261008-96845C01EA', '243812807254', 1.00, 'USD', 'FLEXPAY', 'paid', '2026-10-08 07:50:02', 3, 'RCR-A-261008-96845C01EA', 'S0000030514K', NULL, 1, 'adhesion', 1, 'mobile_money', 'Uld8jnzVCsbD243812807254', NULL, '2026-10-08', '2026-11-08', '2026-10-08 08:50:21', '2026-10-08 07:50:31', '0', '2026-10-08 08:50:21', 3, '2026-10-08 08:50:31', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `payment_logs`
--

CREATE TABLE `payment_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type_transaction` enum('adhesion','cotisation','don') NOT NULL,
  `ref_id` int(10) UNSIGNED DEFAULT NULL,
  `reference` varchar(100) NOT NULL DEFAULT '',
  `evenement` varchar(40) NOT NULL,
  `statut_avant` varchar(20) DEFAULT NULL,
  `statut_apres` varchar(20) DEFAULT NULL,
  `payload` text DEFAULT NULL,
  `ip` varchar(45) NOT NULL DEFAULT '',
  `cree_le` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `payment_logs`
--

INSERT INTO `payment_logs` (`id`, `type_transaction`, `ref_id`, `reference`, `evenement`, `statut_avant`, `statut_apres`, `payload`, `ip`, `cree_le`) VALUES
(45, 'adhesion', 4, 'RCR-A-261008-96845C01EA', 'created', NULL, 'pending', '{\"canal\":\"mobile_money\",\"montant\":1}', '169.159.212.40', '2026-10-08 07:50:02'),
(46, 'adhesion', 4, 'RCR-A-261008-96845C01EA', 'check', 'processing', NULL, '{\"state\":\"PENDING\",\"flexpay_status\":\"2\",\"http\":200,\"error\":\"\"}', '169.159.212.40', '2026-10-08 07:50:10'),
(47, 'adhesion', 4, 'RCR-A-261008-96845C01EA', 'check', 'processing', NULL, '{\"state\":\"PENDING\",\"flexpay_status\":\"2\",\"http\":200,\"error\":\"\"}', '169.159.212.40', '2026-10-08 07:50:15'),
(48, 'adhesion', 4, 'RCR-A-261008-96845C01EA', 'check', 'processing', NULL, '{\"state\":\"PAID\",\"flexpay_status\":\"0\",\"http\":200,\"error\":\"\"}', '169.159.212.40', '2026-10-08 07:50:21'),
(49, 'adhesion', 4, 'RCR-A-261008-96845C01EA', 'status_change', 'processing', 'paid', '{\"verified\":true}', '169.159.212.40', '2026-10-08 07:50:21'),
(50, 'adhesion', 4, 'RCR-A-261008-96845C01EA', 'callback_received', 'paid', NULL, '{\"code\": \"0\", \"reference\": \"RCR-A-261008-96845C01EA\", \"amount\": \"1.0\", \"amountCustomer\": \"1.02\", \"phone\": \"243812807254\", \"currency\": \"USD\", \"createdAt\": \"08-10-2026 08:50:03\", \"channel\": \"mpesa\", \"orderNumber\": \"Uld8jnzVCsbD243812807254\", \"provider_reference\": \"5jLsuSR6gXOsXn60lni1NyKAlsORfRIx\"}', '156.0.198.27', '2026-10-08 07:50:31'),
(51, 'don', 2, 'RCR-D-261009-1840D4E084', 'created', NULL, 'pending', '{\"montant\":5,\"type\":\"ponctuel\",\"canal\":\"carte\",\"source\":\"app\"}', '206.42.68.41', '2026-10-09 18:19:59'),
(52, 'don', 2, 'RCR-D-261009-1840D4E084', 'callback_received', 'processing', NULL, '{\"code\": \"1\", \"reference\": \"RCR-D-261009-1840D4E084\", \"amount\": \"5.0\", \"amountCustomer\": \"5.13\", \"phone\": \"null\", \"currency\": \"USD\", \"createdAt\": \"09-10-2026 19:20:00\", \"channel\": \"visa\", \"orderNumber\": \"M6hqPawi3589798192449816\", \"provider_reference\": \"\"}', '156.0.198.27', '2026-10-09 18:20:46'),
(53, 'don', 2, 'RCR-D-261009-1840D4E084', 'check', 'processing', NULL, '{\"state\":\"PENDING\",\"flexpay_status\":\"4\",\"http\":200,\"error\":\"\"}', '156.0.198.27', '2026-10-09 18:20:47'),
(54, 'don', 2, 'RCR-D-261009-1840D4E084', 'check', 'processing', NULL, '{\"state\":\"PENDING\",\"flexpay_status\":\"4\",\"http\":200,\"error\":\"\"}', '206.42.68.41', '2026-10-09 18:21:01'),
(55, 'don', 2, 'RCR-D-261009-1840D4E084', 'check', 'processing', NULL, '{\"state\":\"PENDING\",\"flexpay_status\":\"4\",\"http\":200,\"error\":\"\"}', '206.42.68.41', '2026-10-09 18:21:05'),
(56, 'don', 2, 'RCR-D-261009-1840D4E084', 'check', 'processing', NULL, '{\"state\":\"PENDING\",\"flexpay_status\":\"4\",\"http\":200,\"error\":\"\"}', '206.42.68.41', '2026-10-09 18:21:09'),
(57, 'don', 2, 'RCR-D-261009-1840D4E084', 'check', 'processing', NULL, '{\"state\":\"PENDING\",\"flexpay_status\":\"4\",\"http\":200,\"error\":\"\"}', '206.42.68.41', '2026-10-09 18:21:13'),
(58, 'don', 2, 'RCR-D-261009-1840D4E084', 'check', 'processing', NULL, '{\"state\":\"PENDING\",\"flexpay_status\":\"4\",\"http\":200,\"error\":\"\"}', '206.42.68.41', '2026-10-09 18:21:17');

-- --------------------------------------------------------

--
-- Structure de la table `permissions`
--

CREATE TABLE `permissions` (
  `id_perm` int(10) UNSIGNED NOT NULL,
  `code` varchar(60) NOT NULL,
  `libelle` varchar(160) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `permissions`
--

INSERT INTO `permissions` (`id_perm`, `code`, `libelle`) VALUES
(1, 'dashboard.voir', 'Voir le tableau de bord'),
(2, 'contenu.gerer', 'Gérer les pages, textes, actualités, événements'),
(3, 'media.gerer', 'Gérer les médias'),
(4, 'membres.voir', 'Consulter les membres'),
(5, 'membres.gerer', 'Créer / modifier les membres et adhésions'),
(6, 'geo.gerer', 'Gérer provinces, territoires, secteurs'),
(7, 'tarifs.gerer', 'Modifier les tarifs de cotisation'),
(8, 'paiements.voir', 'Voir cotisations, dons et paiements'),
(9, 'paiements.valider', 'Valider / corriger manuellement un paiement'),
(10, 'admins.gerer', 'Gérer les administrateurs et les rôles'),
(11, 'systeme.gerer', 'Paramètres techniques, sécurité, sauvegardes'),
(12, 'logs.voir', 'Consulter les journaux');

-- --------------------------------------------------------

--
-- Structure de la table `provinces`
--

CREATE TABLE `provinces` (
  `id_p` int(11) NOT NULL,
  `nom_p` varchar(50) DEFAULT NULL,
  `lettre` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `provinces`
--

INSERT INTO `provinces` (`id_p`, `nom_p`, `lettre`) VALUES
(1, 'Kinshasa', 'KIN'),
(2, 'Kongo Central', 'KOC'),
(3, 'Kwango', 'KWA'),
(4, 'Kwilu', 'KWL'),
(5, 'Mai-Ndombe', 'MND'),
(6, 'Kasaï', 'KAS'),
(7, 'Kasaï Central', 'KSC'),
(8, 'Kasaï Oriental', 'KSO'),
(9, 'Lomami', 'LOM'),
(10, 'Sankuru', 'SAN'),
(11, 'Maniema', 'MAN'),
(12, 'Sud-Kivu', 'SKV'),
(13, 'Nord-Kivu', 'NKV'),
(14, 'Ituri', 'ITU'),
(15, 'Haut-Uele', 'HUE'),
(16, 'Bas-Uele', 'BUE'),
(17, 'Tshopo', 'TSH'),
(18, 'Mongala', 'MON'),
(19, 'Nord-Ubangi', 'NUB'),
(20, 'Sud-Ubangi', 'SUB'),
(21, 'Équateur', 'EQU'),
(22, 'Tshuapa', 'TSU'),
(23, 'Haut-Lomami', 'HLO'),
(24, 'Lualaba', 'LUA'),
(25, 'Haut-Katanga', 'HKA'),
(26, 'Tanganyika', 'TAN');

-- --------------------------------------------------------

--
-- Structure de la table `qualites`
--

CREATE TABLE `qualites` (
  `id_qt` int(11) NOT NULL,
  `designation` varchar(50) DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `qualites`
--

INSERT INTO `qualites` (`id_qt`, `designation`, `prix`) VALUES
(1, 'Fondateur', 0.00),
(2, 'Effectif', 0.00),
(3, 'Sympathisant', 0.00);

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE `roles` (
  `id_role` int(10) UNSIGNED NOT NULL,
  `code` varchar(40) NOT NULL,
  `libelle` varchar(120) NOT NULL,
  `fonction` varchar(160) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `roles`
--

INSERT INTO `roles` (`id_role`, `code`, `libelle`, `fonction`) VALUES
(1, 'admin_principal', 'Administrateur principal', 'Président du Rassemblement'),
(2, 'resp_publications', 'Responsable publications', 'Rapporteur Général'),
(3, 'gest_effectifs', 'Gestionnaire des effectifs', 'Secrétaire Exécutif Spécial chargé des Affaires Intérieures'),
(4, 'resp_numerique', 'Responsable numérique', 'Secrétaire Permanent chargé du Numérique');

-- --------------------------------------------------------

--
-- Structure de la table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id_role` int(10) UNSIGNED NOT NULL,
  `id_perm` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `role_permissions`
--

INSERT INTO `role_permissions` (`id_role`, `id_perm`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(2, 1),
(2, 2),
(2, 3),
(3, 1),
(3, 4),
(3, 5),
(3, 6),
(3, 8),
(4, 1),
(4, 7),
(4, 8),
(4, 10),
(4, 11),
(4, 12);

-- --------------------------------------------------------

--
-- Structure de la table `secteurs`
--

CREATE TABLE `secteurs` (
  `id_sec` int(11) NOT NULL,
  `nom_sec` varchar(50) DEFAULT NULL,
  `id_tr` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `secteurs`
--

INSERT INTO `secteurs` (`id_sec`, `nom_sec`, `id_tr`) VALUES
(185, 'Gombe-Sud', 89),
(186, 'Kivulu', 89),
(187, 'Avuru-Duma', 158),
(188, 'Avuru-Gatanga', 158),
(189, 'Bondongola', 158),
(190, 'Mobati-Boyele', 158),
(191, 'Kolongwandi', 158),
(192, 'Mabinza', 158),
(193, 'Ngbongi', 158),
(194, 'Yoko', 158),
(195, 'Ezo', 199),
(196, 'Mopoyi', 199),
(197, 'Ngindo', 199),
(198, 'Sasa', 199),
(199, 'Bakete', 200),
(200, 'Bokapo', 200),
(201, 'Bokiba', 200),
(202, 'Bolungwa', 200),
(203, 'Makere I', 200),
(204, 'Makere II', 200),
(205, 'Makere-Bakete', 200),
(206, 'Mange', 200),
(207, 'Mondongwale', 200),
(208, 'Bosso', 159),
(209, 'Deni', 159),
(210, 'Duaru', 159),
(211, 'Gama', 159),
(212, 'Gaya', 159),
(213, 'Gbiamange', 159),
(214, 'Goa', 159),
(215, 'Kassa', 159),
(216, 'Mobenge-Mondila', 159),
(217, 'Soa', 159),
(218, 'Bayeu-Bogongea', 157),
(219, 'Bayeu-Bogbama', 157),
(220, 'Mobati', 157),
(221, 'Monganzulu', 157),
(222, 'Nguru', 157),
(223, 'Barissi', 157),
(224, 'Mongingita-Bakango', 157),
(225, 'Babena', 160),
(226, 'Bakengaie', 160),
(227, 'Gamu', 160),
(228, 'Kipate', 160),
(229, 'Kumendeni', 160),
(230, 'Mabanga', 160),
(231, 'Madi', 160),
(232, 'Malele', 160),
(233, 'Ngbaradi', 160),
(234, 'Soronga', 160),
(235, 'Zune', 160),
(236, 'Abarambo', 160),
(237, 'Kembisa', 160),
(238, 'Basankusu', 201),
(239, 'Gombalo', 201),
(240, 'Waka Bokeka', 201),
(241, 'Ekonda', 202),
(242, 'Elanga', 202),
(243, 'Lac Ntomba', 202),
(244, 'Dianga', 177),
(245, 'Bolomba', 177),
(246, 'Busira', 177),
(247, 'Losangania', 177),
(248, 'Mampoko', 177),
(249, 'Dzamba', 204),
(250, 'Ngiri', 204),
(251, 'Bokatola', 205),
(252, 'Duali', 205),
(253, 'Eungu', 205),
(254, 'Banunu Bobangi', 206),
(255, 'Lusankani', 206),
(256, 'Mpama', 206),
(257, 'Bangala', 207),
(258, 'Mweko', 207),
(259, 'Ndobo', 207),
(260, 'Basanga', 192),
(261, 'Lufira', 192),
(262, 'Source du Congo', 192),
(263, 'Bakunda', 193),
(264, 'Kafira', 193),
(265, 'Kisamamba', 193),
(266, 'Luapula', 193),
(267, 'Kaponda', 208),
(268, 'Kinama', 208),
(269, 'Bukanda', 208),
(270, 'Kiona-Ngoy', 194),
(271, 'Balomotwa', 194),
(272, 'Banweshi', 194),
(273, 'Kiona-Nzini', 209),
(274, 'Mpweto', 209),
(275, 'Mwenge', 209),
(276, 'Moero', 209),
(277, 'Balala', 210),
(278, 'Balamba', 210),
(279, 'Baushi', 210),
(280, 'Butumba', 185),
(281, 'Kabondo-Dianda', 185),
(282, 'Kapamay', 185),
(283, 'Kibanda', 185),
(284, 'Kinkondja', 185),
(285, 'Umpungu', 185),
(286, 'Lualaba', 185),
(287, 'Kabongo', 183),
(288, 'Kayamba', 183),
(289, 'Nord-Baluba', 183),
(290, 'Kasongo-Nyembo', 182),
(291, 'Kinda', 182),
(292, 'Mutombo Mukulu', 211),
(293, 'Kayumba Nkulu', 184),
(294, 'Mulongo', 184),
(295, 'Museka', 184),
(296, 'Nkulu', 184),
(297, 'Badia', 184),
(298, 'Mwanza', 184),
(299, 'Malingindu', 155),
(300, 'Ndolomo', 155),
(301, 'Wando', 155),
(302, 'Dongo', 212),
(303, 'Kakwa', 212),
(304, 'Logo-Bagela', 212),
(305, 'Logo-Doka', 212),
(306, 'Logo-Lolia', 212),
(307, 'Logo- Obeleba', 212),
(308, 'Logo-Ogambi', 212),
(309, 'Mondo', 212),
(310, 'Boime', 156),
(311, 'Kereboro', 156),
(312, 'Kopa', 156),
(313, 'Mangbele', 156),
(314, 'Mangbetu', 156),
(315, 'Manziga', 156),
(316, 'Okondo', 156),
(317, 'Azanga', 155),
(318, 'Mayogo-Mabozo', 155),
(319, 'Mayogo-Magbay', 155),
(320, 'Mboli', 155),
(321, 'Medje-Mango', 155),
(322, 'Mongo-Masi', 155),
(323, 'Ndey', 155),
(324, 'Bafwagada', 154),
(325, 'Bafwakoy', 154),
(326, 'Balika-Toriko', 154),
(327, 'Mahaa', 154),
(328, 'Makoda', 154),
(329, 'Malamba', 154),
(330, 'Malika-Ateru', 154),
(331, 'Mangbele', 154),
(332, 'Timoniko', 154),
(333, 'Wadimbisa', 154),
(334, 'Mabudu Malika Babyeru (Mmb)', 154),
(335, 'Andikofa', 213),
(336, 'Andobi', 213),
(337, 'Ateru', 213),
(338, 'Kebo', 213),
(339, 'Mari-Minza', 213),
(340, 'Walese', 213),
(341, 'Gombari', 213),
(342, 'Kibali', 213),
(343, 'Mangbutu', 213),
(344, 'Aluru', 149),
(345, 'Kakwa', 149),
(346, 'Kaliko-Omi', 149),
(347, 'Lu', 149),
(348, 'Nio-Kamule', 149),
(349, 'Otso', 149),
(350, 'Zaki', 149),
(351, 'Ndo', 149),
(352, 'Bahema Baguru', 152),
(353, 'Bahema-Badjere', 152),
(354, 'Bahema-Banywagi', 152),
(355, 'Bahema-Nord', 152),
(356, 'Mabendi', 152),
(357, 'Mambisa', 152),
(358, 'Ndo-Okebo', 152),
(359, 'Banyali-Kilo', 152),
(360, 'Walendu-Djatsi', 152),
(361, 'Walendu-Pitsi', 152),
(362, 'Walendu-Tatsi', 152),
(363, 'Andisoma', 151),
(364, 'Babelebe', 151),
(365, 'Baboa-Bokoe', 151),
(366, 'Bahema d’Irumu', 151),
(367, 'Bahema Mitego', 151),
(368, 'Bahema-Boga', 151),
(369, 'Banyari-Tchabi', 151),
(370, 'Basili', 151),
(371, 'Mobala', 151),
(372, 'Walendu Bindi', 151),
(373, 'Walese Vonkutu', 151),
(374, 'Bahema Sud', 151),
(375, 'Alur-Djuganda', 150),
(376, 'Anghal', 150),
(377, 'Djukoth', 150),
(378, 'Mokambu', 150),
(379, 'Panduru', 150),
(380, 'Wagongo', 150),
(381, 'Walendu-Watsi', 150),
(382, 'War-Palara', 150),
(383, 'Babila Babombi', 214),
(384, 'Babila-Bakwanza', 214),
(385, 'Bandaka', 214),
(386, 'Bombo-Bagumba', 214),
(387, 'Mambasa', 214),
(388, 'Walese-Dese', 214),
(389, 'Walese-Karo', 214),
(390, 'Dekese-Ikolombe-Isolu', 115),
(391, 'Yaelima', 115),
(392, 'Basongo (Entre Lubi-Konduye)', 114),
(393, 'Ell Mapangu', 114),
(394, 'Malu Malu', 114),
(395, 'Sud-Banga', 114),
(396, 'Luebo-Kabambaie', 113),
(397, 'Luebo-Lulengele', 113),
(398, 'Luebo-Wedi', 113),
(399, 'Ndjoko-Punda', 113),
(400, 'Bakuba', 215),
(401, 'Bakwa-Nyambi', 216),
(402, 'Bapende', 216),
(403, 'Kasadisadi', 216),
(404, 'Kasai-Kabambaie', 216),
(405, 'Kasai-Lunyeka', 216),
(406, 'Kasai-Longatshimo', 216),
(407, 'Lovua-Longatshimo', 216),
(408, 'Lovua-Lushiku', 216),
(409, 'Tshikapa', 216),
(410, 'Bena Mamba', 117),
(411, 'Diofwa', 117),
(412, 'Lombelo', 117),
(413, 'Lusonge', 117),
(414, 'Muanza-Ngoma', 117),
(415, 'Tshibote', 117),
(416, 'Tshibungu', 117),
(417, 'Dibanda', 118),
(418, 'Dibataie', 118),
(419, 'Kamwandu', 118),
(420, 'Kasangidi', 118),
(421, 'Tshishilu', 118),
(422, 'Kunduyi', 217),
(423, 'Lubi', 217),
(424, 'Lubudi', 217),
(425, 'Lukibu', 217),
(426, 'Mashala', 217),
(427, 'Bulungu', 218),
(428, 'Kafuba', 218),
(429, 'Kavula', 218),
(430, 'Matamba', 218),
(431, 'Mboie', 218),
(432, 'Miao', 218),
(433, 'Muswaswa', 218),
(434, 'Mutefu', 218),
(435, 'Tshitadi', 218),
(436, 'Bambaie', 119),
(437, 'Kabelekese', 119),
(438, 'Kalunga', 119),
(439, 'Loatshi', 119),
(440, 'Lueta', 119),
(441, 'Lusanza', 119),
(442, 'Mbushimaie', 119),
(443, 'Kalela', 121),
(444, 'Lac Mukamba', 121),
(445, 'Mpemba', 121),
(446, 'Ndomba', 121),
(447, 'Baluba-Lubilanji', 123),
(448, 'Bena- Tshitolo', 123),
(449, 'Mutuayi', 123),
(450, 'Nsangu', 123),
(451, 'Kabala', 219),
(452, 'Mudiba', 219),
(453, 'Mukumbi', 219),
(454, 'Mulenda', 219),
(455, 'Kakangayi', 122),
(456, 'Movo-Nkatshia', 122),
(457, 'Tshijiba', 122),
(458, 'Tshilundu', 122),
(459, 'Bakwa-Kalonji', 220),
(460, 'Kasangulu', 221),
(461, 'Luila', 221),
(462, 'Lukunga-Mputu', 221),
(463, 'Benga', 222),
(464, 'Lubisi', 222),
(465, 'Lula-Lumene', 222),
(466, 'Fubu', 94),
(467, 'Kakongo', 94),
(468, 'Phatu', 94),
(469, 'Tsanga-Sud', 94),
(470, 'Tsundi-Sud', 94),
(471, 'Balari', 223),
(472, 'De La Kenge', 223),
(473, 'Kimbanza', 223),
(474, 'Kimumba', 223),
(475, 'Kinkenge', 223),
(476, 'Kivunda', 223),
(477, 'Mbanza-Mona', 223),
(478, 'Mbanza-Muembe', 223),
(479, 'Mbanza-Ngoyo', 223),
(480, 'Mongo-Luala', 223),
(481, 'Kinkosi-Luidi', 224),
(482, 'Mfidi Malele', 224),
(483, 'Mfuma (Mfuma-Kibambi)', 224),
(484, 'Ngeba', 224),
(485, 'Ngufu', 224),
(486, 'Wungu', 224),
(487, 'Boko', 89),
(488, 'Gombe-Matadi', 89),
(489, 'Kivuvu', 89),
(490, 'Kwilu Ngongo', 89),
(491, 'Lunzadi', 89),
(492, 'Ntimansi', 89),
(493, 'Assolongo', 92),
(494, 'Boma Bungu', 92),
(495, 'La mer', 92),
(496, 'Bundi', 93),
(497, 'Isangila', 93),
(498, 'Lutu', 93),
(499, 'Mbavu', 93),
(500, 'Sumbi', 93),
(501, 'Bamboma', 88),
(502, 'Kimpese', 88),
(503, 'Luima', 88),
(504, 'Palabala', 88),
(505, 'Wombo', 88),
(506, 'Bula-Naku', 225),
(507, 'Loango', 225),
(508, 'Lubolo', 225),
(509, 'Lubuzi', 225),
(510, 'Maduda', 225),
(511, 'Mbanga', 225),
(512, 'Nganda-Tsundi', 225),
(513, 'Nzobe-Luzi', 225),
(514, 'Ganaketi', 97),
(515, 'Lobo', 97),
(516, 'Maziomo', 97),
(517, 'Mukoso', 97),
(518, 'Bangu', 98),
(519, 'Bindu', 98),
(520, 'Kulunji', 98),
(521, 'Muloshi', 98),
(522, 'Mwamushiko', 98),
(523, 'Mwendjila', 98),
(524, 'Kasa', 99),
(525, 'Kasongo-Lunda', 99),
(526, 'Kibunda', 99),
(527, 'Kingulu', 99),
(528, 'Kizamba', 99),
(529, 'Mawanga', 99),
(530, 'Panzi', 99),
(531, 'Swa-Tenda', 99),
(532, 'Bukanga-Lonzo', 95),
(533, 'Dinga', 95),
(534, 'Kolokoso', 95),
(535, 'Mosamba', 95),
(536, 'Pelende-Nord', 95),
(537, 'Lufuna', 96),
(538, 'Popokabaka', 96),
(539, 'Yonso', 96),
(540, 'Kidzweme', 102),
(541, 'Kwango-Kasaï', 102),
(542, 'Kwilu-Ntober', 102),
(543, 'Manzasay', 102),
(544, 'Wamba-Fatundu', 102),
(545, 'Dwe', 103),
(546, 'Imbongo', 103),
(547, 'Kilunda', 103),
(548, 'Kipuka', 103),
(549, 'Kwenge', 103),
(550, 'Kwilu-Kimbata', 103),
(551, 'Luniungu', 103),
(552, 'Mikwi', 103),
(553, 'Niadi Nkara', 103),
(554, 'Nko', 103),
(555, 'Gungu', 104),
(556, 'Kandale', 104),
(557, 'Kilamba', 104),
(558, 'Kilembe', 104),
(559, 'Kisunzu', 104),
(560, 'Kobo', 104),
(561, 'Kondo', 104),
(562, 'Lozo', 104),
(563, 'Lukamba', 104),
(564, 'Mulikalunga', 104),
(565, 'Mungindu', 104),
(566, 'Ngudi', 104),
(567, 'Banga', 105),
(568, 'Belo', 105),
(569, 'Bulwem', 105),
(570, 'Kalanganda', 105),
(571, 'Kanga', 105),
(572, 'Kapia', 105),
(573, 'Kipuku', 105),
(574, 'Madimbi', 105),
(575, 'Mateko', 105),
(576, 'Musanga Idiofa', 105),
(577, 'Sedzo', 105),
(578, 'Yasa-Lokwa', 105),
(579, 'Bindungi', 106),
(580, 'Kibolo', 106),
(581, 'Kinzenga', 106),
(582, 'Kinzenzengo', 106),
(583, 'Kiloy', 106),
(584, 'Masi-Manimba', 106),
(585, 'Mokano', 106),
(586, 'Mosango', 106),
(587, 'Pay-Kongila', 106),
(588, 'Sungu', 106),
(589, 'Baluba-Lubangue', 124),
(590, 'Kabinda', 124),
(591, 'Ludimbi-Lukula', 124),
(592, 'Lufubu-Lomami', 124),
(593, 'Lukashie-Lualu', 124),
(594, 'Vunayi', 124),
(595, 'Kamiji', 226),
(596, 'Luekeshi', 226),
(597, 'Bekalebwe', 127),
(598, 'Kisengwa', 127),
(599, 'Lubao', 127),
(600, 'Tshofa', 127),
(601, 'IN-Kanintshin', 125),
(602, 'Kanda-Kanda', 125),
(603, 'Katshisungu', 125),
(604, 'Kanyoka de Mulundu', 125),
(605, 'Bakwa-Mulumba', 126),
(606, 'Baluba-Shankadi', 126),
(607, 'Bena-Kalambayi', 126),
(608, 'Ngandajika', 126),
(609, 'Tshiyamba', 126),
(610, 'Luena', 187),
(611, 'Lulua-Lukoshi', 187),
(612, 'Mutanda', 187),
(613, 'Muyeye', 187),
(614, 'Mwakandala', 187),
(615, 'Mwatshisenge', 187),
(616, 'Ndumba', 187),
(617, 'Saluseke', 187),
(618, 'Tshisangama', 187),
(619, 'Mwant-Yav', 227),
(620, 'Bayeke', 188),
(621, 'Manzangule', 188),
(622, 'Mulumbu', 188),
(623, 'Mwana-Muadi', 188),
(624, 'Lufupa', 189),
(625, 'Luilu', 189),
(626, 'Mukuleshi', 189),
(627, 'Kayembe-Mukulu', 228),
(628, 'Lumanga', 228),
(629, 'Mbako', 228),
(630, 'Muteba', 228),
(631, 'Sakundundu', 228),
(632, 'Samutoma', 228),
(633, 'Tshibanda', 228),
(634, 'Tshipao', 228),
(635, 'Bateke Nord', 111),
(636, 'Basengele', 107),
(637, 'Bolia', 107),
(638, 'Inongo', 107),
(639, 'Beronge', 109),
(640, 'Lutoy', 109),
(641, 'Pendjwa', 109),
(642, 'Badia', 108),
(643, 'Batere', 108),
(644, 'Kemba', 108),
(645, 'Lwabo', 108),
(646, 'Mfimi', 108),
(647, 'Bateke Sud (Twa)', 229),
(648, 'Baboma Nord', 230),
(649, 'ELL (Entre Lukenie et Lokoro) NKAW', 110),
(650, 'Kangara', 110),
(651, 'Lokolama', 110),
(652, 'Lukenie', 110),
(653, 'Mongama', 231),
(654, 'Babuyu', 135),
(655, 'BB/Bahemba', 135),
(656, 'BB/Kabambare bahombo', 135),
(657, 'BB/Lulindi', 135),
(658, 'BB/Salamabila', 135),
(659, 'BB/Wamaza', 135),
(660, 'Ambwe', 232),
(661, 'Balanga', 232),
(662, 'Bangengele', 232),
(663, 'Wasongola', 232),
(664, 'Bakwange', 133),
(665, 'Basonge I', 133),
(666, 'Basonge II-Mweho', 133),
(667, 'Benia-Samba', 133),
(668, 'Mamba Kasenga', 133),
(669, 'Nonda', 133),
(670, 'Wagenia', 133),
(671, 'Wazimba wa Maringa', 133),
(672, 'Wazimba wa Mulu', 133),
(673, 'Wazura', 133),
(674, 'Alumba', 233),
(675, 'Ankutshu', 233),
(676, 'Bahina', 233),
(677, 'Bakongola', 233),
(678, 'Matapa', 233),
(679, 'Bitule', 234),
(680, 'Obokote', 234),
(681, 'Babene', 134),
(682, 'Bela', 134),
(683, 'Ikama', 134),
(684, 'Wakabango II', 134),
(685, 'Babira Bakwame', 235),
(686, 'Baleka', 235),
(687, 'Ulindi', 235),
(688, 'Bongandanga', 168),
(689, 'Boso-Djanoa', 168),
(690, 'Boso-Melo', 168),
(691, 'Boso-Simba', 168),
(692, 'Banda-Yowa', 167),
(693, 'Itimbiri', 167),
(694, 'Loeka', 167),
(695, 'Molua', 167),
(696, 'Monzamboli', 167),
(697, 'Yandongi', 167),
(698, 'Mongala-Motima', 166),
(699, 'Ngomba-Dako', 166),
(700, 'Ngombe-Mombangi', 166),
(701, 'Bashu', 147),
(702, 'Beni-Mbau', 147),
(703, 'Ruwezori', 147),
(704, 'Watalinga', 147),
(705, 'Bamate', 146),
(706, 'Bapere', 146),
(707, 'Baswagha', 146),
(708, 'Batangi', 146),
(709, 'Bahunde', 145),
(710, 'Bashali', 145),
(711, 'Katoyi', 145),
(712, 'Osso Banyungu', 145),
(713, 'Bakumu', 144),
(714, 'Bwisha', 143),
(715, 'Bwito', 143),
(716, 'Bakono', 236),
(717, 'Wanianga', 236),
(718, 'Bandas', 170),
(719, 'Bili', 170),
(720, 'Bosobolo', 170),
(721, 'Bodangabo', 237),
(722, 'Businga', 237),
(723, 'Karawa', 237),
(724, 'Mobayi-Mbongo', 238),
(725, 'Otto-Banza', 238),
(726, 'Abumombazi', 171),
(727, 'Wapinda', 171),
(728, 'Yakoma', 171),
(729, 'Basambala', 130),
(730, 'Batetela arabisés-Lomami', 130),
(731, 'Djalo', 130),
(732, 'Lonya', 130),
(733, 'Lukumbe', 130),
(734, 'Ngandu de Katako-Kombe', 130),
(735, 'Ukulungu', 130),
(736, 'Watambulu-Nord', 130),
(737, 'Watambulu-Sud', 130),
(738, 'Atshuru', 239),
(739, 'Bankutshu-Dibele', 239),
(740, 'Bankutshu-Lukenie', 239),
(741, 'Basho', 239),
(742, 'Batetela-Dibele', 239),
(743, 'Ohindo', 239),
(744, 'Ahamba-Mange', 129),
(745, 'Kondo-Tshumbe', 129),
(746, 'Lukfungu', 129),
(747, 'Lutshimba-Eduo-Piete-Kolombe', 129),
(748, 'Nambelo-Lohembe', 129),
(749, 'Olemba', 129),
(750, 'Vunge', 129),
(751, 'Watambulu', 129),
(752, 'Bahamba I', 240),
(753, 'Bahamba II', 240),
(754, 'Bakela', 240),
(755, 'Batetela-Lomela', 240),
(756, 'Djonga', 240),
(757, 'Okutu', 240),
(758, 'Okutu Basonge', 131),
(759, 'Mondja-Ngandu', 131),
(760, 'Ndjovu', 131),
(761, 'Ngandu-Wuma', 131),
(762, 'Basonge', 128),
(763, 'Batetela', 128),
(764, 'EKM (Entre Kunduye et Maalaba)', 128),
(765, 'ELK (Entre Lubi et Kunduye)', 128),
(766, 'Kashidi', 128),
(767, 'Lubi', 128),
(768, 'Mpania-Mutombo', 128),
(769, 'Sankuru', 128),
(770, 'Lulenge', 139),
(771, 'Mutambala', 139),
(772, 'Ngandja', 139),
(773, 'Tanganyika', 139),
(774, 'Ntambika', 241),
(775, 'Rubenga', 241),
(776, 'Kabare', 242),
(777, 'Nindja', 242),
(778, 'Bahavu', 243),
(779, 'Buloho', 243),
(780, 'Basile', 141),
(781, 'Burhinyi', 141),
(782, 'Itombwe', 141),
(783, 'Luhwindja', 141),
(784, 'Lwindi', 141),
(785, 'Wamuzimu', 141),
(786, 'Bakisi', 140),
(787, 'Wakabango', 140),
(788, 'Bafuliro', 137),
(789, 'Bavira', 137),
(790, 'Plaine de la Ruzizi', 137),
(791, 'Kaziba', 138),
(792, 'Ngweshe', 138),
(793, 'Banza', 173),
(794, 'Bolingo', 173),
(795, 'Mongala-Kuma', 173),
(796, 'Ndolo-Liboko', 173),
(797, 'Ngombe-Doko', 173),
(798, 'Banga-Kungu', 172),
(799, 'Bowase', 172),
(800, 'Mbari', 172),
(801, 'Nguya', 172),
(802, 'Bomboma', 244),
(803, 'Dongo', 244),
(804, 'Lua', 244),
(805, 'Moanda Bomole', 244),
(806, 'Songo', 244),
(807, 'Libenge-Centre', 174),
(808, 'Libenge-Nord', 174),
(809, 'Libenge-Sud', 174),
(810, 'Luela-Luvunguyi', 245),
(811, 'Lukuswa', 245),
(812, 'Benze', 195),
(813, 'Ruluku', 195),
(814, 'Tumbwe', 195),
(815, 'Baluba', 246),
(816, 'Basonge', 246),
(817, 'Bayashi', 246),
(818, 'Bena-nyembo', 246),
(819, 'Lubunda', 246),
(820, 'Mambwe', 246),
(821, 'Muhona', 246),
(822, 'Munono', 246),
(823, 'Nkuvu', 246),
(824, 'Yambula', 246),
(825, 'Bakongolo', 198),
(826, 'Kamalondo', 198),
(827, 'Kiluba', 198),
(828, 'Kyofwe', 198),
(829, 'Luvua', 198),
(830, 'Nyemba', 198),
(831, 'Bena-Kamanya', 196),
(832, 'Bena-Tanga', 196),
(833, 'Kansabala', 196),
(834, 'Kayabala', 196),
(835, 'Manda', 196),
(836, 'Nganie', 196),
(837, 'Nords-Lukuga', 197),
(838, 'Sud-Lukuga', 197),
(839, 'Bafwandaka', 247),
(840, 'Bakumu d’Angumu', 247),
(841, 'Bakundumu', 247),
(842, 'Barumbi-Opienge', 247),
(843, 'Bekeni-Kondolole', 247),
(844, 'Bemili', 247),
(845, 'Baboro', 162),
(846, 'Babwa de Kole', 162),
(847, 'Bamanga', 162),
(848, 'Banalia-Bangba', 162),
(849, 'Popoy', 162),
(850, 'Bangelema-Mongandjo', 248),
(851, 'Bomenge', 248),
(852, 'Lokutu', 248),
(853, 'Mobango Itimbiri', 248),
(854, 'Turumbu', 248),
(855, 'Wahanga', 248),
(856, 'Yaliwasa', 248),
(857, 'Yamandundu', 248),
(858, 'Baluolambila', 163),
(859, 'Bambelota', 163),
(860, 'Bolomboki', 163),
(861, 'Kombe', 163),
(862, 'Liutua', 163),
(863, 'Lokombe', 163),
(864, 'Luete', 163),
(865, 'Turumbu', 163),
(866, 'Yalihila', 163),
(867, 'Yalikandja-Yanonge', 163),
(868, 'Yalikoka-Mboso', 163),
(869, 'Yaokandja', 163),
(870, 'Yawembe-Basoa-Baonga', 163),
(871, 'Balinga-Lindja', 164),
(872, 'Lye', 164),
(873, 'Kembe', 164),
(874, 'Lobale', 164),
(875, 'Mongo', 164),
(876, 'Tooli', 164),
(877, 'Yalingo', 164),
(878, 'Yamale', 164),
(879, 'Yapandu', 164),
(880, 'Yaende-Loolo', 164),
(881, 'Yeyango', 164),
(882, 'Bakumu d’Obiatuku', 165),
(883, 'Bakumu-Kilinga', 165),
(884, 'Bakumu-Mandombe', 165),
(885, 'Bakumu-Mangongo', 165),
(886, 'Kirundu', 165),
(887, 'Mituku-Bamoya', 165),
(888, 'Mituku-Basikate', 165),
(889, 'Walengola-Babira', 165),
(890, 'Walengola-Baleka', 165),
(891, 'Walengola-Lilo', 165),
(892, 'Walengola-Lowa', 165),
(893, 'Bolinga', 249),
(894, 'Bosoku', 249),
(895, 'Buma', 249),
(896, 'Mombesa', 249),
(897, 'Befumbo', 181),
(898, 'Duale', 181),
(899, 'Lomako', 181),
(900, 'Bolua', 179),
(901, 'Djera', 179),
(902, 'Lofoy', 179),
(903, 'Wini', 179),
(904, 'Lulaka', 250),
(905, 'Loombo', 250),
(906, 'Luando', 250),
(907, 'Luay', 250),
(908, 'Nkole', 250),
(909, 'Djolu', 251),
(910, 'Lingomo', 251),
(911, 'Luo', 251),
(912, 'Yala', 251),
(913, 'Lofome', 180),
(914, 'Loile', 180),
(915, 'Lokima', 180),
(916, 'Tshuapa', 180),
(917, 'Tumbenga', 180),
(918, 'Bianga', 252),
(919, 'Monkoto', 252),
(920, 'Nongo', 252),
(921, 'Barumbu', 84),
(922, 'Gombe', 84),
(923, 'Kinshasa', 84),
(924, 'Kintambo', 84),
(925, 'Lingwala', 84),
(926, 'Mont-Ngafula', 84),
(927, 'Ngaliema', 84),
(928, 'Bandalungwa', 83),
(929, 'Bumbu', 83),
(930, 'Kalamu', 83),
(931, 'Kasa-Vubu', 83),
(932, 'Makala', 83),
(933, 'Ngiri-Ngiri', 83),
(934, 'Selembao', 83),
(935, 'Kisenso', 85),
(936, 'Lemba', 85),
(937, 'Limete', 85),
(938, 'Matete', 85),
(939, 'Ngaba', 85),
(940, 'Kimbaseke', 86),
(941, 'Maluku', 86),
(942, 'Masina', 86),
(943, 'N’djili', 86),
(944, 'N’sele', 86),
(945, 'Plateau des Batéké', 87);

-- --------------------------------------------------------

--
-- Structure de la table `site_blocs`
--

CREATE TABLE `site_blocs` (
  `id` int(10) UNSIGNED NOT NULL,
  `page` varchar(80) NOT NULL,
  `cle` varchar(80) NOT NULL,
  `langue` char(2) NOT NULL DEFAULT 'fr',
  `titre` varchar(255) NOT NULL DEFAULT '',
  `contenu` mediumtext NOT NULL,
  `type` enum('texte','html') NOT NULL DEFAULT 'html',
  `ordre` int(11) NOT NULL DEFAULT 0,
  `meta` text DEFAULT NULL,
  `statut` enum('brouillon','publie','archive') NOT NULL DEFAULT 'publie',
  `modifie_par` int(11) DEFAULT NULL,
  `modifie_le` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `site_blocs`
--

INSERT INTO `site_blocs` (`id`, `page`, `cle`, `langue`, `titre`, `contenu`, `type`, `ordre`, `meta`, `statut`, `modifie_par`, `modifie_le`) VALUES
(1, 'apropos', 'tab-1', 'fr', 'Le Président Fondateur', '<p class=\"article-num\">Chapitre I</p>\n    <h2>Le Président Fondateur</h2>\n    <div class=\"rule\"></div>\n    <div class=\"president-card\">\n      <div class=\"role\">\n        Président Fondateur\n      </div>\n      <h3>\n        Benjamin Jean de Dieu BONIOMA ISELONGE\n      </h3>\n      <p>\n        Président Fondateur du Rassemblement des Chrétiens Républicains.\n      </p>\n    </div>\n    <p>\n      Le Président Fondateur est à l\'origine de la vision et de la création\n      du Rassemblement des Chrétiens Républicains. Il porte la vision\n      fondatrice et contribue à l\'orientation générale du Rassemblement.\n    </p>', 'html', 1, '{\"roman\": \"I\", \"groupe\": \"main\"}', 'publie', NULL, '2026-10-03 11:09:12'),
(2, 'apropos', 'tab-2', 'fr', 'Les Fondateurs (cofondateurs)', '<p class=\"article-num\">Chapitre II</p>\n    <h2>Les Fondateurs (cofondateurs)</h2>\n    <div class=\"rule\"></div>\n    <p>\n      Les fondateurs et cofondateurs sont les personnes qui ont participé\n      à la naissance et à la construction du Rassemblement des Chrétiens\n      Républicains.\n    </p>\n    <ol class=\"founders\">\n      <li>Benjamin Jean de Dieu BONIOMA ISELONGE</li>\n      <li>Richard Ferdinand MBUYA NYUBA BALENDA</li>\n      <li>Jean Marie Yves MAKENGA MBAYI</li>\n      <li>Simon Richard KAZENGA TSHIBANDA BANDA</li>\n      <li>César OKITO LOKOHO</li>\n      <li>Jean Marie Alain NGANDU KABASELE</li>\n      <li>Félix Médard MULANGU KATULUMBA</li>\n      <li>Jean Pierre TSHUNGU DIOH FUM</li>\n      <li>Dominique LUEYA MPANIA AMBA</li>\n      <li>César LOFAKA ISELONGE LOOLI WALONZ</li>\n      <li>Richard MOTOKO BOKOMBA</li>\n      <li>Christian DIUR MAKOK</li>\n      <li>Dismance MUZAMBIKWA MUKUPA</li>\n      <li>Alexis SHALUKOMA MUSHENGEZI</li>\n      <li>Honoré KASEREKA KIABALENGA</li>\n      <li>Raphaël MIHARI ALUTA</li>\n      <li>Arsène TUMBA PUSU KABEMBA</li>\n      <li>Joseph Zabulon BONIOMA LOSEMBE</li>\n      <li>Simon José YOMBO BOTOKO</li>\n      <li>Etienne CINYAMU MUELA</li>\n      <li>Jean Claudel LUPOPONGO</li>\n      <li>Macaire LAZUZA BETA-BETA</li>\n      <li>Jean KIYANGI KIAFUNG</li>\n      <li>Joachim KAFUNGA MATABISI</li>\n    </ol>', 'html', 2, '{\"roman\": \"II\", \"groupe\": \"main\"}', 'publie', NULL, '2026-10-03 11:09:12'),
(3, 'apropos', 'tab-3', 'fr', 'Nos Équipes dirigeantes', '<p class=\"article-num\">Chapitre III</p>\n    <h2>Nos Équipes dirigeantes</h2>\n    <div class=\"rule\"></div>\n    <p>\n      Le Rassemblement des Chrétiens Républicains s\'appuie sur des équipes\n      dirigeantes chargées d\'assurer son organisation, son fonctionnement\n      et la mise en œuvre de ses différentes missions.\n    </p>\n    <div class=\"team-grid\">\n      <div class=\"team-card\">\n        <div class=\"team-number\">01</div>\n        <h3>Présidence</h3>\n        <p>\n          Direction générale et orientation du Rassemblement.\n        </p>\n      </div>\n      <div class=\"team-card\">\n        <div class=\"team-number\">02</div>\n        <h3>Commission exécutive nationale</h3>\n        <p>\n          Coordination et gestion des activités nationales.\n        </p>\n      </div>\n      <div class=\"team-card\">\n        <div class=\"team-number\">03</div>\n        <h3>Structures provinciales</h3>\n        <p>\n          Organisation et représentation du RCR dans les provinces.\n        </p>\n      </div>\n      <div class=\"team-card\">\n        <div class=\"team-number\">04</div>\n        <h3>Ligue des Jeunes</h3>\n        <p>\n          Participation et mobilisation de la jeunesse.\n        </p>\n      </div>\n      <div class=\"team-card\">\n        <div class=\"team-number\">05</div>\n        <h3>Ligue des Femmes</h3>\n        <p>\n          Participation et représentation des femmes.\n        </p>\n      </div>\n      <div class=\"team-card\">\n        <div class=\"team-number\">06</div>\n        <h3>Structures locales</h3>\n        <p>\n          Animation et organisation des activités de proximité.\n        </p>\n      </div>\n    </div>\n    <h3>Organisation nationale</h3>\n    <div class=\"exec-grid\">\n      <div>\n        <h5>Présidence</h5>\n        <ol>\n          <li>Président du Rassemblement, Chef du Parti</li>\n          <li>Vice-Président National de la Politique</li>\n          <li>Vice-Président National des Finances</li>\n          <li>Vice-Président National chargé des Missions</li>\n          <li>Vice-Président National chargé des Missions</li>\n          <li>Vice-Président National chargé des Missions</li>\n          <li>Président de la Ligue des Jeunes</li>\n          <li>Présidente de la Ligue des Femmes</li>\n        </ol>\n      </div>\n      <div>\n        <h5>Commission exécutive nationale</h5>\n        <ol>\n          <li>Secrétaire Général</li>\n          <li>Rapporteur Général</li>\n          <li>Trésorier Général</li>\n          <li>Secrétaire Exécutif Spécial des Effectifs</li>\n          <li>Secrétaire Exécutif Spécial des Relations Extérieures</li>\n          <li>Secrétaire Exécutif Spécial des Arts et Stratégies Martiales</li>\n          <li>Secrétaire Exécutif Spécial des Droits Humains</li>\n          <li>Secrétaire Exécutif National du Budget</li>\n          <li>Secrétaire Exécutif National du Bien-Être</li>\n          <li>Secrétaire Exécutif National du Commerce</li>\n          <li>Secrétaire Exécutif National de l\'Éducation</li>\n          <li>Secrétaire Exécutif National de l\'Environnement</li>\n          <li>Secrétaire Exécutif National de l\'Agriculture</li>\n          <li>Secrétaire Exécutif National du Travail</li>\n          <li>Secrétaire Exécutif National de l\'Urbanisme</li>\n          <li>Secrétaire Exécutif National de l\'Énergie</li>\n          <li>Secrétaire Exécutif National des Transports</li>\n          <li>Secrétaire Exécutif National de la Jeunesse</li>\n          <li>Secrétaire Exécutif National des Cultures et des Arts</li>\n          <li>Sous-Secrétaire Exécutif National du Budget</li>\n          <li>Sous-Secrétaire Exécutif National du Bien-Être</li>\n          <li>Sous-Secrétaire Exécutif National des Finances</li>\n          <li>Sous-Secrétaire Exécutif National de l\'Éducation</li>\n          <li>Sous-Secrétaire Exécutif National de l\'Environnement</li>\n          <li>Sous-Secrétaire Exécutif National de l\'Agriculture</li>\n          <li>Sous-Secrétaire Exécutif National de la Jeunesse</li>\n        </ol>\n      </div>\n    </div>', 'html', 3, '{\"roman\": \"III\", \"groupe\": \"main\"}', 'publie', NULL, '2026-10-03 11:09:12'),
(4, 'apropos', 'tab-4', 'fr', 'Les Militants', '<p class=\"article-num\">Chapitre IV</p>\n    <h2>Les Militants</h2>\n    <div class=\"rule\"></div>\n    <div class=\"militants-box\">\n      <strong>\n        Les militants constituent la base active du Rassemblement\n        des Chrétiens Républicains.\n      </strong>\n    </div>\n    <p>\n      Les militants sont les membres engagés dans la vie et les activités\n      du Rassemblement. Ils participent aux différentes actions\n      d\'organisation, de mobilisation, de formation et de sensibilisation\n      selon les responsabilités et les structures auxquelles ils sont\n      rattachés.\n    </p>\n    <p>\n      Ils contribuent à la diffusion des valeurs et des principes du\n      Rassemblement et participent à la vie de ses structures nationales,\n      provinciales et locales.\n    </p>\n    <p>\n      Le militant constitue ainsi un élément essentiel de l\'organisation\n      et de la dynamique du Rassemblement.\n    </p>', 'html', 4, '{\"roman\": \"IV\", \"groupe\": \"main\"}', 'publie', NULL, '2026-10-03 11:09:12'),
(5, 'apropos', 'tab-5', 'fr', 'Nos Instances du RCR', '<p class=\"article-num\">Chapitre V</p>\n    <h2>Nos instances du RCR</h2>\n    <div class=\"rule\"></div>\n    <p>\n      Il s\'agit de trouver des images des chefs-lieux des provinces où le\n      RCR est présent, ainsi que les photos des Présidents Provinciaux\n      concernés : Ituri, Kongo Central, Kwilu, Maï-Ndombe, Tshopo,\n      Kinshasa et Kasaï.\n    </p>', 'html', 5, '{\"roman\": \"V\", \"groupe\": \"main\"}', 'publie', NULL, '2026-10-03 11:09:12'),
(6, 'apropos', 'tab-6', 'fr', 'Notre Académie', '<p class=\"article-num\">Chapitre VI</p>\n    <h2>Notre Académie</h2>\n    <div class=\"rule\"></div>\n    <p>\n      Le Rassemblement des Chrétiens Républicains, RCR, constitue en lui-même\n      le système académique qui forme le nouveau membre dès son recrutement,\n      en qualité soit de membre sympathisant, soit de membre effectif.\n    </p>\n    <h3>\n      Le programme dispensé par le Rassemblement forme les membres à :\n    </h3>\n    <ol>\n      <li>\n        L\'Amour de Dieu et du Prochain, pour devenir un véritable Serviteur\n        du Peuple ;\n      </li>\n      <li>\n        La bonne manière de mettre au service du Congolais toutes les\n        richesses nationales du pays ;\n      </li>\n      <li>\n        L\'entrepreneuriat privé et public ;\n      </li>\n      <li>\n        Le leadership politique ;\n      </li>\n      <li>\n        Les stratégies et arts martiaux ;\n      </li>\n      <li>\n        L\'organisation d\'activités et de manifestations politiques et sociales ;\n      </li>\n      <li>\n        L\'art de la mobilisation et de la vulgarisation professionnelle ;\n      </li>\n      <li>\n        L\'art de la prospection de bouche à oreille.\n      </li>\n    </ol>', 'html', 6, '{\"roman\": \"VI\", \"groupe\": \"main\"}', 'publie', NULL, '2026-10-03 11:09:12'),
(7, 'apropos', 'tab-7', 'fr', 'Notre vision de la société', '<p class=\"article-num\">Chapitre VII</p>\n    <h2>Notre vision de la société</h2>\n    <div class=\"rule\"></div>\n    <p>\n      Le Rassemblement des Chrétiens Républicains est l\'expression de notre\n      volonté de résister aux forces que développent ceux qui veulent et\n      cherchent à nous écraser, à réduire à néant nos rêves, nos espoirs,\n      notre avenir, ainsi que nous-mêmes.\n    </p>\n    <p>\n      L\'épanouissement de la personne et le développement de la société que\n      vise le Rassemblement des Chrétiens Républicains nous obligent à mettre\n      en place des systèmes d\'organisation de nos différentes communautés\n      intégrant les valeurs et principes chrétiens.\n    </p>\n    <p>\n      Notre idéal est d\'installer un État de droit chrétien, c\'est-à-dire\n      un État dont le droit est fondé sur les principes et valeurs chrétiennes.\n    </p>', 'html', 7, '{\"roman\": \"VII\", \"groupe\": \"main\"}', 'publie', NULL, '2026-10-03 11:09:12'),
(8, 'apropos', 'tab-8', 'fr', 'Notre vision de la politique', '<p class=\"article-num\">Chapitre VIII</p>\n    <h2>Notre vision de la politique</h2>\n    <div class=\"rule\"></div>\n    <p>\n      Quand l\'idée nous est venue d\'inventer un nouveau parti que nous avons\n      voulu, de surcroît, chrétien, nous avons d\'abord pensé convaincre nos\n      sœurs et frères en Christ de constituer une structure politique.\n    </p>\n    <p>\n      L\'homme, d\'une manière générale, et le chrétien en particulier, a le\n      devoir d\'organiser ou de réorganiser la société, de l\'améliorer et\n      d\'améliorer ses relations avec ses semblables et avec son Créateur.\n    </p>\n    <p>\n      Le Rassemblement des Chrétiens Républicains se conçoit comme une\n      organisation d\'hommes et de femmes intègres, sujets de leur histoire,\n      qui s\'attachent aux valeurs des civilisations chrétiennes.\n    </p>', 'html', 8, '{\"roman\": \"VIII\", \"groupe\": \"main\"}', 'publie', NULL, '2026-10-03 11:09:12'),
(9, 'apropos', 'tab-9', 'fr', 'Notre société de confiance', '<p class=\"article-num\">Chapitre IX</p>\n    <h2>Notre société de confiance</h2>\n    <div class=\"rule\"></div>\n    <p>\n      Le développement et la perfection sont devenus des objectifs\n      fondamentaux de l\'existence humaine.\n    </p>\n    <p>\n      Pour le Rassemblement des Chrétiens Républicains, le Royaume de Dieu\n      étant l\'objectif, la voie pour y parvenir est la réalisation d\'une\n      Nation chrétienne, d\'une République chrétienne et d\'un État chrétien\n      au Congo RD.\n    </p>', 'html', 9, '{\"roman\": \"IX\", \"groupe\": \"main\"}', 'publie', NULL, '2026-10-03 11:09:12'),
(10, 'apropos', 'tab-10', 'fr', 'Nos objectifs', '<p class=\"article-num\">Chapitre X</p>\n    <h2>Nos objectifs</h2>\n    <div class=\"rule\"></div>\n    <p>\n      C\'est dans un environnement de turbulence politique qu\'un groupe\n      d\'hommes et de femmes décide de participer à la transformation\n      positive de la communauté congolaise.\n    </p>\n    <p>\n      Le Rassemblement des Chrétiens Républicains soutient la transformation\n      recherchée et le développement intégral, harmonieux et durable de\n      l\'homme.\n    </p>\n    <h3>Notre démarche vise notamment :</h3>\n    <ol>\n      <li>\n        La conquête et la conservation du pouvoir par voie démocratique ;\n      </li>\n      <li>\n        La reconnaissance constitutionnelle de l\'autorité de Dieu Tout-Puissant ;\n      </li>\n      <li>\n        L\'introduction de l\'Amour de Dieu et du Prochain dans la politique\n        et la justice congolaises ;\n      </li>\n      <li>\n        Une économie de marché intégrant la gestion de l\'environnement ;\n      </li>\n      <li>\n        Une monnaie et d\'autres symboles de souveraineté purement congolais ;\n      </li>\n      <li>\n        Une répartition équitable et équilibrée des richesses nationales ;\n      </li>\n      <li>\n        Un État de droit, indépendant et chrétien, au service de la population\n        congolaise.\n      </li>\n    </ol>', 'html', 10, '{\"roman\": \"X\", \"groupe\": \"main\"}', 'publie', NULL, '2026-10-03 11:09:12'),
(11, 'apropos', 'tab-11', 'fr', 'Amour', '<p class=\"article-num\">Valeur fondamentale</p>\n    <h2>Amour</h2>\n    <svg\n      class=\"value-mark\"\n      viewBox=\"0 0 48 48\"\n      fill=\"none\">\n      <path\n        d=\"M24 40C24 40 6 28.5 6 16.8\n        6 10.8 10.8 6 16.7 6\n        c3.6 0 6.9 1.8 8.9 4.6\n        A10.7 10.7 0 0 1 33.9 6\n        C39.8 6 44.6 10.8 44.6 16.8\n        44.6 28.5 24 40 24 40Z\"\n        stroke=\"#9C7A2E\"\n        stroke-width=\"1.6\"/>\n    </svg>\n    <div class=\"rule\"></div>\n    <p>\n      L\'Amour constitue une valeur fondamentale du Rassemblement des\n      Chrétiens Républicains et s\'inscrit dans la relation avec Dieu,\n      avec soi-même et avec les autres.\n    </p>\n    <p>\n      Le Rassemblement des Chrétiens Républicains affirme l\'importance\n      de l\'amour du prochain, de la solidarité et du respect de la dignité\n      humaine.\n    </p>', 'html', 11, '{\"roman\": \"A\", \"groupe\": \"values\"}', 'publie', NULL, '2026-10-03 11:09:12'),
(12, 'apropos', 'tab-12', 'fr', 'Démocratie', '<p class=\"article-num\">Valeur fondamentale</p>\n    <h2>Démocratie</h2>\n    <svg\n      class=\"value-mark\"\n      viewBox=\"0 0 48 48\"\n      fill=\"none\">\n      <path\n        d=\"M24 6v6M12 30l12-18 12 18\n        M8 30h8M32 30h8\n        M8 30a4 4 0 0 0 8 0\n        M32 30a4 4 0 0 0 8 0\n        M10 42h28\"\n        stroke=\"#9C7A2E\"\n        stroke-width=\"1.6\"\n        stroke-linecap=\"round\"\n        stroke-linejoin=\"round\"/>\n    </svg>\n    <div class=\"rule\"></div>\n    <p>\n      La démocratie constitue une valeur fondamentale permettant\n      l\'expression de la volonté collective, la participation et\n      la responsabilisation des membres de la communauté.\n    </p>\n    <p>\n      Le Rassemblement affirme son attachement à la démocratie représentative,\n      délibérative et participative.\n    </p>', 'html', 12, '{\"roman\": \"B\", \"groupe\": \"values\"}', 'publie', NULL, '2026-10-03 11:09:12'),
(13, 'apropos', 'tab-13', 'fr', 'Justice', '<p class=\"article-num\">Valeur fondamentale</p>\n    <h2>Justice</h2>\n    <svg\n      class=\"value-mark\"\n      viewBox=\"0 0 48 48\"\n      fill=\"none\">\n      <path\n        d=\"M24 6v34M14 40h20\n        M8 16l8-4 8 4\n        M24 16l8-4 8 4\n        M8 16a4 6 0 0 0 16 0\n        M32 16a4 6 0 0 0 16 0\"\n        stroke=\"#9C7A2E\"\n        stroke-width=\"1.6\"\n        stroke-linecap=\"round\"\n        stroke-linejoin=\"round\"/>\n    </svg>\n    <div class=\"rule\"></div>\n    <p>\n      La justice est présentée comme une valeur chrétienne et républicaine,\n      destinée à garantir la dignité, l\'égalité fondamentale et la protection\n      de toute personne.\n    </p>\n    <p>\n      Le Rassemblement affirme sa volonté de promouvoir la justice,\n      la paix sociale et l\'égalité devant le droit.\n    </p>', 'html', 13, '{\"roman\": \"C\", \"groupe\": \"values\"}', 'publie', NULL, '2026-10-03 11:09:12'),
(14, 'mention', 'edition', 'fr', '1. Édition du site', '<h2>\n                    1. Édition du site\n                </h2>\n                <p>\n                    Le présent site internet est édité par le\n                    <strong>Rassemblement des Chrétiens Républicains (RCR)</strong>.\n                </p>\n                <div class=\"legal-info\">\n                    <p>\n                        <strong>Nom officiel :</strong>\n                        Rassemblement des Chrétiens Républicains — RCR\n                    </p>\n                    <p>\n                        <strong>Siège social :</strong>\n                        03, Avenue OLONGI, Quartier MAMA-YEMO,\n                        Commune NGALIEMA, Ville de KINSHASA - RDC\n                    </p>\n                    <p>\n                        <strong>Téléphone :</strong>\n                        +243 82 377 9098\n                    </p>\n                    <p>\n                        <strong>Adresse électronique :</strong>\n                        <a href=\"mailto:contact@rcr.cd\">\n                            contact@rcr.cd\n                        </a>\n                    </p>\n                    <p>\n                        <strong>Numéro d’enregistrement :</strong>\n                        Arrêté d\'enregistrement 010/2006 du 30.01.2006\n                    </p>\n                    <p>\n                        <strong>Directeur de publication :</strong>\n                        Bonioma Iselonge Benjamin — Président du RCR\n                    </p>\n                    <p>\n                        <strong>Hébergeur :</strong>\n                        SCPT (CD Registry) —\n                        Société Congolaise des Postes et Télécommunications\n                    </p>\n                    <p>\n                        <strong>Adresse de l’hébergeur :</strong>\n                        1009, Boulevard du 30 juin,\n                        Kinshasa - RD-CONGO\n                    </p>\n                    <p>\n                        <strong>Site de l’hébergeur :</strong>\n                        <a href=\"https://services.hosting.cd\"\n                           target=\"_blank\"\n                           rel=\"noopener noreferrer\">\n                            services.hosting.cd\n                        </a>\n                    </p>\n                </div>\n                <p>\n                    Le site est accessible à l’adresse :\n                    <strong>https://www.rcr.cd</strong>.\n                </p>', 'html', 1, NULL, 'publie', NULL, '2026-10-03 11:09:12'),
(15, 'mention', 'propriete', 'fr', '2. Propriété intellectuelle', '<h2>\n                    2. Propriété intellectuelle\n                </h2>\n                <p>\n                    L’ensemble des éléments présents sur ce site, notamment\n                    les textes, images, photographies, logos, graphismes,\n                    vidéos, icônes, documents et éléments de conception,\n                    est protégé par les règles applicables en matière\n                    de propriété intellectuelle.\n                </p>\n                <p>\n                    Sauf autorisation écrite préalable du\n                    Rassemblement des Chrétiens Républicains,\n                    toute reproduction, représentation, modification,\n                    adaptation, distribution ou exploitation, totale ou\n                    partielle, du contenu du site est interdite.\n                </p>\n                <p>\n                    Toute utilisation non autorisée d’un élément du site\n                    pourra faire l’objet de mesures ou de poursuites\n                    conformément aux textes applicables.\n                </p>', 'html', 2, NULL, 'publie', NULL, '2026-10-03 11:09:12'),
(16, 'mention', 'responsabilite', 'fr', '3. Limitation de responsabilité', '<h2>\n                    3. Limitation de responsabilité\n                </h2>\n                <p>\n                    Le Rassemblement des Chrétiens Républicains s’efforce\n                    de maintenir les informations publiées sur ce site à\n                    jour et accessibles. Toutefois, aucune garantie absolue\n                    ne peut être donnée concernant l’exhaustivité,\n                    l’exactitude ou la disponibilité permanente des contenus.\n                </p>\n                <p>\n                    L’utilisateur reconnaît utiliser le site sous sa propre\n                    responsabilité. Le RCR ne pourra être tenu responsable\n                    des dommages directs ou indirects résultant de\n                    l’utilisation du site, de son indisponibilité ou de\n                    l’utilisation des informations qui y sont publiées,\n                    dans les limites prévues par la législation applicable.\n                </p>\n                <p>\n                    Les espaces interactifs, notamment les formulaires de\n                    contact, les commentaires ou les espaces de contribution,\n                    doivent être utilisés de manière respectueuse et conforme\n                    aux lois en vigueur.\n                </p>\n                <p>\n                    Le RCR se réserve le droit de retirer tout contenu\n                    manifestement illicite, injurieux, diffamatoire,\n                    discriminatoire, violent ou contraire aux règles\n                    d’utilisation du site.\n                </p>', 'html', 3, NULL, 'publie', NULL, '2026-10-03 11:09:12'),
(17, 'mention', 'donnees', 'fr', '4. Données personnelles', '<h2>\n                    4. Données personnelles\n                </h2>\n                <p>\n                    Certaines données personnelles peuvent être collectées\n                    lorsque l’utilisateur remplit un formulaire d’adhésion,\n                    de contact, de soutien ou tout autre formulaire disponible\n                    sur le site.\n                </p>\n                <p>\n                    Les données collectées sont utilisées uniquement pour\n                    les finalités indiquées au moment de leur collecte,\n                    notamment :\n                </p>\n                <ul>\n                    <li>\n                        la gestion des demandes envoyées au site ;\n                    </li>\n                    <li>\n                        la gestion des adhésions et des membres ;\n                    </li>\n                    <li>\n                        la gestion des contributions et des soutiens ;\n                    </li>\n                    <li>\n                        la communication avec les utilisateurs ;\n                    </li>\n                    <li>\n                        la sécurité et l’amélioration du fonctionnement\n                        du site.\n                    </li>\n                </ul>\n                <p>\n                    Les données personnelles ne sont pas vendues.\n                    Elles peuvent être transmises à des prestataires\n                    techniques uniquement lorsque cela est nécessaire\n                    au fonctionnement du service, dans le respect des\n                    obligations de confidentialité.\n                </p>\n                <div class=\"legal-info\">\n                    <p>\n                        <strong>Responsable du traitement :</strong>\n                        Administrateur des données personnelles\n                    </p>\n                    <p>\n                        <strong>Contact relatif aux données personnelles :</strong>\n                        <a href=\"mailto:adp@rcr.cd\">\n                            adp@rcr.cd\n                        </a>\n                    </p>\n                    <p>\n                        <strong>Durée de conservation :</strong>\n                        5 ans après la dernière consultation\n                    </p>\n                </div>\n                <p>\n                    Selon la législation applicable, l’utilisateur peut\n                    disposer de droits d’accès, de rectification, de\n                    suppression, d’opposition, de limitation ou de\n                    portabilité de ses données. Toute demande peut être\n                    envoyée à l’adresse de contact indiquée ci-dessus.\n                </p>', 'html', 4, NULL, 'publie', NULL, '2026-10-03 11:09:12'),
(18, 'mention', 'cookies', 'fr', '5. Cookies et liens hypertextes', '<h2>\n                    5. Cookies et liens hypertextes\n                </h2>\n                <p>\n                    Le site peut utiliser des cookies ou des technologies\n                    similaires nécessaires à son fonctionnement, à sa\n                    sécurité ou à la mesure de fréquentation.\n                </p>\n                <p>\n                    Un cookie est un petit fichier enregistré sur le terminal\n                    de l’utilisateur lors de sa navigation. L’utilisateur\n                    peut gérer ou désactiver les cookies à partir des\n                    paramètres de son navigateur, sous réserve que certaines\n                    fonctions du site puissent alors être limitées.\n                </p>\n                <p>\n                    Le site peut contenir des liens vers des sites externes.\n                    Le Rassemblement des Chrétiens Républicains ne contrôle\n                    pas ces sites et ne peut être tenu responsable de leur\n                    contenu, de leurs pratiques ou de leur disponibilité.\n                </p>', 'html', 5, NULL, 'publie', NULL, '2026-10-03 11:09:12'),
(19, 'mention', 'droit', 'fr', '6. Droit applicable', '<h2>\n                    6. Droit applicable\n                </h2>\n                <p>\n                    Les présentes mentions légales sont soumises au droit\n                    applicable en République démocratique du Congo,\n                    sous réserve des règles impératives qui pourraient\n                    s’appliquer.\n                </p>\n                <p>\n                    En cas de litige relatif à l’utilisation du site,\n                    les parties rechercheront prioritairement une solution\n                    amiable. À défaut, le litige sera soumis aux juridictions\n                    compétentes selon les règles de procédure applicables.\n                </p>', 'html', 6, NULL, 'publie', NULL, '2026-10-03 11:09:12'),
(20, 'mention', 'dons', 'fr', '7. Cotisations et dons', '<h2>\n                    7. Cotisations et dons\n                </h2>\n                <p>\n                    Les modalités relatives aux cotisations, aux dons et\n                    aux contributions sont présentées sur les pages\n                    concernées du site.\n                </p>\n                <p>\n                    Les montants, conditions, moyens de paiement et règles\n                    de traitement des contributions doivent être communiqués\n                    de manière claire à l’utilisateur avant toute validation.\n                </p>\n                <p>\n                    Les informations définitives relatives au régime applicable\n                    aux cotisations et aux dons devront être validées par le\n                    responsable juridique ou administratif du RCR avant\n                    publication.\n                </p>', 'html', 7, NULL, 'publie', NULL, '2026-10-03 11:09:12'),
(21, 'politique-confidentialite', 'protection', 'fr', 'I. Protection des données', '<h2>\n                    I. Protection des données\n                </h2>\n                <p>\n                    Le <strong>Rassemblement des Chrétiens Républicains\n                    (RCR)</strong> accorde une importance capitale à la\n                    protection de la vie privée et des données personnelles\n                    de ses membres, sympathisants et visiteurs naviguant\n                    sur <strong>rcr.cd</strong>.\n                </p>', 'html', 1, NULL, 'publie', NULL, '2026-10-03 11:09:12'),
(22, 'politique-confidentialite', 'collecte', 'fr', 'Article 1 — Données collectées', '<h2>\n                    Article 1 — Données collectées\n                </h2>\n                <p>\n                    Nous pouvons collecter différents types de données\n                    lorsque vous interagissez avec notre plateforme.\n                    Les informations peuvent notamment comprendre :\n                </p>\n                <h3>\n                    Données d’identification\n                </h3>\n                <p>\n                    Nom, prénom, adresse e-mail, numéro de téléphone,\n                    commune ou ville de résidence, ainsi que toute autre\n                    information fournie volontairement via nos formulaires\n                    de contact, d’adhésion ou d’inscription.\n                </p>\n                <h3>\n                    Données de navigation\n                </h3>\n                <p>\n                    Des données techniques de navigation peuvent être\n                    collectées, notamment au moyen de cookies techniques\n                    et, lorsque cela est utilisé, de dispositifs de mesure\n                    d’audience nécessaires au bon fonctionnement et à\n                    l’optimisation du site.\n                </p>\n                <h3>\n                    Données financières\n                </h3>\n                <p>\n                    Les informations relatives aux contributions et\n                    cotisations peuvent être traitées par l’intermédiaire\n                    de passerelles de paiement partenaires agréées,\n                    notamment les services de paiement mobile ou\n                    électronique utilisés par le site.\n                </p>', 'html', 2, NULL, 'publie', NULL, '2026-10-03 11:09:12'),
(23, 'politique-confidentialite', 'finalite', 'fr', 'Article 2 — Finalité du traitement des données', '<h2>\n                    Article 2 — Finalité du traitement des données\n                </h2>\n                <p>\n                    Les informations recueillies font l’objet d’un\n                    traitement informatique destiné notamment à :\n                </p>\n                <ul>\n                    <li>\n                        répondre aux demandes transmises via les formulaires\n                        de contact ;\n                    </li>\n                    <li>\n                        gérer administrativement le fichier des membres\n                        et sympathisants du RCR ;\n                    </li>\n                    <li>\n                        assurer la communication institutionnelle,\n                        l’information politique et la convocation aux\n                        activités statutaires du parti ;\n                    </li>\n                    <li>\n                        gérer les demandes d’adhésion et les informations\n                        nécessaires à leur traitement ;\n                    </li>\n                    <li>\n                        assurer le suivi administratif des cotisations,\n                        contributions et soutiens effectués en ligne ;\n                    </li>\n                    <li>\n                        sécuriser et améliorer l’expérience de navigation\n                        sur le site rcr.cd.\n                    </li>\n                </ul>', 'html', 3, NULL, 'publie', NULL, '2026-10-03 11:09:12'),
(24, 'politique-confidentialite', 'securite', 'fr', 'Article 3 — Sécurité des données', '<h2>\n                    Article 3 — Sécurité des données\n                </h2>\n                <p>\n                    Des mesures techniques et organisationnelles sont mises\n                    en place afin de protéger les données personnelles\n                    contre les accès non autorisés, la modification,\n                    la divulgation, la perte ou la destruction.\n                </p>\n                <p>\n                    Ces mesures peuvent notamment comprendre l’utilisation\n                    du protocole de chiffrement <strong>HTTPS</strong>,\n                    la sécurisation des accès administratifs et la\n                    restriction de l’accès aux données aux seuls\n                    administrateurs ou responsables habilités.\n                </p>\n                <div class=\"legal-info\">\n                    <p>\n                        <strong>Principe de sécurité :</strong>\n                        l’accès aux données personnelles est limité aux\n                        personnes qui en ont besoin dans le cadre de\n                        leurs fonctions.\n                    </p>\n                    <p>\n                        <strong>Transmission sécurisée :</strong>\n                        les échanges avec le site sont protégés par\n                        les mécanismes de sécurité disponibles sur\n                        l’infrastructure du site.\n                    </p>\n                </div>', 'html', 4, NULL, 'publie', NULL, '2026-10-03 11:09:12'),
(25, 'politique-confidentialite', 'finances', 'fr', 'Article 4 — Données financières et traçabilité des contributions', '<h2>\n                    Article 4 — Données financières et traçabilité des contributions\n                </h2>\n                <h3>\n                    Sécurisation des paiements\n                </h3>\n                <p>\n                    Les transactions financières, cotisations et\n                    contributions en ligne effectuées sur\n                    <strong>rcr.cd</strong> sont traitées par le biais\n                    de passerelles de paiement sécurisées.\n                </p>\n                <p>\n                    Le RCR ne stocke aucune donnée sensible liée aux\n                    moyens de paiement, tels que les codes secrets ou\n                    les détails complets des portefeuilles électroniques,\n                    sur ses propres serveurs, dans la mesure où ces\n                    informations sont traitées par le prestataire\n                    de paiement.\n                </p>\n                <h3>\n                    Registre des adhésions\n                </h3>\n                <p>\n                    Les informations collectées dans le cadre des\n                    formulaires d’adhésion font l’objet d’un traitement\n                    interne strict destiné exclusivement à la gestion\n                    de la vie du parti, conformément à ses textes\n                    fondamentaux et aux règles applicables.\n                </p>', 'html', 5, NULL, 'publie', NULL, '2026-10-03 11:09:12'),
(26, 'politique-confidentialite', 'partage', 'fr', 'Article 5 — Partage des données', '<h2>\n                    Article 5 — Partage des données\n                </h2>\n                <p>\n                    Les données personnelles collectées sont destinées\n                    au Rassemblement des Chrétiens Républicains et\n                    sont traitées dans le cadre des finalités indiquées\n                    dans la présente politique.\n                </p>\n                <p>\n                    Elles ne sont pas vendues, échangées, louées ou\n                    cédées à des tiers à des fins commerciales.\n                </p>\n                <p>\n                    Lorsque cela est nécessaire au fonctionnement d’un\n                    service, certaines données strictement nécessaires\n                    peuvent être traitées par des prestataires techniques\n                    ou des partenaires intervenant dans le fonctionnement\n                    du site, notamment pour le traitement des paiements,\n                    l’hébergement ou les services techniques.\n                </p>', 'html', 6, NULL, 'publie', NULL, '2026-10-03 11:09:12'),
(27, 'politique-confidentialite', 'droits', 'fr', 'Article 6 — Droits des utilisateurs', '<h2>\n                    Article 6 — Droits des utilisateurs\n                </h2>\n                <p>\n                    Conformément aux réglementations applicables en matière\n                    de protection des données personnelles, vous pouvez\n                    disposer de droits concernant les informations\n                    personnelles vous concernant.\n                </p>\n                <ul>\n                    <li>\n                        droit d’accès aux données personnelles vous concernant ;\n                    </li>\n                    <li>\n                        droit de demander la rectification des informations\n                        inexactes ou incomplètes ;\n                    </li>\n                    <li>\n                        droit de demander la suppression de certaines données,\n                        lorsque les conditions légales le permettent ;\n                    </li>\n                    <li>\n                        droit de vous opposer à certains traitements,\n                        lorsque la réglementation applicable le prévoit.\n                    </li>\n                </ul>\n                <div class=\"legal-info\">\n                    <p>\n                        <strong>Pour exercer vos droits :</strong>\n                    </p>\n                    <p>\n                        Vous pouvez adresser votre demande par e-mail à :\n                        <a href=\"mailto:contact@rcr.cd\">\n                            contact@rcr.cd\n                        </a>\n                    </p>\n                </div>', 'html', 7, NULL, 'publie', NULL, '2026-10-03 11:09:12'),
(28, 'politique-confidentialite', 'adhesion', 'fr', 'II. Conditions particulières d’adhésion et de contribution en ligne', '<h2>\n                    II. Conditions particulières d’adhésion et de contribution en ligne\n                </h2>\n                <p>\n                    L’utilisation des formulaires d’adhésion et de\n                    contribution financière en ligne sur\n                    <strong>rcr.cd</strong> implique la prise de\n                    connaissance et l’acceptation des clauses\n                    applicables ci-dessous.\n                </p>\n                <h3>\n                    Article A — Conditions d’adhésion en ligne au RCR\n                </h3>\n                <p>\n                    <strong>1.</strong>\n                    L’utilisation du formulaire d’adhésion en ligne\n                    vaut acceptation pleine et entière des statuts et\n                    du règlement intérieur du Rassemblement des Chrétiens\n                    Républicains (RCR).\n                </p>\n                <p>\n                    <strong>2.</strong>\n                    Le demandeur certifie sur l’honneur l’exactitude de\n                    l’ensemble des informations renseignées dans le\n                    formulaire. Toute fausse déclaration pourra entraîner\n                    l’annulation de la demande ou de l’adhésion,\n                    conformément aux règles applicables du RCR.\n                </p>\n                <p>\n                    <strong>3.</strong>\n                    L’adhésion devient effective après validation par\n                    les instances compétentes du parti et, le cas échéant,\n                    après encaissement effectif de la cotisation statutaire\n                    initiale.\n                </p>', 'html', 8, NULL, 'publie', NULL, '2026-10-03 11:09:12'),
(29, 'politique-confidentialite', 'contributions', 'fr', 'Article B — Modalités des contributions financières et dons', '<h2>\n                    Article B — Modalités des contributions financières et dons\n                </h2>\n                <h3>\n                    1. Caractère des contributions\n                </h3>\n                <p>\n                    Les versements, cotisations ou contributions volontaires\n                    réalisés sur <strong>rcr.cd</strong> constituent des\n                    actes de soutien aux activités politiques et citoyennes\n                    du RCR.\n                </p>\n                <p>\n                    Sauf dispositions légales contraires ou conditions\n                    particulières applicables à une transaction,\n                    ces versements ne sont pas remboursables.\n                </p>\n                <h3>\n                    2. Validation des transactions\n                </h3>\n                <p>\n                    L’utilisateur garantit qu’il est pleinement habilité\n                    à utiliser le moyen de paiement mobile ou électronique\n                    employé pour effectuer sa contribution.\n                </p>\n                <p>\n                    En cas d’échec de la transaction ou de rejet par\n                    le service de paiement, notamment <strong>FlexPaie</strong>,\n                    la contribution ne sera pas considérée comme validée\n                    tant que son paiement effectif n’aura pas été confirmé.\n                </p>\n                <h3>\n                    3. Transparence comptable\n                </h3>\n                <p>\n                    Le RCR s’engage à assurer la traçabilité rigoureuse\n                    de l’ensemble des fonds perçus en ligne, dans le\n                    respect des dispositions légales applicables au\n                    financement des partis politiques en République\n                    Démocratique du Congo.\n                </p>', 'html', 9, NULL, 'publie', NULL, '2026-10-03 11:09:12'),
(30, 'politique-confidentialite', 'cookies', 'fr', 'Cookies et données de navigation', '<h2>\n                    Cookies et données de navigation\n                </h2>\n                <p>\n                    Le site peut utiliser des cookies techniques ou des\n                    technologies similaires nécessaires à son bon\n                    fonctionnement, à sa sécurité et, le cas échéant,\n                    à la mesure de fréquentation.\n                </p>\n                <p>\n                    Les cookies peuvent permettre au site de reconnaître\n                    certaines informations techniques relatives à la\n                    navigation et de faciliter l’utilisation de certaines\n                    fonctionnalités.\n                </p>\n                <p>\n                    L’utilisateur peut gérer ou désactiver les cookies\n                    à partir des paramètres de son navigateur, sous\n                    réserve que certaines fonctionnalités du site puissent\n                    alors être limitées.\n                </p>', 'html', 10, NULL, 'publie', NULL, '2026-10-03 11:09:12'),
(31, 'politique-confidentialite', 'contact', 'fr', 'Contact', '<h2>\n                    Contact\n                </h2>\n                <p>\n                    Pour toute question relative à la présente Politique\n                    de confidentialité, à l’utilisation de vos données\n                    personnelles ou à l’exercice de vos droits, vous\n                    pouvez contacter le Rassemblement des Chrétiens\n                    Républicains.\n                </p>\n                <div class=\"legal-info\">\n                    <p>\n                        <strong>Rassemblement des Chrétiens Républicains — RCR</strong>\n                    </p>\n                    <p>\n                        <strong>Adresse :</strong>\n                        03, Avenue Olongi, Quartier Mama-Yemo,\n                        Ngaliema — Kinshasa, RDC\n                    </p>\n                    <p>\n                        <strong>Téléphones :</strong>\n                        +243 82 377 9098 / +243 85 046 6052\n                    </p>\n                    <p>\n                        <strong>E-mail :</strong>\n                        <a href=\"mailto:contact@rcr.cd\">\n                            contact@rcr.cd\n                        </a>\n                    </p>\n                </div>', 'html', 11, NULL, 'publie', NULL, '2026-10-03 11:09:12'),
(32, 'home', 'notre_maitre', 'fr', 'Notre Maître', '<section id=\"about\" class=\"py-5\">\r\n    <div class=\"container\">\r\n        <div class=\"text-center mb-5\">\r\n            <h2 class=\"fw-bold text-primary\">\r\n                Notre Maître\r\n            </h2>\r\n            <p class=\"text-muted\">\r\n                Nous, Chrétiens Républicains, avons pour Maître\r\n                le Seigneur Jésus-Christ.\r\n            </p>\r\n        </div>\r\n        <div class=\"row align-items-center g-4\">\r\n            <div class=\"col-lg-5\">\r\n                <img src=\"./media/mbres/JS.jpg\"\r\n                     class=\"img-fluid rounded\"\r\n                     alt=\"Jésus-Christ\">\r\n            </div>\r\n            <div class=\"col-lg-7\">\r\n                <div class=\"card border-0 p-4\">\r\n                    <h4 class=\"mb-3\">\r\n                        Notre Doctrine\r\n                    </h4>\r\n                    <p class=\"text-muted\" style=\"text-align: justify;\">\r\n                        Cette doctrine est unique à son genre.\r\n                        Le Christianisme est considéré comme un mode de vie\r\n                        capable de transformer positivement la société\r\n                        et de réorganiser notre civilisation\r\n                        pour le bien-être de tous.\r\n                    </p>\r\n                    <p class=\"text-muted\" style=\"text-align: justify;\">\r\n                        Jésus-Christ a changé le cours de l\'Histoire.\r\n                        Son enseignement basé sur l\'amour,\r\n                        la justice et la sagesse continue\r\n                        d\'influencer les nations.\r\n                    </p>\r\n                    <h5 class=\"fw-bold text-dark\">\r\n                                Sa vie et son message provoquent\r\n                                des changements\r\n                            </h5>\r\n                            <p class=\"text-muted\"\r\n                               style=\"text-align: justify;\">\r\n                                Là où son enseignement a été reconnu,\r\n                                les effets ont été visibles :\r\n                                protection des enfants,\r\n                                fondation des écoles,\r\n                                abolition de l\'esclavage,\r\n                                valorisation du mariage\r\n                                et promotion des droits humains.\r\n                            </p>\r\n                            <p class=\"text-muted\">\r\n                                <strong>\r\n                                    Deux missions importantes :\r\n                                </strong>\r\n                            </p>\r\n                            <ol class=\"text-muted\">\r\n                                <li>\r\n                                    Consacrer Dieu comme\r\n                                    le seul Maître des Nations.\r\n                                </li>\r\n                                <li>\r\n                                    Mettre les richesses\r\n                                    des Nations au service de tous.\r\n                                </li>\r\n                            </ol>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</section>', 'html', 50, NULL, 'publie', 1, '2026-10-08 09:49:05');

-- --------------------------------------------------------

--
-- Structure de la table `site_menu`
--

CREATE TABLE `site_menu` (
  `id` int(10) UNSIGNED NOT NULL,
  `zone` enum('header','footer') NOT NULL DEFAULT 'header',
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `langue` char(2) NOT NULL DEFAULT 'fr',
  `libelle` varchar(120) NOT NULL,
  `url` varchar(255) NOT NULL,
  `ordre` int(11) NOT NULL DEFAULT 0,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `classe` varchar(40) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `site_menu`
--

INSERT INTO `site_menu` (`id`, `zone`, `parent_id`, `langue`, `libelle`, `url`, `ordre`, `actif`, `classe`) VALUES
(1, 'header', NULL, 'fr', 'Accueil', '?pages=home', 1, 1, ''),
(2, 'header', NULL, 'fr', 'Qui sommes-nous ?', '#', 2, 1, ''),
(3, 'header', 2, 'fr', 'Présentation', '?pages=apropos', 1, 1, ''),
(4, 'header', 2, 'fr', 'Chef du parti', '?pages=parti', 2, 1, ''),
(5, 'header', NULL, 'fr', 'Où en sommes-nous ?', '#', 3, 1, ''),
(6, 'header', 5, 'fr', 'Nos événements', '?pages=publication', 1, 1, ''),
(7, 'header', NULL, 'fr', 'Contact', '?pages=contact', 4, 1, ''),
(8, 'header', NULL, 'fr', 'Votre soutien', '?pages=soutenir', 5, 1, 'support-item'),
(9, 'header', NULL, 'fr', 'Nous rejoindre', '#', 6, 1, 'dropdown join-item'),
(10, 'header', 9, 'fr', 'Adhérer', './adhere/adhesion.php', 1, 1, ''),
(11, 'header', 9, 'fr', 'Renouveler mon adhésion', '@renouveler', 2, 1, ''),
(12, 'footer', NULL, 'fr', 'Menu', '#', 1, 1, ''),
(13, 'footer', 12, 'fr', 'Accueil', '?pages=home', 1, 1, ''),
(14, 'footer', 12, 'fr', 'Qui sommes-nous', '?pages=apropos', 2, 1, ''),
(15, 'footer', 12, 'fr', 'Nos événements', '?pages=publication', 3, 1, ''),
(16, 'footer', 12, 'fr', 'Mot du Président', '?pages=parti', 4, 1, ''),
(17, 'footer', 12, 'fr', 'Nos Idées Forces', '?pages=home#textes-fondamentaux', 5, 1, ''),
(18, 'footer', 12, 'fr', 'Soutenir', '?pages=soutenir', 6, 1, ''),
(19, 'footer', NULL, 'fr', 'Nous rejoindre', '#', 2, 1, ''),
(20, 'footer', 19, 'fr', 'J\'adhère', './adhere/adhesion.php', 1, 1, ''),
(21, 'footer', 19, 'fr', 'Je renouvelle', '@renouveler', 2, 1, ''),
(22, 'footer', 19, 'fr', 'Je soutiens', '?pages=soutenir', 3, 1, '');

-- --------------------------------------------------------

--
-- Structure de la table `site_reglages`
--

CREATE TABLE `site_reglages` (
  `cle` varchar(80) NOT NULL,
  `langue` char(2) NOT NULL DEFAULT 'fr',
  `valeur` text NOT NULL,
  `groupe` varchar(40) NOT NULL DEFAULT 'general',
  `libelle` varchar(160) NOT NULL DEFAULT '',
  `type` enum('texte','html','nombre','url') NOT NULL DEFAULT 'texte',
  `modifie_par` int(11) DEFAULT NULL,
  `modifie_le` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `site_reglages`
--

INSERT INTO `site_reglages` (`cle`, `langue`, `valeur`, `groupe`, `libelle`, `type`, `modifie_par`, `modifie_le`) VALUES
('app_android_url', 'fr', '', 'contact', 'Application Android : lien de téléchargement (URL https ou chemin, ex. telechargements/rcr.apk)', 'url', NULL, '2026-10-07 10:09:47'),
('app_ios_url', 'fr', '', 'contact', 'Application iPhone : lien App Store / TestFlight (vide = « Bientôt »)', 'url', NULL, '2026-10-07 10:09:47'),
('btn_adherer', 'fr', '', 'boutons', 'Bouton adhésion', 'texte', 1, '2026-10-03 16:22:56'),
('btn_rejoindre', 'fr', '', 'boutons', 'Bouton rejoindre', 'texte', 1, '2026-10-03 16:24:13'),
('btn_soutenir', 'fr', '', 'boutons', 'Bouton soutien', 'texte', 1, '2026-10-03 16:22:56'),
('contact_adresse', 'fr', 'Ngaliema - Kinshasa', 'contact', 'contact_adresse', 'texte', 1, '2026-10-03 16:22:56'),
('contact_adresse_complete', 'fr', '03, Avenue Olongi,Quartier Mama-Yemo,Ngaliema - Kinshasa', 'contact', 'Adresse complète (pied de page, page contact)', 'texte', 1, '2026-10-08 09:54:54'),
('contact_email', 'fr', 'contact@rcr.cd', 'contact', 'contact_email', 'texte', 1, '2026-10-03 16:22:56'),
('contact_intro', 'fr', 'Retrouvez ici nos coordonnées et un formulaire pour nous écrire directement.', 'pages', 'Page contact : introduction', 'texte', NULL, '2026-10-07 10:09:47'),
('contact_telephone', 'fr', '+243 82 377 9098', 'contact', 'contact_telephone', 'texte', 1, '2026-10-03 16:22:56'),
('contact_telephone2', 'fr', '+243 85 046 6052', 'contact', 'contact_telephone2', 'texte', 1, '2026-10-03 16:22:56'),
('contact_telephone3', 'fr', '', 'contact', 'Téléphone 3 (page contact)', 'texte', NULL, '2026-10-07 10:09:47'),
('contact_titre', 'fr', 'Contactez-nous', 'pages', 'Page contact : titre', 'texte', NULL, '2026-10-07 10:09:47'),
('equipe_intro', 'fr', 'Découvrez les responsables et membres du Rassemblement des Chrétiens Républicains.', 'accueil', 'Accueil : introduction de la section équipe', 'texte', NULL, '2026-10-07 10:09:47'),
('equipe_surtitre', 'fr', 'Notre équipe', 'accueil', 'Accueil : surtitre de la section équipe', 'texte', NULL, '2026-10-07 10:09:47'),
('equipe_titre', 'fr', 'Les membres du RCR', 'accueil', 'Accueil : titre de la section équipe', 'texte', NULL, '2026-10-07 10:09:47'),
('evenements_intro', 'fr', 'Découvrez les dernières activités, conférences et événements organisés par le RCR.', 'pages', 'Page actualités : introduction', 'texte', NULL, '2026-10-07 10:09:47'),
('evenements_titre', 'fr', 'Nos événements', 'pages', 'Page actualités : titre', 'texte', NULL, '2026-10-07 10:09:47'),
('footer_contact_intro', 'fr', 'Une question, une suggestion ou besoin d\'informations ?', 'contact', 'Pied de page : texte « Contactez-nous »', 'texte', NULL, '2026-10-07 10:09:47'),
('home_citation', 'fr', '« Sans Rassemblement des Chrétiens Républicains, la démocratie Congolaise serait affaiblie »', 'accueil', 'Accueil : citation', 'texte', 1, '2026-10-03 16:22:56'),
('home_presentation', 'fr', 'est en dignité et notoriété le Premier Plus Grand Serviteur et le Président Fondateur du mouvement.', 'accueil', 'Accueil : présentation officielle', 'texte', 1, '2026-10-03 16:22:56'),
('home_soustitre', 'fr', 'Un engagement citoyen pour un avenir meilleur et durable', 'accueil', 'Accueil : sous-titre', 'texte', 1, '2026-10-08 09:54:54'),
('home_surtitre', 'fr', 'Chrétiens Républicains', 'accueil', 'Accueil : surtitre', 'texte', 1, '2026-10-08 09:54:54'),
('home_titre', 'fr', 'RASSEMBLER POUR BATIR UN PAYS PLUS BEAUX QU\'AVANT', 'accueil', 'Accueil : titre principal', 'texte', 1, '2026-10-08 09:54:54'),
('partenaires_intro', 'fr', 'Les organisations et personnalités qui accompagnent l’action du Rassemblement des Chrétiens Républicains.', 'accueil', 'Accueil : introduction de la section partenaires', 'texte', NULL, '2026-10-07 10:09:47'),
('partenaires_surtitre', 'fr', 'Ils nous soutiennent', 'accueil', 'Accueil : surtitre de la section partenaires', 'texte', NULL, '2026-10-07 10:09:47'),
('partenaires_titre', 'fr', 'Nos partenaires', 'accueil', 'Accueil : titre de la section partenaires', 'texte', NULL, '2026-10-07 10:09:47'),
('parti_creation', 'fr', '2006', 'identite', 'Année de création', 'texte', 1, '2026-10-03 16:22:56'),
('parti_devise', 'fr', 'Rassembler pour bâtir un pays plus beau qu\'avant', 'identite', 'Devise', 'texte', 1, '2026-10-03 16:22:56'),
('parti_juridique', 'fr', 'Arrêté N°010/2006 du 30 janvier 2006', 'identite', 'Personnalité juridique', 'texte', 1, '2026-10-03 16:22:56'),
('parti_nom', 'fr', 'Rassemblement des Chrétiens Républicains', 'identite', 'Nom complet du parti', 'texte', 1, '2026-10-03 16:22:56'),
('parti_sigle', 'fr', 'RCR', 'identite', 'Sigle', 'texte', 1, '2026-10-03 16:22:56'),
('president_bio', 'fr', 'Initiateur et Président du Rassemblement, il est le Garant de l\'orientation de la Vision et de la Discipline, le Dépositaire Général de la doctrine chrétienne-républicaine et le Chef du Parti.', 'identite', 'Présentation du Président', 'html', 1, '2026-10-03 16:22:56'),
('president_idees', 'fr', 'Construire une nation fondée sur les valeurs chrétiennes, la démocratie, la responsabilité, la justice et l\'unité nationale.', 'identite', 'Les idées du Président', 'texte', 1, '2026-10-03 16:22:56'),
('president_nom', 'fr', 'Benjamin Jean de Dieu BONIOMA ISELONGE', 'identite', 'Nom du Président Fondateur', 'texte', 1, '2026-10-03 16:22:56'),
('president_titre', 'fr', 'Président du Parti', 'identite', 'Titre du Président', 'texte', 1, '2026-10-03 16:22:56'),
('reseau_facebook', 'fr', 'https://www.facebook.com/share/1EbvrqyTGK/', 'contact', 'reseau_facebook', 'texte', 1, '2026-10-03 16:22:56'),
('reseau_instagram', 'fr', '', 'contact', 'Lien Instagram', 'url', NULL, '2026-10-07 10:09:47'),
('reseau_linkedin', 'fr', '', 'contact', 'Lien LinkedIn', 'url', NULL, '2026-10-07 10:09:47'),
('reseau_tiktok', 'fr', '', 'contact', 'Lien TikTok', 'url', NULL, '2026-10-07 10:09:47'),
('reseau_whatsapp', 'fr', '', 'contact', 'À renseigner : reseau_whatsapp', 'texte', 1, '2026-10-03 16:22:56'),
('reseau_x', 'fr', '', 'contact', 'À renseigner : reseau_x', 'texte', 1, '2026-10-03 16:22:56'),
('reseau_youtube', 'fr', '', 'contact', 'À renseigner : reseau_youtube', 'texte', 1, '2026-10-03 16:22:56'),
('seo_description_defaut', 'fr', 'Site officiel du Rassemblement des Chrétiens Républicains (RCR), RDC.', 'seo', 'Meta description par défaut', 'texte', 1, '2026-10-03 16:22:56'),
('seo_image', 'fr', '', 'seo', 'Image de partage par défaut (URL absolue)', 'url', NULL, '2026-10-07 10:09:47'),
('soutenir_titre', 'fr', 'Soutenir le RCR', 'pages', 'Page « Je soutiens » : titre', 'texte', NULL, '2026-10-07 10:09:47'),
('textes_intro', 'fr', 'Découvrez les documents et textes fondamentaux du Rassemblement des Chrétiens Républicains.', 'accueil', 'Textes fondamentaux : introduction', 'texte', 1, '2026-10-03 16:22:56'),
('textes_titre', 'fr', 'Nos textes fondamentaux', 'accueil', 'Textes fondamentaux : titre', 'texte', 1, '2026-10-03 16:22:56'),
('valeur_amour_texte', 'fr', 'Amour de Dieu et du prochain', 'valeurs', 'Valeur : texte Amour', 'texte', 1, '2026-10-03 16:22:56'),
('valeur_amour_titre', 'fr', 'Amour', 'valeurs', 'Valeur : titre Amour', 'texte', 1, '2026-10-03 16:22:56'),
('valeur_democratie_texte', 'fr', 'Liberté, responsabilité et devoir', 'valeurs', 'Valeur : texte Démocratie', 'texte', 1, '2026-10-03 16:22:56'),
('valeur_democratie_titre', 'fr', 'Démocratie', 'valeurs', 'Valeur : titre Démocratie', 'texte', 1, '2026-10-03 16:22:56'),
('valeur_justice_texte', 'fr', 'Expression du droit et de la vérité', 'valeurs', 'Valeur : texte Justice', 'texte', 1, '2026-10-03 16:22:56'),
('valeur_justice_titre', 'fr', 'Justice', 'valeurs', 'Valeur : titre Justice', 'texte', 1, '2026-10-03 16:22:56');

-- --------------------------------------------------------

--
-- Structure de la table `sous_categorie`
--

CREATE TABLE `sous_categorie` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `id_cat` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `territoires`
--

CREATE TABLE `territoires` (
  `id_tr` int(11) NOT NULL,
  `nom_tr` varchar(50) DEFAULT NULL,
  `id_p` int(11) DEFAULT NULL,
  `changer` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `territoires`
--

INSERT INTO `territoires` (`id_tr`, `nom_tr`, `id_p`, `changer`) VALUES
(83, 'Funa', 1, 1),
(84, 'Lukunga', 1, 0),
(85, 'Mont-Amba', 1, 0),
(86, 'Tshangu', 1, 0),
(87, 'Plateau', 1, 0),
(88, 'Songololo', 2, 0),
(89, 'Mbanza-Ngungu', 2, 0),
(92, 'Moanda', 2, 0),
(93, 'Seke-Banza', 2, 0),
(94, 'Lukula', 2, 0),
(95, 'Kenge', 3, 0),
(96, 'Popokabaka', 3, 0),
(97, 'Feshi', 3, 0),
(98, 'Kahemba', 3, 0),
(99, 'Kasongo-Lunda', 3, 0),
(102, 'Bagata', 4, 0),
(103, 'Bulungu', 4, 0),
(104, 'Gungu', 4, 0),
(105, 'Idiofa', 4, 0),
(106, 'Masi-Manimba', 4, 0),
(107, 'Inongo', 5, 0),
(108, 'Kutu', 5, 0),
(109, 'Kiri', 5, 0),
(110, 'Oshwe', 5, 0),
(111, 'Bolobo', 5, 0),
(113, 'Luebo', 6, 0),
(114, 'Ilebo', 6, 0),
(115, 'Dekese', 6, 0),
(116, 'Kananga', 7, 0),
(117, 'Demba', 7, 0),
(118, 'Dibaya', 7, 0),
(119, 'Luiza', 7, 0),
(121, 'Kabeya-Kamwanga', 8, 0),
(122, 'Miabi', 8, 0),
(123, 'Katanda', 8, 0),
(124, 'Kabinda', 9, 0),
(125, 'Luilu', 9, 0),
(126, 'Ngandajika', 9, 0),
(127, 'Lubao', 9, 0),
(128, 'Lusambo', 10, 0),
(129, 'Lodja', 10, 0),
(130, 'Katako-Kombe', 10, 0),
(131, 'Lubefu', 10, 0),
(133, 'Kasongo', 11, 0),
(134, 'Pangi', 11, 0),
(135, 'Kabambare', 11, 0),
(137, 'Uvira', 12, 0),
(138, 'Walungu', 12, 0),
(139, 'Fizi', 12, 0),
(140, 'Shabunda', 12, 0),
(141, 'Mwenga', 12, 0),
(143, 'Rutshuru', 13, 0),
(144, 'Nyiragongo', 13, 0),
(145, 'Masisi', 13, 0),
(146, 'Lubero', 13, 0),
(147, 'Beni', 13, 0),
(149, 'Aru', 14, 0),
(150, 'Mahagi', 14, 0),
(151, 'Irumu', 14, 0),
(152, 'Djugu', 14, 0),
(154, 'Wamba', 15, 0),
(155, 'Dungu', 15, 0),
(156, 'Niangara', 15, 0),
(157, 'Buta', 16, 0),
(158, 'Aketi', 16, 0),
(159, 'Bondo', 16, 0),
(160, 'Poko', 16, 0),
(162, 'Banalia', 17, 0),
(163, 'Isangi', 17, 0),
(164, 'Opala', 17, 0),
(165, 'Ubundu', 17, 0),
(166, 'Lisala', 18, 0),
(167, 'Bumba', 18, 0),
(168, 'Bongandanga', 18, 0),
(170, 'Bosobolo', 19, 0),
(171, 'Yakoma', 19, 0),
(172, 'Gemena', 20, 0),
(173, 'Budjala', 20, 0),
(174, 'Libenge', 20, 0),
(177, 'Bolomba', 21, 0),
(179, 'Boende', 22, 0),
(180, 'Ikela', 22, 0),
(181, 'Befale', 22, 0),
(182, 'Kamina', 23, 0),
(183, 'Kabongo', 23, 0),
(184, 'Malemba-Nkulu', 23, 0),
(185, 'Bukama', 23, 0),
(187, 'Dilolo', 24, 0),
(188, 'Lubudi', 24, 0),
(189, 'Mutshatsha', 24, 0),
(192, 'Kambove', 25, 0),
(193, 'Kasenga', 25, 0),
(194, 'Mitwaba', 25, 0),
(195, 'Kalemie', 26, 0),
(196, 'Moba', 26, 0),
(197, 'Nyunzu', 26, 0),
(198, 'Manono', 26, 0),
(199, 'ANGO', 16, 0),
(200, 'BAMBESA', 16, 0),
(201, 'BASANKUSU', 21, 0),
(202, 'BIKORO', 21, 0),
(204, 'BOMONGO', 21, 0),
(205, 'INGENDE', 21, 0),
(206, 'LUKOLELA', 21, 0),
(207, 'MANKANZA', 21, 0),
(208, 'Kipushi', 25, 0),
(209, 'Pweto', 25, 0),
(210, 'Sakania', 25, 0),
(211, 'Kaniama', 23, 0),
(212, 'Faradje', 15, 0),
(213, 'Watsa', 15, 0),
(214, 'Mambasa', 14, 0),
(215, 'Mweka', 6, 0),
(216, 'Tshikapa', 6, 0),
(217, 'Dimbelenge', 7, 0),
(218, 'Kazumba', 7, 0),
(219, 'Lupatapata', 8, 0),
(220, 'Tshilenge', 8, 0),
(221, 'Kasangulu', 2, 0),
(222, 'Kimvula', 2, 0),
(223, 'Luozi', 2, 0),
(224, 'Madimba', 2, 0),
(225, 'Tshela', 2, 0),
(226, 'Kamiji', 9, 0),
(227, 'Kapanga', 24, 0),
(228, 'Sandoa', 24, 0),
(229, 'Kwamouth', 5, 0),
(230, 'Mushie', 5, 0),
(231, 'Yumbi', 5, 0),
(232, 'Kailo', 11, 0),
(233, 'Kibombo', 11, 0),
(234, 'Lubutu', 11, 0),
(235, 'Punia', 11, 0),
(236, 'Walikale', 13, 0),
(237, 'Businga', 19, 0),
(238, 'Mobayi Mbongo', 19, 0),
(239, 'Kole', 10, 0),
(240, 'Lomela', 10, 0),
(241, 'Idjwi', 12, 0),
(242, 'Kabare', 12, 0),
(243, 'Kalehe', 12, 0),
(244, 'Kungu', 20, 0),
(245, 'Kabolo', 26, 0),
(246, 'Kongolo', 26, 0),
(247, 'Bafwasende', 17, 0),
(248, 'Basoko', 17, 0),
(249, 'Yahuma', 17, 0),
(250, 'Bokungu', 22, 0),
(251, 'Djolu', 22, 0),
(252, 'Monkoto', 22, 0);

-- --------------------------------------------------------

--
-- Structure de la table `vu`
--

CREATE TABLE `vu` (
  `id_vu` int(11) NOT NULL,
  `id_act` int(11) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `date_vu` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `activite`
--
ALTER TABLE `activite`
  ADD PRIMARY KEY (`id_act`),
  ADD KEY `idx_activite_etat` (`etat_modifier`,`date_pub`),
  ADD KEY `idx_activite_categ` (`categorie`,`etat_modifier`),
  ADD KEY `idx_activite_date` (`date_pub`);

--
-- Index pour la table `adhesion`
--
ALTER TABLE `adhesion`
  ADD PRIMARY KEY (`id_ad`),
  ADD UNIQUE KEY `uq_adhesion_codes` (`codes`),
  ADD KEY `idx_adhesion_statut` (`statut`,`date_echeance`),
  ADD KEY `idx_adhesion_qt` (`id_qt`),
  ADD KEY `idx_adhesion_prov` (`province`),
  ADD KEY `idx_adhesion_date` (`dat_adhesion`),
  ADD KEY `idx_adhesion_mail` (`mail`),
  ADD KEY `idx_adhesion_token` (`payment_token`);

--
-- Index pour la table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_adm`),
  ADD KEY `idx_admin_pseudo` (`pseudo`);

--
-- Index pour la table `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD PRIMARY KEY (`id_tok`),
  ADD UNIQUE KEY `uq_api_token_hash` (`token_hash`),
  ADD KEY `idx_api_tokens_membre` (`id_ad`,`revoque`),
  ADD KEY `idx_api_tokens_expire` (`expire_le`);

--
-- Index pour la table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_action` (`action`,`cree_le`),
  ADD KEY `idx_adm` (`id_adm`,`cree_le`),
  ADD KEY `idx_audit_cible` (`cible`,`cible_id`);

--
-- Index pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id_cat`);

--
-- Index pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD PRIMARY KEY (`id_cmt`),
  ADD KEY `idx_comm_act` (`id_act`);

--
-- Index pour la table `cotisation`
--
ALTER TABLE `cotisation`
  ADD PRIMARY KEY (`id_cot`);

--
-- Index pour la table `dislike`
--
ALTER TABLE `dislike`
  ADD PRIMARY KEY (`id_dislike`),
  ADD KEY `idx_dislike_act` (`id_act`,`ip`);

--
-- Index pour la table `dons`
--
ALTER TABLE `dons`
  ADD PRIMARY KEY (`id_don`),
  ADD UNIQUE KEY `uq_dons_reference` (`reference`),
  ADD UNIQUE KEY `uq_dons_order` (`order_number`),
  ADD KEY `idx_dons_status` (`status`),
  ADD KEY `idx_dons_date` (`created_at`,`status`),
  ADD KEY `idx_dons_membre` (`id_ad`),
  ADD KEY `idx_dons_order` (`order_number`),
  ADD KEY `idx_dons_echeance` (`type_don`,`prochaine_echeance`),
  ADD KEY `idx_dons_transaction` (`transaction_id`(100));

--
-- Index pour la table `encadreur`
--
ALTER TABLE `encadreur`
  ADD PRIMARY KEY (`id_en`),
  ADD KEY `idx_encadreur_dest` (`id_dest`),
  ADD KEY `idx_encadreur_exp` (`id_exp`);

--
-- Index pour la table `equipes`
--
ALTER TABLE `equipes`
  ADD PRIMARY KEY (`id_eq`);

--
-- Index pour la table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id_gd`),
  ADD KEY `FK_grades_qualites` (`id_qt`),
  ADD KEY `idx_grades_qt` (`id_qt`,`actif`,`ordre`);

--
-- Index pour la table `grades_historique`
--
ALTER TABLE `grades_historique`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_gd` (`id_gd`);

--
-- Index pour la table `historiquepaiement`
--
ALTER TABLE `historiquepaiement`
  ADD PRIMARY KEY (`id_ht`),
  ADD KEY `idx_hist_ad` (`id_ad`);

--
-- Index pour la table `horsline`
--
ALTER TABLE `horsline`
  ADD PRIMARY KEY (`id_hor`),
  ADD KEY `idx_horsline_ip` (`ip_user`),
  ADD KEY `idx_horsline_temps` (`temps`);

--
-- Index pour la table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id_like`),
  ADD KEY `idx_likes_act` (`id_act`,`ip`);

--
-- Index pour la table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id_att`),
  ADD UNIQUE KEY `uniq_ip_adresse` (`ip_adresse`),
  ADD KEY `idx_bloque_jusqu_a` (`bloque_jusqu_a`);

--
-- Index pour la table `messages_contact`
--
ALTER TABLE `messages_contact`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_msg_statut` (`statut`,`cree_le`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_notif_cle` (`cle_unique`),
  ADD KEY `idx_notif_statut` (`statut`,`canal`,`cree_le`),
  ADD KEY `idx_notif_membre` (`id_ad`,`cree_le`),
  ADD KEY `idx_notif_evt` (`evenement`,`statut`);

--
-- Index pour la table `onlines`
--
ALTER TABLE `onlines`
  ADD PRIMARY KEY (`id_on`),
  ADD KEY `idx_onlines_usr` (`id_usr`),
  ADD KEY `idx_onlines_temps` (`temps`);

--
-- Index pour la table `operateurs`
--
ALTER TABLE `operateurs`
  ADD PRIMARY KEY (`id_op`);

--
-- Index pour la table `parner`
--
ALTER TABLE `parner`
  ADD PRIMARY KEY (`id_parn`),
  ADD KEY `idx_parner_dest` (`id_dest`),
  ADD KEY `idx_parner_exp` (`id_exp`);

--
-- Index pour la table `partenaire`
--
ALTER TABLE `partenaire`
  ADD PRIMARY KEY (`id_part`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_reset_token` (`token_hash`),
  ADD KEY `idx_reset_membre` (`id_ad`,`cree_le`),
  ADD KEY `idx_reset_ip` (`ip`,`cree_le`);

--
-- Index pour la table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_payments_reference` (`reference`),
  ADD UNIQUE KEY `uq_payments_order` (`order_number`),
  ADD KEY `fk_payments_adhesion` (`id_ad`),
  ADD KEY `idx_payments_status` (`status`,`created_at`),
  ADD KEY `idx_payments_membre` (`id_ad`,`status`),
  ADD KEY `idx_payments_date` (`created_at`),
  ADD KEY `idx_payments_order` (`order_number`),
  ADD KEY `idx_payments_transaction` (`transaction_id`(100)),
  ADD KEY `idx_payments_adhesion_id` (`adhesion_id`);

--
-- Index pour la table `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ref` (`reference`),
  ADD KEY `idx_evt` (`evenement`,`cree_le`),
  ADD KEY `idx_paylog_refid` (`type_transaction`,`ref_id`),
  ADD KEY `idx_pl_ref_date` (`reference`,`cree_le`);

--
-- Index pour la table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id_perm`),
  ADD UNIQUE KEY `uq_perm_code` (`code`);

--
-- Index pour la table `provinces`
--
ALTER TABLE `provinces`
  ADD PRIMARY KEY (`id_p`);

--
-- Index pour la table `qualites`
--
ALTER TABLE `qualites`
  ADD PRIMARY KEY (`id_qt`);

--
-- Index pour la table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_role`),
  ADD UNIQUE KEY `uq_role_code` (`code`);

--
-- Index pour la table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id_role`,`id_perm`);

--
-- Index pour la table `secteurs`
--
ALTER TABLE `secteurs`
  ADD PRIMARY KEY (`id_sec`),
  ADD KEY `fk_secteurs_territoires` (`id_tr`),
  ADD KEY `idx_sect_terr` (`id_tr`);

--
-- Index pour la table `site_blocs`
--
ALTER TABLE `site_blocs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_bloc` (`page`,`cle`,`langue`),
  ADD KEY `idx_page` (`page`,`langue`,`statut`,`ordre`);

--
-- Index pour la table `site_menu`
--
ALTER TABLE `site_menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_zone` (`zone`,`langue`,`actif`,`ordre`);

--
-- Index pour la table `site_reglages`
--
ALTER TABLE `site_reglages`
  ADD PRIMARY KEY (`cle`,`langue`),
  ADD KEY `idx_groupe` (`groupe`);

--
-- Index pour la table `sous_categorie`
--
ALTER TABLE `sous_categorie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_scat_cat` (`id_cat`);

--
-- Index pour la table `territoires`
--
ALTER TABLE `territoires`
  ADD PRIMARY KEY (`id_tr`),
  ADD KEY `fk_territoires_provinces` (`id_p`),
  ADD KEY `idx_terr_prov` (`id_p`);

--
-- Index pour la table `vu`
--
ALTER TABLE `vu`
  ADD PRIMARY KEY (`id_vu`),
  ADD KEY `idx_vu_act` (`id_act`,`ip`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `activite`
--
ALTER TABLE `activite`
  MODIFY `id_act` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `adhesion`
--
ALTER TABLE `adhesion`
  MODIFY `id_ad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_adm` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `api_tokens`
--
ALTER TABLE `api_tokens`
  MODIFY `id_tok` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id_cat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `commentaire`
--
ALTER TABLE `commentaire`
  MODIFY `id_cmt` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `cotisation`
--
ALTER TABLE `cotisation`
  MODIFY `id_cot` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `dislike`
--
ALTER TABLE `dislike`
  MODIFY `id_dislike` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `dons`
--
ALTER TABLE `dons`
  MODIFY `id_don` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `encadreur`
--
ALTER TABLE `encadreur`
  MODIFY `id_en` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `equipes`
--
ALTER TABLE `equipes`
  MODIFY `id_eq` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `grades`
--
ALTER TABLE `grades`
  MODIFY `id_gd` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `grades_historique`
--
ALTER TABLE `grades_historique`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `historiquepaiement`
--
ALTER TABLE `historiquepaiement`
  MODIFY `id_ht` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `horsline`
--
ALTER TABLE `horsline`
  MODIFY `id_hor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=542;

--
-- AUTO_INCREMENT pour la table `likes`
--
ALTER TABLE `likes`
  MODIFY `id_like` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id_att` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `messages_contact`
--
ALTER TABLE `messages_contact`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `onlines`
--
ALTER TABLE `onlines`
  MODIFY `id_on` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `operateurs`
--
ALTER TABLE `operateurs`
  MODIFY `id_op` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `parner`
--
ALTER TABLE `parner`
  MODIFY `id_parn` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `partenaire`
--
ALTER TABLE `partenaire`
  MODIFY `id_part` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `payment_logs`
--
ALTER TABLE `payment_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT pour la table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id_perm` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `provinces`
--
ALTER TABLE `provinces`
  MODIFY `id_p` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT pour la table `qualites`
--
ALTER TABLE `qualites`
  MODIFY `id_qt` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `roles`
--
ALTER TABLE `roles`
  MODIFY `id_role` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `secteurs`
--
ALTER TABLE `secteurs`
  MODIFY `id_sec` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=946;

--
-- AUTO_INCREMENT pour la table `site_blocs`
--
ALTER TABLE `site_blocs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT pour la table `site_menu`
--
ALTER TABLE `site_menu`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pour la table `sous_categorie`
--
ALTER TABLE `sous_categorie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `territoires`
--
ALTER TABLE `territoires`
  MODIFY `id_tr` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=253;

--
-- AUTO_INCREMENT pour la table `vu`
--
ALTER TABLE `vu`
  MODIFY `id_vu` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD CONSTRAINT `fk_api_tokens_membre` FOREIGN KEY (`id_ad`) REFERENCES `adhesion` (`id_ad`) ON DELETE CASCADE;

--
-- Contraintes pour la table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_adhesion` FOREIGN KEY (`id_ad`) REFERENCES `adhesion` (`id_ad`);

--
-- Contraintes pour la table `secteurs`
--
ALTER TABLE `secteurs`
  ADD CONSTRAINT `fk_secteurs_territoires` FOREIGN KEY (`id_tr`) REFERENCES `territoires` (`id_tr`);

--
-- Contraintes pour la table `territoires`
--
ALTER TABLE `territoires`
  ADD CONSTRAINT `fk_territoires_provinces` FOREIGN KEY (`id_p`) REFERENCES `provinces` (`id_p`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
