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
$pp = new ProduitController();
$list = $pp->listAllAdmin();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Produits — Commerce</title>
    <link rel="stylesheet" href="../../commerce.css">
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
<div class="content commerce-page">
    <div class="container">
        <div class="topbar">
            <div>
                <div class="page-title">Catalogue produits</div>
                <p class="hint" style="margin:6px 0 0">Prix en TND · visibilité catalogue</p>
            </div>
            <div class="actions">
                <input class="search-input" placeholder="Rechercher…" id="searchInput" aria-label="Filtrer le tableau">
                <a href="commerceHub.php" class="btn btn-secondary">← Hub</a>
                <a href="addProduit.php" class="btn btn-primary">+ Produit</a>
            </div>
        </div>
        <div class="commerce-table-wrap">
        <table class="table-modern" id="dataTable">
            <thead>
            <tr>
                <th>ID</th>
                <th>Réf.</th>
                <th>Désignation</th>
                <th>Prix</th>
                <th>Stock</th>
                <th>Vendeur</th>
                <th>Actif</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($list as $p) {
                $stock = (int) $p['stock'];
                $low = $stock > 0 && $stock <= 5;
            ?>
                <tr>
                    <td><?= (int) $p['idproduit'] ?></td>
                    <td><strong><?= htmlspecialchars($p['reference']) ?></strong></td>
                    <td><?= htmlspecialchars($p['designation']) ?></td>
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
    </div>
</div>
<script>
document.getElementById('searchInput').addEventListener('input', function(e){
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('#dataTable tbody tr').forEach(function(r){
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
</body>
</html>
