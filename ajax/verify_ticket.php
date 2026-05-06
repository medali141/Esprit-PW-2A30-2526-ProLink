<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controller/presenceC.php';

$qrData  = trim((string)($_POST['qr_data']  ?? ''));
$idEvent = (int)($_POST['id_event'] ?? 0);

if ($qrData === '' || $idEvent < 1) {
    echo json_encode(['ok' => false, 'msg' => 'Données manquantes.']);
    exit;
}

$c = new PresenceC();
echo json_encode($c->scanTicket($qrData, $idEvent));