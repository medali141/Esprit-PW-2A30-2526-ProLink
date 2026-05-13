-- 1) Évaluation d'un candidat accepté (note 1-5 + commentaire libre).
ALTER TABLE `project_candidature`
    ADD COLUMN `evaluation_note`    TINYINT  NULL DEFAULT NULL,
    ADD COLUMN `evaluation_comment` TEXT     NULL DEFAULT NULL,
    ADD COLUMN `evaluated_at`       TIMESTAMP NULL DEFAULT NULL;

-- 2) Notifications utilisateur.
-- Affichées via une cloche dans la navbar front-office et une page
-- notifications.php dédiée. Déclenchées par le serveur (nouvelle
-- candidature, acceptation/refus, évaluation reçue).
CREATE TABLE IF NOT EXISTS `user_notification` (
    `id_notification` INT NOT NULL AUTO_INCREMENT,
    `id_user`         INT NOT NULL,
    `type`            VARCHAR(40) NOT NULL,
    `title`           VARCHAR(180) NOT NULL,
    `body`            TEXT NULL DEFAULT NULL,
    `link`            VARCHAR(255) NULL DEFAULT NULL,
    `is_read`         TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `read_at`         TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id_notification`),
    KEY `idx_notif_user_read` (`id_user`, `is_read`, `created_at`),
    CONSTRAINT `fk_notif_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`iduser`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
