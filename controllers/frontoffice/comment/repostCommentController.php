<?php
require_once __DIR__ . '/../../../config/database.php';

$pdo = getPDO();
$currentUserId = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commentId = intval($_POST['comment_id'] ?? 0);
    if ($commentId <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid comment']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT id FROM reposts WHERE user_id = ? AND comment_id = ?');
    $stmt->execute([$currentUserId, $commentId]);
    $exists = $stmt->fetch();
    if ($exists) {
        $del = $pdo->prepare('DELETE FROM reposts WHERE id = ?');
        $del->execute([$exists['id']]);
        $action = 'removed';
    } else {
        $ins = $pdo->prepare('INSERT INTO reposts (user_id, comment_id, createdAt) VALUES (?, ?, NOW())');
        $ins->execute([$currentUserId, $commentId]);
        $action = 'added';
    }

    $c = $pdo->prepare('SELECT COUNT(*) AS cnt FROM reposts WHERE comment_id = ?');
    $c->execute([$commentId]);
    $count = $c->fetchColumn();

    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'action' => $action, 'count' => (int)$count]);
    exit;
}

header('HTTP/1.1 405 Method Not Allowed');
exit;
