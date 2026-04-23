-- =============================================================================
-- ProLink — base `prolink` complète (utilisateurs, catalogue, commandes)
-- Import : créer la base vide puis exécuter ce fichier, ou l’importer via phpMyAdmin.
-- Prix catalogue : TND · Livraison : Tunisie par défaut
-- =============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET NAMES utf8mb4;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;

START TRANSACTION;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `commande_produit`;
DROP TABLE IF EXISTS `commande`;
DROP TABLE IF EXISTS `produit`;
DROP TABLE IF EXISTS `user`;
SET FOREIGN_KEY_CHECKS = 1;

-- -----------------------------------------------------------------------------
-- Table `user`
-- -----------------------------------------------------------------------------
CREATE TABLE `user` (
  `iduser` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `type` enum('admin','candidat','entrepreneur') NOT NULL,
  `age` int(11) NOT NULL,
  `photo` varchar(255) DEFAULT NULL COMMENT 'Chemin relatif depuis view/, ex. uploads/profiles/user_1.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`iduser`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `user` (`iduser`, `nom`, `prenom`, `email`, `mdp`, `type`, `age`, `photo`, `created_at`) VALUES
(5, 'chihaoui', 'mohammed ali', 'chihaouidali443@gmail.com', '$2y$10$legH0oYVvjIhe4ZLne4sB.c9YW8gctbNfWe5jGmRNTfPkckJp.zAe', 'admin', 75, NULL, '2026-04-15 13:30:47'),
(7, 'chihaoui', 'mohammed ali', 'chihaouidali44@gmail.com', '$2y$10$NTalhxoL.rH.uSh8sjUJlu1yhfRbg4moZpn3M3qGey03UQ8zUfmwe', 'candidat', 24, NULL, '2026-04-15 13:41:01'),
(8, 'aissa', 'idouni', 'issado@gmail.com', '$2y$10$7rT5hKUUaUBeuQUYeTkqXuVdu5nIHRn85NU9fjLAsorNAC.P3fdCy', 'candidat', 75, NULL, '2026-04-15 13:44:12');

-- -----------------------------------------------------------------------------
-- Tables commerce : produit, commande, commande_produit
-- -----------------------------------------------------------------------------
CREATE TABLE `produit` (
  `idproduit` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(50) NOT NULL,
  `designation` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `prix_unitaire` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `id_vendeur` int(11) NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idproduit`),
  UNIQUE KEY `reference` (`reference`),
  KEY `idx_produit_vendeur` (`id_vendeur`),
  CONSTRAINT `fk_produit_vendeur` FOREIGN KEY (`id_vendeur`) REFERENCES `user` (`iduser`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `date_livraison_prevue` date DEFAULT NULL,
  `date_livraison_effective` datetime DEFAULT NULL,
  `numero_suivi` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`idcommande`),
  KEY `idx_commande_acheteur` (`id_acheteur`),
  CONSTRAINT `fk_commande_acheteur` FOREIGN KEY (`id_acheteur`) REFERENCES `user` (`iduser`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `commande_produit` (
  `idcommande` int(11) NOT NULL,
  `idproduit` int(11) NOT NULL,
  `quantite` int(11) NOT NULL DEFAULT 1,
  `prix_unitaire` decimal(12,2) NOT NULL,
  PRIMARY KEY (`idcommande`,`idproduit`),
  KEY `idx_cp_produit` (`idproduit`),
  CONSTRAINT `fk_cp_commande` FOREIGN KEY (`idcommande`) REFERENCES `commande` (`idcommande`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cp_produit` FOREIGN KEY (`idproduit`) REFERENCES `produit` (`idproduit`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Produits (TND) — vendeur iduser 7 · stocks initiaux avant démo commandes
INSERT INTO `produit` (`idproduit`, `reference`, `designation`, `description`, `prix_unitaire`, `stock`, `id_vendeur`, `actif`) VALUES
(1, 'KBD-MEC-001', 'Clavier mécanique gaming RGB', 'Switches bleus, rétroéclairage RGB, AZERTY FR.', 459.00, 40, 7, 1),
(2, 'KBD-MEM-002', 'Clavier membrane filaire bureau', 'Silencieux, pavé numérique, USB.', 129.90, 60, 7, 1),
(3, 'KBD-WL-003', 'Clavier sans fil compact', 'Bluetooth + récepteur USB, autonomie longue durée.', 249.00, 35, 7, 1),
(4, 'MSR-OPT-001', 'Souris optique filaire USB', '1600 DPI, ambidextre, câble 1,5 m.', 59.90, 80, 7, 1),
(5, 'MSR-ERG-002', 'Souris verticale ergonomique', 'Réduit la tension du poignet, sans fil.', 189.00, 25, 7, 1),
(6, 'MSR-GAM-003', 'Souris gaming RGB', '8200 DPI, 7 boutons programmables.', 299.00, 30, 7, 1),
(7, 'PC-PRT-004', 'PC portable 15 pouces bureautique', 'Intel Core i5, 16 Go RAM, SSD 512 Go, Windows 11.', 6490.00, 8, 7, 1),
(8, 'PC-PRT-005', 'PC portable 14 pouces ultraportable', 'Intel Core i7, 16 Go RAM, SSD 1 To, léger.', 8990.00, 5, 7, 1),
(9, 'PC-DESK-006', 'PC de bureau tour', 'Intel Core i5, 16 Go RAM, SSD 512 Go + HDD 1 To.', 4290.00, 12, 7, 1),
(10, 'PC-AIO-007', 'PC tout-en-un 24 pouces', 'Écran FHD intégré, idéal bureau / comptoir.', 5590.00, 6, 7, 1);

-- Commandes de démonstration (acheteurs 7 et 8)
INSERT INTO `commande` (`idcommande`, `id_acheteur`, `statut`, `montant_total`, `notes`, `adresse_livraison`, `code_postal`, `ville`, `pays`, `date_livraison_prevue`, `numero_suivi`, `date_livraison_effective`) VALUES
(1, 7, 'en_attente_paiement', 307.80, NULL, '12 avenue Habib Bourguiba', '1000', 'Tunis', 'Tunisie', NULL, NULL, NULL),
(2, 8, 'payee', 299.00, 'Commande bureau', 'Immeuble Les Lilas, appartement 4', '3000', 'Sfax', 'Tunisie', NULL, NULL, NULL),
(3, 7, 'en_preparation', 4290.00, NULL, 'Zone industrielle El Bosten, lot 7', '2013', 'Ben Arous', 'Tunisie', DATE_ADD(CURDATE(), INTERVAL 7 DAY), NULL, NULL),
(4, 8, 'expediee', 898.50, NULL, 'Rue Hédi Chaker', '8000', 'Nabeul', 'Tunisie', NULL, 'ARAMEX-TN-2026-88421', NULL),
(5, 8, 'livree', 6490.00, 'Livraison conforme', '1 rue de la Liberté', '4000', 'Sousse', 'Tunisie', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'POSTE-TN-990012', DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO `commande_produit` (`idcommande`, `idproduit`, `quantite`, `prix_unitaire`) VALUES
(1, 4, 2, 59.90),
(1, 5, 1, 189.00),
(2, 6, 1, 299.00),
(3, 9, 1, 4290.00),
(4, 4, 15, 59.90),
(5, 7, 1, 6490.00);

UPDATE `produit` SET `stock` = `stock` - 2 WHERE `idproduit` = 4;
UPDATE `produit` SET `stock` = `stock` - 1 WHERE `idproduit` = 5;
UPDATE `produit` SET `stock` = `stock` - 1 WHERE `idproduit` = 6;
UPDATE `produit` SET `stock` = `stock` - 1 WHERE `idproduit` = 9;
UPDATE `produit` SET `stock` = `stock` - 15 WHERE `idproduit` = 4;
UPDATE `produit` SET `stock` = `stock` - 1 WHERE `idproduit` = 7;

ALTER TABLE `user` AUTO_INCREMENT = 9;
ALTER TABLE `produit` AUTO_INCREMENT = 11;
ALTER TABLE `commande` AUTO_INCREMENT = 6;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
