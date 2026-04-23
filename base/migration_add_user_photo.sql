-- À exécuter une fois sur une base existante (avant d’utiliser les photos de profil).
ALTER TABLE `user`
  ADD COLUMN `photo` varchar(255) DEFAULT NULL COMMENT 'Chemin relatif depuis view/' AFTER `age`;
