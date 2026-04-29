-- ProLink — tables module événements (import dans la base `prolink`)
-- Exécuter ce fichier si les tables n'existent pas encore.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `evenement` (
  `id_event` int(11) NOT NULL AUTO_INCREMENT,
  `titre_event` varchar(200) NOT NULL,
  `description_event` text NOT NULL,
  `type_event` varchar(100) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `lieu_event` varchar(120) NOT NULL,
  `capacite_max` int(11) NOT NULL DEFAULT 0,
  `statut` varchar(32) NOT NULL DEFAULT 'Ouvert',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `participation` (
  `id_participation` int(11) NOT NULL AUTO_INCREMENT,
  `id_event` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` varchar(32) NOT NULL,
  PRIMARY KEY (`id_participation`),
  KEY `idx_part_event` (`id_event`),
  CONSTRAINT `fk_part_evenement` FOREIGN KEY (`id_event`) REFERENCES `evenement` (`id_event`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
