<?php
require_once __DIR__ . '/../../../config/database.php';

$pdo = getPDO();
$currentUserId = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = intval($_POST['post_id'] ?? 0);
    if ($postId <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid post']);
        exit;
    }

    // prevent duplicate repost by same user on same post
    $stmt = $pdo->prepare('SELECT id FROM reposts WHERE user_id = ? AND post_id = ?');
    $stmt->execute([$currentUserId, $postId]);
    $exists = $stmt->fetch();
    if ($exists) {
        // remove repost (toggle)
        $del = $pdo->prepare('DELETE FROM reposts WHERE id = ?');
        $del->execute([$exists['id']]);
        $action = 'removed';
    } else {
        $ins = $pdo->prepare('INSERT INTO reposts (user_id, post_id, createdAt) VALUES (?, ?, NOW())');
        $ins->execute([$currentUserId, $postId]);
        $action = 'added';
    }

    $c = $pdo->prepare('SELECT COUNT(*) AS cnt FROM reposts WHERE post_id = ?');
    $c->execute([$postId]);
    $count = $c->fetchColumn();

    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'action' => $action, 'count' => (int)$count]);
    exit;
}

header('HTTP/1.1 405 Method Not Allowed');
exit;
