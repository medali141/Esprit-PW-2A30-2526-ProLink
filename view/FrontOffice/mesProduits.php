<?php
require_once __DIR__ . '/../../init.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/ProduitController.php';
require_once __DIR__ . '/../../controller/AchatsStockOffresController.php';
require_once __DIR__ . '/../../model/CommerceRegles.php';

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
$page = CommerceRegles::sanitizePage((int) ($_GET['page'] ?? 1));
$perPage = 10;
$totalRows = count($list);
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page = CommerceRegles::sanitizePage($page, $totalPages);
$start = ($page - 1) * $perPage;
$rows = array_slice($list, $start, $perPage);

$stockCtlMp = new AchatsStockOffresController();
$stockSignalsMp = [];
foreach ($stockCtlMp->getReapproDashboard(90) as $row) {
    $pid = (int) ($row['idproduit'] ?? 0);
    $nv = (string) ($row['niveau_alerte'] ?? '');
    if ($pid > 0 && ($nv === 'critique' || $nv === 'vigilance')) {
        $stockSignalsMp[$pid] = $nv;
    }
}
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
        <p class="fo-lead">Catalogue vendeur — les ventes passent par le panier client. Les pastilles reflètent le même pilotage stock que sur la boutique.</p>
    </header>
    <p class="fo-catalog-supply-banner" role="note" style="margin-bottom:18px">
        <strong>Cohérence boutique</strong> — Priorité réappro et surveillance utilisent les seuils et délais définis côté gestion d’achats.
    </p>
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
                <option value="0" <?= $idcategorie === 0 ? 'selected' : '' ?>>Toutes les catégories</option>
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
            <thead><tr><th>Photo</th><th>Réf.</th><th>Désignation</th><th>Catégorie</th><th>Prix</th><th>Stock</th><th>Pilotage</th><th>Catalogue</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $p):
                    $pidRow = (int) $p['idproduit'];
                    $sigMp = $stockSignalsMp[$pidRow] ?? null;
                ?>
                <tr>
                    <?php $photo = trim((string) ($p['photo'] ?? '')); ?>
                    <td><img src="<?= htmlspecialchars($photo !== '' ? '../' . ltrim($photo, '/') : '../assets/product-placeholder.svg') ?>" alt="Photo <?= htmlspecialchars($p['designation']) ?>" style="width:44px;height:44px;object-fit:cover;border-radius:8px;border:1px solid #dbe4ef"></td>
                    <td><strong><?= htmlspecialchars($p['reference']) ?></strong></td>
                    <td><?= htmlspecialchars($p['designation']) ?></td>
                    <td><?= htmlspecialchars((string) ($p['categorie_libelle'] ?? '—')) ?></td>
                    <td><?= number_format((float) $p['prix_unitaire'], 3, ',', ' ') ?> TND</td>
                    <td><?= (int) $p['stock'] ?></td>
                    <td>
                        <?php if ($sigMp === 'critique'): ?>
                            <span class="fo-pilotage-pill fo-pilotage-pill--crit">Priorité réappro</span>
                        <?php elseif ($sigMp === 'vigilance'): ?>
                            <span class="fo-pilotage-pill fo-pilotage-pill--warn">Surveillance</span>
                        <?php else: ?>
                            <span class="hint" style="font-size:12px">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?php if ((int) $p['actif']): ?>
                        <span class="fo-badge fo-badge--payee">Visible</span>
                    <?php else: ?>
                        <span class="fo-badge fo-badge--brouillon">Masqué</span>
                    <?php endif; ?></td>
                    <td>
                        <div style="display:flex;flex-wrap:wrap;gap:6px">
                            <a href="produitDetails.php?id=<?= (int) $p['idproduit'] ?>" class="fo-btn fo-btn--secondary fo-btn--product-spec" style="text-decoration:none;padding:8px 12px;font-size:0.85rem">Caractéristiques</a>
                            <a href="vendeurProduit.php?id=<?= (int) $p['idproduit'] ?>" class="fo-btn fo-btn--secondary" style="text-decoration:none;padding:8px 12px;font-size:0.85rem">Modifier</a>
                        </div>
                    </td>
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
