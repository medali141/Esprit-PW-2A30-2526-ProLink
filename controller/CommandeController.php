<?php
/**
 * Contrôleur commandes — tables `commande` et `commande_produit`.
 */
require_once __DIR__ . '/../config.php';

class CommandeController {

    public function listAllAdmin(): array {
        $sql = "SELECT c.*, u.prenom, u.nom, u.email
                FROM commande c
                INNER JOIN user u ON u.iduser = c.id_acheteur
                ORDER BY c.date_commande DESC";
        $db = Config::getConnexion();
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listByAcheteur(int $idAcheteur): array {
        $sql = "SELECT * FROM commande WHERE id_acheteur = :a ORDER BY date_commande DESC";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute(['a' => $idAcheteur]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $sql = "SELECT c.*, u.prenom, u.nom, u.email
                FROM commande c
                INNER JOIN user u ON u.iduser = c.id_acheteur
                WHERE c.idcommande = :id LIMIT 1";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute(['id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getLignes(int $idCommande): array {
        $sql = "SELECT cp.*, p.reference, p.designation, p.id_vendeur,
                       uv.prenom AS v_prenom, uv.nom AS v_nom
                FROM commande_produit cp
                INNER JOIN produit p ON p.idproduit = cp.idproduit
                INNER JOIN user uv ON uv.iduser = p.id_vendeur
                WHERE cp.idcommande = :id
                ORDER BY p.designation";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute(['id' => $idCommande]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array<int,int> $cart idproduit => quantite
     * @param array{adresse_livraison:string,code_postal:string,ville:string,pays?:string,notes?:string} $livraison
     */
    public function createFromCart(int $acheteurId, array $cart, array $livraison): int {
        if (empty($cart)) {
            throw new InvalidArgumentException('Panier vide');
        }

        $db = Config::getConnexion();
        $db->beginTransaction();

        try {
            $total = 0.0;
            $resolved = [];

            foreach ($cart as $idProduit => $qte) {
                $idProduit = (int) $idProduit;
                $qte = (int) $qte;
                if ($idProduit <= 0 || $qte <= 0) {
                    continue;
                }

                $st = $db->prepare("SELECT idproduit, prix_unitaire, stock, actif FROM produit WHERE idproduit = :id FOR UPDATE");
                $st->execute(['id' => $idProduit]);
                $p = $st->fetch(PDO::FETCH_ASSOC);
                if (!$p || !(int) $p['actif']) {
                    throw new RuntimeException('Produit indisponible : #' . $idProduit);
                }
                if ((int) $p['stock'] < $qte) {
                    throw new RuntimeException('Stock insuffisant pour le produit #' . $idProduit);
                }

                $pu = (float) $p['prix_unitaire'];
                $lineTotal = $pu * $qte;
                $total += $lineTotal;
                $resolved[] = ['id' => $idProduit, 'qte' => $qte, 'pu' => $pu];
            }

            if (empty($resolved)) {
                throw new InvalidArgumentException('Panier invalide');
            }

            $pays = $livraison['pays'] ?? 'Tunisie';
            $notes = $livraison['notes'] ?? null;

            $ins = $db->prepare(
                "INSERT INTO commande (id_acheteur, statut, montant_total, notes, adresse_livraison, code_postal, ville, pays)
                 VALUES (:acheteur, 'en_attente_paiement', :total, :notes, :adr, :cp, :ville, :pays)"
            );
            $ins->execute([
                'acheteur' => $acheteurId,
                'total' => round($total, 2),
                'notes' => $notes,
                'adr' => $livraison['adresse_livraison'],
                'cp' => $livraison['code_postal'],
                'ville' => $livraison['ville'],
                'pays' => $pays,
            ]);
            $idCommande = (int) $db->lastInsertId();

            $insL = $db->prepare(
                "INSERT INTO commande_produit (idcommande, idproduit, quantite, prix_unitaire)
                 VALUES (:idc, :idp, :qte, :pu)"
            );
            $upd = $db->prepare("UPDATE produit SET stock = stock - :q WHERE idproduit = :id");

            foreach ($resolved as $line) {
                $insL->execute([
                    'idc' => $idCommande,
                    'idp' => $line['id'],
                    'qte' => $line['qte'],
                    'pu' => $line['pu'],
                ]);
                $upd->execute(['q' => $line['qte'], 'id' => $line['id']]);
            }

            $db->commit();
            return $idCommande;
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function updateMeta(
        int $id,
        string $statut,
        ?string $numeroSuivi,
        ?string $datePrevue,
        ?string $dateEffective,
        ?string $notes
    ): void {
        $allowed = [
            'brouillon', 'en_attente_paiement', 'payee', 'en_preparation',
            'expediee', 'livree', 'annulee',
        ];
        if (!in_array($statut, $allowed, true)) {
            throw new InvalidArgumentException('Statut invalide');
        }

        $db = Config::getConnexion();
        $db->beginTransaction();

        try {
            $st = $db->prepare("SELECT statut FROM commande WHERE idcommande = :id FOR UPDATE");
            $st->execute(['id' => $id]);
            $old = $st->fetch(PDO::FETCH_ASSOC);
            if (!$old) {
                throw new RuntimeException('Commande introuvable');
            }
            $oldStatut = $old['statut'];

            if ($statut === 'annulee' && $oldStatut !== 'annulee') {
                $this->restoreStockForCommande($db, $id);
            }

            if ($oldStatut === 'annulee' && $statut !== 'annulee') {
                $this->decrementStockForCommande($db, $id);
            }

            $up = $db->prepare(
                "UPDATE commande SET
                    statut = :st,
                    numero_suivi = :ns,
                    date_livraison_prevue = :dp,
                    date_livraison_effective = :de,
                    notes = :notes
                 WHERE idcommande = :id"
            );
            $up->execute([
                'st' => $statut,
                'ns' => ($numeroSuivi !== null && $numeroSuivi !== '') ? $numeroSuivi : null,
                'dp' => ($datePrevue !== null && $datePrevue !== '') ? $datePrevue : null,
                'de' => ($dateEffective !== null && $dateEffective !== '') ? $dateEffective : null,
                'notes' => $notes ?? '',
                'id' => $id,
            ]);

            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    private function restoreStockForCommande(PDO $db, int $idCommande): void {
        $st = $db->prepare("SELECT idproduit, quantite FROM commande_produit WHERE idcommande = :id");
        $st->execute(['id' => $idCommande]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        $upd = $db->prepare("UPDATE produit SET stock = stock + :q WHERE idproduit = :idp");
        foreach ($rows as $r) {
            $upd->execute(['q' => (int) $r['quantite'], 'idp' => (int) $r['idproduit']]);
        }
    }

    private function decrementStockForCommande(PDO $db, int $idCommande): void {
        $st = $db->prepare("SELECT idproduit, quantite FROM commande_produit WHERE idcommande = :id");
        $st->execute(['id' => $idCommande]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        $sel = $db->prepare("SELECT stock FROM produit WHERE idproduit = :id FOR UPDATE");
        $upd = $db->prepare("UPDATE produit SET stock = stock - :q WHERE idproduit = :idp");
        foreach ($rows as $r) {
            $q = (int) $r['quantite'];
            $idp = (int) $r['idproduit'];
            $sel->execute(['id' => $idp]);
            $stock = (int) $sel->fetchColumn();
            if ($stock < $q) {
                throw new RuntimeException('Stock insuffisant pour réactiver la commande');
            }
            $upd->execute(['q' => $q, 'idp' => $idp]);
        }
    }

    public function countAll(): int {
        $db = Config::getConnexion();
        return (int) $db->query("SELECT COUNT(*) FROM commande")->fetchColumn();
    }

    public function countProduitsActifs(): int {
        $db = Config::getConnexion();
        return (int) $db->query("SELECT COUNT(*) FROM produit WHERE actif = 1")->fetchColumn();
    }

    /** Commandes contenant au moins un produit du vendeur. */
    public function listByVendeur(int $idVendeur): array {
        $sql = "SELECT DISTINCT c.*, u.prenom, u.nom, u.email
                FROM commande c
                INNER JOIN user u ON u.iduser = c.id_acheteur
                INNER JOIN commande_produit cp ON cp.idcommande = c.idcommande
                INNER JOIN produit p ON p.idproduit = cp.idproduit
                WHERE p.id_vendeur = :v
                ORDER BY c.date_commande DESC";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute(['v' => $idVendeur]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
