<?php
require_once __DIR__ . '/../../../config/database.php';

$pdo = getPDO();
$currentUserId = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commentId = intval($_POST['comment_id'] ?? 0);
    $type = trim($_POST['type'] ?? 'like');
    if ($commentId <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid comment']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT id FROM reactions WHERE user_id = ? AND comment_id = ? AND type = ?');
    $stmt->execute([$currentUserId, $commentId, $type]);
    $existing = $stmt->fetch();
    if ($existing) {
        $del = $pdo->prepare('DELETE FROM reactions WHERE id = ?');
        $del->execute([$existing['id']]);
        $action = 'removed';
    } else {
        $ins = $pdo->prepare('INSERT INTO reactions (user_id, comment_id, type, createdAt) VALUES (?, ?, ?, NOW())');
        $ins->execute([$currentUserId, $commentId, $type]);
        $action = 'added';
    }

    $c = $pdo->prepare('SELECT COUNT(*) AS cnt FROM reactions WHERE comment_id = ?');
    $c->execute([$commentId]);
    $count = $c->fetchColumn();

    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'action' => $action, 'count' => (int)$count]);
    exit;
}

header('HTTP/1.1 405 Method Not Allowed');
exit;
