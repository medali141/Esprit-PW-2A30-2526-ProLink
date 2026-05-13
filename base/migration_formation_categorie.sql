-- Ajoute une colonne `categorie` à la table `formation`.
-- Utilisée par le back-office (formulaire d'ajout / modification) pour
-- regrouper les formations par thème (Développement Web, Design, ...).

ALTER TABLE `formation`
    ADD COLUMN `categorie` VARCHAR(80) NULL DEFAULT NULL AFTER `titre`;
