<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../controller/ProduitP.php';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qty'])) {
    foreach ($_POST['qty'] as $pid => $q) {
        $pid = (int) $pid;
        $q = max(0, (int) $q);
        if ($q === 0) {
            unset($_SESSION['cart'][$pid]);
        } else {
            $_SESSION['cart'][$pid] = $q;
        }
    }
}

$pp = new ProduitP();
$lines = [];
$total = 0.0;
foreach ($_SESSION['cart'] as $pid => $qte) {
    $pid = (int) $pid;
    $qte = (int) $qte;
    if ($pid <= 0 || $qte <= 0) {
        continue;
    }
    $p = $pp->getById($pid);
    if (!$p || !(int) $p['actif']) {
        unset($_SESSION['cart'][$pid]);
        continue;
    }
    $qte = min($qte, (int) $p['stock']);
    if ($qte <= 0) {
        unset($_SESSION['cart'][$pid]);
        continue;
    }
    $_SESSION['cart'][$pid] = $qte;
    $sub = (float) $p['prix_unitaire'] * $qte;
    $total += $sub;
    $lines[] = ['p' => $p, 'qte' => $qte, 'sub' => $sub];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panier — ProLink</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container">
    <h1>Panier</h1>
    <?php if (empty($lines)): ?>
        <p class="hint">Votre panier est vide. <a href="catalogue.php">Voir le catalogue</a></p>
    <?php else: ?>
        <form method="post" class="card" style="margin-top:16px" novalidate data-validate="panier-form">
            <table class="table-modern">
                <thead><tr><th>Produit</th><th>Prix</th><th>Qté</th><th>Sous-total</th></tr></thead>
                <tbody>
                <?php foreach ($lines as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['p']['designation']) ?></td>
                        <td><?= number_format((float) $row['p']['prix_unitaire'], 3, ',', ' ') ?> TND</td>
                        <td><input type="number" name="qty[<?= (int) $row['p']['idproduit'] ?>]" min="0" max="<?= (int) $row['p']['stock'] ?>" value="<?= (int) $row['qte'] ?>" style="width:80px"></td>
                        <td><?= number_format($row['sub'], 3, ',', ' ') ?> TND</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p style="margin-top:14px"><strong>Total :</strong> <?= number_format($total, 3, ',', ' ') ?> TND</p>
            <button type="submit">Mettre à jour le panier</button>
            <a href="checkout.php" class="btn register" style="margin-left:10px;display:inline-block;padding:10px 14px;border-radius:10px;text-decoration:none">Commander</a>
        </form>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
<script src="../assets/forms-validation.js"></script>
</body>
</html>
