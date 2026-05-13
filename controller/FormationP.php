<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Formation.php';

class FormationP {
    /**
     * Catégories proposées dans le formulaire d'ajout / modification. Le
     * champ reste libre (VARCHAR) en base, donc on peut faire évoluer la
     * liste sans toucher au schéma.
     */
    public const CATEGORIES = [
        'Développement Web',
        'Développement Mobile',
        'Data Science / IA',
        'Design / UX',
        'Marketing Digital',
        'Business / Entrepreneuriat',
        'Langues',
        'Soft Skills',
        'Autre',
    ];

    public function categories(): array { return self::CATEGORIES; }

    public function listAll(): array {
        $db = Config::getConnexion();
        try {
            $st = $db->query('SELECT * FROM Formation ORDER BY id_formation DESC');
            return $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) { return []; }
    }

    /**
     * @return int|false id_formation créé, ou false en cas d’échec
     */
    public function add(array $data): int|false {
        $db = Config::getConnexion();
        try {
            $st = $db->prepare('INSERT INTO Formation (titre, categorie, description, date_debut, date_fin) VALUES (:titre, :cat, :desc, :dd, :df)');
            $ok = $st->execute([
                'titre' => trim((string) ($data['titre'] ?? '')),
                'cat'   => $this->normalizeCategorie($data['categorie'] ?? null),
                'desc'  => $data['description'] ?? null,
                'dd'    => $data['date_debut'] ?? null,
                'df'    => $data['date_fin'] ?? null,
            ]);
            if (!$ok) {
                return false;
            }
            $id = (int) $db->lastInsertId();
            return $id > 0 ? $id : false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function get(int $id) {
        $db = Config::getConnexion();
        $st = $db->prepare('SELECT * FROM Formation WHERE id_formation = :id');
        $st->execute(['id'=>$id]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    public function update(int $id, array $data): bool {
        $db = Config::getConnexion();
        $st = $db->prepare('UPDATE Formation SET titre=:titre, categorie=:cat, description=:desc, date_debut=:dd, date_fin=:df WHERE id_formation = :id');
        return $st->execute([
            'titre' => trim((string) ($data['titre'] ?? '')),
            'cat'   => $this->normalizeCategorie($data['categorie'] ?? null),
            'desc'  => $data['description'] ?? null,
            'dd'    => $data['date_debut'] ?? null,
            'df'    => $data['date_fin'] ?? null,
            'id'    => $id,
        ]);
    }

    private function normalizeCategorie($value): ?string {
        $v = trim((string) ($value ?? ''));
        return $v === '' ? null : $v;
    }

    public function getInscription(int $idInscription): ?array {
        $db = Config::getConnexion();
        try {
            $st = $db->prepare(
                'SELECT i.*, f.titre AS formation_titre, f.categorie AS formation_categorie,
                        f.date_debut AS formation_date_debut, f.date_fin AS formation_date_fin
                 FROM inscription i
                 INNER JOIN Formation f ON i.id_formation = f.id_formation
                 WHERE i.id_inscription = :id LIMIT 1'
            );
            $st->execute(['id' => $idInscription]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            return null;
        }
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

    /**
     * Add an inscription row. Expects keys: id_formation, nom, prenom, email,
     * telephone (optional), id_user (optional). Renvoie l'id créé ou false.
     *
     * @return int|false
     */
    public function addInscription(array $data) {
        $db = Config::getConnexion();
        try {
            $st = $db->prepare(
                'INSERT INTO inscription (id_formation, id_user, nom, prenom, email, telephone)
                 VALUES (:idf, :iu, :nom, :prenom, :email, :tel)'
            );
            $ok = $st->execute([
                'idf'    => (int) ($data['id_formation'] ?? $data['id'] ?? 0),
                'iu'     => isset($data['id_user']) && (int) $data['id_user'] > 0 ? (int) $data['id_user'] : null,
                'nom'    => trim((string) ($data['nom'] ?? '')),
                'prenom' => trim((string) ($data['prenom'] ?? '')),
                'email'  => trim((string) ($data['email'] ?? '')),
                'tel'    => trim((string) ($data['telephone'] ?? $data['tel'] ?? '')),
            ]);
            return $ok ? (int) $db->lastInsertId() : false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Retrouve l'inscription de l'utilisateur connecté pour une formation.
     * On essaye d'abord par id_user (lien direct), puis par e-mail en
     * fallback pour les inscriptions historiques sans id_user.
     *
     * @return array<string, mixed>|null
     */
    public function findInscriptionForUser(int $idUser, int $idFormation, ?string $email = null): ?array
    {
        $db = Config::getConnexion();
        try {
            $st = $db->prepare(
                'SELECT * FROM inscription
                 WHERE id_formation = :idf AND id_user = :iu
                 ORDER BY id_inscription DESC LIMIT 1'
            );
            $st->execute(['idf' => $idFormation, 'iu' => $idUser]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            if ($row) return $row;

            if ($email !== null && $email !== '') {
                $st2 = $db->prepare(
                    'SELECT * FROM inscription
                     WHERE id_formation = :idf AND email = :em
                     ORDER BY id_inscription DESC LIMIT 1'
                );
                $st2->execute(['idf' => $idFormation, 'em' => $email]);
                $row2 = $st2->fetch(PDO::FETCH_ASSOC);
                return $row2 ?: null;
            }
        } catch (Exception $e) {
            return null;
        }
        return null;
    }

    /**
     * Enregistre la dernière tentative de quiz. Si le score atteint le seuil
     * passant, on marque l'inscription comme validée (quiz_passed=1 + date).
     */
    public function recordQuizAttempt(int $idInscription, int $score, bool $passed): bool
    {
        $db = Config::getConnexion();
        try {
            if ($passed) {
                $st = $db->prepare(
                    'UPDATE inscription
                     SET quiz_score = :s, quiz_passed = 1,
                         quiz_passed_at = COALESCE(quiz_passed_at, CURRENT_TIMESTAMP)
                     WHERE id_inscription = :id'
                );
            } else {
                $st = $db->prepare('UPDATE inscription SET quiz_score = :s WHERE id_inscription = :id');
            }
            return $st->execute(['s' => $score, 'id' => $idInscription]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Vérifie qu'une inscription appartient bien à l'utilisateur connecté
     * (via id_user OU e-mail, fallback pour les anciennes lignes).
     */
    public function inscriptionBelongsToUser(array $row, int $idUser, ?string $email = null): bool
    {
        if (!$row) return false;
        if (isset($row['id_user']) && (int) $row['id_user'] === $idUser && $idUser > 0) return true;
        if ($email !== null && $email !== '' && isset($row['email']) && strcasecmp((string) $row['email'], $email) === 0) return true;
        return false;
    }
}
