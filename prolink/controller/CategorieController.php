<?php
require_once __DIR__ . '/../model/Categorie.php';
require_once __DIR__ . '/../model/Database.php';

class CategorieController {
    private $db;
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Afficher toutes les catégories
    public function afficherToutes() {
        $query = "SELECT * FROM Categorie ORDER BY nom_categorie";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Afficher une catégorie par ID
    public function afficherUne($id) {
        $query = "SELECT * FROM Categorie WHERE id_categorie = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt;
    }

    // Ajouter une catégorie
    public function ajouter($categorie) {
        $query = "INSERT INTO Categorie (nom_categorie, description) VALUES (:nom, :desc)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom', $categorie->getNomCategorie());
        $stmt->bindParam(':desc', $categorie->getDescription());
        return $stmt->execute();
    }

    // Modifier une catégorie
    public function modifier($id, $categorie) {
        $query = "UPDATE Categorie SET nom_categorie = :nom, description = :desc WHERE id_categorie = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nom', $categorie->getNomCategorie());
        $stmt->bindParam(':desc', $categorie->getDescription());
        return $stmt->execute();
    }

    // Supprimer une catégorie
    public function supprimer($id) {
        $query = "DELETE FROM Categorie WHERE id_categorie = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>