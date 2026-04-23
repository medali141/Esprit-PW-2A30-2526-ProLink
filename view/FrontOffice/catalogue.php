<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../controller/ProduitController.php';
$pp = new ProduitController();
$produits = $pp->listCatalogueActifs();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catalogue — ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Catalogue produits</h1>
        <p class="fo-lead">Ajoutez au panier puis validez la commande (livraison en Tunisie par défaut). Prix affichés en dinar tunisien (TND).</p>
    </header>
    <?php if (isset($_GET['added'])): ?>
        <p class="fo-banner fo-banner--ok fade-in" role="status">Produit ajouté au panier.</p>
    <?php endif; ?>
    <div class="fo-product-grid">
        <?php foreach ($produits as $p):
            $stock = (int) $p['stock'];
            $out = $stock <= 0;
            $low = $stock > 0 && $stock <= 5;
        ?>
            <article class="fo-product-card<?= $out ? ' fo-product-card--out' : '' ?>">
                <h2><?= htmlspecialchars($p['designation']) ?></h2>
                <span class="fo-ref"><?= htmlspecialchars($p['reference']) ?></span>
                <?php if (!empty($p['description'])): ?>
                    <p class="fo-desc"><?php $d = (string) $p['description']; $short = strlen($d) > 120 ? substr($d, 0, 117) . '…' : $d; ?><?= nl2br(htmlspecialchars($short)) ?></p>
                <?php endif; ?>
                <div class="fo-price"><?= number_format((float) $p['prix_unitaire'], 3, ',', ' ') ?> TND</div>
                <div class="fo-meta">
                    <?php if ($out): ?>
                        <span class="fo-stock-pill">Rupture de stock</span>
                    <?php else: ?>
                        <span class="fo-stock-pill<?= $low ? ' fo-stock-pill--low' : '' ?>">Stock <?= $stock ?></span>
                        · <?= htmlspecialchars(trim(($p['vendeur_prenom'] ?? '') . ' ' . ($p['vendeur_nom'] ?? ''))) ?>
                    <?php endif; ?>
                </div>
                <?php if (!$out): ?>
                    <a class="fo-btn fo-btn--primary" href="cart_add.php?id=<?= (int) $p['idproduit'] ?>&qte=1">Ajouter au panier</a>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
    <?php if (empty($produits)): ?>
        <div class="fo-empty">
            <p class="hint" style="margin:0 0 12px">Aucun produit actif pour le moment.</p>
            <a href="home.php">Retour à l’accueil</a>
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
