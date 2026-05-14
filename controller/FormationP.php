<?php
require_once __DIR__ . '/../config.php';
<<<<<<< HEAD
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
=======

class FormationP {
    private $conn;

    public function __construct() {
        $database = Config::getConnexion();
        $this->conn = $database;
    }

    public function listAll(): array {
    try {
        $st = $this->conn->query('SELECT f.*, c.nom_categorie,
                                  cert.id_certification,
                                  cert.titre as certification_titre
                                  FROM formation f
                                  LEFT JOIN categorie c ON f.id_categorie = c.id_categorie
                                  LEFT JOIN certifications cert ON cert.id_formation = f.id_formation
                                  ORDER BY f.date_debut DESC');
        return $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
    } catch (Exception $e) { 
        return []; 
    }
}
    public function add(array $data): bool {
        try {
            $this->conn->beginTransaction();
            
            // Insertion de la formation
            $st = $this->conn->prepare('INSERT INTO formation (id_categorie, titre, description, type, date_debut, date_fin, places_max, statut) 
                                        VALUES (:id_categorie, :titre, :desc, :type, :dd, :df, :places, :statut)');
            $success = $st->execute([
                'id_categorie' => $data['id_categorie'] ?? null,
                'titre' => $data['titre'] ?? '', 
                'desc' => $data['description'] ?? null, 
                'type' => $data['type'] ?? 'en_ligne',
                'dd' => $data['date_debut'] ?? null, 
                'df' => $data['date_fin'] ?? null,
                'places' => $data['places_max'] ?? 30,
                'statut' => $data['statut'] ?? 'inscrit'
            ]);
            
            if(!$success) {
                $this->conn->rollBack();
                return false;
            }
            
            $formationId = $this->conn->lastInsertId();
            
            // Insertion de la certification si renseignée
            if(!empty($data['certification'])) {
                $st2 = $this->conn->prepare('INSERT INTO certifications (id_formation, titre, description, niveau, duree_heures, actif) 
                                             VALUES (:id_formation, :titre, :desc, :niveau, :duree, 1)');
                $success2 = $st2->execute([
                    'id_formation' => $formationId,
                    'titre' => $data['certification'],
                    'desc' => $data['certification_description'] ?? null,
                    'niveau' => $data['niveau'] ?? 'debutant',
                    'duree' => $data['duree_heures'] ?? 20
                ]);
                
                if(!$success2) {
                    $this->conn->rollBack();
                    return false;
                }
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();
>>>>>>> formation
            return false;
        }
    }

    public function get(int $id) {
<<<<<<< HEAD
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
=======
        try {
            $st = $this->conn->prepare('SELECT f.*, c.nom_categorie,
                                        cert.id_certification,
                                        cert.titre as certification_titre,
                                        cert.description as certification_description,
                                        cert.niveau as certification_niveau,
                                        cert.duree_heures
                                        FROM formation f
                                        LEFT JOIN categorie c ON f.id_categorie = c.id_categorie
                                        LEFT JOIN certifications cert ON cert.id_formation = f.id_formation
                                        WHERE f.id_formation = :id');
            $st->execute(['id' => $id]);
            return $st->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    public function update(int $id, array $data): bool {
        try {
            $st = $this->conn->prepare('UPDATE formation SET 
                                        id_categorie = :id_categorie,
                                        titre = :titre, 
                                        description = :desc, 
                                        type = :type,
                                        date_debut = :dd, 
                                        date_fin = :df,
                                        places_max = :places,
                                        statut = :statut
                                        WHERE id_formation = :id');
            return $st->execute([
                'id' => $id,
                'id_categorie' => $data['id_categorie'] ?? null,
                'titre' => $data['titre'] ?? '', 
                'desc' => $data['description'] ?? null, 
                'type' => $data['type'] ?? 'en_ligne',
                'dd' => $data['date_debut'] ?? null, 
                'df' => $data['date_fin'] ?? null,
                'places' => $data['places_max'] ?? 30,
                'statut' => $data['statut'] ?? 'inscrit'
            ]);
        } catch (Exception $e) {
            return false;
>>>>>>> formation
        }
    }

    public function delete(int $id): bool {
<<<<<<< HEAD
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
=======
        try {
            $st = $this->conn->prepare('DELETE FROM formation WHERE id_formation = :id');
            return $st->execute(['id' => $id]);
>>>>>>> formation
        } catch (Exception $e) {
            return false;
        }
    }

<<<<<<< HEAD
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
=======
    public function addInscription(array $data): bool {
        try {
            $st = $this->conn->prepare('INSERT INTO inscription (id_formation, nom, prenom, email, telephone, date_inscription) 
                                        VALUES (:idf, :nom, :prenom, :email, :tel, CURDATE())');
            return $st->execute([
                'idf' => (int)($data['id_formation'] ?? $data['id'] ?? 0),
                'nom' => trim($data['nom'] ?? ''),
                'prenom' => trim($data['prenom'] ?? ''),
                'email' => trim($data['email'] ?? ''),
                'tel' => trim($data['telephone'] ?? $data['tel'] ?? '')
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function getAllCategories(): array {
        try {
            $st = $this->conn->query('SELECT * FROM categorie ORDER BY nom_categorie ASC');
            return $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            return [];
        }
    }

    // ============= MÉTHODES POUR LES CERTIFICATIONS =============

    public function getCertificationByFormation(int $formationId) {
        try {
            $st = $this->conn->prepare('SELECT * FROM certifications WHERE id_formation = :id_formation LIMIT 1');
            $st->execute(['id_formation' => $formationId]);
            return $st->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    public function getQuizByCertification(int $idCertification) {
        try {
            $st = $this->conn->prepare('SELECT * FROM quiz_certification WHERE id_certification = :id_certification LIMIT 1');
            $st->execute(['id_certification' => $idCertification]);
            return $st->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    public function getQuestionsByQuiz(int $idQuiz): array {
        try {
            $st = $this->conn->prepare('SELECT * FROM quiz_questions WHERE id_quiz = :id_quiz ORDER BY id_question');
            $st->execute(['id_quiz' => $idQuiz]);
            $questions = $st->fetchAll(PDO::FETCH_ASSOC);
            
            foreach($questions as &$q) {
                $st2 = $this->conn->prepare('SELECT * FROM quiz_reponses WHERE id_question = :id_question');
                $st2->execute(['id_question' => $q['id_question']]);
                $q['reponses'] = $st2->fetchAll(PDO::FETCH_ASSOC);
            }
            return $questions;
        } catch (Exception $e) {
            return [];
        }
    }

    public function saveQuizAttempt(int $userId, int $idQuiz, int $score, string $statut): bool {
        try {
            $st = $this->conn->prepare('INSERT INTO user_quiz_attempts (id_user, id_quiz, score, statut, date_tentative) 
                                        VALUES (:id_user, :id_quiz, :score, :statut, NOW())');
            return $st->execute([
                'id_user' => $userId,
                'id_quiz' => $idQuiz,
                'score' => $score,
                'statut' => $statut
            ]);
>>>>>>> formation
        } catch (Exception $e) {
            return false;
        }
    }

<<<<<<< HEAD
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
=======
    public function saveAchievement(int $userId, int $idCertification, string $numeroCert, int $score): bool {
        try {
            $st = $this->conn->prepare('INSERT INTO user_achievements (id_user, id_certification, date_obtention, statut, numero_certificat, score) 
                                        VALUES (:id_user, :id_certification, CURDATE(), "obtenu", :numero_certificat, :score)');
            return $st->execute([
                'id_user' => $userId,
                'id_certification' => $idCertification,
                'numero_certificat' => $numeroCert,
                'score' => $score
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function hasObtainedCertification(int $userId, int $idCertification): bool {
        try {
            $st = $this->conn->prepare('SELECT COUNT(*) FROM user_achievements 
                                        WHERE id_user = :id_user AND id_certification = :id_certification AND statut = "obtenu"');
            $st->execute(['id_user' => $userId, 'id_certification' => $idCertification]);
            return $st->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
>>>>>>> formation
