-- Ajout des coordonnées du candidat et du CV sur la candidature.
-- nom / prenom / email permettent au candidat d'envoyer des coordonnées
-- éventuellement différentes de son profil (mail pro par exemple).
-- cv_fichier stocke le chemin relatif (sous view/uploads/cv/) du fichier
-- uploadé.

ALTER TABLE `project_candidature`
    ADD COLUMN `nom`        VARCHAR(100) NULL DEFAULT NULL AFTER `id_user`,
    ADD COLUMN `prenom`     VARCHAR(100) NULL DEFAULT NULL AFTER `nom`,
    ADD COLUMN `email`      VARCHAR(150) NULL DEFAULT NULL AFTER `prenom`,
    ADD COLUMN `cv_fichier` VARCHAR(255) NULL DEFAULT NULL AFTER `email`;
