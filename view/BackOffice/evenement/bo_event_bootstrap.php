<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../../controller/AuthController.php';
$__u = (new AuthController())->profile();
if (!$__u || strtolower($__u['type'] ?? '') !== 'admin') {
    header('Location: ../../login.php');
    exit;
}
