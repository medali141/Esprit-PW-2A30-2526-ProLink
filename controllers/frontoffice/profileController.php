<?php
require_once __DIR__ . '/../../config/database.php';

$pdo = getPDO();
$currentUserId = 1;

// user's own posts
$pStmt = $pdo->prepare('SELECT p.*, (SELECT COUNT(*) FROM reactions r WHERE r.post_id = p.id) AS reactions_count FROM posts p WHERE p.user_id = ? ORDER BY p.createdAt DESC');
$pStmt->execute([$currentUserId]);
$myPosts = $pStmt->fetchAll();

// user's own comments
$cStmt = $pdo->prepare('SELECT c.*, p.title AS post_title FROM comments c JOIN posts p ON p.id = c.post_id WHERE c.user_id = ? ORDER BY c.createdAt DESC');
$cStmt->execute([$currentUserId]);
$myComments = $cStmt->fetchAll();

// user's reposted posts
$rpStmt = $pdo->prepare('SELECT rp.*, p.* FROM reposts rp JOIN posts p ON p.id = rp.post_id WHERE rp.user_id = ? ORDER BY rp.createdAt DESC');
$rpStmt->execute([$currentUserId]);
$repostedPosts = $rpStmt->fetchAll();

// user's reposted comments
$rcStmt = $pdo->prepare('SELECT rp.*, c.*, p.title AS post_title FROM reposts rp JOIN comments c ON c.id = rp.comment_id JOIN posts p ON p.id = c.post_id WHERE rp.user_id = ? ORDER BY rp.createdAt DESC');
$rcStmt->execute([$currentUserId]);
$repostedComments = $rcStmt->fetchAll();

include __DIR__ . '/../../views/layouts/header.php';
include __DIR__ . '/../../views/frontoffice/profile.php';
include __DIR__ . '/../../views/layouts/footer.php';
