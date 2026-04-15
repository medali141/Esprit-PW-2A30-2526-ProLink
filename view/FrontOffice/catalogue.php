<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../controller/ProduitP.php';
$pp = new ProduitP();
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
    <style>
        .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px;margin-top:24px}
        .pcard{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:18px;display:flex;flex-direction:column;gap:10px}
        .pcard h3{margin:0;font-size:1.05rem}
        .price{font-weight:800;color:var(--accent);font-size:1.15rem}
        .stock{font-size:13px;color:var(--muted)}
    </style>
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container">
    <h1 style="margin-bottom:8px">Catalogue produits</h1>
    <p class="hint">Ajoutez au panier puis validez la commande (livraison en Tunisie par défaut). Prix en dinar tunisien (TND).</p>
    <?php if (isset($_GET['added'])): ?>
        <p class="fade-in" style="color:var(--accent)">Produit ajouté au panier.</p>
    <?php endif; ?>
    <div class="grid">
        <?php foreach ($produits as $p): ?>
            <div class="pcard">
                <h3><?= htmlspecialchars($p['designation']) ?></h3>
                <span class="hint"><?= htmlspecialchars($p['reference']) ?></span>
                <?php if (!empty($p['description'])): ?>
                    <p class="hint" style="margin:0;font-size:13px"><?php $d = (string) $p['description']; $short = strlen($d) > 120 ? substr($d, 0, 117) . '...' : $d; ?><?= nl2br(htmlspecialchars($short)) ?></p>
                <?php endif; ?>
                <div class="price"><?= number_format((float) $p['prix_unitaire'], 3, ',', ' ') ?> TND</div>
                <div class="stock">Stock : <?= (int) $p['stock'] ?> · Vendeur : <?= htmlspecialchars(trim(($p['vendeur_prenom'] ?? '') . ' ' . ($p['vendeur_nom'] ?? ''))) ?></div>
                <?php if ((int) $p['stock'] > 0): ?>
                    <a class="btn register" style="display:inline-block;text-align:center;padding:10px;border-radius:10px;text-decoration:none;margin-top:auto"
                       href="cart_add.php?id=<?= (int) $p['idproduit'] ?>&qte=1">Ajouter au panier</a>
                <?php else: ?>
                    <span class="hint">Rupture de stock</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if (empty($produits)): ?>
        <p class="hint" style="margin-top:24px">Aucun produit actif pour le moment.</p>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
