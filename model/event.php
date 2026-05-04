<?php
declare(strict_types=1);

class Event
{
    private $titre_event;
    private $description_event;
    private $type_event;
    private $date_debut;
    private $date_fin;
    private $lieu_event;
    private $capacite_max;

    public function __construct(
        $titre_event,
        $description_event,
        $type_event,
        $date_debut,
        $date_fin,
        $lieu_event,
        $capacite_max
    ) {
        $this->titre_event = $titre_event;
        $this->description_event = $description_event;
        $this->type_event = $type_event;
        $this->date_debut = $date_debut;
        $this->date_fin = $date_fin;
        $this->lieu_event = $lieu_event;
        $this->capacite_max = $capacite_max;
    }

    public function getTitreEvent() { return $this->titre_event; }
    public function getDescriptionEvent() { return $this->description_event; }
    public function getTypeEvent() { return $this->type_event; }
    public function getDateDebut() { return $this->date_debut; }
    public function getDateFin() { return $this->date_fin; }
    public function getLieuEvent() { return $this->lieu_event; }
    public function getCapaciteMax() { return $this->capacite_max; }
}
