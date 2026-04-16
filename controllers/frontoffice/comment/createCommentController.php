<?php
require_once __DIR__ . '/../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getPDO();
    $currentUserId = 1;
    $postId = intval($_POST['post_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');

    if ($postId <= 0 || $content === '') {
        header('Location: index.php?page=forum');
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO comments (post_id, user_id, content, createdAt) VALUES (?, ?, ?, NOW())');
    $stmt->execute([$postId, $currentUserId, $content]);

    header('Location: index.php?page=forum');
    exit;
}
