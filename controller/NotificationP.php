<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';

/**
 * Notifications utilisateur.
 *
 * Types courants utilisés dans le code applicatif :
 *   - candidature_received     -> envoyée au porteur quand un candidat postule
 *   - candidature_accepted     -> envoyée au candidat
 *   - candidature_rejected     -> envoyée au candidat
 *   - candidature_evaluated    -> envoyée au candidat après évaluation
 *   - projet_publie            -> envoyée à tous les utilisateurs (nouveau projet)
 *   - formation_ajoutee        -> envoyée à tous les utilisateurs (nouvelle formation)
 *
 * Les notifications sont affichées via la cloche dans la navbar front-office
 * et la page notifications.php.
 */
class NotificationP
{
    public function create(int $idUser, string $type, string $title, ?string $body = null, ?string $link = null): bool
    {
        if ($idUser < 1) return false;
        $type  = trim($type);
        $title = trim($title);
        if ($type === '' || $title === '') return false;
        $db = Config::getConnexion();
        try {
            $st = $db->prepare(
                'INSERT INTO user_notification (id_user, type, title, body, link)
                 VALUES (:u, :t, :ti, :b, :l)'
            );
            return $st->execute([
                'u'  => $idUser,
                't'  => mb_substr($type, 0, 40),
                'ti' => mb_substr($title, 0, 180),
                'b'  => $body !== null ? mb_substr($body, 0, 2000) : null,
                'l'  => $link !== null ? mb_substr($link, 0, 255) : null,
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Crée une notification identique pour chaque ligne de la table « user »
     * (tous les rôles : admin, candidat, entrepreneur, etc.).
     *
     * @param int|null $excludeUserId optionnel — exclut un utilisateur (ex. éviter doublon rare)
     * @return int nombre de lignes insérées
     */
    public function broadcastToAllUsers(string $type, string $title, ?string $body = null, ?string $link = null, ?int $excludeUserId = null): int
    {
        $type  = trim($type);
        $title = trim($title);
        if ($type === '' || $title === '') {
            return 0;
        }
        $type  = mb_substr($type, 0, 40);
        $title = mb_substr($title, 0, 180);
        $b     = $body !== null ? mb_substr($body, 0, 2000) : null;
        $l     = $link !== null ? mb_substr($link, 0, 255) : null;
        $db = Config::getConnexion();
        try {
            $sql = 'INSERT INTO user_notification (id_user, type, title, body, link)
                    SELECT iduser, :t, :ti, :b, :l FROM user WHERE iduser > 0';
            $params = ['t' => $type, 'ti' => $title, 'b' => $b, 'l' => $l];
            if ($excludeUserId !== null && $excludeUserId > 0) {
                $sql .= ' AND iduser <> :ex';
                $params['ex'] = $excludeUserId;
            }
            $st = $db->prepare($sql);
            $st->execute($params);
            return (int) $st->rowCount();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listForUser(int $idUser, bool $unreadOnly = false, int $limit = 20): array
    {
        if ($idUser < 1) return [];
        $limit = max(1, min(100, $limit));
        $db = Config::getConnexion();
        try {
            $sql = 'SELECT * FROM user_notification WHERE id_user = :u';
            if ($unreadOnly) $sql .= ' AND is_read = 0';
            $sql .= ' ORDER BY created_at DESC, id_notification DESC LIMIT ' . $limit;
            $st = $db->prepare($sql);
            $st->execute(['u' => $idUser]);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            return is_array($rows) ? $rows : [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function countUnread(int $idUser): int
    {
        if ($idUser < 1) return 0;
        $db = Config::getConnexion();
        try {
            $st = $db->prepare('SELECT COUNT(*) FROM user_notification WHERE id_user = :u AND is_read = 0');
            $st->execute(['u' => $idUser]);
            return (int) $st->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    public function markRead(int $idNotification, int $idUser): bool
    {
        if ($idNotification < 1 || $idUser < 1) return false;
        $db = Config::getConnexion();
        try {
            $st = $db->prepare(
                'UPDATE user_notification
                 SET is_read = 1, read_at = CURRENT_TIMESTAMP
                 WHERE id_notification = :id AND id_user = :u AND is_read = 0'
            );
            return $st->execute(['id' => $idNotification, 'u' => $idUser]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function markAllRead(int $idUser): int
    {
        if ($idUser < 1) return 0;
        $db = Config::getConnexion();
        try {
            $st = $db->prepare(
                'UPDATE user_notification
                 SET is_read = 1, read_at = CURRENT_TIMESTAMP
                 WHERE id_user = :u AND is_read = 0'
            );
            $st->execute(['u' => $idUser]);
            return (int) $st->rowCount();
        } catch (Exception $e) {
            return 0;
        }
    }

    public function delete(int $idNotification, int $idUser): bool
    {
        if ($idNotification < 1 || $idUser < 1) return false;
        $db = Config::getConnexion();
        try {
            $st = $db->prepare(
                'DELETE FROM user_notification WHERE id_notification = :id AND id_user = :u'
            );
            return $st->execute(['id' => $idNotification, 'u' => $idUser]);
        } catch (Exception $e) {
            return false;
        }
    }
}
