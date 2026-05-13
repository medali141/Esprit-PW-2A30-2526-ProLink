<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controller/ProduitController.php';

$pp = new ProduitController();
$idProduit = (int) ($_GET['id'] ?? 0);
$produit = $idProduit > 0 ? $pp->getById($idProduit) : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Détails produit — ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Caractéristiques produit</h1>
        <p class="fo-lead">Page ouverte depuis un QR code ou la boutique.</p>
    </header>

    <?php if (!$produit): ?>
        <div class="fo-empty">
            <p class="hint" style="margin:0 0 12px">Produit introuvable.</p>
            <a href="catalogue.php">Retour au catalogue</a>
        </div>
    <?php else: ?>
        <?php $photo = trim((string) ($produit['photo'] ?? '')); ?>
        <article class="fo-product-detail">
            <div class="fo-product-detail__media">
                <img src="<?= htmlspecialchars($photo !== '' ? '../' . ltrim($photo, '/') : '../assets/product-placeholder.svg') ?>" alt="Photo <?= htmlspecialchars((string) $produit['designation']) ?>">
            </div>
            <div class="fo-product-detail__body">
                <h2><?= htmlspecialchars((string) $produit['designation']) ?></h2>
                <p class="fo-ref">Réf. <?= htmlspecialchars((string) $produit['reference']) ?></p>
                <p><strong>Catégorie:</strong> <?= htmlspecialchars((string) ($produit['categorie_libelle'] ?? '—')) ?></p>
                <p><strong>Prix unitaire:</strong> <?= number_format((float) ($produit['prix_unitaire'] ?? 0), 3, ',', ' ') ?> TND</p>
                <p><strong>Stock:</strong> <?= (int) ($produit['stock'] ?? 0) ?></p>
                <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars((string) ($produit['description'] ?? 'Aucune description fournie.'))) ?></p>
                <div class="fo-actions" style="margin-top:16px">
                    <a href="catalogue.php" class="fo-btn fo-btn--secondary" style="text-decoration:none">Retour boutique</a>
                </div>
            </div>
        </article>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
