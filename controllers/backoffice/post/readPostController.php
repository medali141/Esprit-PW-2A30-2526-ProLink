<?php
require_once __DIR__ . '/../../../config/database.php';

$pdo = getPDO();

$stmt = $pdo->query('SELECT p.*, u.name AS author FROM posts p JOIN users u ON u.id = p.user_id ORDER BY p.createdAt DESC');
$posts = $stmt->fetchAll();

include __DIR__ . '/../../../views/layouts/back_header.php';
include __DIR__ . '/../../../views/backoffice/post/read.php';
include __DIR__ . '/../../../views/layouts/back_footer.php';
