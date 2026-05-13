<?php
require_once __DIR__ . '/../../init.php';
requireLogin('Connectez-vous pour ajouter un article au panier.');
require_once __DIR__ . '/../../controller/ProduitController.php';

$id = (int) ($_GET['id'] ?? 0);
$qte = max(1, (int) ($_GET['qte'] ?? 1));

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
