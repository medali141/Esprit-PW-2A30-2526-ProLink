<?php
require_once __DIR__ . '/../../../config/database.php';

$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $content = trim($_POST['content'] ?? '');
    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE comments SET content = ? WHERE id = ?');
        $stmt->execute([$content, $id]);
    }
    header('Location: index.php?page=backoffice');
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php?page=backoffice');
    exit;
}
$stmt = $pdo->prepare('SELECT * FROM comments WHERE id = ?');
$stmt->execute([$id]);
$comment = $stmt->fetch();

include __DIR__ . '/../../../views/layouts/back_header.php';
include __DIR__ . '/../../../views/backoffice/comment/update.php';
include __DIR__ . '/../../../views/layouts/back_footer.php';
