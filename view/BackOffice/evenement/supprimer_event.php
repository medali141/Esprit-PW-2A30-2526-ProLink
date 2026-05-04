<?php
require_once __DIR__ . '/bo_event_bootstrap.php';
require_once __DIR__ . '/../../../controller/eventC.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id < 1) {
    header('Location: liste_event.php');
    exit;
}
$eventC = new EventC();
$eventC->deleteEvent($id);
$eAllowed = ['id_event', 'titre_event', 'description_event', 'type_event', 'date_debut', 'date_fin', 'lieu_event', 'capacite_max', 'statut', 'created_at'];
$pAllowed = ['id_participation', 'titre_event', 'nom', 'prenom', 'email', 'telephone', 'date_inscription', 'statut', 'id_event'];
$q = ['deleted' => '1'];
if (isset($_GET['esort']) && in_array((string) $_GET['esort'], $eAllowed, true)) {
    $q['esort'] = (string) $_GET['esort'];
}
if (isset($_GET['edir']) && in_array(strtolower((string) $_GET['edir']), ['asc', 'desc'], true)) {
    $q['edir'] = strtolower((string) $_GET['edir']);
}
if (isset($_GET['psort']) && in_array((string) $_GET['psort'], $pAllowed, true)) {
    $q['psort'] = (string) $_GET['psort'];
}
if (isset($_GET['pdir']) && in_array(strtolower((string) $_GET['pdir']), ['asc', 'desc'], true)) {
    $q['pdir'] = strtolower((string) $_GET['pdir']);
}
header('Location: liste_event.php?' . http_build_query($q));
exit;
