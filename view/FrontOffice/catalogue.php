<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once __DIR__ . '/../../controller/ProduitController.php';
$pp = new ProduitController();
$q = trim((string) ($_GET['q'] ?? ''));
$tri = (string) ($_GET['tri'] ?? 'designation');
$allowedTri = ['designation', 'prix_asc', 'prix_desc', 'stock_asc', 'stock_desc', 'recent'];
if (!in_array($tri, $allowedTri, true)) {
    $tri = 'designation';
}
$ordre = strtolower((string) ($_GET['ordre'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
$idcategorie = (int) ($_GET['cat'] ?? 0);
$categories = $pp->listCategories();
$produits = $pp->listCatalogueFiltered($q, $tri, $ordre, $idcategorie);
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
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Catalogue produits</h1>
        <p class="fo-lead">Ajoutez au panier puis validez la commande (livraison en Tunisie par défaut). Prix affichés en dinar tunisien (TND).</p>
    </header>
    <?php if (isset($_GET['added'])): ?>
        <p class="fo-banner fo-banner--ok fade-in" role="status">Produit ajouté au panier.</p>
    <?php endif; ?>
    <?php if (isset($_GET['err']) && $_GET['err'] === 'csrf'): ?>
        <p class="fo-banner fo-banner--err">Action refusée (session expirée). Rechargez la page puis réessayez.</p>
    <?php endif; ?>
    <form method="get" class="fo-filters" action="catalogue.php" aria-label="Recherche et tri catalogue">
        <div class="fo-filters__field">
            <label for="cat-q">Recherche</label>
            <input type="search" name="q" id="cat-q" value="<?= htmlspecialchars($q) ?>" placeholder="Nom, référence, vendeur…" autocomplete="off">
        </div>
        <div class="fo-filters__field">
            <label for="cat-famille">Catégorie</label>
            <select name="cat" id="cat-famille">
                <option value="0" <?= $idcategorie === 0 ? 'selected' : '' ?>>Toutes</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['idcategorie'] ?>" <?= $idcategorie === (int) $c['idcategorie'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['libelle']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="fo-filters__field">
            <label for="cat-tri">Trier par</label>
            <select name="tri" id="cat-tri">
                <option value="designation" <?= $tri === 'designation' ? 'selected' : '' ?>>Nom (A–Z / Z–A)</option>
                <option value="prix_asc" <?= $tri === 'prix_asc' ? 'selected' : '' ?>>Prix croissant</option>
                <option value="prix_desc" <?= $tri === 'prix_desc' ? 'selected' : '' ?>>Prix décroissant</option>
                <option value="stock_asc" <?= $tri === 'stock_asc' ? 'selected' : '' ?>>Stock (faible d’abord)</option>
                <option value="stock_desc" <?= $tri === 'stock_desc' ? 'selected' : '' ?>>Stock (élevé d’abord)</option>
                <option value="recent" <?= $tri === 'recent' ? 'selected' : '' ?>>Récemment ajoutés</option>
            </select>
        </div>
        <?php if ($tri === 'designation'): ?>
        <div class="fo-filters__field">
            <label for="cat-ordre">Ordre</label>
            <select name="ordre" id="cat-ordre">
                <option value="asc" <?= $ordre === 'asc' ? 'selected' : '' ?>>A → Z</option>
                <option value="desc" <?= $ordre === 'desc' ? 'selected' : '' ?>>Z → A</option>
            </select>
        </div>
        <?php endif; ?>
        <div class="fo-filters__actions">
            <button type="submit" class="fo-btn fo-btn--primary">Appliquer</button>
            <a href="catalogue.php" class="fo-btn fo-btn--secondary" style="text-decoration:none">Réinitialiser</a>
        </div>
    </form>
    <?php if ($q !== '' || $idcategorie > 0): ?>
        <p class="fo-result-hint"><?= count($produits) ?> produit(s) trouvé(s)</p>
    <?php endif; ?>
    <div class="fo-product-grid">
        <?php foreach ($produits as $p):
            $stock = (int) $p['stock'];
            $out = $stock <= 0;
            $low = $stock > 0 && $stock <= 5;
        ?>
            <article class="fo-product-card<?= $out ? ' fo-product-card--out' : '' ?>">
                <?php $photo = trim((string) ($p['photo'] ?? '')); ?>
                <div class="fo-product-media">
                    <img src="<?= htmlspecialchars($photo !== '' ? '../' . ltrim($photo, '/') : '../assets/product-placeholder.svg') ?>" alt="Photo <?= htmlspecialchars($p['designation']) ?>" loading="lazy">
                </div>
                <?php if (!empty($p['categorie_libelle'])): ?>
                    <span class="fo-cat-pill"><?= htmlspecialchars((string) $p['categorie_libelle']) ?></span>
                <?php endif; ?>
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
                    <form method="post" action="cart_add.php" style="margin-top:auto">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="id" value="<?= (int) $p['idproduit'] ?>">
                        <input type="hidden" name="qte" value="1">
                        <button type="submit" class="fo-btn fo-btn--primary" style="width:100%">Ajouter au panier</button>
                    </form>
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
