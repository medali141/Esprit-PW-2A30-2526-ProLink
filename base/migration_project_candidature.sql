-- Table des candidatures à un projet (front-office).
-- Un utilisateur ne peut postuler qu'une seule fois sur un même projet (UNIQUE
-- sur la paire (id_project, id_user)) ; il pourra ensuite retirer puis
-- repostuler en supprimant puis recréant si nécessaire.

CREATE TABLE IF NOT EXISTS `project_candidature` (
    `id_candidature` INT NOT NULL AUTO_INCREMENT,
    `id_project`     INT NOT NULL,
    `id_user`        INT NOT NULL,
    `message`        TEXT NULL DEFAULT NULL,
    `statut`         VARCHAR(20) NOT NULL DEFAULT 'en_attente',
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_candidature`),
    UNIQUE KEY `uk_project_user` (`id_project`, `id_user`),
    KEY `idx_project` (`id_project`),
    KEY `idx_user` (`id_user`),
    CONSTRAINT `fk_cand_project` FOREIGN KEY (`id_project`) REFERENCES `project` (`idproject`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_cand_user`    FOREIGN KEY (`id_user`)    REFERENCES `user` (`iduser`)      ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
