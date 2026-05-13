<?php
/**
 * Délivrance manuelle d'un certificat depuis le back-office (admin).
 * Délègue le rendu PDF à FormationCertificatePdf.
 *
 * URL : certificat.php?id_inscription=123
 */
require_once __DIR__ . '/../../../controller/AuthController.php';
require_once __DIR__ . '/../_layout/paths.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$auth = new AuthController();
$user = $auth->profile();
if (!$user || strtolower($user['type'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../../controller/FormationP.php';
require_once __DIR__ . '/../../../lib/FormationCertificatePdf.php';

$fp = new FormationP();
$idInscription = isset($_GET['id_inscription']) ? (int) $_GET['id_inscription'] : 0;
$row = $idInscription > 0 ? $fp->getInscription($idInscription) : null;

if (!$row) {
    http_response_code(404);
    echo 'Inscription introuvable.';
    exit;
}

FormationCertificatePdf::render($row);
