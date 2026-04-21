<?php
require_once 'Database.php';

class Formation {
    private $id_formation;
    private $id_categorie;
    private $titre;
    private $type;
    private $date_debut;
    private $date_fin;
    private $places_max;
    private $statut;
    private $date_inscription;

    // Constructeur
    public function __construct($id_categorie, $titre, $type, $date_debut, $date_fin, $places_max, $statut, $date_inscription) {
        $this->id_categorie = $id_categorie;
        $this->titre = $titre;
        $this->type = $type;
        $this->date_debut = $date_debut;
        $this->date_fin = $date_fin;
        $this->places_max = $places_max;
        $this->statut = $statut;
        $this->date_inscription = $date_inscription;
    }

    // Getters
    public function getIdFormation() { return $this->id_formation; }
    public function getIdCategorie() { return $this->id_categorie; }
    public function getTitre() { return $this->titre; }
    public function getType() { return $this->type; }
    public function getDateDebut() { return $this->date_debut; }
    public function getDateFin() { return $this->date_fin; }
    public function getPlacesMax() { return $this->places_max; }
    public function getStatut() { return $this->statut; }
    public function getDateInscription() { return $this->date_inscription; }
    
    // Setters
    public function setIdFormation($id) { $this->id_formation = $id; }
}
?>