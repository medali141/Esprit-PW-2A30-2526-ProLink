<?php
/**
 * Tarifs conventionnels (vendeur / produit / période) et demandes d’achat internes.
 */
require_once __DIR__ . '/AchatsAuditLog.php';
require_once __DIR__ . '/../config.php';

class AchatsProcessusAvancesController {
    private ?bool $tablesReady = null;

    private function ensureTables(): void {
        if ($this->tablesReady === true) {
            return;
        }
        $db = Config::getConnexion();
        $db->exec(
            "CREATE TABLE IF NOT EXISTS `tarif_cadre` (
              `idtarif` int(11) NOT NULL AUTO_INCREMENT,
              `id_vendeur` int(11) NOT NULL,
              `idproduit` int(11) NOT NULL,
              `prix_negocie` decimal(12,2) NOT NULL,
              `date_debut` date NOT NULL,
              `date_fin` date NOT NULL,
              `reference_contrat` varchar(120) DEFAULT NULL,
              `commentaire` varchar(400) DEFAULT NULL,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`idtarif`),
              KEY `idx_tc_vp` (`id_vendeur`,`idproduit`),
              KEY `idx_tc_dates` (`date_debut`,`date_fin`),
              CONSTRAINT `fk_tc_vendeur` FOREIGN KEY (`id_vendeur`) REFERENCES `user` (`iduser`)
                ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `fk_tc_produit` FOREIGN KEY (`idproduit`) REFERENCES `produit` (`idproduit`)
                ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $db->exec(
            "CREATE TABLE IF NOT EXISTS `demande_achat` (
              `idda` int(11) NOT NULL AUTO_INCREMENT,
              `libelle` varchar(200) NOT NULL,
              `notes` text DEFAULT NULL,
              `statut` enum('brouillon','soumise','validee','rejetee') NOT NULL DEFAULT 'brouillon',
              `motif_rejet` varchar(500) DEFAULT NULL,
              `id_createur` int(11) NOT NULL,
              `validated_at` timestamp NULL DEFAULT NULL,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`idda`),
              KEY `idx_da_statut` (`statut`),
              CONSTRAINT `fk_da_user` FOREIGN KEY (`id_createur`) REFERENCES `user` (`iduser`)
                ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $db->exec(
            "CREATE TABLE IF NOT EXISTS `demande_achat_ligne` (
              `iddal` int(11) NOT NULL AUTO_INCREMENT,
              `idda` int(11) NOT NULL,
              `idproduit` int(11) NOT NULL,
              `quantite` int(11) NOT NULL,
              `prix_estime` decimal(12,2) NOT NULL DEFAULT 0.00,
              PRIMARY KEY (`iddal`),
              KEY `idx_dal_da` (`idda`),
              KEY `idx_dal_p` (`idproduit`),
              CONSTRAINT `fk_dal_da` FOREIGN KEY (`idda`) REFERENCES `demande_achat` (`idda`)
                ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk_dal_prod` FOREIGN KEY (`idproduit`) REFERENCES `produit` (`idproduit`)
                ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $this->tablesReady = true;
    }

    private function tariffOverlapsOther(
        int $idVendeur,
        int $idproduit,
        string $deb,
        string $fin,
        ?int $excludeIdtarif
    ): bool {
        $db = Config::getConnexion();
        $sql = 'SELECT COUNT(*) FROM tarif_cadre WHERE id_vendeur = :v AND idproduit = :p
                AND NOT (date_fin < :d1 OR date_debut > :d2)';
        $params = ['v' => $idVendeur, 'p' => $idproduit, 'd1' => $deb, 'd2' => $fin];
        if ($excludeIdtarif !== null && $excludeIdtarif > 0) {
            $sql .= ' AND idtarif <> :ex';
            $params['ex'] = $excludeIdtarif;
        }
        $st = $db->prepare($sql);
        $st->execute($params);
        return ((int) $st->fetchColumn()) > 0;
    }

    /** @return list<array<string,mixed>> */
    public function listTarifsCadre(): array {
        $this->ensureTables();
        $db = Config::getConnexion();
        $sql = "SELECT t.*,
                       u.prenom AS vendeur_prenom, u.nom AS vendeur_nom,
                       p.reference, p.designation, p.prix_unitaire AS prix_catalogue
                FROM tarif_cadre t
                INNER JOIN user u ON u.iduser = t.id_vendeur
                INNER JOIN produit p ON p.idproduit = t.idproduit
                ORDER BY t.date_fin DESC, t.idtarif DESC";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function countTarifsActifsAuJourdHui(?string $dateRef = null): int {
        $this->ensureTables();
        $d = $dateRef ?: date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
            $d = date('Y-m-d');
        }
        $db = Config::getConnexion();
        $st = $db->prepare(
            'SELECT COUNT(*) FROM tarif_cadre WHERE date_debut <= :d AND date_fin >= :d2'
        );
        $st->execute(['d' => $d, 'd2' => $d]);
        return (int) $st->fetchColumn();
    }

    public function countDemandesOuvertes(): int {
        $this->ensureTables();
        return (int) Config::getConnexion()->query(
            "SELECT COUNT(*) FROM demande_achat WHERE statut IN ('brouillon','soumise')"
        )->fetchColumn();
    }

    public function addTarifCadre(
        int $idVendeur,
        int $idproduit,
        float $prixNegocie,
        string $dateDebut,
        string $dateFin,
        ?string $refContrat,
        ?string $commentaire,
        int $idActorAdmin
    ): void {
        $this->ensureTables();
        if ($idVendeur <= 0 || $idproduit <= 0) {
            throw new InvalidArgumentException('Paramètres invalides');
        }
        if ($prixNegocie < 0 || $prixNegocie > 999999999.99) {
            throw new InvalidArgumentException('Prix négocié invalide');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateDebut) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFin)) {
            throw new InvalidArgumentException('Dates invalides');
        }
        if ($dateFin < $dateDebut) {
            throw new InvalidArgumentException('La date de fin doit être ≥ à la date de début');
        }

        $db = Config::getConnexion();
        $chk = $db->prepare('SELECT id_vendeur FROM produit WHERE idproduit = :p LIMIT 1');
        $chk->execute(['p' => $idproduit]);
        $pv = $chk->fetchColumn();
        if ($pv === false) {
            throw new InvalidArgumentException('Produit introuvable');
        }
        if ((int) $pv !== $idVendeur) {
            throw new InvalidArgumentException('Le fournisseur doit être le vendeur du produit catalogue');
        }

        if ($this->tariffOverlapsOther($idVendeur, $idproduit, $dateDebut, $dateFin, null)) {
            throw new InvalidArgumentException('Chevauchement avec une autre période tarifaire pour ce couple fournisseur / produit');
        }

        $refContrat = $refContrat !== null ? trim($refContrat) : null;
        if ($refContrat !== null && strlen($refContrat) > 120) {
            throw new InvalidArgumentException('Référence contrat trop longue');
        }
        $commentaire = $commentaire !== null ? trim($commentaire) : null;
        if ($commentaire !== null && strlen($commentaire) > 400) {
            throw new InvalidArgumentException('Commentaire trop long');
        }

        $ins = $db->prepare(
            'INSERT INTO tarif_cadre (id_vendeur, idproduit, prix_negocie, date_debut, date_fin, reference_contrat, commentaire)
             VALUES (:v, :p, :px, :d1, :d2, :r, :c)'
        );
        $ins->execute([
            'v' => $idVendeur,
            'p' => $idproduit,
            'px' => round($prixNegocie, 2),
            'd1' => $dateDebut,
            'd2' => $dateFin,
            'r' => $refContrat !== '' ? $refContrat : null,
            'c' => $commentaire !== '' ? $commentaire : null,
        ]);
        $id = (int) $db->lastInsertId();

        AchatsAuditLog::append($idActorAdmin, 'tarif_cadre_cree', 'tarif_cadre', $id, [
            'idproduit' => $idproduit,
            'id_vendeur' => $idVendeur,
            'prix_negocie' => round($prixNegocie, 2),
            'periode' => [$dateDebut, $dateFin],
        ]);
    }

    public function deleteTarifCadre(int $idtarif, int $idActorAdmin): void {
        $this->ensureTables();
        if ($idtarif <= 0) {
            throw new InvalidArgumentException('Identifiant invalide');
        }
        $db = Config::getConnexion();
        $row = $db->prepare('SELECT idtarif, id_vendeur, idproduit FROM tarif_cadre WHERE idtarif = :i LIMIT 1');
        $row->execute(['i' => $idtarif]);
        $before = $row->fetch(PDO::FETCH_ASSOC);
        if (!$before) {
            throw new InvalidArgumentException('Contrat inexistant');
        }
        $db->prepare('DELETE FROM tarif_cadre WHERE idtarif = :i LIMIT 1')->execute(['i' => $idtarif]);

        AchatsAuditLog::append($idActorAdmin, 'tarif_cadre_supprime', 'tarif_cadre', $idtarif, [
            'id_vendeur' => (int) $before['id_vendeur'],
            'idproduit' => (int) $before['idproduit'],
        ]);
    }

    public function createDemandeAchat(string $libelle, ?string $notes, int $idCreateurAdmin): int {
        $this->ensureTables();
        $libelle = trim($libelle);
        if ($libelle === '' || strlen($libelle) > 200) {
            throw new InvalidArgumentException('Libellé invalide');
        }
        $notes = $notes !== null ? trim($notes) : null;
        if ($notes !== null && strlen($notes) > 4000) {
            throw new InvalidArgumentException('Notes trop longues');
        }
        $db = Config::getConnexion();
        $ins = $db->prepare(
            'INSERT INTO demande_achat (libelle, notes, statut, id_createur) VALUES (:l, :n, \'brouillon\', :u)'
        );
        $ins->execute([
            'l' => $libelle,
            'n' => $notes !== '' ? $notes : null,
            'u' => $idCreateurAdmin,
        ]);
        $idda = (int) $db->lastInsertId();
        AchatsAuditLog::append($idCreateurAdmin, 'demande_creee', 'demande_achat', $idda, ['libelle' => $libelle]);

        return $idda;
    }

    /** @return array<string,mixed>|null */
    public function getDemandeAchat(int $idda): ?array {
        $this->ensureTables();
        $db = Config::getConnexion();
        $st = $db->prepare(
            'SELECT d.*, u.prenom, u.nom FROM demande_achat d
             INNER JOIN user u ON u.iduser = d.id_createur WHERE d.idda = :id'
        );
        $st->execute(['id' => $idda]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** @return list<array<string,mixed>> */
    public function listLignesDemande(int $idda): array {
        $this->ensureTables();
        $db = Config::getConnexion();
        $sql = 'SELECT l.*, p.reference, p.designation, p.prix_unitaire AS prix_catalogue
                FROM demande_achat_ligne l
                INNER JOIN produit p ON p.idproduit = l.idproduit
                WHERE l.idda = :id
                ORDER BY l.iddal ASC';
        $st = $db->prepare($sql);
        $st->execute(['id' => $idda]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return list<array<string,mixed>> */
    public function listDemandesAchat(): array {
        $this->ensureTables();
        $sql = 'SELECT d.*, u.prenom, u.nom,
                (SELECT COALESCE(SUM(l.quantite * l.prix_estime), 0) FROM demande_achat_ligne l WHERE l.idda = d.idda) AS montant_estime,
                (SELECT COUNT(*) FROM demande_achat_ligne lx WHERE lx.idda = d.idda) AS nb_lignes
                FROM demande_achat d
                INNER JOIN user u ON u.iduser = d.id_createur
                ORDER BY FIELD(d.statut,\'soumise\',\'brouillon\',\'validee\',\'rejetee\'), d.created_at DESC';
        return Config::getConnexion()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function addLigneDemande(int $idda, int $idproduit, int $quantite, float $prixEstime, int $idActorAdmin): void {
        $this->ensureTables();
        $dem = $this->getDemandeAchat($idda);
        if (!$dem || ($dem['statut'] ?? '') !== 'brouillon') {
            throw new InvalidArgumentException('Modification impossible (statut non brouillon)');
        }
        if ($idproduit <= 0 || $quantite < 1 || $quantite > 100000) {
            throw new InvalidArgumentException('Ligne invalide');
        }
        if ($prixEstime < 0 || $prixEstime > 999999999.99) {
            throw new InvalidArgumentException('Prix estimé invalide');
        }
        $db = Config::getConnexion();
        $chk = $db->prepare('SELECT idproduit FROM produit WHERE idproduit = :p LIMIT 1');
        $chk->execute(['p' => $idproduit]);
        if (!$chk->fetchColumn()) {
            throw new InvalidArgumentException('Produit inconnu');
        }
        $ins = $db->prepare(
            'INSERT INTO demande_achat_ligne (idda, idproduit, quantite, prix_estime) VALUES (:d, :p, :q, :px)'
        );
        $ins->execute(['d' => $idda, 'p' => $idproduit, 'q' => $quantite, 'px' => round($prixEstime, 2)]);

        AchatsAuditLog::append($idActorAdmin, 'demande_ligne_ajoutee', 'demande_achat', $idda, [
            'idproduit' => $idproduit,
            'quantite' => $quantite,
        ]);
    }

    public function deleteLigneDemande(int $iddal, int $iddaExpected, int $idActorAdmin): void {
        $this->ensureTables();
        $dem = $this->getDemandeAchat($iddaExpected);
        if (!$dem || ($dem['statut'] ?? '') !== 'brouillon') {
            throw new InvalidArgumentException('Suppression ligne impossible');
        }
        $db = Config::getConnexion();
        $st = $db->prepare(
            'DELETE FROM demande_achat_ligne WHERE iddal = :i AND idda = :d LIMIT 1'
        );
        $st->execute(['i' => $iddal, 'd' => $iddaExpected]);
        if ($st->rowCount() === 0) {
            throw new InvalidArgumentException('Ligne introuvable');
        }
        AchatsAuditLog::append($idActorAdmin, 'demande_ligne_supprimee', 'demande_achat', $iddaExpected, [
            'iddal' => $iddal,
        ]);
    }

    public function submitDemandeAchat(int $idda, int $idActorAdmin): void {
        $this->ensureTables();
        $dem = $this->getDemandeAchat($idda);
        if (!$dem || ($dem['statut'] ?? '') !== 'brouillon') {
            throw new InvalidArgumentException('Soumission impossible');
        }
        $nb = count($this->listLignesDemande($idda));
        if ($nb < 1) {
            throw new InvalidArgumentException('Ajoutez au moins une ligne avant soumission');
        }
        $db = Config::getConnexion();
        $db->prepare('UPDATE demande_achat SET statut = \'soumise\' WHERE idda = :id LIMIT 1')->execute(['id' => $idda]);
        AchatsAuditLog::append($idActorAdmin, 'demande_soumise', 'demande_achat', $idda, ['nb_lignes' => $nb]);
    }

    public function validerDemandeAchat(int $idda, int $idActorAdmin): void {
        $this->ensureTables();
        $dem = $this->getDemandeAchat($idda);
        if (!$dem || ($dem['statut'] ?? '') !== 'soumise') {
            throw new InvalidArgumentException('Validation impossible — statut attendu : soumise');
        }
        $db = Config::getConnexion();
        $db->prepare(
            'UPDATE demande_achat SET statut = \'validee\', validated_at = NOW(), motif_rejet = NULL WHERE idda = :id LIMIT 1'
        )->execute(['id' => $idda]);

        AchatsAuditLog::append($idActorAdmin, 'demande_validee', 'demande_achat', $idda, []);
    }

    public function rejeterDemandeAchat(int $idda, string $motif, int $idActorAdmin): void {
        $this->ensureTables();
        $dem = $this->getDemandeAchat($idda);
        if (!$dem || ($dem['statut'] ?? '') !== 'soumise') {
            throw new InvalidArgumentException('Rejet impossible — statut attendu : soumise');
        }
        $motif = trim($motif);
        if ($motif === '' || strlen($motif) > 500) {
            throw new InvalidArgumentException('Motif de rejet requis (max 500 car.)');
        }
        $db = Config::getConnexion();
        $db->prepare(
            'UPDATE demande_achat SET statut = \'rejetee\', motif_rejet = :m, validated_at = NOW() WHERE idda = :id LIMIT 1'
        )->execute(['m' => $motif, 'id' => $idda]);

        AchatsAuditLog::append($idActorAdmin, 'demande_rejetee', 'demande_achat', $idda, ['motif' => $motif]);
    }
}
