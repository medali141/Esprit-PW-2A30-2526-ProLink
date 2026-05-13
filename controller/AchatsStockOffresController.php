<?php
/**
 * Réapprovisionnement (seuils stock, ventes sur période) et appels d’offres.
 */
require_once __DIR__ . '/AchatsAuditLog.php';
require_once __DIR__ . '/../config.php';

class AchatsStockOffresController {
    private ?bool $extraTablesReady = null;

    private const DEFAULT_STOCK_MIN = 10;
    private const DEFAULT_STOCK_CIBLE = 40;
    private const DEFAULT_LEAD_JOURS = 14;

    private const AO_STATUTS = ['brouillon', 'publie', 'attribue', 'annule'];

    private function ensureExtraTables(): void {
        if ($this->extraTablesReady === true) {
            return;
        }
        $db = Config::getConnexion();
        $db->exec(
            "CREATE TABLE IF NOT EXISTS `reappro_config` (
              `idproduit` int(11) NOT NULL,
              `stock_minimum` int(11) NOT NULL DEFAULT 10,
              `stock_cible` int(11) NOT NULL DEFAULT 40,
              `lead_time_jours` int(11) NOT NULL DEFAULT 14,
              `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`idproduit`),
              CONSTRAINT `fk_reappro_produit` FOREIGN KEY (`idproduit`) REFERENCES `produit` (`idproduit`)
                ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $db->exec(
            "CREATE TABLE IF NOT EXISTS `appel_offre` (
              `idao` int(11) NOT NULL AUTO_INCREMENT,
              `titre` varchar(200) NOT NULL,
              `description` text DEFAULT NULL,
              `date_limite` date NOT NULL,
              `statut` enum('brouillon','publie','attribue','annule') NOT NULL DEFAULT 'brouillon',
              `id_reponse_retenue` int(11) DEFAULT NULL,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`idao`),
              KEY `idx_ao_statut` (`statut`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $db->exec(
            "CREATE TABLE IF NOT EXISTS `appel_offre_reponse` (
              `idr` int(11) NOT NULL AUTO_INCREMENT,
              `idao` int(11) NOT NULL,
              `id_vendeur` int(11) NOT NULL,
              `prix_propose` decimal(12,2) NOT NULL,
              `delai_jours` int(11) NOT NULL DEFAULT 7,
              `notes` varchar(500) DEFAULT NULL,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`idr`),
              UNIQUE KEY `uq_ao_vendeur` (`idao`,`id_vendeur`),
              KEY `idx_aor_ao` (`idao`),
              CONSTRAINT `fk_aor_ao` FOREIGN KEY (`idao`) REFERENCES `appel_offre` (`idao`)
                ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk_aor_user` FOREIGN KEY (`id_vendeur`) REFERENCES `user` (`iduser`)
                ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->extraTablesReady = true;
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function getReapproDashboard(int $fenetreJours = 90): array {
        $this->ensureExtraTables();
        $fenetreJours = max(7, min(365, $fenetreJours));
        $db = Config::getConnexion();

        $sql = "SELECT p.idproduit, p.reference, p.designation, p.stock, p.actif,
                       cat.libelle AS categorie_libelle,
                       COALESCE(rc.stock_minimum, :dmin) AS stock_minimum,
                       COALESCE(rc.stock_cible, :dcib) AS stock_cible,
                       COALESCE(rc.lead_time_jours, :dlead) AS lead_time_jours,
                       COALESCE(v.qty_vendue, 0) AS qty_vendue_periode
                FROM produit p
                INNER JOIN categorie cat ON cat.idcategorie = p.idcategorie
                LEFT JOIN reappro_config rc ON rc.idproduit = p.idproduit
                LEFT JOIN (
                    SELECT cp.idproduit, SUM(cp.quantite) AS qty_vendue
                    FROM commande_produit cp
                    INNER JOIN commande c ON c.idcommande = cp.idcommande
                    WHERE c.statut NOT IN ('annulee','brouillon')
                      AND c.date_commande >= DATE_SUB(NOW(), INTERVAL :win DAY)
                    GROUP BY cp.idproduit
                ) v ON v.idproduit = p.idproduit
                WHERE p.actif = 1
                ORDER BY p.reference ASC";

        $st = $db->prepare($sql);
        $st->execute([
            'win' => $fenetreJours,
            'dmin' => self::DEFAULT_STOCK_MIN,
            'dcib' => self::DEFAULT_STOCK_CIBLE,
            'dlead' => self::DEFAULT_LEAD_JOURS,
        ]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $days = (float) $fenetreJours;
        foreach ($rows as &$r) {
            $stock = (int) ($r['stock'] ?? 0);
            $qty = (float) ($r['qty_vendue_periode'] ?? 0);
            $vmin = (int) ($r['stock_minimum'] ?? self::DEFAULT_STOCK_MIN);
            $vcib = max($vmin, (int) ($r['stock_cible'] ?? self::DEFAULT_STOCK_CIBLE));
            $lead = (int) ($r['lead_time_jours'] ?? self::DEFAULT_LEAD_JOURS);

            $avgDaily = $days > 0 ? ($qty / $days) : 0.0;
            $couverture = $avgDaily > 1e-9 ? ($stock / $avgDaily) : ($stock > 0 ? 999.0 : 0.0);

            $suggest = max(0, $vcib - $stock);

            $niveau = 'ok';
            if ($stock <= $vmin || ($avgDaily > 0 && $couverture < $lead)) {
                $niveau = 'critique';
            } elseif ($stock <= (int) ceil($vcib * 0.55) || ($avgDaily > 0 && $couverture < $lead * 1.35)) {
                $niveau = 'vigilance';
            }

            $r['avg_daily_sales'] = round($avgDaily, 3);
            $r['couverture_jours'] = round($couverture, 1);
            $r['suggestion_reappro'] = $suggest;
            $r['niveau_alerte'] = $niveau;
            $r['fenetre_jours'] = $fenetreJours;
        }
        unset($r);

        usort($rows, static function (array $a, array $b): int {
            $order = ['critique' => 0, 'vigilance' => 1, 'ok' => 2];
            $ka = $order[$a['niveau_alerte'] ?? 'ok'] ?? 2;
            $kb = $order[$b['niveau_alerte'] ?? 'ok'] ?? 2;
            if ($ka !== $kb) {
                return $ka <=> $kb;
            }
            return strcmp((string) ($a['reference'] ?? ''), (string) ($b['reference'] ?? ''));
        });

        return $rows;
    }

    public function upsertReapproConfig(int $idproduit, int $stockMin, int $stockCible, int $leadJours): void {
        $this->ensureExtraTables();
        if ($idproduit <= 0) {
            throw new InvalidArgumentException('Produit invalide');
        }
        if ($stockMin < 0 || $stockMin > 100000 || $stockCible < 1 || $stockCible > 1000000) {
            throw new InvalidArgumentException('Seuils invalides');
        }
        if ($stockCible < $stockMin) {
            throw new InvalidArgumentException('Le stock cible doit être ≥ au minimum');
        }
        if ($leadJours < 1 || $leadJours > 365) {
            throw new InvalidArgumentException('Délai d’approvisionnement invalide');
        }

        $db = Config::getConnexion();
        $chk = $db->prepare('SELECT idproduit FROM produit WHERE idproduit = :id LIMIT 1');
        $chk->execute(['id' => $idproduit]);
        if (!$chk->fetchColumn()) {
            throw new InvalidArgumentException('Produit introuvable');
        }

        $up = $db->prepare(
            'INSERT INTO reappro_config (idproduit, stock_minimum, stock_cible, lead_time_jours)
             VALUES (:id, :mn, :ci, :ld)
             ON DUPLICATE KEY UPDATE stock_minimum = VALUES(stock_minimum),
               stock_cible = VALUES(stock_cible), lead_time_jours = VALUES(lead_time_jours)'
        );
        $up->execute([
            'id' => $idproduit,
            'mn' => $stockMin,
            'ci' => $stockCible,
            'ld' => $leadJours,
        ]);
    }

    /** @return list<array<string,mixed>> */
    public function listVendeursPourOffres(): array {
        $db = Config::getConnexion();
        $st = $db->query(
            "SELECT iduser, prenom, nom, email, type FROM user
             WHERE type IN ('candidat','entrepreneur')
             ORDER BY nom ASC, prenom ASC"
        );
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return list<array<string,mixed>> */
    public function listAppelsOffres(): array {
        $this->ensureExtraTables();
        $db = Config::getConnexion();
        $sql = "SELECT ao.*,
                       (SELECT COUNT(*) FROM appel_offre_reponse r WHERE r.idao = ao.idao) AS nb_reponses
                FROM appel_offre ao
                ORDER BY ao.created_at DESC, ao.idao DESC";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function countAppelsOffresPublies(): int {
        $this->ensureExtraTables();
        return (int) Config::getConnexion()->query(
            "SELECT COUNT(*) FROM appel_offre WHERE statut = 'publie'"
        )->fetchColumn();
    }

    /**
     * AO publiés — réponses fournisseurs (front office).
     *
     * @return list<array<string,mixed>>
     */
    public function listAppelsOffresPublies(): array {
        $this->ensureExtraTables();
        $db = Config::getConnexion();
        $sql = "SELECT ao.*,
                       (SELECT COUNT(*) FROM appel_offre_reponse r WHERE r.idao = ao.idao) AS nb_reponses
                FROM appel_offre ao
                WHERE ao.statut = 'publie'
                ORDER BY ao.date_limite ASC, ao.idao DESC";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<string,mixed>|null */
    public function getReponseVendeurPourAo(int $idao, int $idVendeur): ?array {
        $this->ensureExtraTables();
        if ($idao <= 0 || $idVendeur <= 0) {
            return null;
        }
        $db = Config::getConnexion();
        $st = $db->prepare(
            'SELECT * FROM appel_offre_reponse WHERE idao = :a AND id_vendeur = :v LIMIT 1'
        );
        $st->execute(['a' => $idao, 'v' => $idVendeur]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getAppelOffre(int $idao): ?array {
        $this->ensureExtraTables();
        $db = Config::getConnexion();
        $st = $db->prepare('SELECT * FROM appel_offre WHERE idao = :id LIMIT 1');
        $st->execute(['id' => $idao]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** @return list<array<string,mixed>> */
    public function listReponses(int $idao): array {
        $this->ensureExtraTables();
        $db = Config::getConnexion();
        $sql = "SELECT r.*, u.prenom, u.nom, u.email, u.type
                FROM appel_offre_reponse r
                INNER JOIN user u ON u.iduser = r.id_vendeur
                WHERE r.idao = :id
                ORDER BY r.prix_propose ASC, r.delai_jours ASC, r.idr ASC";
        $st = $db->prepare($sql);
        $st->execute(['id' => $idao]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function createAppelOffre(string $titre, string $description, string $dateLimite): int {
        $this->ensureExtraTables();
        $titre = trim($titre);
        if ($titre === '' || strlen($titre) > 200) {
            throw new InvalidArgumentException('Titre invalide');
        }
        $description = trim($description);
        if (strlen($description) > 8000) {
            throw new InvalidArgumentException('Description trop longue');
        }
        $dl = trim($dateLimite);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dl)) {
            throw new InvalidArgumentException('Date limite invalide');
        }
        [$y, $m, $d] = array_map('intval', explode('-', $dl));
        if (!checkdate($m, $d, $y)) {
            throw new InvalidArgumentException('Date limite invalide');
        }

        $db = Config::getConnexion();
        $ins = $db->prepare(
            'INSERT INTO appel_offre (titre, description, date_limite, statut) VALUES (:t, :d, :dl, \'brouillon\')'
        );
        $ins->execute(['t' => $titre, 'd' => $description !== '' ? $description : null, 'dl' => $dl]);
        return (int) $db->lastInsertId();
    }

    public function setStatutAppelOffre(int $idao, string $statut): void {
        $this->ensureExtraTables();
        if (!in_array($statut, self::AO_STATUTS, true)) {
            throw new InvalidArgumentException('Statut inconnu');
        }
        $db = Config::getConnexion();
        $up = $db->prepare('UPDATE appel_offre SET statut = :s WHERE idao = :id LIMIT 1');
        $up->execute(['s' => $statut, 'id' => $idao]);
        if ($up->rowCount() === 0) {
            throw new InvalidArgumentException('Appel d’offres introuvable');
        }
    }

    public function addReponseOffre(int $idao, int $idVendeur, float $prix, int $delaiJours, string $notes): void {
        $this->ensureExtraTables();
        $ao = $this->getAppelOffre($idao);
        if (!$ao) {
            throw new InvalidArgumentException('Appel d’offres introuvable');
        }
        $stAo = (string) ($ao['statut'] ?? '');
        if ($stAo === 'attribue' || $stAo === 'annule') {
            throw new InvalidArgumentException('Cet appel d’offres ne accepte plus de réponses');
        }
        if ($prix < 0 || $prix > 999999999.99) {
            throw new InvalidArgumentException('Montant invalide');
        }
        if ($delaiJours < 1 || $delaiJours > 365) {
            throw new InvalidArgumentException('Délai invalide');
        }
        $notes = trim($notes);
        if (strlen($notes) > 500) {
            throw new InvalidArgumentException('Notes trop longues');
        }

        $db = Config::getConnexion();
        $chk = $db->prepare('SELECT 1 FROM user WHERE iduser = :u LIMIT 1');
        $chk->execute(['u' => $idVendeur]);
        if (!$chk->fetchColumn()) {
            throw new InvalidArgumentException('Vendeur inconnu');
        }

        $ins = $db->prepare(
            'INSERT INTO appel_offre_reponse (idao, id_vendeur, prix_propose, delai_jours, notes)
             VALUES (:ao, :v, :p, :dj, :n)
             ON DUPLICATE KEY UPDATE prix_propose = VALUES(prix_propose),
               delai_jours = VALUES(delai_jours), notes = VALUES(notes)'
        );
        $ins->execute([
            'ao' => $idao,
            'v' => $idVendeur,
            'p' => round($prix, 2),
            'dj' => $delaiJours,
            'n' => $notes !== '' ? $notes : null,
        ]);
    }

    public function attribuerReponse(int $idao, int $idr, ?int $idActorAdmin = null): void {
        $this->ensureExtraTables();
        $ao = $this->getAppelOffre($idao);
        if (!$ao) {
            throw new InvalidArgumentException('Appel d’offres introuvable');
        }
        $st = (string) ($ao['statut'] ?? '');
        if ($st === 'attribue' || $st === 'annule') {
            throw new InvalidArgumentException('Cet AO ne peut plus être attribué');
        }
        $db = Config::getConnexion();
        $stRow = $db->prepare(
            'SELECT idao FROM appel_offre_reponse WHERE idr = :idr AND idao = :idao LIMIT 1'
        );
        $stRow->execute(['idr' => $idr, 'idao' => $idao]);
        if (!$stRow->fetchColumn()) {
            throw new InvalidArgumentException('Réponse introuvable pour cet AO');
        }
        $detail = $db->prepare(
            'SELECT prix_propose, delai_jours, id_vendeur FROM appel_offre_reponse WHERE idr = :idr LIMIT 1'
        );
        $detail->execute(['idr' => $idr]);
        $repRow = $detail->fetch(PDO::FETCH_ASSOC) ?: [];

        $up = $db->prepare(
            'UPDATE appel_offre SET statut = \'attribue\', id_reponse_retenue = :idr WHERE idao = :idao LIMIT 1'
        );
        $up->execute(['idr' => $idr, 'idao' => $idao]);

        if ($idActorAdmin !== null && $idActorAdmin > 0) {
            AchatsAuditLog::append($idActorAdmin, 'ao_attribue', 'appel_offre', $idao, [
                'idr' => $idr,
                'id_vendeur' => isset($repRow['id_vendeur']) ? (int) $repRow['id_vendeur'] : null,
                'prix_propose' => isset($repRow['prix_propose']) ? (float) $repRow['prix_propose'] : null,
                'delai_jours' => isset($repRow['delai_jours']) ? (int) $repRow['delai_jours'] : null,
            ]);
        }
    }
}
