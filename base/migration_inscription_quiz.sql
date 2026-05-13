-- Ajoute :
--  * id_user : lien optionnel vers l'utilisateur connecté qui s'est inscrit.
--    Permet au front-office de retrouver son inscription pour passer le
--    quiz et télécharger son certificat (au lieu de matcher uniquement
--    par e-mail).
--  * quiz_score / quiz_passed / quiz_passed_at : statut du quiz associé.
--
-- Note : pas de FK sur id_user pour ne pas casser les inscriptions
-- existantes saisies sans utilisateur connecté.

ALTER TABLE `inscription`
    ADD COLUMN `id_user`        INT NULL DEFAULT NULL AFTER `id_formation`,
    ADD COLUMN `quiz_score`     INT NULL DEFAULT NULL,
    ADD COLUMN `quiz_passed`    TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN `quiz_passed_at` TIMESTAMP NULL DEFAULT NULL;

CREATE INDEX `idx_inscription_user_formation` ON `inscription` (`id_user`, `id_formation`);
