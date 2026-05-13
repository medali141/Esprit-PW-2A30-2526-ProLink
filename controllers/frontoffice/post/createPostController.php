<?php
require_once __DIR__ . '/../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getPDO();
    // no user integration yet — demo user id 1
    $currentUserId = 1;
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($title === '' || $content === '') {
        header('Location: index.php?page=forum');
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content, createdAt) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$currentUserId, $title, $content]);

    header('Location: index.php?page=forum');
    exit;
}
