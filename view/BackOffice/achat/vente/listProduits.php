<?php
require_once __DIR__ . '/../../../../controller/AuthController.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$auth = new AuthController();
$user = $auth->profile();
if (!$user || strtolower($user['type'] ?? '') !== 'admin') {
    header('Location: ../../../login.php');
    exit;
}
require_once __DIR__ . '/../../../../controller/ProduitController.php';
<<<<<<< HEAD
require_once __DIR__ . '/../../../../model/CommerceRegles.php';
$pp = new ProduitController();
$q = trim((string) ($_GET['q'] ?? ''));
$tri = (string) ($_GET['tri'] ?? 'date');
$triAllowed = ['date', 'id', 'reference', 'designation', 'prix', 'stock', 'actif', 'vendeur', 'categorie'];
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
$list = $pp->listAllAdminFiltered($q, $tri, $ordre, $actif, $idcategorie);
$page = CommerceRegles::sanitizePage((int) ($_GET['page'] ?? 1));
$perPage = 12;
$totalRows = count($list);
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page = CommerceRegles::sanitizePage($page, $totalPages);
$start = ($page - 1) * $perPage;
$rows = array_slice($list, $start, $perPage);
$triOpts = [
    'date' => 'Date création',
    'id' => 'ID',
    'reference' => 'Référence',
    'designation' => 'Désignation',
    'prix' => 'Prix',
    'stock' => 'Stock',
    'actif' => 'Actif',
    'vendeur' => 'Vendeur (nom)',
    'categorie' => 'Catégorie',
];
$catLabel = '';
if ($idcategorie > 0) {
    foreach ($categories as $cat) {
        if ((int) ($cat['idcategorie'] ?? 0) === $idcategorie) {
            $catLabel = (string) ($cat['libelle'] ?? '');
            break;
        }
    }
}
$activeFilters = [];
if ($q !== '') {
    $activeFilters[] = ['k' => 'Recherche', 'v' => $q];
}
if ($idcategorie > 0 && $catLabel !== '') {
    $activeFilters[] = ['k' => 'Catégorie', 'v' => $catLabel];
}
if ($actif !== '') {
    $activeFilters[] = ['k' => 'Catalogue', 'v' => $actif === '1' ? 'Actifs seulement' : 'Inactifs seulement'];
}
if ($tri !== 'date') {
    $activeFilters[] = ['k' => 'Tri', 'v' => $triOpts[$tri] ?? $tri];
}
if ($ordre !== 'desc') {
    $activeFilters[] = ['k' => 'Ordre', 'v' => 'Croissant'];
}
=======
$pp = new ProduitController();
$list = $pp->listAllAdmin();
>>>>>>> formation
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Produits — Commerce</title>
<<<<<<< HEAD
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
<link rel="stylesheet" href="../../commerce.css">
=======
    <link rel="stylesheet" href="../../commerce.css">
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
>>>>>>> formation
<div class="content commerce-page">
    <div class="container">
        <div class="topbar">
            <div>
                <div class="page-title">Catalogue produits</div>
                <p class="hint" style="margin:6px 0 0">Prix en TND · visibilité catalogue</p>
            </div>
            <div class="actions">
<<<<<<< HEAD
                <a href="gestionAchats.php" class="btn btn-secondary">← Achats</a>
                <a href="addProduit.php" class="btn btn-primary">+ Produit</a>
            </div>
        </div>
        <form method="get" class="commerce-filters" action="listProduits.php" aria-label="Recherche et tri produits" data-enhanced="1">
            <div class="commerce-filters__field">
                <label for="f-q">Recherche</label>
                <input class="search-input" type="text" name="q" id="f-q" value="<?= htmlspecialchars($q) ?>" placeholder="Réf., désignation, vendeur…">
            </div>
            <div class="commerce-filters__field">
                <label for="f-tri">Trier par</label>
                <select name="tri" id="f-tri">
                    <?php foreach ($triOpts as $k => $lab): ?>
                        <option value="<?= htmlspecialchars($k) ?>" <?= $tri === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="commerce-filters__field">
                <label for="f-ordre">Ordre</label>
                <select name="ordre" id="f-ordre">
                    <option value="desc" <?= $ordre === 'desc' ? 'selected' : '' ?>>Décroissant</option>
                    <option value="asc" <?= $ordre === 'asc' ? 'selected' : '' ?>>Croissant</option>
                </select>
            </div>
            <div class="commerce-filters__field">
                <label for="f-cat">Catégorie</label>
                <select name="cat" id="f-cat">
                    <option value="0" <?= $idcategorie === 0 ? 'selected' : '' ?>>Toutes les catégories</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['idcategorie'] ?>" <?= $idcategorie === (int) $c['idcategorie'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['libelle']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="commerce-filters__field">
                <label for="f-actif">Catalogue</label>
                <select name="actif" id="f-actif">
                    <option value="" <?= $actif === '' ? 'selected' : '' ?>>Tous</option>
                    <option value="1" <?= $actif === '1' ? 'selected' : '' ?>>Actifs seulement</option>
                    <option value="0" <?= $actif === '0' ? 'selected' : '' ?>>Inactifs seulement</option>
                </select>
            </div>
            <div class="commerce-filters__actions">
                <button type="submit" class="btn btn-primary">Appliquer</button>
                <a class="btn btn-secondary" href="listProduits.php">Réinitialiser</a>
            </div>
        </form>
        <?php if (!empty($activeFilters)): ?>
            <div class="commerce-active-filters" aria-label="Filtres actifs">
                <?php foreach ($activeFilters as $f): ?>
                    <span class="commerce-active-filter-chip"><strong><?= htmlspecialchars($f['k']) ?>:</strong> <?= htmlspecialchars($f['v']) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($q !== '' || $actif !== '' || $idcategorie > 0): ?>
            <p class="commerce-result-hint"><?= $totalRows ?> résultat(s)</p>
        <?php endif; ?>
