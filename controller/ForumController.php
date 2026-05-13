<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/ForumImageHelper.php';
require_once __DIR__ . '/../lib/ProfanityFilter.php';

/**
 * Back-office : catégories, sujets et messages du forum.
 */
class ForumController
{
    private string $lastPublicError = '';

    public function getLastPublicError(): string
    {
        return $this->lastPublicError;
    }
    /**
     * Liste les catégories. Sans argument : tri par défaut (ordre asc, id asc),
     * utilisé partout pour la rétro-compatibilité. Avec $sort/$order on autorise
     * un tri dynamique côté back-office (colonnes cliquables du tableau).
     *
     * @return list<array<string, mixed>>
     */
    public function listCategories(?string $sort = null, string $order = 'asc'): array
    {
        $db = Config::getConnexion();
        if ($sort === null) {
            $st = $db->query('SELECT * FROM `forum_categorie` ORDER BY `ordre` ASC, `id_categorie` ASC');
            return $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
        }
        $allowed = [
            'id_categorie' => '`id_categorie`',
            'titre'        => '`titre`',
            'description'  => '`description`',
            'ordre'        => '`ordre`',
        ];
        $col   = $allowed[$sort] ?? '`ordre`';
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $st = $db->query("SELECT * FROM `forum_categorie` ORDER BY {$col} {$order}, `id_categorie` ASC");
        return $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /** @return array<string, mixed>|false */
    public function getCategory(int $id)
    {
        if ($id < 1) {
            return false;
        }
        $db = Config::getConnexion();
        $st = $db->prepare('SELECT * FROM `forum_categorie` WHERE `id_categorie` = :id LIMIT 1');
        $st->execute(['id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: false;
    }

    public function addCategory(string $titre, string $description, int $ordre): bool
    {
        $this->lastPublicError = '';
        $titre = trim($titre);
        if ($titre === '') {
            $this->lastPublicError = 'Titre requis.';
            return false;
        }
        $err = ProfanityFilter::checkAll(['Titre' => $titre, 'Description' => $description]);
        if ($err !== null) {
            $this->lastPublicError = $err;
            return false;
        }
        $db = Config::getConnexion();
        $st = $db->prepare('INSERT INTO `forum_categorie` (`titre`, `description`, `ordre`) VALUES (:t, :d, :o)');
        return $st->execute(['t' => $titre, 'd' => $description, 'o' => $ordre]);
    }

    public function updateCategory(int $id, string $titre, string $description, int $ordre): bool
    {
        $this->lastPublicError = '';
        if ($id < 1) {
            return false;
        }
        $titre = trim($titre);
        if ($titre === '') {
            $this->lastPublicError = 'Titre requis.';
            return false;
        }
        $err = ProfanityFilter::checkAll(['Titre' => $titre, 'Description' => $description]);
        if ($err !== null) {
            $this->lastPublicError = $err;
            return false;
        }
        $db = Config::getConnexion();
        $st = $db->prepare('UPDATE `forum_categorie` SET `titre` = :t, `description` = :d, `ordre` = :o WHERE `id_categorie` = :id');
        return $st->execute(['id' => $id, 't' => $titre, 'd' => $description, 'o' => $ordre]);
    }

    public function deleteCategory(int $id): bool
    {
        if ($id < 1) {
            return false;
        }
        $db = Config::getConnexion();
        $st = $db->prepare('DELETE FROM `forum_categorie` WHERE `id_categorie` = :id');
        return $st->execute(['id' => $id]);
    }

    /** @return list<array<string, mixed>> */
    public function listSujets(?int $idCategorie, string $sort = 'created_at', string $order = 'desc'): array
    {
        $allowed = ['id_sujet', 'titre', 'created_at', 'epingle', 'verrouille', 'cat_titre'];
        if (!in_array($sort, $allowed, true)) {
            $sort = 'created_at';
        }
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $map = [
            'id_sujet' => 's.`id_sujet`',
            'titre' => 's.`titre`',
            'created_at' => 's.`created_at`',
            'epingle' => 's.`epingle`',
            'verrouille' => 's.`verrouille`',
            'cat_titre' => 'c.`titre`',
        ];
        $col = $map[$sort] ?? 's.`created_at`';
        $db = Config::getConnexion();
        $sql = "SELECT s.*, c.`titre` AS cat_titre, u.`prenom`, u.`nom`, u.`email`
            FROM `forum_sujet` s
            INNER JOIN `forum_categorie` c ON s.`id_categorie` = c.`id_categorie`
            INNER JOIN `user` u ON s.`id_user` = u.`iduser`";
        if ($idCategorie !== null && $idCategorie > 0) {
            $sql .= ' WHERE s.`id_categorie` = :cid';
        }
        $sql .= " ORDER BY s.`epingle` DESC, {$col} {$order}";
        $st = $db->prepare($sql);
        if ($idCategorie !== null && $idCategorie > 0) {
            $st->execute(['cid' => $idCategorie]);
        } else {
            $st->execute();
        }
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : [];
    }

    /** @return array<string, mixed>|false */
    public function getSujet(int $id)
    {
        if ($id < 1) {
            return false;
        }
        $db = Config::getConnexion();
        $st = $db->prepare(
            "SELECT s.*, c.`titre` AS cat_titre
            FROM `forum_sujet` s
            INNER JOIN `forum_categorie` c ON s.`id_categorie` = c.`id_categorie`
            WHERE s.`id_sujet` = :id LIMIT 1"
        );
        $st->execute(['id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: false;
    }

    /** @return int|false */
    public function createSujetWithFirstMessage(int $idCategorie, int $idUser, string $titre, string $contenu)
    {
        $this->lastPublicError = '';
        $img = ForumImageHelper::processUpload('photo');
        if (!$img['ok']) {
            $this->lastPublicError = (string) ($img['error'] ?? '');
            return false;
        }
        $titre = trim($titre);
        $contenu = trim($contenu);
        $path = $img['path'];
        if ($titre === '' || $idCategorie < 1 || $idUser < 1) {
            if ($path !== null) {
                ForumImageHelper::removeFileIfSafe($path);
            }
            $this->lastPublicError = 'Titre ou données invalides.';
            return false;
        }
        if (strlen($contenu) < 2 && $path === null) {
            $this->lastPublicError = 'Écrivez un message d’au moins 2 caractères ou ajoutez une photo.';
            return false;
        }
        $profanity = ProfanityFilter::checkAll(['Titre' => $titre, 'Message' => $contenu]);
        if ($profanity !== null) {
            if ($path !== null) {
                ForumImageHelper::removeFileIfSafe($path);
            }
            $this->lastPublicError = $profanity;
            return false;
        }
        $db = Config::getConnexion();
        try {
            $db->beginTransaction();
            $st = $db->prepare(
                'INSERT INTO `forum_sujet` (`id_categorie`, `id_user`, `titre`) VALUES (:c, :u, :t)'
            );
            $st->execute(['c' => $idCategorie, 'u' => $idUser, 't' => $titre]);
            $idSujet = (int) $db->lastInsertId();
            if ($idSujet < 1) {
                $db->rollBack();
                if ($path !== null) {
                    ForumImageHelper::removeFileIfSafe($path);
                }
                return false;
            }
            $m = $db->prepare(
                'INSERT INTO `forum_message` (`id_sujet`, `id_user`, `contenu`, `image_fichier`) VALUES (:s, :u, :x, :img)'
            );
            $m->execute(['s' => $idSujet, 'u' => $idUser, 'x' => $contenu, 'img' => $path]);
            $db->commit();
            return $idSujet;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            if ($path !== null) {
                ForumImageHelper::removeFileIfSafe($path);
            }
            return false;
        }
    }

    public function deleteSujet(int $id): bool
    {
        if ($id < 1) {
            return false;
        }
        $db = Config::getConnexion();
        $st = $db->prepare('DELETE FROM `forum_sujet` WHERE `id_sujet` = :id');
        return $st->execute(['id' => $id]);
    }

    public function toggleEpingle(int $id): bool
    {
        if ($id < 1) {
            return false;
        }
        $db = Config::getConnexion();
        $st = $db->prepare('UPDATE `forum_sujet` SET `epingle` = 1 - `epingle` WHERE `id_sujet` = :id');
        return $st->execute(['id' => $id]);
    }

    public function toggleVerrou(int $id): bool
    {
        if ($id < 1) {
            return false;
        }
        $db = Config::getConnexion();
        $st = $db->prepare('UPDATE `forum_sujet` SET `verrouille` = 1 - `verrouille` WHERE `id_sujet` = :id');
        return $st->execute(['id' => $id]);
    }

    /** @return list<array<string, mixed>> */
    public function listMessagesBySujet(int $idSujet): array
    {
        if ($idSujet < 1) {
            return [];
        }
        $db = Config::getConnexion();
        $st = $db->prepare(
            "SELECT m.*, u.`prenom`, u.`nom`, u.`email`
            FROM `forum_message` m
            INNER JOIN `user` u ON m.`id_user` = u.`iduser`
            WHERE m.`id_sujet` = :s
            ORDER BY m.`created_at` ASC, m.`id_message` ASC"
        );
        $st->execute(['s' => $idSujet]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : [];
    }

    public function deleteMessage(int $id): bool
    {
        if ($id < 1) {
            return false;
        }
        $db = Config::getConnexion();
        $c = $db->prepare('SELECT `id_sujet`, `image_fichier` FROM `forum_message` WHERE `id_message` = :id LIMIT 1');
        $c->execute(['id' => $id]);
        $row = $c->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }
        $idSujet = (int) $row['id_sujet'];
        $imgFile = $row['image_fichier'] ?? null;
        $cnt = $db->prepare('SELECT COUNT(*) AS c FROM `forum_message` WHERE `id_sujet` = :s');
        $cnt->execute(['s' => $idSujet]);
        $n = (int) ($cnt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
        if ($n <= 1) {
            return false;
        }
        $d = $db->prepare('DELETE FROM `forum_message` WHERE `id_message` = :id');
        if (!$d->execute(['id' => $id])) {
            return false;
        }
        if (!empty($imgFile)) {
            ForumImageHelper::removeFileIfSafe((string) $imgFile);
        }
        return true;
    }

    public function countCategories(): int
    {
        $db = Config::getConnexion();
        $n = (int) $db->query('SELECT COUNT(*) FROM `forum_categorie`')->fetchColumn();
        return $n;
    }

    public function countSujets(): int
    {
        $db = Config::getConnexion();
        $n = (int) $db->query('SELECT COUNT(*) FROM `forum_sujet`')->fetchColumn();
        return $n;
    }

    public function countMessages(): int
    {
        $db = Config::getConnexion();
        $n = (int) $db->query('SELECT COUNT(*) FROM `forum_message`')->fetchColumn();
        return $n;
    }

    /**
     * Front office : catégories avec nombre de sujets.
     * @return list<array<string, mixed>>
     */
    public function listCategoriesWithStats(): array
    {
        $db = Config::getConnexion();
        $sql = 'SELECT c.*, (SELECT COUNT(*) FROM `forum_sujet` s WHERE s.`id_categorie` = c.`id_categorie`) AS nb_sujets
            FROM `forum_categorie` c
            ORDER BY c.`ordre` ASC, c.`id_categorie` ASC';
        $st = $db->query($sql);
        $rows = $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
        return is_array($rows) ? $rows : [];
    }

    /**
     * Front office : réponse sur un sujet (refus si verrouillé).
     * @return true|string Erreur ou true
     */
    public function addMessagePublic(int $idSujet, int $idUser, string $contenu)
    {
        $img = ForumImageHelper::processUpload('photo');
        if (!$img['ok']) {
            return (string) ($img['error'] ?? 'Image invalide.');
        }
        $contenu = trim($contenu);
        $path = $img['path'];
        if ($idSujet < 1 || $idUser < 1) {
            if ($path !== null) {
                ForumImageHelper::removeFileIfSafe($path);
            }
            return 'Message invalide.';
        }
        if (strlen($contenu) < 2 && $path === null) {
            return 'Message trop court, ou ajoutez une photo.';
        }
        $profanity = ProfanityFilter::firstMatch($contenu);
        if ($profanity !== null) {
            if ($path !== null) {
                ForumImageHelper::removeFileIfSafe($path);
            }
            return 'Votre message contient un terme interdit (« ' . $profanity . ' »). Reformulez s\'il vous plaît.';
        }
        $s = $this->getSujet($idSujet);
        if (!$s) {
            if ($path !== null) {
                ForumImageHelper::removeFileIfSafe($path);
            }
            return 'Sujet introuvable.';
        }
        if ((int) ($s['verrouille'] ?? 0) === 1) {
            if ($path !== null) {
                ForumImageHelper::removeFileIfSafe($path);
            }
            return 'Ce sujet est verrouillé.';
        }
        $db = Config::getConnexion();
        $st = $db->prepare(
            'INSERT INTO `forum_message` (`id_sujet`, `id_user`, `contenu`, `image_fichier`) VALUES (:s, :u, :x, :img)'
        );
        try {
            if ($st->execute(['s' => $idSujet, 'u' => $idUser, 'x' => $contenu, 'img' => $path])) {
                $u = $db->prepare('UPDATE `forum_sujet` SET `updated_at` = CURRENT_TIMESTAMP WHERE `id_sujet` = :id');
                $u->execute(['id' => $idSujet]);
                return true;
            }
        } catch (Exception $e) {
            if ($path !== null) {
                ForumImageHelper::removeFileIfSafe($path);
            }
            return 'Erreur lors de l’envoi.';
        }
        if ($path !== null) {
            ForumImageHelper::removeFileIfSafe($path);
        }
        return 'Erreur lors de l’envoi.';
    }
}
