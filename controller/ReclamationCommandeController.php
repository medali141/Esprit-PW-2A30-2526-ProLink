<?php
/**
 * Reclamations client limitees aux commandes de l'acheteur connecte.
 */
require_once __DIR__ . '/../config.php';

class ReclamationCommandeController {
    private ?bool $tableReady = null;
    private ?bool $userPointsReady = null;

    /**
     * @return list<array<string,mixed>>
     */
    public function listUserCommandes(int $idAcheteur): array {
        $this->ensureTable();
        $db = Config::getConnexion();
        $st = $db->prepare(
            "SELECT idcommande, date_commande, statut, montant_total
             FROM commande
             WHERE id_acheteur = :id
             ORDER BY date_commande DESC, idcommande DESC"
        );
        $st->execute(['id' => $idAcheteur]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function listByUser(int $idAcheteur): array {
        $this->ensureTable();
        $db = Config::getConnexion();
        $st = $db->prepare(
            "SELECT r.*, c.date_commande, c.statut AS commande_statut,
                    adm.nom AS admin_nom, adm.prenom AS admin_prenom
             FROM commande_reclamation r
             INNER JOIN commande c ON c.idcommande = r.idcommande
             LEFT JOIN user adm ON adm.iduser = r.admin_id
             WHERE r.id_acheteur = :id
             ORDER BY r.created_at DESC, r.idreclamation DESC"
        );
        $st->execute(['id' => $idAcheteur]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function listAllAdmin(string $statut = '', string $q = ''): array {
        $this->ensureTable();
        $allowed = ['ouverte', 'en_cours', 'resolue', 'rejetee'];
        $where = ['1=1'];
        $params = [];
        $statut = trim($statut);
        if ($statut !== '' && in_array($statut, $allowed, true)) {
            $where[] = 'r.statut = :st';
            $params['st'] = $statut;
        }
        $q = trim($q);
        if ($q !== '') {
            $where[] = '(CAST(r.idreclamation AS CHAR) LIKE :q OR CAST(r.idcommande AS CHAR) LIKE :q
                OR r.sujet LIKE :q OR r.message LIKE :q
                OR u.email LIKE :q OR u.nom LIKE :q OR u.prenom LIKE :q)';
            $params['q'] = '%' . $q . '%';
        }
        $db = Config::getConnexion();
        $sql = "SELECT r.*,
                       c.date_commande, c.statut AS commande_statut, c.montant_total,
                       u.nom AS client_nom, u.prenom AS client_prenom, u.email AS client_email,
                       adm.nom AS admin_nom, adm.prenom AS admin_prenom
                FROM commande_reclamation r
                INNER JOIN commande c ON c.idcommande = r.idcommande
                INNER JOIN user u ON u.iduser = r.id_acheteur
                LEFT JOIN user adm ON adm.iduser = r.admin_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY
                    CASE r.statut
                        WHEN 'ouverte' THEN 0
                        WHEN 'en_cours' THEN 1
                        WHEN 'resolue' THEN 2
                        ELSE 3
                    END ASC,
                    r.created_at DESC, r.idreclamation DESC";
        $st = $db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<string,int>
     */
    public function getAdminStats(): array {
        $this->ensureTable();
        $db = Config::getConnexion();
        $row = $db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN statut = 'ouverte' THEN 1 ELSE 0 END) AS ouvertes,
                SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) AS en_cours,
                SUM(CASE WHEN statut = 'resolue' THEN 1 ELSE 0 END) AS resolues,
                SUM(CASE WHEN statut = 'rejetee' THEN 1 ELSE 0 END) AS rejetees,
                SUM(COALESCE(compensation_points, 0)) AS points_offerts,
                AVG(CASE WHEN user_rating BETWEEN 1 AND 5 THEN user_rating ELSE NULL END) AS note_moyenne
             FROM commande_reclamation"
        )->fetch(PDO::FETCH_ASSOC) ?: [];
        return [
            'total' => (int) ($row['total'] ?? 0),
            'ouvertes' => (int) ($row['ouvertes'] ?? 0),
            'en_cours' => (int) ($row['en_cours'] ?? 0),
            'resolues' => (int) ($row['resolues'] ?? 0),
            'rejetees' => (int) ($row['rejetees'] ?? 0),
            'points_offerts' => (int) ($row['points_offerts'] ?? 0),
            'note_moyenne' => (int) round((float) ($row['note_moyenne'] ?? 0)),
        ];
    }

    public function respondAsAdmin(int $idReclamation, int $idAdmin, string $statut, string $reponse, int $points): void {
        $this->ensureTable();
        $this->ensureUserPointsColumn();
        $allowed = ['ouverte', 'en_cours'];
        if ($idReclamation <= 0 || $idAdmin <= 0) {
            throw new InvalidArgumentException('Reclamation invalide.');
        }
        if (!in_array($statut, $allowed, true)) {
            throw new InvalidArgumentException('Statut invalide.');
        }
        $reponse = trim($reponse);
        if (strlen($reponse) < 5 || strlen($reponse) > 3000) {
            throw new InvalidArgumentException('Reponse admin: entre 5 et 3000 caracteres.');
        }
        if ($points < 0 || $points > 2000) {
            throw new InvalidArgumentException('Points de compensation invalides (0 a 2000).');
        }

        $db = Config::getConnexion();
        $db->beginTransaction();
        try {
            $sel = $db->prepare(
                "SELECT id_acheteur, compensation_points, statut
                 FROM commande_reclamation
                 WHERE idreclamation = :id
                 FOR UPDATE"
            );
            $sel->execute(['id' => $idReclamation]);
            $row = $sel->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                throw new InvalidArgumentException('Reclamation introuvable.');
            }
            $idAcheteur = (int) ($row['id_acheteur'] ?? 0);
            $oldPoints = (int) ($row['compensation_points'] ?? 0);
            $currentStatut = (string) ($row['statut'] ?? 'ouverte');
            if ($currentStatut === 'resolue') {
                throw new InvalidArgumentException('Cette reclamation est deja cloturee par le client.');
            }
            $delta = $points - $oldPoints;

            $up = $db->prepare(
                "UPDATE commande_reclamation
                 SET statut = :st,
                     admin_response = :rp,
                     admin_id = :aid,
                     compensation_points = :pts,
                     resolved_at = NULL
                 WHERE idreclamation = :id"
            );
            $up->execute([
                'st' => $statut,
                'rp' => $reponse,
                'aid' => $idAdmin,
                'pts' => $points,
                'id' => $idReclamation,
            ]);

            if ($delta !== 0 && $idAcheteur > 0) {
                $updPts = $db->prepare(
                    "UPDATE user
                     SET points_fidelite = GREATEST(0, COALESCE(points_fidelite, 0) + :delta)
                     WHERE iduser = :uid"
                );
                $updPts->execute(['delta' => $delta, 'uid' => $idAcheteur]);
            }

            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function closeByUserWithRating(int $idReclamation, int $idAcheteur, int $rating): void {
        $this->ensureTable();
        if ($idReclamation <= 0 || $idAcheteur <= 0) {
            throw new InvalidArgumentException('Reclamation invalide.');
        }
        if ($rating < 1 || $rating > 5) {
            throw new InvalidArgumentException('La note doit etre comprise entre 1 et 5.');
        }
        $db = Config::getConnexion();
        $db->beginTransaction();
        try {
            $sel = $db->prepare(
                "SELECT id_acheteur, statut
                 FROM commande_reclamation
                 WHERE idreclamation = :id
                 FOR UPDATE"
            );
            $sel->execute(['id' => $idReclamation]);
            $row = $sel->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                throw new InvalidArgumentException('Reclamation introuvable.');
            }
            if ((int) ($row['id_acheteur'] ?? 0) !== $idAcheteur) {
                throw new InvalidArgumentException('Vous ne pouvez cloturer que vos propres reclamations.');
            }
            if ((string) ($row['statut'] ?? '') === 'resolue') {
                throw new InvalidArgumentException('Cette reclamation est deja cloturee.');
            }
            $up = $db->prepare(
                "UPDATE commande_reclamation
                 SET statut = 'resolue',
                     user_rating = :rt,
                     user_closed_at = NOW(),
                     resolved_at = NOW()
                 WHERE idreclamation = :id"
            );
            $up->execute([
                'rt' => $rating,
                'id' => $idReclamation,
            ]);
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function addForUser(int $idAcheteur, int $idCommande, string $sujet, string $message): void {
        $this->ensureTable();
        $sujet = trim($sujet);
        $message = trim($message);

        if ($idCommande <= 0) {
            throw new InvalidArgumentException('Commande invalide.');
        }
        if ($sujet === '' || strlen($sujet) > 120) {
            throw new InvalidArgumentException('Sujet obligatoire (max 120 caracteres).');
        }
        if (strlen($message) < 10 || strlen($message) > 1500) {
            throw new InvalidArgumentException('Message: entre 10 et 1500 caracteres.');
        }

        $db = Config::getConnexion();
        $check = $db->prepare("SELECT idcommande FROM commande WHERE idcommande = :idc AND id_acheteur = :idu LIMIT 1");
        $check->execute(['idc' => $idCommande, 'idu' => $idAcheteur]);
        if (!$check->fetch(PDO::FETCH_ASSOC)) {
            throw new InvalidArgumentException('Cette commande ne vous appartient pas.');
        }

        $ins = $db->prepare(
            "INSERT INTO commande_reclamation (idcommande, id_acheteur, sujet, message, statut)
             VALUES (:idc, :idu, :s, :m, 'ouverte')"
        );
        $ins->execute([
            'idc' => $idCommande,
            'idu' => $idAcheteur,
            's' => $sujet,
            'm' => $message,
        ]);
    }

    private function ensureTable(): void {
        if ($this->tableReady !== null) {
            return;
        }
        $db = Config::getConnexion();
        $db->exec(
            "CREATE TABLE IF NOT EXISTS commande_reclamation (
                idreclamation INT AUTO_INCREMENT PRIMARY KEY,
                idcommande INT NOT NULL,
                id_acheteur INT NOT NULL,
                sujet VARCHAR(120) NOT NULL,
                message TEXT NOT NULL,
                statut VARCHAR(30) NOT NULL DEFAULT 'ouverte',
                admin_response TEXT NULL,
                admin_id INT NULL,
                compensation_points INT NOT NULL DEFAULT 0,
                user_rating TINYINT NULL,
                user_closed_at DATETIME NULL DEFAULT NULL,
                resolved_at DATETIME NULL DEFAULT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_reclam_user (id_acheteur),
                INDEX idx_reclam_commande (idcommande),
                INDEX idx_reclam_statut (statut)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $this->ensureReclamationColumns($db);
        $this->tableReady = true;
    }

    private function ensureReclamationColumns(PDO $db): void {
        $columns = [
            'admin_response' => "ALTER TABLE commande_reclamation ADD COLUMN admin_response TEXT NULL AFTER statut",
            'admin_id' => "ALTER TABLE commande_reclamation ADD COLUMN admin_id INT NULL AFTER admin_response",
            'compensation_points' => "ALTER TABLE commande_reclamation ADD COLUMN compensation_points INT NOT NULL DEFAULT 0 AFTER admin_id",
            'user_rating' => "ALTER TABLE commande_reclamation ADD COLUMN user_rating TINYINT NULL AFTER compensation_points",
            'user_closed_at' => "ALTER TABLE commande_reclamation ADD COLUMN user_closed_at DATETIME NULL DEFAULT NULL AFTER user_rating",
            'resolved_at' => "ALTER TABLE commande_reclamation ADD COLUMN resolved_at DATETIME NULL DEFAULT NULL AFTER compensation_points",
        ];
        foreach ($columns as $name => $sql) {
            try {
                $db->exec($sql);
            } catch (Throwable $e) {
                // colonne deja presente
            }
        }
    }

    private function ensureUserPointsColumn(): void {
        if ($this->userPointsReady !== null) {
            return;
        }
        $db = Config::getConnexion();
        $st = $db->query("SHOW COLUMNS FROM user LIKE 'points_fidelite'");
        $exists = (bool) $st->fetch(PDO::FETCH_ASSOC);
        if (!$exists) {
            try {
                $db->exec("ALTER TABLE user ADD COLUMN points_fidelite INT NOT NULL DEFAULT 0 AFTER age");
                $exists = true;
            } catch (Throwable $e) {
                $exists = false;
            }
        }
        $this->userPointsReady = $exists;
    }
}