=======
                <input class="search-input" placeholder="Rechercher…" id="searchInput" aria-label="Filtrer le tableau">
                <a href="commerceHub.php" class="btn btn-secondary">← Hub</a>
                <a href="addProduit.php" class="btn btn-primary">+ Produit</a>
            </div>
        </div>
>>>>>>> formation
        <div class="commerce-table-wrap">
        <table class="table-modern" id="dataTable">
            <thead>
            <tr>
                <th>ID</th>
<<<<<<< HEAD
                <th>Photo</th>
                <th>Réf.</th>
                <th>Désignation</th>
                <th>Catégorie</th>
=======
                <th>Réf.</th>
                <th>Désignation</th>
>>>>>>> formation
                <th>Prix</th>
                <th>Stock</th>
                <th>Vendeur</th>
                <th>Actif</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
<<<<<<< HEAD
            <?php foreach ($rows as $p) {
=======
            <?php foreach ($list as $p) {
>>>>>>> formation
                $stock = (int) $p['stock'];
                $low = $stock > 0 && $stock <= 5;
            ?>
                <tr>
                    <td><?= (int) $p['idproduit'] ?></td>
<<<<<<< HEAD
                    <?php $photo = trim((string) ($p['photo'] ?? '')); ?>
                    <td><img src="<?= htmlspecialchars($photo !== '' ? '../../../' . ltrim($photo, '/') : '../../../assets/product-placeholder.svg') ?>" alt="Photo <?= htmlspecialchars($p['designation']) ?>" style="width:44px;height:44px;object-fit:cover;border-radius:8px;border:1px solid #dbe4ef"></td>
                    <td><strong><?= htmlspecialchars($p['reference']) ?></strong></td>
                    <td><?= htmlspecialchars($p['designation']) ?></td>
                    <td><span class="commerce-cat-pill"><?= htmlspecialchars((string) ($p['categorie_libelle'] ?? '—')) ?></span></td>
=======
                    <td><strong><?= htmlspecialchars($p['reference']) ?></strong></td>
                    <td><?= htmlspecialchars($p['designation']) ?></td>
>>>>>>> formation
                    <td><?= number_format((float) $p['prix_unitaire'], 3, ',', ' ') ?> TND</td>
                    <td><span class="commerce-stock-pill<?= $low ? ' commerce-stock-pill--low' : '' ?>"><?= $stock ?></span></td>
                    <td><?= htmlspecialchars(trim(($p['vendeur_prenom'] ?? '') . ' ' . ($p['vendeur_nom'] ?? ''))) ?></td>
                    <td>
                        <?php if ((int) $p['actif']): ?>
                            <span class="commerce-yesno commerce-yesno--yes">Oui</span>
                        <?php else: ?>
                            <span class="commerce-yesno commerce-yesno--no">Non</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="commerce-actions">
                            <a class="btn btn-secondary" href="editProduit.php?id=<?= (int) $p['idproduit'] ?>">Modifier</a>
                            <a class="btn btn-danger js-delete" href="#" data-confirm="Retirer ce produit du catalogue (ou le désactiver s'il a des commandes) ?" data-href="deleteProduit.php?id=<?= (int) $p['idproduit'] ?>">Retirer</a>
                        </div>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        </div>
<<<<<<< HEAD
        <?php if ($totalPages > 1): ?>
            <div class="commerce-filters__actions" style="margin-top:12px">
                <?php if ($page > 1): ?>
                    <a class="btn btn-secondary" href="?q=<?= urlencode($q) ?>&tri=<?= urlencode($tri) ?>&ordre=<?= urlencode($ordre) ?>&cat=<?= $idcategorie ?>&actif=<?= urlencode($actif) ?>&page=<?= $page - 1 ?>">Précédent</a>
                <?php endif; ?>
                <span class="commerce-result-hint">Page <?= $page ?> / <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a class="btn btn-secondary" href="?q=<?= urlencode($q) ?>&tri=<?= urlencode($tri) ?>&ordre=<?= urlencode($ordre) ?>&cat=<?= $idcategorie ?>&actif=<?= urlencode($actif) ?>&page=<?= $page + 1 ?>">Suivant</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
(function () {
    var form = document.querySelector('.commerce-filters[data-enhanced="1"]');
    if (!form) return;
    var selects = form.querySelectorAll('select');
    for (var i = 0; i < selects.length; i++) {
        selects[i].addEventListener('change', function () { form.submit(); });
    }
})();
=======
    </div>
</div>
<script>
document.getElementById('searchInput').addEventListener('input', function(e){
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('#dataTable tbody tr').forEach(function(r){
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
>>>>>>> formation
</script>
</body>
</html>
