-- Ajout optionnel d’une image par message forum (exécuter sur `prolink` si la colonne n’existe pas)
SET NAMES utf8mb4;

ALTER TABLE `forum_message`
  ADD COLUMN `image_fichier` varchar(255) DEFAULT NULL
  COMMENT 'Relatif a view/ (ex. uploads/forum/...)' AFTER `contenu`;
