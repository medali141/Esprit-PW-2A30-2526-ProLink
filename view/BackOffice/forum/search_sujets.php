<?php
require_once __DIR__ . '/forum_bootstrap.php';
require_once __DIR__ . '/../../../controller/ForumController.php';

header('Content-Type: application/json; charset=utf-8');

$fc = new ForumController();
$cat = isset($_GET['cat']) ? (int) $_GET['cat'] : null;
$cat = $cat > 0 ? $cat : null;
$sort = (string) ($_GET['sort'] ?? 'created_at');
$dir = (string) ($_GET['dir'] ?? 'desc');
$q = isset($_GET['q']) ? trim((string) $_GET['q']) : null;

$rows = $fc->listSujets($cat, $sort, $dir, $q);

$out = array_map(function ($r) {
    return [
        'id_sujet' => (int) ($r['id_sujet'] ?? 0),
        'titre' => (string) ($r['titre'] ?? ''),
        'cat_titre' => (string) ($r['cat_titre'] ?? ''),
        'prenom' => (string) ($r['prenom'] ?? ''),
        'nom' => (string) ($r['nom'] ?? ''),
        'created_at' => (string) ($r['created_at'] ?? ''),
        'epingle' => (int) ($r['epingle'] ?? 0),
        'verrouille' => (int) ($r['verrouille'] ?? 0),
    ];
}, $rows);

echo json_encode(array_values($out));
