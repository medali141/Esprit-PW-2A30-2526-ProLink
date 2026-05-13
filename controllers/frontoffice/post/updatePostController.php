<?php
require_once __DIR__ . '/../../../config/database.php';

$pdo = getPDO();
$currentUserId = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if ($id <= 0 || $title === '' || $content === '') {
        header('Location: index.php?page=forum');
        exit;
    }
    $stmt = $pdo->prepare('UPDATE posts SET title = ?, content = ? WHERE id = ?');
    $stmt->execute([$title, $content, $id]);
    header('Location: index.php?page=forum');
    exit;
}

// if GET, show simple edit form
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php?page=forum');
    exit;
}
$stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
$stmt->execute([$id]);
$post = $stmt->fetch();

include __DIR__ . '/../../../views/layouts/header.php';
include __DIR__ . '/../../../views/frontoffice/post/update.php';
include __DIR__ . '/../../../views/layouts/footer.php';
