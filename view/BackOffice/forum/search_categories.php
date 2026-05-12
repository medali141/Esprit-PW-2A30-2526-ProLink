<?php
require_once __DIR__ . '/forum_bootstrap.php';
require_once __DIR__ . '/../../../controller/ForumController.php';

header('Content-Type: application/json; charset=utf-8');

$fc = new ForumController();
$q = isset($_GET['q']) ? trim((string) $_GET['q']) : null;
$sort = (string) ($_GET['sort'] ?? 'ordre');
$dir = (string) ($_GET['dir'] ?? 'asc');

$rows = $fc->searchCategories($q, $sort, $dir);

$out = array_map(function ($r) {
    return [
        'id_categorie' => (int) ($r['id_categorie'] ?? 0),
        'titre' => (string) ($r['titre'] ?? ''),
        'description' => (string) ($r['description'] ?? ''),
        'ordre' => (int) ($r['ordre'] ?? 0),
    ];
}, $rows);

echo json_encode(array_values($out));
