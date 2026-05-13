<?php
require_once __DIR__ . '/../config.php';

class AdminMessengerController {
    private bool $ready = false;

    public function ensureTable(): void {
        if ($this->ready) return;
        $db = Config::getConnexion();
        $db->exec(
            "CREATE TABLE IF NOT EXISTS admin_message (
                idmessage INT AUTO_INCREMENT PRIMARY KEY,
                id_sender INT NOT NULL,
                id_receiver INT NOT NULL,
                message TEXT NOT NULL,
                is_read TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_sender_receiver (id_sender, id_receiver, idmessage),
                INDEX idx_receiver_read (id_receiver, is_read, idmessage),
                CONSTRAINT fk_admin_message_sender FOREIGN KEY (id_sender) REFERENCES user(iduser) ON DELETE CASCADE,
                CONSTRAINT fk_admin_message_receiver FOREIGN KEY (id_receiver) REFERENCES user(iduser) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $this->ready = true;
    }

    public function getPrimaryAdminId(): int {
        $this->ensureTable();
        $db = Config::getConnexion();
        $st = $db->query("SELECT iduser FROM user WHERE type = 'admin' ORDER BY iduser ASC LIMIT 1");
        $id = (int) ($st->fetchColumn() ?: 0);
        return $id > 0 ? $id : 0;
    }

    /** @return list<int> */
    public function getAdminIds(): array {
        $this->ensureTable();
        $db = Config::getConnexion();
        $rows = $db->query("SELECT iduser FROM user WHERE type = 'admin' ORDER BY iduser ASC")->fetchAll(PDO::FETCH_ASSOC);
        $ids = [];
        foreach ($rows as $r) {
            $id = (int) ($r['iduser'] ?? 0);
            if ($id > 0) $ids[] = $id;
        }
        return $ids;
    }

    public function sendMessage(int $senderId, int $receiverId, string $message): void {
        $this->ensureTable();
        $content = trim($message);
        if ($senderId <= 0 || $receiverId <= 0 || $content === '') {
            return;
        }
        if (mb_strlen($content) > 2000) {
            $content = mb_substr($content, 0, 2000);
        }
        $db = Config::getConnexion();
        $st = $db->prepare(
            "INSERT INTO admin_message (id_sender, id_receiver, message, is_read)
             VALUES (:s, :r, :m, 0)"
        );
        $st->execute([
            's' => $senderId,
            'r' => $receiverId,
            'm' => $content,
        ]);
    }

    public function sendMessageToAllAdmins(int $senderId, string $message): int {
        $this->ensureTable();
        $content = trim($message);
        if ($senderId <= 0 || $content === '') return 0;
        if (mb_strlen($content) > 2000) {
            $content = mb_substr($content, 0, 2000);
        }
        $adminIds = $this->getAdminIds();
        if (empty($adminIds)) return 0;
        $db = Config::getConnexion();
        $st = $db->prepare(
            "INSERT INTO admin_message (id_sender, id_receiver, message, is_read)
             VALUES (:s, :r, :m, 0)"
        );
        $count = 0;
        foreach ($adminIds as $aid) {
            if ($aid === $senderId) continue;
            $st->execute(['s' => $senderId, 'r' => $aid, 'm' => $content]);
            $count++;
        }
        return $count;
    }

    /** @return list<array<string,mixed>> */
    public function listConversation(int $a, int $b, int $limit = 120): array {
        $this->ensureTable();
        if ($a <= 0 || $b <= 0) return [];
        $limit = max(20, min(300, $limit));
        $db = Config::getConnexion();
        $sql = "SELECT m.idmessage, m.id_sender, m.id_receiver, m.message, m.is_read, m.created_at,
                       su.nom AS sender_nom, su.prenom AS sender_prenom
                FROM admin_message m
                INNER JOIN user su ON su.iduser = m.id_sender
                WHERE (m.id_sender = :a AND m.id_receiver = :b)
                   OR (m.id_sender = :b AND m.id_receiver = :a)
                ORDER BY m.idmessage DESC
                LIMIT $limit";
        $st = $db->prepare($sql);
        $st->execute(['a' => $a, 'b' => $b]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        return array_reverse($rows);
    }

    public function markAsRead(int $readerId, int $otherId): void {
        $this->ensureTable();
        if ($readerId <= 0 || $otherId <= 0) return;
        $db = Config::getConnexion();
        $st = $db->prepare(
            "UPDATE admin_message
             SET is_read = 1
             WHERE id_receiver = :reader AND id_sender = :other AND is_read = 0"
        );
        $st->execute(['reader' => $readerId, 'other' => $otherId]);
    }

    /** @return list<array<string,mixed>> */
    public function listUserConversationWithAdmins(int $userId, int $limit = 160): array {
        $this->ensureTable();
        if ($userId <= 0) return [];
        $limit = max(20, min(400, $limit));
        $db = Config::getConnexion();
        $sql = "SELECT m.idmessage, m.id_sender, m.id_receiver, m.message, m.is_read, m.created_at,
                       su.nom AS sender_nom, su.prenom AS sender_prenom, su.type AS sender_type
                FROM admin_message m
                INNER JOIN user su ON su.iduser = m.id_sender
                INNER JOIN user ru ON ru.iduser = m.id_receiver
                WHERE (m.id_sender = :uid AND ru.type = 'admin')
                   OR (m.id_receiver = :uid AND su.type = 'admin')
                ORDER BY m.idmessage DESC
                LIMIT $limit";
        $st = $db->prepare($sql);
        $st->execute(['uid' => $userId]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        return array_reverse($rows);
    }

    public function markUserThreadAsRead(int $userId): void {
        $this->ensureTable();
        if ($userId <= 0) return;
        $db = Config::getConnexion();
        $st = $db->prepare(
            "UPDATE admin_message m
             INNER JOIN user su ON su.iduser = m.id_sender
             SET m.is_read = 1
             WHERE m.id_receiver = :uid AND su.type = 'admin' AND m.is_read = 0"
        );
        $st->execute(['uid' => $userId]);
    }

    /** @return list<array<string,mixed>> */
    public function listAdminInboxUsers(int $adminId): array {
        $this->ensureTable();
        if ($adminId <= 0) return [];
        $db = Config::getConnexion();
        $sql = "SELECT
                    u.iduser,
                    u.nom,
                    u.prenom,
                    u.email,
                    x.last_at,
                    x.last_message,
                    SUM(CASE WHEN m.id_receiver = :admin AND m.is_read = 0 THEN 1 ELSE 0 END) AS unread_count
                FROM (
                    SELECT
                        CASE WHEN id_sender = :admin THEN id_receiver ELSE id_sender END AS uid,
                        MAX(created_at) AS last_at,
                        SUBSTRING_INDEX(
                            GROUP_CONCAT(message ORDER BY idmessage DESC SEPARATOR '\n'),
                            '\n',
                            1
                        ) AS last_message
                    FROM admin_message
                    WHERE id_sender = :admin OR id_receiver = :admin
                    GROUP BY uid
                ) x
                INNER JOIN user u ON u.iduser = x.uid
                LEFT JOIN admin_message m
                    ON ((m.id_sender = x.uid AND m.id_receiver = :admin) OR (m.id_sender = :admin AND m.id_receiver = x.uid))
                GROUP BY u.iduser, u.nom, u.prenom, u.email, x.last_at, x.last_message
                ORDER BY x.last_at DESC";
        $st = $db->prepare($sql);
        $st->execute(['admin' => $adminId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return list<array<string,mixed>> */
    public function listUsersWhoSentMessages(int $limit = 200): array {
        $this->ensureTable();
        $limit = max(20, min(500, $limit));
        $db = Config::getConnexion();
        $sql = "SELECT
                    u.iduser,
                    u.nom,
                    u.prenom,
                    u.email,
                    MAX(m.created_at) AS last_at,
                    SUBSTRING_INDEX(
                        GROUP_CONCAT(m.message ORDER BY m.idmessage DESC SEPARATOR '\n'),
                        '\n',
                        1
                    ) AS last_message,
                    COUNT(*) AS sent_count
                FROM admin_message m
                INNER JOIN user u ON u.iduser = m.id_sender
                INNER JOIN user ru ON ru.iduser = m.id_receiver
                WHERE u.type <> 'admin'
                  AND ru.type = 'admin'
                GROUP BY u.iduser, u.nom, u.prenom, u.email
                ORDER BY last_at DESC
                LIMIT $limit";
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserChatProfile(int $userId): ?array {
        $this->ensureTable();
        if ($userId <= 0) return null;
        $db = Config::getConnexion();
        $sql = "SELECT
                    u.iduser,
                    u.nom,
                    u.prenom,
                    u.email,
                    (
                        SELECT c.telephone_livraison
                        FROM commande c
                        WHERE c.id_acheteur = u.iduser
                          AND c.telephone_livraison IS NOT NULL
                          AND c.telephone_livraison <> ''
                        ORDER BY c.idcommande DESC
                        LIMIT 1
                    ) AS telephone
                FROM user u
                WHERE u.iduser = :uid
                LIMIT 1";
        $st = $db->prepare($sql);
        $st->execute(['uid' => $userId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}

