<?php
require_once __DIR__ . '/../../../config/database.php';

$pdo = getPDO();

// fetch posts with author and counts
$stmt = $pdo->query(
    "SELECT p.*, u.name AS author,
        (SELECT COUNT(*) FROM reactions r WHERE r.post_id = p.id) AS reactions_count,
        (SELECT COUNT(*) FROM reposts rp WHERE rp.post_id = p.id) AS reposts_count
    FROM posts p
    JOIN users u ON u.id = p.user_id
    ORDER BY p.createdAt DESC"
);
$posts = $stmt->fetchAll();

// fetch comments
$cStmt = $pdo->query(
    "SELECT c.*, u.name AS author,
        (SELECT COUNT(*) FROM reactions r WHERE r.comment_id = c.id) AS reactions_count,
        (SELECT COUNT(*) FROM reposts rp WHERE rp.comment_id = c.id) AS reposts_count
    FROM comments c
    JOIN users u ON u.id = c.user_id
    ORDER BY c.createdAt ASC"
);
$comments = $cStmt->fetchAll();

$commentsByPost = [];
foreach ($comments as $c) {
    $commentsByPost[$c['post_id']][] = $c;
}

// include fragment only
include __DIR__ . '/../../../views/frontoffice/_posts_fragment.php';

// no footer/header for fragment
exit;
