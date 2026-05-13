<?php
class Project {
    public ?int $idproject = null;
    public string $title = '';
    public string $description = '';
    public ?int $owner_id = null;
    public string $status = 'draft';
    public function __construct(array $data = []) {
        if ($data) {
            $this->idproject = isset($data['idproject']) ? (int)$data['idproject'] : null;
            $this->title = $data['title'] ?? '';
            $this->description = $data['description'] ?? '';
            $this->owner_id = isset($data['owner_id']) ? (int)$data['owner_id'] : null;
            $this->status = $data['status'] ?? 'draft';
        }
    }
}
