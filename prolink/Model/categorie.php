<?php
class Categorie {
    private $id_categorie;
    private $nom_categorie;
    private $description;

    // Constructeur
    public function __construct($nom_categorie, $description) {
        $this->nom_categorie = $nom_categorie;
        $this->description = $description;
    }

    // Getters
    public function getIdCategorie() { return $this->id_categorie; }
    public function getNomCategorie() { return $this->nom_categorie; }
    public function getDescription() { return $this->description; }
    
    // Setters
    public function setIdCategorie($id_categorie) { $this->id_categorie = $id_categorie; }
}
?>