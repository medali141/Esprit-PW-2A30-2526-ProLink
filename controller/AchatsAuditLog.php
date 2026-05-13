<?php
/**
 * Journal des opérations acheteur (insertion en base, pas d’édition des entrées existantes).
 */
require_once __DIR__ . '/../config.php';

class AchatsAuditLog {
    private static ?bool $tableReady = null;

    private static function ensureTable(): void {
        if (self::$tableReady === true) {
            return;
        }
        $db = Config::getConnexion();
        $db->exec(
            "CREATE TABLE IF NOT EXISTS `achat_audit` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `action` varchar(80) NOT NULL,
              `entity` varchar(64) NOT NULL,
              `entity_id` int(11) DEFAULT NULL,
              `payload` longtext DEFAULT NULL COMMENT 'JSON',
              `id_user` int(11) NOT NULL,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              KEY `idx_audit_created` (`created_at`),
              KEY `idx_audit_entity` (`entity`,`entity_id`),
              CONSTRAINT `fk_audit_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`iduser`)
                ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        self::$tableReady = true;
    }

    /** @param array<string,mixed>|null $payload */
    public static function append(int $idUser, string $action, string $entity, ?int $entityId, ?array $payload): void {
        if ($idUser <= 0) {
            return;
        }
        self::ensureTable();
        $db = Config::getConnexion();
        $json = $payload !== null && $payload !== []
            ? json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
            : null;
        $st = $db->prepare(
            'INSERT INTO achat_audit (`action`, `entity`, entity_id, payload, id_user)
             VALUES (:a, :e, :ei, :p, :u)'
        );
        $st->execute([
            'a' => $action,
            'e' => $entity,
            'ei' => $entityId,
            'p' => $json,
            'u' => $idUser,
        ]);
    }

    /** @return list<array<string,mixed>> */
    public static function listRecent(int $limit = 120): array {
        self::ensureTable();
        if ($limit < 1) {
            $limit = 1;
        }
        if ($limit > 500) {
            $limit = 500;
        }
        $db = Config::getConnexion();
        $sql = 'SELECT l.*, u.prenom, u.nom, u.email
                FROM achat_audit l
                INNER JOIN user u ON u.iduser = l.id_user
                ORDER BY l.created_at DESC, l.id DESC
                LIMIT ' . (int) $limit;
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
