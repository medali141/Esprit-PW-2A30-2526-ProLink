<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Project.php';

class ProjectP {
    public function listAll(): array {
        $db = Config::getConnexion();
        try {
            $st = $db->query('SELECT * FROM project ORDER BY idproject DESC');
            return $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function add(array $data): bool {
        $db = Config::getConnexion();
        try {
            $st = $db->prepare('INSERT INTO project (title, description, owner_id, status) VALUES (:title, :desc, :owner, :status)');
            return $st->execute(['title'=>$data['title'] ?? '', 'desc'=>$data['description'] ?? '', 'owner'=> $data['owner_id'] ?? null, 'status'=>$data['status'] ?? 'draft']);
        } catch (Exception $e) { return false; }
    }

    public function get(int $id) {
        $db = Config::getConnexion();
        $st = $db->prepare('SELECT * FROM project WHERE idproject = :id');
        $st->execute(['id'=>$id]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    public function update(int $id, array $data): bool {
        $db = Config::getConnexion();
        $st = $db->prepare('UPDATE project SET title=:title, description=:desc, owner_id=:owner, status=:status WHERE idproject=:id');
        return $st->execute(['title'=>$data['title'] ?? '', 'desc'=>$data['description'] ?? '', 'owner'=>$data['owner_id'] ?? null, 'status'=>$data['status'] ?? 'draft', 'id'=>$id]);
    }

    public function delete(int $id): bool {
        $db = Config::getConnexion();
        $st = $db->prepare('DELETE FROM project WHERE idproject = :id');
        return $st->execute(['id'=>$id]);
    }
}
