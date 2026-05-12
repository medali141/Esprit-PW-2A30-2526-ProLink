<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Formation.php';

class FormationP {
    public function listAll(): array {
        $db = Config::getConnexion();
        try {
            $st = $db->query('SELECT * FROM Formation ORDER BY id_formation DESC');
            return $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) { return []; }
    }

    public function add(array $data): bool {
        $db = Config::getConnexion();
        $st = $db->prepare('INSERT INTO Formation (titre, description, date_debut, date_fin) VALUES (:titre, :desc, :dd, :df)');
        return $st->execute(['titre'=>$data['titre'] ?? '', 'desc'=>$data['description'] ?? null, 'dd'=>$data['date_debut'] ?? null, 'df'=>$data['date_fin'] ?? null]);
    }

    public function get(int $id) {
        $db = Config::getConnexion();
        $st = $db->prepare('SELECT * FROM Formation WHERE id_formation = :id');
        $st->execute(['id'=>$id]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    public function update(int $id, array $data): bool {
        $db = Config::getConnexion();
        $st = $db->prepare('UPDATE Formation SET titre=:titre, description=:desc, date_debut=:dd, date_fin=:df WHERE id_formation = :id');
        return $st->execute(['titre'=>$data['titre'] ?? '', 'desc'=>$data['description'] ?? null, 'dd'=>$data['date_debut'] ?? null, 'df'=>$data['date_fin'] ?? null, 'id'=>$id]);
    }

    public function delete(int $id): bool {
        $db = Config::getConnexion();
        $st = $db->prepare('DELETE FROM Formation WHERE id_formation = :id');
        return $st->execute(['id'=>$id]);
    }

    public function listInscriptions(int $formationId): array {
        $db = Config::getConnexion();
        try {
            $st = $db->prepare('SELECT * FROM inscription WHERE id_formation = :id');
            $st->execute(['id'=>$formationId]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    /** Add an inscription row. Expects keys: id_formation, nom, prenom, email, telephone (telephone optional) */
    public function addInscription(array $data): bool {
        $db = Config::getConnexion();
        try {
            $st = $db->prepare('INSERT INTO inscription (id_formation, nom, prenom, email, telephone) VALUES (:idf, :nom, :prenom, :email, :tel)');
            return $st->execute([
                'idf' => (int) ($data['id_formation'] ?? $data['id'] ?? 0),
                'nom' => trim($data['nom'] ?? ''),
                'prenom' => trim($data['prenom'] ?? ''),
                'email' => trim($data['email'] ?? ''),
                'tel' => trim($data['telephone'] ?? $data['tel'] ?? '')
            ]);
        } catch (Exception $e) {
            return false;
        }
    }
}
