-- Module Forum — exécuter sur la base `prolink` si les tables n'existent pas
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `forum_categorie` (
  `id_categorie` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `ordre` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `forum_sujet` (
  `id_sujet` int(11) NOT NULL AUTO_INCREMENT,
  `id_categorie` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `epingle` tinyint(1) NOT NULL DEFAULT 0,
  `verrouille` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_sujet`),
  KEY `idx_sujet_cat` (`id_categorie`),
  KEY `idx_sujet_user` (`id_user`),
  CONSTRAINT `fk_sujet_categorie` FOREIGN KEY (`id_categorie`) REFERENCES `forum_categorie` (`id_categorie`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sujet_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`iduser`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `forum_message` (
  `id_message` int(11) NOT NULL AUTO_INCREMENT,
  `id_sujet` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `image_fichier` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_message`),
  KEY `idx_msg_sujet` (`id_sujet`),
  KEY `idx_msg_user` (`id_user`),
  CONSTRAINT `fk_message_sujet` FOREIGN KEY (`id_sujet`) REFERENCES `forum_sujet` (`id_sujet`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_message_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`iduser`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
