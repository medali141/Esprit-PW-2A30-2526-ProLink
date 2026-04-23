<?php
class Event {
    private string $titre_event;
    private string $description_event;
    private string $type_event;
    private string $date_debut;
    private string $date_fin;
    private string $lieu_event;
    private int $capacite_max;
    private string $statut;

    public function __construct($titre, $description, $type, $date_debut, $date_fin, $lieu, $capacite_max, $statut) {
        $this->titre_event     = $titre;
        $this->description_event = $description;
        $this->type_event      = $type;
        $this->date_debut      = $date_debut;
        $this->date_fin        = $date_fin;
        $this->lieu_event      = $lieu;
        $this->capacite_max    = $capacite_max;
        $this->statut          = $statut;
    }

    public function getTitreEvent()       { return $this->titre_event; }
    public function getDescriptionEvent() { return $this->description_event; }
    public function getTypeEvent()        { return $this->type_event; }
    public function getDateDebut()        { return $this->date_debut; }
    public function getDateFin()          { return $this->date_fin; }
    public function getLieuEvent()        { return $this->lieu_event; }
    public function getCapaciteMax()      { return $this->capacite_max; }
    public function getStatut()           { return $this->statut; }
}
?>