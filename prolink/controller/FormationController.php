<?php
require_once __DIR__ . '/../model/Formation.php';
require_once __DIR__ . '/../model/Database.php';

class FormationController {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    // Afficher toutes les formations
    public function afficherToutes() {
        $query = "SELECT f.*, c.nom_categorie 
                  FROM Formation f
                  INNER JOIN Categorie c ON f.id_categorie = c.id_categorie
                  ORDER BY f.date_debut DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Afficher une formation par ID
    public function afficherUne($id) {
        $query = "SELECT f.*, c.nom_categorie 
                  FROM Formation f
                  INNER JOIN Categorie c ON f.id_categorie = c.id_categorie
                  WHERE f.id_formation = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt;
    }

    // Ajouter une formation
    public function ajouter($formation) {
        $query = "INSERT INTO Formation (id_categorie, titre, type, date_debut, date_fin, places_max, statut, date_inscription) 
                  VALUES (:id_categorie, :titre, :type, :date_debut, :date_fin, :places_max, :statut, :date_inscription)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_categorie', $formation->getIdCategorie());
        $stmt->bindParam(':titre', $formation->getTitre());
        $stmt->bindParam(':type', $formation->getType());
        $stmt->bindParam(':date_debut', $formation->getDateDebut());
        $stmt->bindParam(':date_fin', $formation->getDateFin());
        $stmt->bindParam(':places_max', $formation->getPlacesMax());
        $stmt->bindParam(':statut', $formation->getStatut());
        $stmt->bindParam(':date_inscription', $formation->getDateInscription());
        
        return $stmt->execute();
    }

    // Modifier une formation
    public function modifier($id, $formation) {
        $query = "UPDATE Formation SET 
                  id_categorie = :id_categorie, 
                  titre = :titre, 
                  type = :type, 
                  date_debut = :date_debut, 
                  date_fin = :date_fin, 
                  places_max = :places_max, 
                  statut = :statut 
                  WHERE id_formation = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':id_categorie', $formation->getIdCategorie());
        $stmt->bindParam(':titre', $formation->getTitre());
        $stmt->bindParam(':type', $formation->getType());
        $stmt->bindParam(':date_debut', $formation->getDateDebut());
        $stmt->bindParam(':date_fin', $formation->getDateFin());
        $stmt->bindParam(':places_max', $formation->getPlacesMax());
        $stmt->bindParam(':statut', $formation->getStatut());
        
        return $stmt->execute();
    }

    // Supprimer une formation
    public function supprimer($id) {
        $query = "DELETE FROM Formation WHERE id_formation = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>