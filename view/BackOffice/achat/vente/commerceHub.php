<?php
/**
 * Ancienne entrée « Hub commerce » : le contenu détaillé est sur gestionAchats.php
 * pour éviter une double navigation vers les mêmes écrans.
 */
require_once __DIR__ . '/../../../../controller/AuthController.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$auth = new AuthController();
$user = $auth->profile();
if (!$user || strtolower($user['type'] ?? '') !== 'admin') {
    header('Location: ../../../login.php');
    exit;
}
header('Location: gestionAchats.php');
exit;
