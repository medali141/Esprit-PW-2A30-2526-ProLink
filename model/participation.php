<?php
class Participation {
    private int    $id_participation;
    private int    $id_event;
    private string $nom;
    private string $prenom;
    private string $email;
    private string $telephone;
    private string $statut;

    public function __construct($id_event, $nom, $prenom, $email, $telephone, $statut = 'en attente') {
        $this->id_event  = $id_event;
        $this->nom       = $nom;
        $this->prenom    = $prenom;
        $this->email     = $email;
        $this->telephone = $telephone;
        $this->statut    = $statut;
    }

    public function getIdEvent()   { return $this->id_event; }
    public function getNom()       { return $this->nom; }
    public function getPrenom()    { return $this->prenom; }
    public function getEmail()     { return $this->email; }
    public function getTelephone() { return $this->telephone; }
    public function getStatut()    { return $this->statut; }
}
?>