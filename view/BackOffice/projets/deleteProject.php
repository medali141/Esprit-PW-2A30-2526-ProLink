<?php
// projects/deleteProject.php — admin-only placeholder for deleting a project
require_once __DIR__ . '/../../../controller/AuthController.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$auth = new AuthController();
$user = $auth->profile();
if (!$user || strtolower($user['type'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require_once __DIR__ . '/../../../controller/ProjectP.php';
$pp = new ProjectP();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    $pp->delete($id);
}
header('Location: listProjects.php');
exit;
