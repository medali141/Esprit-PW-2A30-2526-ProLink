-- ProLink — schéma + données démo (fichier unique).
-- Usage : mysql -u root < base/prolink.sql   (depuis la racine du projet)
--          ou import phpMyAdmin sur la base `prolink`.
--
-- Comptes démo — mot de passe pour TOUS : demo123
--   Admin     : chihaouidali443@gmail.com
--   Candidat  : chihaouidali44@gmail.com
--   Candidat  : issado@gmail.com

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

CREATE DATABASE IF NOT EXISTS `prolink` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `prolink`;

START TRANSACTION;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `commande_produit`;
DROP TABLE IF EXISTS `commande`;
DROP TABLE IF EXISTS `appel_offre_reponse`;
DROP TABLE IF EXISTS `appel_offre`;
DROP TABLE IF EXISTS `demande_achat_ligne`;
DROP TABLE IF EXISTS `demande_achat`;
DROP TABLE IF EXISTS `tarif_cadre`;
DROP TABLE IF EXISTS `achat_audit`;
DROP TABLE IF EXISTS `reappro_config`;
DROP TABLE IF EXISTS `produit`;
DROP TABLE IF EXISTS `budget_achat`;
DROP TABLE IF EXISTS `categorie`;
DROP TABLE IF EXISTS `user`;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `user` (
  `iduser` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `payment_email` varchar(150) DEFAULT NULL,
  `mdp` varchar(255) NOT NULL,
  `type` enum('admin','candidat','entrepreneur') NOT NULL,
  `age` int(11) NOT NULL,
  `points_fidelite` int(11) NOT NULL DEFAULT 0,
  `financial_account_name` varchar(120) DEFAULT NULL,
  `financial_account_number` varchar(80) DEFAULT NULL,
  `financial_bank_name` varchar(120) DEFAULT NULL,
  `totp_secret` varchar(64) DEFAULT NULL,
  `face_id_credential_id` varchar(255) DEFAULT NULL,
  `face_id_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `face_photo_hash` varchar(128) DEFAULT NULL,
  `face_photo_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`iduser`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user` (`iduser`, `nom`, `prenom`, `email`, `mdp`, `type`, `age`, `created_at`) VALUES
(5, 'chihaoui', 'mohammed ali', 'chihaouidali443@gmail.com', '$2y$10$Nm08Ic5P/ZRNkXUzE8RqvuUcqiHMGFAIArOrKtHItxhGuA021VRM6', 'admin', 75, '2026-04-15 13:30:47'),
(7, 'chihaoui', 'mohammed ali', 'chihaouidali44@gmail.com', '$2y$10$Nm08Ic5P/ZRNkXUzE8RqvuUcqiHMGFAIArOrKtHItxhGuA021VRM6', 'candidat', 24, '2026-04-15 13:41:01'),
(8, 'aissa', 'idouni', 'issado@gmail.com', '$2y$10$Nm08Ic5P/ZRNkXUzE8RqvuUcqiHMGFAIArOrKtHItxhGuA021VRM6', 'candidat', 75, '2026-04-15 13:44:12');

CREATE TABLE `categorie` (
  `idcategorie` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(40) NOT NULL,
  `libelle` varchar(150) NOT NULL,
  `ordre` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`idcategorie`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categorie` (`idcategorie`, `code`, `libelle`, `ordre`) VALUES
(1, 'peripheriques', 'Périphériques - saisie et audio', 10),
(2, 'pc', 'Ordinateurs bureau et portables', 20),
(3, 'telephones', 'Smartphones et téléphones mobiles', 30),
(4, 'tablettes', 'Tablettes tactiles', 40),
(5, 'chaises', 'Sièges et mobilier de bureau', 50),
(6, 'accessoires', 'Connectique et accessoires', 60);

CREATE TABLE `budget_achat` (
  `idbudget` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(150) NOT NULL,
  `annee` int(11) NOT NULL,
  `idcategorie` int(11) DEFAULT NULL COMMENT 'NULL = enveloppe globale',
  `montant_alloue` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idbudget`),
  KEY `idx_budget_annee` (`annee`),
  KEY `idx_budget_categorie` (`idcategorie`),
  CONSTRAINT `fk_budget_categorie` FOREIGN KEY (`idcategorie`) REFERENCES `categorie` (`idcategorie`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `budget_achat` (`idbudget`, `libelle`, `annee`, `idcategorie`, `montant_alloue`) VALUES
(1, 'Enveloppe achats globale', 2026, NULL, 95000.00),
(2, 'Budget ordinateurs et postes', 2026, 2, 42000.00),
(3, 'Budget mobilier & sièges', 2026, 5, 8000.00);

CREATE TABLE `produit` (
  `idproduit` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(50) NOT NULL,
  `designation` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `idcategorie` int(11) NOT NULL DEFAULT 1,
  `prix_unitaire` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `id_vendeur` int(11) NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idproduit`),
  UNIQUE KEY `reference` (`reference`),
  KEY `idx_produit_vendeur` (`id_vendeur`),
  KEY `idx_produit_categorie` (`idcategorie`),
  CONSTRAINT `fk_produit_vendeur` FOREIGN KEY (`id_vendeur`) REFERENCES `user` (`iduser`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_produit_categorie` FOREIGN KEY (`idcategorie`) REFERENCES `categorie` (`idcategorie`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `commande` (
  `idcommande` int(11) NOT NULL AUTO_INCREMENT,
  `id_acheteur` int(11) NOT NULL,
  `statut` enum('brouillon','en_attente_paiement','payee','en_preparation','expediee','livree','annulee') NOT NULL DEFAULT 'brouillon',
  `montant_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `date_commande` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` varchar(500) DEFAULT NULL,
  `adresse_livraison` varchar(300) NOT NULL,
  `code_postal` varchar(20) NOT NULL,
  `ville` varchar(100) NOT NULL,
  `pays` varchar(100) NOT NULL DEFAULT 'Tunisie',
  `telephone_livraison` varchar(20) DEFAULT NULL,
  `date_livraison_prevue` date DEFAULT NULL,
  `date_livraison_effective` datetime DEFAULT NULL,
  `numero_suivi` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`idcommande`),
  KEY `idx_commande_acheteur` (`id_acheteur`),
  CONSTRAINT `fk_commande_acheteur` FOREIGN KEY (`id_acheteur`) REFERENCES `user` (`iduser`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `commande_produit` (
  `idcommande` int(11) NOT NULL,
  `idproduit` int(11) NOT NULL,
  `quantite` int(11) NOT NULL DEFAULT 1,
  `prix_unitaire` decimal(12,2) NOT NULL,
  PRIMARY KEY (`idcommande`,`idproduit`),
  KEY `idx_cp_produit` (`idproduit`),
  CONSTRAINT `fk_cp_commande` FOREIGN KEY (`idcommande`) REFERENCES `commande` (`idcommande`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cp_produit` FOREIGN KEY (`idproduit`) REFERENCES `produit` (`idproduit`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `produit` (`idproduit`, `reference`, `designation`, `description`, `idcategorie`, `prix_unitaire`, `stock`, `id_vendeur`, `actif`) VALUES
(1, 'KBD-MEC-001', 'Clavier mécanique gaming RGB', 'Switches bleus, rétroéclairage RGB, AZERTY FR.', 1, 459.00, 40, 7, 1),
(2, 'KBD-MEM-002', 'Clavier membrane filaire bureau', 'Silencieux, pavé numérique, USB.', 1, 129.90, 60, 7, 1),
(3, 'KBD-WL-003', 'Clavier sans fil compact', 'Bluetooth + récepteur USB, autonomie longue durée.', 1, 249.00, 35, 7, 1),
(4, 'MSR-OPT-001', 'Souris optique filaire USB', '1600 DPI, ambidextre, câble 1,5 m.', 1, 59.90, 63, 7, 1),
(5, 'MSR-ERG-002', 'Souris verticale ergonomique', 'Réduit la tension du poignet, sans fil.', 1, 189.00, 24, 7, 1),
(6, 'MSR-GAM-003', 'Souris gaming RGB', '8200 DPI, 7 boutons programmables.', 1, 299.00, 29, 7, 1),
(7, 'PC-PRT-004', 'PC portable 15 pouces bureautique', 'Intel Core i5, 16 Go RAM, SSD 512 Go, Windows 11.', 2, 6490.00, 7, 7, 1),
(8, 'PC-PRT-005', 'PC portable 14 pouces ultraportable', 'Intel Core i7, 16 Go RAM, SSD 1 To, léger.', 2, 8990.00, 5, 7, 1),
(9, 'PC-DESK-006', 'PC de bureau tour', 'Intel Core i5, 16 Go RAM, SSD 512 Go + HDD 1 To.', 2, 4290.00, 11, 7, 1),
(10, 'PC-AIO-007', 'PC tout-en-un 24 pouces', 'Écran FHD intégré, idéal bureau / comptoir.', 2, 5590.00, 6, 7, 1),
(11, 'MIC-USB-01', 'Micro-cravate USB', 'Idéal visioconférence, réduction de bruit logicielle.', 1, 79.90, 20, 7, 1),
(12, 'WEB-CAM-01', 'Webcam Full HD 1080p', 'Autofocus, double micro intégré, clip écran.', 1, 149.00, 29, 7, 1),
(13, 'TEL-AND-01', 'Smartphone 6,5" 128 Go', 'Android, double capteur photo, batterie 5000 mAh.', 3, 899.00, 24, 7, 1),
(14, 'TEL-IPS-02', 'Smartphone 6,7" AMOLED 256 Go', 'Charge rapide 65 W, NFC, étanchéité IP68.', 3, 1199.00, 19, 7, 1),
(15, 'TAB-10-3', 'Tablette 10" Wi-Fi 64 Go', 'Écran FHD, idéal lecture et streaming.', 4, 549.00, 19, 7, 1),
(16, 'TAB-12P-04', 'Tablette 12" pro + stylet', '256 Go, multitâche bureautique, stylet inclus.', 4, 1249.00, 14, 7, 1),
(17, 'CHR-BUR-01', 'Chaise de bureau tissu', 'Réglable hauteur, accoudoirs fixes, noir.', 5, 279.00, 23, 7, 1),
(18, 'CHR-GAM-02', 'Siège gaming cuir PU', 'Appui-tête, bascule, accoudoirs 3D.', 5, 429.00, 17, 7, 1),
(19, 'ACC-MAT-01', 'Tapis de souris XXL', 'Surface tissu, base antidérapante 900×400 mm.', 6, 45.00, 98, 7, 1),
(20, 'ACC-HDM-02', 'Câble HDMI 2.0 - 2 m', '4K 60 Hz, Ethernet canal.', 6, 24.90, 32, 7, 1),
(21, 'ACC-HUB-03', 'Hub USB-C 4 ports', 'USB 3.2, compatible Mac / PC.', 6, 89.00, 40, 7, 1),
(22, 'CAS-AUD-01', 'Écouteurs filaires intra-auriculaires', 'Jack 3,5 mm, télécommande micro.', 1, 39.00, 50, 7, 1);

INSERT INTO `commande` (`idcommande`, `id_acheteur`, `statut`, `montant_total`, `notes`, `adresse_livraison`, `code_postal`, `ville`, `pays`, `date_livraison_prevue`, `numero_suivi`, `date_livraison_effective`) VALUES
(1, 7, 'en_attente_paiement', 308.80, NULL, '12 avenue Habib Bourguiba', '1000', 'Tunis', 'Tunisie', NULL, NULL, NULL),
(2, 8, 'payee', 299.00, 'Commande bureau', 'Immeuble Les Lilas, appartement 4', '3000', 'Sfax', 'Tunisie', NULL, NULL, NULL),
(3, 7, 'en_preparation', 4290.00, NULL, 'Zone industrielle El Bosten, lot 7', '2013', 'Ben Arous', 'Tunisie', DATE_ADD(CURDATE(), INTERVAL 7 DAY), NULL, NULL),
(4, 8, 'expediee', 898.50, NULL, 'Rue Hédi Chaker', '8000', 'Nabeul', 'Tunisie', NULL, 'ARAMEX-TN-2026-88421', NULL),
(5, 8, 'livree', 6490.00, 'Livraison conforme', '1 rue de la Liberté', '4000', 'Sousse', 'Tunisie', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'POSTE-TN-990012', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(6, 8, 'payee', 1107.00, 'Démo : tablette + chaises', 'Immeuble Les Lilas, appartement 4', '3000', 'Sfax', 'Tunisie', NULL, NULL, NULL),
(7, 7, 'en_preparation', 1058.80, 'Démo : téléphone + micros', '12 avenue Habib Bourguiba', '1000', 'Tunis', 'Tunisie', DATE_ADD(CURDATE(), INTERVAL 5 DAY), NULL, NULL),
(8, 8, 'expediee', 1752.70, 'Démo : tablette pro + siège + câbles', 'Rue Hédi Chaker', '8000', 'Nabeul', 'Tunisie', NULL, 'DHL-TN-2026-44102', NULL),
(9, 7, 'livree', 1438.00, 'Démo : smartphone + webcam + tapis', '12 avenue Habib Bourguiba', '1000', 'Tunis', 'Tunisie', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'POSTE-TN-991100', DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO `commande_produit` (`idcommande`, `idproduit`, `quantite`, `prix_unitaire`) VALUES
(1, 4, 2, 59.90), (1, 5, 1, 189.00),
(2, 6, 1, 299.00),
(3, 9, 1, 4290.00),
(4, 4, 15, 59.90),
(5, 7, 1, 6490.00),
(6, 15, 1, 549.00), (6, 17, 2, 279.00),
(7, 13, 1, 899.00), (7, 11, 2, 79.90),
(8, 16, 1, 1249.00), (8, 18, 1, 429.00), (8, 20, 3, 24.90),
(9, 14, 1, 1199.00), (9, 12, 1, 149.00), (9, 19, 2, 45.00);

CREATE TABLE `reappro_config` (
  `idproduit` int(11) NOT NULL,
  `stock_minimum` int(11) NOT NULL DEFAULT 10,
  `stock_cible` int(11) NOT NULL DEFAULT 40,
  `lead_time_jours` int(11) NOT NULL DEFAULT 14,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idproduit`),
  CONSTRAINT `fk_reappro_produit` FOREIGN KEY (`idproduit`) REFERENCES `produit` (`idproduit`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `appel_offre` (
  `idao` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `date_limite` date NOT NULL,
  `statut` enum('brouillon','publie','attribue','annule') NOT NULL DEFAULT 'brouillon',
  `id_reponse_retenue` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idao`),
  KEY `idx_ao_statut` (`statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `appel_offre_reponse` (
  `idr` int(11) NOT NULL AUTO_INCREMENT,
  `idao` int(11) NOT NULL,
  `id_vendeur` int(11) NOT NULL,
  `prix_propose` decimal(12,2) NOT NULL,
  `delai_jours` int(11) NOT NULL DEFAULT 7,
  `notes` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idr`),
  UNIQUE KEY `uq_ao_vendeur` (`idao`,`id_vendeur`),
  KEY `idx_aor_ao` (`idao`),
  CONSTRAINT `fk_aor_ao` FOREIGN KEY (`idao`) REFERENCES `appel_offre` (`idao`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_aor_user` FOREIGN KEY (`id_vendeur`) REFERENCES `user` (`iduser`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `reappro_config` (`idproduit`, `stock_minimum`, `stock_cible`, `lead_time_jours`) VALUES
(4, 40, 90, 10),
(7, 6, 18, 21),
(16, 10, 28, 14);

INSERT INTO `appel_offre` (`idao`, `titre`, `description`, `date_limite`, `statut`, `id_reponse_retenue`) VALUES
(1, 'Lot sièges ergo - open space', '24 sièges certifiés ergonomie, options accoudoirs 3D. Livraison Grand Tunis.', DATE_ADD(CURDATE(), INTERVAL 18 DAY), 'publie', NULL),
(2, 'Renouvellement souris optique USB', 'Besoin indicatif 180 unités, qualité bureau.', DATE_ADD(CURDATE(), INTERVAL 35 DAY), 'brouillon', NULL);

INSERT INTO `appel_offre_reponse` (`idr`, `idao`, `id_vendeur`, `prix_propose`, `delai_jours`, `notes`) VALUES
(1, 1, 7, 11850.00, 12, 'Offre catalogue ProLink - montant global indicatif TND.'),
(2, 1, 8, 10990.00, 19, 'Variant fournisseur alternatif, délai rallongé.');

CREATE TABLE `achat_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(80) NOT NULL,
  `entity` varchar(64) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `payload` longtext DEFAULT NULL COMMENT 'JSON',
  `id_user` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_created` (`created_at`),
  KEY `idx_audit_entity` (`entity`,`entity_id`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`iduser`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tarif_cadre` (
  `idtarif` int(11) NOT NULL AUTO_INCREMENT,
  `id_vendeur` int(11) NOT NULL,
  `idproduit` int(11) NOT NULL,
  `prix_negocie` decimal(12,2) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `reference_contrat` varchar(120) DEFAULT NULL,
  `commentaire` varchar(400) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idtarif`),
  KEY `idx_tc_vp` (`id_vendeur`,`idproduit`),
  KEY `idx_tc_dates` (`date_debut`,`date_fin`),
  CONSTRAINT `fk_tc_vendeur` FOREIGN KEY (`id_vendeur`) REFERENCES `user` (`iduser`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_tc_produit` FOREIGN KEY (`idproduit`) REFERENCES `produit` (`idproduit`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `demande_achat` (
  `idda` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(200) NOT NULL,
  `notes` text DEFAULT NULL,
  `statut` enum('brouillon','soumise','validee','rejetee') NOT NULL DEFAULT 'brouillon',
  `motif_rejet` varchar(500) DEFAULT NULL,
  `id_createur` int(11) NOT NULL,
  `validated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idda`),
  KEY `idx_da_statut` (`statut`),
  CONSTRAINT `fk_da_user` FOREIGN KEY (`id_createur`) REFERENCES `user` (`iduser`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `demande_achat_ligne` (
  `iddal` int(11) NOT NULL AUTO_INCREMENT,
  `idda` int(11) NOT NULL,
  `idproduit` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_estime` decimal(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`iddal`),
  KEY `idx_dal_da` (`idda`),
  KEY `idx_dal_p` (`idproduit`),
  CONSTRAINT `fk_dal_da` FOREIGN KEY (`idda`) REFERENCES `demande_achat` (`idda`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dal_prod` FOREIGN KEY (`idproduit`) REFERENCES `produit` (`idproduit`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tarif_cadre` (`idtarif`, `id_vendeur`, `idproduit`, `prix_negocie`, `date_debut`, `date_fin`, `reference_contrat`, `commentaire`) VALUES
(1, 7, 7, 6190.00, '2026-01-01', '2026-12-31', 'CC-DEMO-PC-2026', 'Prix plateau négocié - démo pédagogique.');

INSERT INTO `demande_achat` (`idda`, `libelle`, `notes`, `statut`, `motif_rejet`, `id_createur`, `validated_at`) VALUES
(1, 'Lot souris open space - pilotage Q1', NULL, 'soumise', NULL, 5, NULL);

INSERT INTO `demande_achat_ligne` (`iddal`, `idda`, `idproduit`, `quantite`, `prix_estime`) VALUES
(1, 1, 4, 30, 57.50),
(2, 1, 6, 10, 289.00);

INSERT INTO `achat_audit` (`id`, `action`, `entity`, `entity_id`, `payload`, `id_user`, `created_at`) VALUES
(1, 'demande_soumise', 'demande_achat', 1, '{"nb_lignes":2}', 5, NOW()),
(2, 'tarif_cadre_cree', 'tarif_cadre', 1, '{"idproduit":7,"id_vendeur":7,"prix_negocie":6190,"periode":["2026-01-01","2026-12-31"]}', 5, NOW());

ALTER TABLE `user` AUTO_INCREMENT = 9;
ALTER TABLE `categorie` AUTO_INCREMENT = 7;
ALTER TABLE `budget_achat` AUTO_INCREMENT = 4;
ALTER TABLE `produit` AUTO_INCREMENT = 23;
ALTER TABLE `commande` AUTO_INCREMENT = 10;
ALTER TABLE `appel_offre` AUTO_INCREMENT = 3;
ALTER TABLE `appel_offre_reponse` AUTO_INCREMENT = 3;
ALTER TABLE `achat_audit` AUTO_INCREMENT = 3;
ALTER TABLE `tarif_cadre` AUTO_INCREMENT = 2;
ALTER TABLE `demande_achat` AUTO_INCREMENT = 2;
ALTER TABLE `demande_achat_ligne` AUTO_INCREMENT = 3;

COMMIT;
