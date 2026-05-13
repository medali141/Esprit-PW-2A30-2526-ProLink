<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../controller/AuthController.php';
<<<<<<< HEAD
require_once __DIR__ . '/../../controller/ProduitController.php';
=======
require_once __DIR__ . '/../../controller/ProduitP.php';
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5

$auth = new AuthController();
$u = $auth->profile();
if (!$u) {
    header('Location: ../login.php');
    exit;
}
if (strtolower($u['type'] ?? '') !== 'entrepreneur') {
    header('Location: home.php');
    exit;
}

<<<<<<< HEAD
$pp = new ProduitController();
=======
$pp = new ProduitP();
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
$list = $pp->listByVendeur((int) $u['iduser']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mes produits — ProLink</title>
<<<<<<< HEAD
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Mes produits</h1>
        <p class="fo-lead">Gérez votre catalogue. Les achats clients passent par le panier ProLink.</p>
    </header>
    <div class="fo-toolbar">
        <a href="vendeurProduit.php" class="fo-btn fo-btn--primary" style="text-decoration:none;width:fit-content">+ Nouveau produit</a>
    </div>
    <?php if (empty($list)): ?>
        <div class="fo-empty">
            <p class="hint" style="margin:0 0 12px">Aucun produit pour l’instant.</p>
            <a href="vendeurProduit.php">Créer un produit</a>
        </div>
    <?php else: ?>
        <div class="fo-table-wrap">
        <table class="table-modern">
            <thead><tr><th>Réf.</th><th>Désignation</th><th>Prix</th><th>Stock</th><th>Catalogue</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($list as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['reference']) ?></strong></td>
                    <td><?= htmlspecialchars($p['designation']) ?></td>
                    <td><?= number_format((float) $p['prix_unitaire'], 3, ',', ' ') ?> TND</td>
                    <td><?= (int) $p['stock'] ?></td>
                    <td><?php if ((int) $p['actif']): ?>
                        <span class="fo-badge fo-badge--payee">Visible</span>
                    <?php else: ?>
                        <span class="fo-badge fo-badge--brouillon">Masqué</span>
                    <?php endif; ?></td>
                    <td><a href="vendeurProduit.php?id=<?= (int) $p['idproduit'] ?>" class="fo-btn fo-btn--secondary" style="text-decoration:none;padding:8px 12px;font-size:0.85rem">Modifier</a></td>
=======
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container">
    <h1>Mes produits (vente)</h1>
    <p class="hint">Gérez votre catalogue. Les commandes clients passent par le panier ProLink.</p>
    <a href="vendeurProduit.php" class="btn register" style="display:inline-block;padding:10px 16px;border-radius:10px;text-decoration:none;margin:12px 0">+ Nouveau produit</a>
    <?php if (empty($list)): ?>
        <p class="hint">Aucun produit pour l’instant.</p>
    <?php else: ?>
        <table class="table-modern card" style="margin-top:12px;padding:0">
            <thead><tr><th>Réf.</th><th>Désignation</th><th>Prix</th><th>Stock</th><th>Actif</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($list as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['reference']) ?></td>
                    <td><?= htmlspecialchars($p['designation']) ?></td>
                    <td><?= number_format((float) $p['prix_unitaire'], 3, ',', ' ') ?> TND</td>
                    <td><?= (int) $p['stock'] ?></td>
                    <td><?= (int) $p['actif'] ? 'Oui' : 'Non' ?></td>
                    <td>
                        <a href="vendeurProduit.php?id=<?= (int) $p['idproduit'] ?>">Modifier</a>
                    </td>
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
<<<<<<< HEAD
        </div>
=======
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
