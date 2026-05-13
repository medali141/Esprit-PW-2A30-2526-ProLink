<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
<<<<<<< HEAD
require_once __DIR__ . '/../../controller/ProduitController.php';
=======
require_once __DIR__ . '/../../controller/ProduitP.php';
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5

$id = (int) ($_GET['id'] ?? 0);
$qte = max(1, (int) ($_GET['qte'] ?? 1));

if ($id > 0) {
<<<<<<< HEAD
    $pp = new ProduitController();
=======
    $pp = new ProduitP();
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
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
