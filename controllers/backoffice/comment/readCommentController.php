<?php
require_once __DIR__ . '/../../../config/database.php';

$pdo = getPDO();

$stmt = $pdo->query('SELECT c.*, u.name AS author FROM comments c JOIN users u ON u.id = c.user_id ORDER BY c.createdAt DESC');
$comments = $stmt->fetchAll();

include __DIR__ . '/../../../views/layouts/back_header.php';
include __DIR__ . '/../../../views/backoffice/comment/read.php';
include __DIR__ . '/../../../views/layouts/back_footer.php';
