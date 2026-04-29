<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../../controller/AuthController.php';
$__forumUser = (new AuthController())->profile();
if (!$__forumUser || strtolower($__forumUser['type'] ?? '') !== 'admin') {
    header('Location: ../../login.php');
    exit;
}
