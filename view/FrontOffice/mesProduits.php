<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/ProduitController.php';
require_once __DIR__ . '/../../model/CommerceMetier.php';

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

$pp = new ProduitController();
$q = trim((string) ($_GET['q'] ?? ''));
$tri = (string) ($_GET['tri'] ?? 'date');
$triAllowed = ['date', 'id', 'reference', 'designation', 'prix', 'stock', 'actif', 'categorie'];
if (!in_array($tri, $triAllowed, true)) {
    $tri = 'date';
}
$ordre = strtolower((string) ($_GET['ordre'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
$actif = (string) ($_GET['actif'] ?? '');
if (!in_array($actif, ['', '0', '1'], true)) {
    $actif = '';
}
$idcategorie = (int) ($_GET['cat'] ?? 0);
$categories = $pp->listCategories();
$list = $pp->listByVendeurFiltered((int) $u['iduser'], $q, $tri, $ordre, $actif, $idcategorie);
$page = CommerceMetier::sanitizePage((int) ($_GET['page'] ?? 1));
$perPage = 10;
$totalRows = count($list);
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page = CommerceMetier::sanitizePage($page, $totalPages);
$start = ($page - 1) * $perPage;
$rows = array_slice($list, $start, $perPage);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mes produits — ProLink</title>
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
    <form method="get" class="fo-filters" action="mesProduits.php" aria-label="Filtres mes produits">
        <div class="fo-filters__field">
            <label for="mp-q">Recherche</label>
            <input type="search" name="q" id="mp-q" value="<?= htmlspecialchars($q) ?>" placeholder="Réf. ou désignation…">
        </div>
        <div class="fo-filters__field">
            <label for="mp-tri">Trier par</label>
            <select name="tri" id="mp-tri">
                <option value="date" <?= $tri === 'date' ? 'selected' : '' ?>>Date</option>
                <option value="reference" <?= $tri === 'reference' ? 'selected' : '' ?>>Référence</option>
                <option value="designation" <?= $tri === 'designation' ? 'selected' : '' ?>>Désignation</option>
                <option value="prix" <?= $tri === 'prix' ? 'selected' : '' ?>>Prix</option>
                <option value="stock" <?= $tri === 'stock' ? 'selected' : '' ?>>Stock</option>
                <option value="actif" <?= $tri === 'actif' ? 'selected' : '' ?>>Visibilité</option>
                <option value="categorie" <?= $tri === 'categorie' ? 'selected' : '' ?>>Catégorie</option>
            </select>
        </div>
        <div class="fo-filters__field">
            <label for="mp-cat">Famille</label>
            <select name="cat" id="mp-cat">
                <option value="0" <?= $idcategorie === 0 ? 'selected' : '' ?>>Toutes</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['idcategorie'] ?>" <?= $idcategorie === (int) $c['idcategorie'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['libelle']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="fo-filters__field">
            <label for="mp-ordre">Ordre</label>
            <select name="ordre" id="mp-ordre">
                <option value="desc" <?= $ordre === 'desc' ? 'selected' : '' ?>>Décroissant</option>
                <option value="asc" <?= $ordre === 'asc' ? 'selected' : '' ?>>Croissant</option>
            </select>
        </div>
        <div class="fo-filters__field">
            <label for="mp-actif">Catalogue</label>
            <select name="actif" id="mp-actif">
                <option value="" <?= $actif === '' ? 'selected' : '' ?>>Tous</option>
                <option value="1" <?= $actif === '1' ? 'selected' : '' ?>>Visibles</option>
                <option value="0" <?= $actif === '0' ? 'selected' : '' ?>>Masqués</option>
            </select>
        </div>
        <div class="fo-filters__actions">
            <button type="submit" class="fo-btn fo-btn--primary">Appliquer</button>
            <a href="mesProduits.php" class="fo-btn fo-btn--secondary" style="text-decoration:none">Réinitialiser</a>
        </div>
    </form>
    <?php if ($q !== '' || $actif !== '' || $idcategorie > 0): ?>
        <p class="fo-result-hint"><?= $totalRows ?> ligne(s)</p>
    <?php endif; ?>
    <?php if (empty($rows)): ?>
        <div class="fo-empty">
            <p class="hint" style="margin:0 0 12px">Aucun produit pour l’instant.</p>
            <a href="vendeurProduit.php">Créer un produit</a>
        </div>
    <?php else: ?>
        <div class="fo-table-wrap">
        <table class="table-modern">
            <thead><tr><th>Photo</th><th>Réf.</th><th>Désignation</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Catalogue</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $p): ?>
                <tr>
                    <?php $photo = trim((string) ($p['photo'] ?? '')); ?>
                    <td><img src="<?= htmlspecialchars($photo !== '' ? '../' . ltrim($photo, '/') : '../assets/product-placeholder.svg') ?>" alt="Photo <?= htmlspecialchars($p['designation']) ?>" style="width:44px;height:44px;object-fit:cover;border-radius:8px;border:1px solid #dbe4ef"></td>
                    <td><strong><?= htmlspecialchars($p['reference']) ?></strong></td>
                    <td><?= htmlspecialchars($p['designation']) ?></td>
                    <td><?= htmlspecialchars((string) ($p['categorie_libelle'] ?? '—')) ?></td>
                    <td><?= number_format((float) $p['prix_unitaire'], 3, ',', ' ') ?> TND</td>
                    <td><?= (int) $p['stock'] ?></td>
                    <td><?php if ((int) $p['actif']): ?>
                        <span class="fo-badge fo-badge--payee">Visible</span>
                    <?php else: ?>
                        <span class="fo-badge fo-badge--brouillon">Masqué</span>
                    <?php endif; ?></td>
                    <td><a href="vendeurProduit.php?id=<?= (int) $p['idproduit'] ?>" class="fo-btn fo-btn--secondary" style="text-decoration:none;padding:8px 12px;font-size:0.85rem">Modifier</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php if ($totalPages > 1): ?>
            <div class="fo-actions" style="margin-top:12px">
                <?php if ($page > 1): ?>
                    <a class="fo-btn fo-btn--secondary" style="text-decoration:none" href="?q=<?= urlencode($q) ?>&tri=<?= urlencode($tri) ?>&ordre=<?= urlencode($ordre) ?>&cat=<?= $idcategorie ?>&actif=<?= urlencode($actif) ?>&page=<?= $page - 1 ?>">Précédent</a>
                <?php endif; ?>
                <span class="fo-result-hint" style="margin:0">Page <?= $page ?> / <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a class="fo-btn fo-btn--secondary" style="text-decoration:none" href="?q=<?= urlencode($q) ?>&tri=<?= urlencode($tri) ?>&ordre=<?= urlencode($ordre) ?>&cat=<?= $idcategorie ?>&actif=<?= urlencode($actif) ?>&page=<?= $page + 1 ?>">Suivant</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
