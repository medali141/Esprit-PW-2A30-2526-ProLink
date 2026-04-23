<?php
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
require_once __DIR__ . '/../../../../controller/ProduitController.php';
$pp = new ProduitController();
$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    if ($pp->countOrdered($id) > 0) {
        $pp->setActif($id, 0);
    } else {
        try {
            $pp->deleteHard($id);
        } catch (Exception $e) {
            $pp->setActif($id, 0);
        }
    }
}
header('Location: listProduits.php');
exit;
