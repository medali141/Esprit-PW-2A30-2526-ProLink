<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/ProfanityFilter.php';
require_once __DIR__ . '/NotificationP.php';

/**
 * Candidatures sur projet (front-office).
 *
 * Statuts possibles :
 *   - en_attente  : la candidature vient d'être créée
 *   - acceptee    : le propriétaire l'a acceptée
 *   - refusee     : le propriétaire l'a refusée
 *   - retiree     : le candidat l'a retirée lui-même
 */
class CandidatureP
{
    public const STATUS_PENDING  = 'en_attente';
    public const STATUS_ACCEPTED = 'acceptee';
    public const STATUS_REJECTED = 'refusee';
    public const STATUS_WITHDRAWN = 'retiree';

    /** Tailles et extensions autorisées pour le CV. */
    public const CV_MAX_BYTES = 5 * 1024 * 1024; // 5 Mo
    public const CV_ALLOWED_EXT = ['pdf', 'doc', 'docx'];
    public const CV_ALLOWED_MIME = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/octet-stream', // certains navigateurs renvoient ça pour docx
    ];

    /** Dossier physique de stockage des CVs (créé si manquant). */
    public static function cvDir(): string
    {
        return realpath(__DIR__ . '/../view/uploads') . DIRECTORY_SEPARATOR . 'cv';
    }

    /** URL relative web vers le dossier des CVs (pour afficher un lien). */
    public static function cvWebPath(): string
    {
        return 'view/uploads/cv/';
    }

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED,
        self::STATUS_WITHDRAWN,
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING  => 'En attente',
        self::STATUS_ACCEPTED => 'Acceptée',
        self::STATUS_REJECTED => 'Refusée',
        self::STATUS_WITHDRAWN => 'Retirée',
    ];

    private string $lastError = '';
    public function getLastError(): string { return $this->lastError; }

    /**
     * Crée (ou recrée si retirée) la candidature de l'utilisateur sur le projet.
     *
     * @param array<string, mixed> $data clés : nom, prenom, email, message
     * @param array<string, mixed>|null $cvFile entrée $_FILES['cv'] (peut être null)
     * @return int|false  id de la candidature, ou false en cas d'erreur (lastError renseigné)
     */
    public function apply(int $idProject, int $idUser, array $data, ?array $cvFile = null)
    {
        $this->lastError = '';
        if ($idProject < 1 || $idUser < 1) {
            $this->lastError = 'Paramètres invalides.';
            return false;
        }
        $nom    = trim((string) ($data['nom'] ?? ''));
        $prenom = trim((string) ($data['prenom'] ?? ''));
        $email  = trim((string) ($data['email'] ?? ''));
        $message = trim((string) ($data['message'] ?? ''));

        if ($nom === '' || $email === '') {
            $this->lastError = 'Le nom et l\'email sont obligatoires.';
            return false;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->lastError = 'L\'email saisi n\'est pas valide.';
            return false;
        }
        if ($message !== '' && mb_strlen($message) > 4000) {
            $message = mb_substr($message, 0, 4000);
        }
        $profanity = ProfanityFilter::checkAll([
            'Nom' => $nom, 'Prénom' => $prenom, 'Message' => $message,
        ]);
        if ($profanity !== null) {
            $this->lastError = $profanity;
            return false;
        }

        $existing = $this->findForUser($idProject, $idUser);
        if ($existing && (string) $existing['statut'] !== self::STATUS_WITHDRAWN) {
            $this->lastError = 'Vous avez déjà postulé sur ce projet.';
            return false;
        }

        $cvRelative = $existing['cv_fichier'] ?? null;
        if ($cvFile !== null && isset($cvFile['error']) && $cvFile['error'] !== UPLOAD_ERR_NO_FILE) {
            $stored = $this->storeCv($cvFile);
            if ($stored === null) {
                return false;
            }
            if (!empty($existing['cv_fichier'])) {
                $this->deleteCv((string) $existing['cv_fichier']);
            }
            $cvRelative = $stored;
        }

        $db = Config::getConnexion();
        try {
            if ($existing) {
                $st = $db->prepare(
                    'UPDATE project_candidature
                     SET nom = :nom, prenom = :prenom, email = :email,
                         cv_fichier = :cv, message = :m, statut = :s,
                         updated_at = CURRENT_TIMESTAMP
                     WHERE id_candidature = :id'
                );
                $st->execute([
                    'nom' => $nom, 'prenom' => $prenom, 'email' => $email,
                    'cv'  => $cvRelative, 'm' => $message, 's' => self::STATUS_PENDING,
                    'id'  => (int) $existing['id_candidature'],
                ]);
                return (int) $existing['id_candidature'];
            }

            $st = $db->prepare(
                'INSERT INTO project_candidature
                    (id_project, id_user, nom, prenom, email, cv_fichier, message, statut)
                 VALUES (:p, :u, :nom, :prenom, :email, :cv, :m, :s)'
            );
            $ok = $st->execute([
                'p' => $idProject, 'u' => $idUser,
                'nom' => $nom, 'prenom' => $prenom, 'email' => $email,
                'cv'  => $cvRelative, 'm' => $message, 's' => self::STATUS_PENDING,
            ]);
            if ($ok) {
                $newId = (int) $db->lastInsertId();
                $this->notifyOwnerOfNewCandidature($idProject, $idUser, $nom, $prenom);
                return $newId;
            }
            return false;
        } catch (Exception $e) {
            if ($cvRelative !== null && $cvRelative !== ($existing['cv_fichier'] ?? null)) {
                $this->deleteCv((string) $cvRelative);
            }
            $this->lastError = 'Erreur lors de l\'enregistrement de la candidature.';
            return false;
        }
    }

    /**
     * Stocke le CV uploadé sous view/uploads/cv/, en renvoyant son nom de
     * fichier final (ou null en cas d'erreur, lastError renseigné).
     */
    private function storeCv(array $file): ?string
    {
        $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err === UPLOAD_ERR_NO_FILE) {
            $this->lastError = 'Aucun fichier CV reçu.';
            return null;
        }
        if ($err !== UPLOAD_ERR_OK) {
            $this->lastError = 'Erreur lors de l\'envoi du CV (code ' . $err . ').';
            return null;
        }
        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > self::CV_MAX_BYTES) {
            $this->lastError = 'Le CV doit faire moins de ' . (self::CV_MAX_BYTES / 1024 / 1024) . ' Mo.';
            return null;
        }
        $origName = (string) ($file['name'] ?? '');
        $ext = strtolower((string) pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($ext, self::CV_ALLOWED_EXT, true)) {
            $this->lastError = 'Format de CV non autorisé. Utilisez PDF, DOC ou DOCX.';
            return null;
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            $this->lastError = 'Upload CV invalide.';
            return null;
        }
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detected = $finfo ? finfo_file($finfo, $tmp) : null;
            if ($finfo) finfo_close($finfo);
            if ($detected !== null && !in_array($detected, self::CV_ALLOWED_MIME, true)) {
                $this->lastError = 'Le type du fichier CV n\'est pas accepté (' . htmlspecialchars((string) $detected) . ').';
                return null;
            }
        }
        $dir = self::cvDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (!is_dir($dir) || !is_writable($dir)) {
            $this->lastError = 'Impossible d\'écrire dans le dossier des CV.';
            return null;
        }
        try {
            $rand = bin2hex(random_bytes(6));
        } catch (Exception $e) {
            $rand = substr(md5((string) microtime(true)), 0, 12);
        }
        $newName = 'cv_' . date('YmdHis') . '_' . $rand . '.' . $ext;
        $target  = $dir . DIRECTORY_SEPARATOR . $newName;
        if (!@move_uploaded_file($tmp, $target)) {
            $this->lastError = 'Échec de l\'enregistrement du CV.';
            return null;
        }
        @chmod($target, 0644);
        return $newName;
    }

    /** Supprime un CV stocké si son chemin est sûr (pas de ".." ou /). */
    public function deleteCv(string $filename): bool
    {
        $filename = basename($filename);
        if ($filename === '' || $filename === '.' || $filename === '..') return false;
        $path = self::cvDir() . DIRECTORY_SEPARATOR . $filename;
        if (is_file($path)) {
            return @unlink($path);
        }
        return false;
    }

    /** @return array<string, mixed>|null */
    public function findForUser(int $idProject, int $idUser): ?array
    {
        if ($idProject < 1 || $idUser < 1) return null;
        $db = Config::getConnexion();
        try {
            $st = $db->prepare(
                'SELECT * FROM project_candidature
                 WHERE id_project = :p AND id_user = :u LIMIT 1'
            );
            $st->execute(['p' => $idProject, 'u' => $idUser]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /** @return array<string, mixed>|null */
    public function get(int $idCandidature): ?array
    {
        if ($idCandidature < 1) return null;
        $db = Config::getConnexion();
        try {
            $st = $db->prepare('SELECT * FROM project_candidature WHERE id_candidature = :id LIMIT 1');
            $st->execute(['id' => $idCandidature]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Liste les candidatures d'un projet avec les infos basiques de
     * l'utilisateur (prenom, nom, email, type).
     *
     * @return list<array<string, mixed>>
     */
    public function listForProject(int $idProject): array
    {
        if ($idProject < 1) return [];
        $db = Config::getConnexion();
        try {
            $st = $db->prepare(
                "SELECT c.*,
                        u.prenom AS account_prenom,
                        u.nom    AS account_nom,
                        u.email  AS account_email,
                        u.type   AS account_type
                 FROM project_candidature c
                 INNER JOIN user u ON c.id_user = u.iduser
                 WHERE c.id_project = :p
                 ORDER BY (c.statut = 'en_attente') DESC, c.created_at DESC"
            );
            $st->execute(['p' => $idProject]);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            return is_array($rows) ? $rows : [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function countByStatus(int $idProject): array
    {
        $out = array_fill_keys(self::STATUSES, 0);
        if ($idProject < 1) return $out;
        $db = Config::getConnexion();
        try {
            $st = $db->prepare(
                'SELECT statut, COUNT(*) AS n FROM project_candidature
                 WHERE id_project = :p GROUP BY statut'
            );
            $st->execute(['p' => $idProject]);
            while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
                $s = (string) $r['statut'];
                if (array_key_exists($s, $out)) $out[$s] = (int) $r['n'];
            }
        } catch (Exception $e) {
        }
        return $out;
    }

    public function updateStatus(int $idCandidature, string $newStatus): bool
    {
        if (!in_array($newStatus, self::STATUSES, true)) return false;
        $before = $this->get($idCandidature);
        if (!$before) return false;
        $db = Config::getConnexion();
        try {
            $st = $db->prepare(
                'UPDATE project_candidature SET statut = :s, updated_at = CURRENT_TIMESTAMP
                 WHERE id_candidature = :id'
            );
            $ok = $st->execute(['s' => $newStatus, 'id' => $idCandidature]);
            if ($ok && (string) $before['statut'] !== $newStatus) {
                $this->notifyCandidateOfStatusChange((array) $before, $newStatus);
            }
            return $ok;
        } catch (Exception $e) {
            return false;
        }
    }

    public function withdraw(int $idCandidature, int $idUser): bool
    {
        $c = $this->get($idCandidature);
        if (!$c || (int) $c['id_user'] !== $idUser) return false;
        return $this->updateStatus($idCandidature, self::STATUS_WITHDRAWN);
    }

    /**
     * Évalue un candidat accepté. La note doit être dans [1..5]. Le commentaire
     * (optionnel) passe par le filtre de gros mots. À l'enregistrement, une
     * notification est envoyée au candidat pour qu'il consulte son évaluation.
     */
    public function evaluate(int $idCandidature, int $note, string $comment = ''): bool
    {
        $this->lastError = '';
        if ($note < 1 || $note > 5) {
            $this->lastError = 'La note doit être comprise entre 1 et 5.';
            return false;
        }
        $comment = trim($comment);
        if ($comment !== '') {
            $hit = ProfanityFilter::firstMatch($comment);
            if ($hit !== null) {
                $this->lastError = 'Votre commentaire contient un terme interdit (« ' . $hit . ' »). Reformulez s\'il vous plaît.';
                return false;
            }
            if (mb_strlen($comment) > 2000) {
                $comment = mb_substr($comment, 0, 2000);
            }
        }
        $row = $this->get($idCandidature);
        if (!$row) {
            $this->lastError = 'Candidature introuvable.';
            return false;
        }
        if ((string) $row['statut'] !== self::STATUS_ACCEPTED) {
            $this->lastError = 'Seules les candidatures acceptées peuvent être évaluées.';
            return false;
        }

        $db = Config::getConnexion();
        try {
            $st = $db->prepare(
                'UPDATE project_candidature
                 SET evaluation_note = :n, evaluation_comment = :c, evaluated_at = CURRENT_TIMESTAMP,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id_candidature = :id'
            );
            $ok = $st->execute([
                'n'  => $note,
                'c'  => $comment === '' ? null : $comment,
                'id' => $idCandidature,
            ]);
            if ($ok) {
                $this->notifyCandidateOfEvaluation((array) $row, $note, $comment);
            }
            return $ok;
        } catch (Exception $e) {
            $this->lastError = 'Erreur lors de l\'enregistrement de l\'évaluation.';
            return false;
        }
    }

    // ------------------------------------------------------------------
    // Notifications internes (envoyées via NotificationP).
    // ------------------------------------------------------------------

    private function notifyOwnerOfNewCandidature(int $idProject, int $candidateUserId, string $nom, string $prenom): void
    {
        try {
            $ownerId = $this->getOwnerId($idProject);
            if ($ownerId <= 0 || $ownerId === $candidateUserId) return;
            $projectTitle = $this->getProjectTitle($idProject);
            $fullName = trim($prenom . ' ' . $nom);
            if ($fullName === '') $fullName = 'Un candidat';
            $notif = new NotificationP();
            $notif->create(
                $ownerId,
                'candidature_received',
                'Nouvelle candidature reçue',
                $fullName . ' a postulé à votre projet « ' . $projectTitle . ' ».',
                'view/FrontOffice/project_candidatures.php?id=' . $idProject
            );
        } catch (Exception $e) {
            // best-effort
        }
    }

    private function notifyCandidateOfStatusChange(array $row, string $newStatus): void
    {
        try {
            $idUser = (int) ($row['id_user'] ?? 0);
            if ($idUser <= 0) return;
            $projectTitle = $this->getProjectTitle((int) $row['id_project']);
            $link = 'view/FrontOffice/project.php?id=' . (int) $row['id_project'];

            switch ($newStatus) {
                case self::STATUS_ACCEPTED:
                    $title = '🎉 Candidature acceptée !';
                    $body  = 'Votre candidature au projet « ' . $projectTitle . ' » a été acceptée.';
                    $type  = 'candidature_accepted';
                    break;
                case self::STATUS_REJECTED:
                    $title = 'Candidature non retenue';
                    $body  = 'Votre candidature au projet « ' . $projectTitle . ' » n\'a pas été retenue cette fois-ci.';
                    $type  = 'candidature_rejected';
                    break;
                case self::STATUS_PENDING:
                    $title = 'Candidature remise en attente';
                    $body  = 'Votre candidature au projet « ' . $projectTitle . ' » est de nouveau en attente d\'examen.';
                    $type  = 'candidature_pending';
                    break;
                default:
                    return;
            }
            (new NotificationP())->create($idUser, $type, $title, $body, $link);
        } catch (Exception $e) {
        }
    }

    private function notifyCandidateOfEvaluation(array $row, int $note, string $comment): void
    {
        try {
            $idUser = (int) ($row['id_user'] ?? 0);
            if ($idUser <= 0) return;
            $projectTitle = $this->getProjectTitle((int) $row['id_project']);
            $stars = str_repeat('★', $note) . str_repeat('☆', 5 - $note);
            $body = 'Vous avez été évalué(e) sur le projet « ' . $projectTitle . ' » : ' . $stars . ' (' . $note . '/5).';
            if ($comment !== '') {
                $body .= "\n« " . mb_substr($comment, 0, 240) . (mb_strlen($comment) > 240 ? '…' : '') . ' »';
            }
            (new NotificationP())->create(
                $idUser,
                'candidature_evaluated',
                'Vous avez reçu une évaluation',
                $body,
                'view/FrontOffice/project.php?id=' . (int) $row['id_project']
            );
        } catch (Exception $e) {
        }
    }

    private function getOwnerId(int $idProject): int
    {
        try {
            $db = Config::getConnexion();
            $st = $db->prepare('SELECT owner_id FROM project WHERE idproject = :id LIMIT 1');
            $st->execute(['id' => $idProject]);
            $v = $st->fetchColumn();
            return $v !== false && $v !== null ? (int) $v : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getProjectTitle(int $idProject): string
    {
        try {
            $db = Config::getConnexion();
            $st = $db->prepare('SELECT title FROM project WHERE idproject = :id LIMIT 1');
            $st->execute(['id' => $idProject]);
            $v = $st->fetchColumn();
            return (string) ($v ?: 'projet #' . $idProject);
        } catch (Exception $e) {
            return 'projet #' . $idProject;
        }
    }
}
