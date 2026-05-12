<?php
require_once __DIR__ . '/../../../config/database.php';

$pdo = getPDO();
$currentUserId = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $content = trim($_POST['content'] ?? '');
    if ($id <= 0 || $content === '') {
        header('Location: index.php?page=forum');
        exit;
    }
    $stmt = $pdo->prepare('UPDATE comments SET content = ? WHERE id = ?');
    $stmt->execute([$content, $id]);
    header('Location: index.php?page=forum');
    exit;
}

// GET -> show form
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php?page=forum');
    exit;
}
$stmt = $pdo->prepare('SELECT * FROM comments WHERE id = ?');
$stmt->execute([$id]);
$comment = $stmt->fetch();

include __DIR__ . '/../../../views/layouts/header.php';
include __DIR__ . '/../../../views/frontoffice/comment/update.php';
include __DIR__ . '/../../../views/layouts/footer.php';
