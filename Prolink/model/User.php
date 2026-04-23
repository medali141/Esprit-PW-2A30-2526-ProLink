<?php
class User {
    private $iduser;
    private $nom;
    private $prenom;
    private $email;
    private $mdp;
    private $type;
    private $age;

    public function __construct($nom, $prenom, $email, $mdp, $type, $age) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->mdp = $mdp;
        $this->type = $type;
        $this->age = $age;
    }

    public function getNom() { return $this->nom; }
    public function getPrenom() { return $this->prenom; }
    public function getEmail() { return $this->email; }
    public function getMdp() { return $this->mdp; }
    public function getType() { return $this->type; }
    public function getAge() { return $this->age; }
}