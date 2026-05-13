<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../controller/ProduitController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: catalogue.php');
    exit;
}
if (empty($_SESSION['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
    header('Location: catalogue.php?err=csrf');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$qte = max(1, (int) ($_POST['qte'] ?? 1));

if ($id > 0) {
    $pp = new ProduitController();
    $p = $pp->getById($id);
    if ($p && (int) $p['actif'] === 1 && (int) $p['stock'] >= $qte) {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $cur = (int) ($_SESSION['cart'][$id] ?? 0);
        $maxAdd = (int) $p['stock'] - $cur;
        if ($maxAdd > 0) {
            $add = min($qte, $maxAdd);
            $_SESSION['cart'][$id] = $cur + $add;
        }
    }
}

header('Location: catalogue.php?added=1');
exit;
