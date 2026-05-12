<?php
class Formation {
    public ?int $id = null;
    public string $titre = '';
    public ?string $description = null;
    public ?string $date_debut = null;
    public ?string $date_fin = null;
    public function __construct(array $data = []) {
        if ($data) {
            $this->id = isset($data['id_formation']) ? (int)$data['id_formation'] : null;
            $this->titre = $data['titre'] ?? '';
            $this->description = $data['description'] ?? null;
            $this->date_debut = $data['date_debut'] ?? null;
            $this->date_fin = $data['date_fin'] ?? null;
        }
    }
}
