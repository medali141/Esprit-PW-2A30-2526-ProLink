<?php
require_once __DIR__ . '/../config.php';

class ProduitP {

    public function listAllAdmin(): array {
        $sql = "SELECT p.*, u.nom AS vendeur_nom, u.prenom AS vendeur_prenom, u.email AS vendeur_email
                FROM produit p
                INNER JOIN user u ON u.iduser = p.id_vendeur
                ORDER BY p.created_at DESC";
        $db = Config::getConnexion();
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listCatalogueActifs(): array {
        $sql = "SELECT p.*, u.prenom AS vendeur_prenom, u.nom AS vendeur_nom
                FROM produit p
                INNER JOIN user u ON u.iduser = p.id_vendeur
                WHERE p.actif = 1
                ORDER BY p.designation ASC";
        $db = Config::getConnexion();
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listByVendeur(int $idVendeur): array {
        $sql = "SELECT * FROM produit WHERE id_vendeur = :v ORDER BY created_at DESC";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute(['v' => $idVendeur]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $sql = "SELECT * FROM produit WHERE idproduit = :id LIMIT 1";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute(['id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function add(array $data): void {
        $sql = "INSERT INTO produit (reference, designation, description, prix_unitaire, stock, id_vendeur, actif)
                VALUES (:reference, :designation, :description, :prix_unitaire, :stock, :id_vendeur, :actif)";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute([
            'reference' => $data['reference'],
            'designation' => $data['designation'],
            'description' => $data['description'] ?? null,
            'prix_unitaire' => $data['prix_unitaire'],
            'stock' => (int) $data['stock'],
            'id_vendeur' => (int) $data['id_vendeur'],
            'actif' => isset($data['actif']) ? (int) (bool) $data['actif'] : 1,
        ]);
    }

    public function update(int $id, array $data): void {
        $sql = "UPDATE produit SET
                reference = :reference,
                designation = :designation,
                description = :description,
                prix_unitaire = :prix_unitaire,
                stock = :stock,
                id_vendeur = :id_vendeur,
                actif = :actif
                WHERE idproduit = :id";
        $db = Config::getConnexion();
        $st = $db->prepare($sql);
        $st->execute([
            'id' => $id,
            'reference' => $data['reference'],
            'designation' => $data['designation'],
            'description' => $data['description'] ?? null,
            'prix_unitaire' => $data['prix_unitaire'],
            'stock' => (int) $data['stock'],
            'id_vendeur' => (int) $data['id_vendeur'],
            'actif' => (int) (bool) ($data['actif'] ?? 1),
        ]);
    }

    public function setActif(int $id, int $actif): void {
        $db = Config::getConnexion();
        $st = $db->prepare("UPDATE produit SET actif = :a WHERE idproduit = :id");
        $st->execute(['a' => $actif ? 1 : 0, 'id' => $id]);
    }

    public function countOrdered(int $idProduit): int {
        $db = Config::getConnexion();
        $st = $db->prepare("SELECT COUNT(*) FROM commande_produit WHERE idproduit = :id");
        $st->execute(['id' => $idProduit]);
        return (int) $st->fetchColumn();
    }

    public function deleteHard(int $id): void {
        $db = Config::getConnexion();
        $st = $db->prepare("DELETE FROM produit WHERE idproduit = :id");
        $st->execute(['id' => $id]);
    }

    /** Comptes pouvant être vendeurs (FK user) — priorité entrepreneurs. */
    public function listVendeursForSelect(): array {
        $sql = "SELECT iduser, nom, prenom, email, type FROM user ORDER BY FIELD(type,'entrepreneur','admin','candidat'), nom, prenom";
        $db = Config::getConnexion();
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
