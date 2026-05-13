<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
<<<<<<< HEAD
require_once __DIR__ . '/../../controller/ProduitController.php';
=======
require_once __DIR__ . '/../../controller/ProduitP.php';
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5

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

<<<<<<< HEAD
$pp = new ProduitController();
=======
$pp = new ProduitP();
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
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
<<<<<<< HEAD
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Panier</h1>
        <p class="fo-lead">Ajustez les quantités puis passez commande ou continuez vos achats sur le catalogue.</p>
    </header>
    <?php if (empty($lines)): ?>
        <div class="fo-empty">
            <p class="hint" style="margin:0 0 12px">Votre panier est vide.</p>
            <a href="catalogue.php">Voir le catalogue</a>
        </div>
    <?php else: ?>
        <form method="post" class="fo-form-card" novalidate data-validate="panier-form" style="max-width:none">
            <div class="fo-table-wrap">
            <table class="table-modern">
                <thead><tr><th>Produit</th><th>Prix unitaire</th><th>Qté</th><th>Sous-total</th></tr></thead>
                <tbody>
                <?php foreach ($lines as $row): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['p']['designation']) ?></strong><br><span class="hint"><?= htmlspecialchars($row['p']['reference']) ?></span></td>
                        <td><?= number_format((float) $row['p']['prix_unitaire'], 3, ',', ' ') ?> TND</td>
                        <td><input type="number" name="qty[<?= (int) $row['p']['idproduit'] ?>]" min="0" max="<?= (int) $row['p']['stock'] ?>" value="<?= (int) $row['qte'] ?>" style="width:88px"></td>
                        <td><strong><?= number_format($row['sub'], 3, ',', ' ') ?> TND</strong></td>
=======
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
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
<<<<<<< HEAD
            </div>
            <div class="fo-cart-summary">Total : <?= number_format($total, 3, ',', ' ') ?> TND</div>
            <div class="fo-actions">
                <button type="submit" class="fo-btn fo-btn--secondary">Mettre à jour le panier</button>
                <a href="checkout.php" class="fo-btn fo-btn--primary">Commander</a>
                <a href="catalogue.php" class="hint" style="align-self:center">Continuer les achats</a>
            </div>
=======
            <p style="margin-top:14px"><strong>Total :</strong> <?= number_format($total, 3, ',', ' ') ?> TND</p>
            <button type="submit">Mettre à jour le panier</button>
            <a href="checkout.php" class="btn register" style="margin-left:10px;display:inline-block;padding:10px 14px;border-radius:10px;text-decoration:none">Commander</a>
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
        </form>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
<script src="../assets/forms-validation.js"></script>
</body>
</html>
