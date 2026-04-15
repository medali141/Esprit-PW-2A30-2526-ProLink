<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/ProduitP.php';

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

$pp = new ProduitP();
$list = $pp->listByVendeur((int) $u['iduser']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mes produits — ProLink</title>
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
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
