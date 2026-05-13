<?php
// logout.php - simple logout and redirect to login
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
session_unset();
session_destroy();

// Redirect to the project's login page. Compute project folder to avoid duplicate segments.
$projectFolder = basename(dirname(__DIR__));
$root = $projectFolder ? '/' . $projectFolder : '';
$loginPath = $root . '/view/login.php';
header('Location: ' . $loginPath);
exit;
?>