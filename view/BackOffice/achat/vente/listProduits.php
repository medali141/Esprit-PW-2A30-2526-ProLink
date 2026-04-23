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
require_once __DIR__ . '/../../../../controller/ProduitP.php';
$pp = new ProduitP();
$list = $pp->listAllAdmin();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Produits — BackOffice</title>
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
<div class="content">
    <div class="container">
        <div class="topbar">
            <div class="page-title">Produits</div>
            <div class="actions">
                <input class="search-input" placeholder="Rechercher..." id="searchInput">
                <a href="commerceHub.php" class="btn btn-secondary">Hub commerce</a>
                <a href="addProduit.php" class="btn btn-primary">+ Ajouter</a>
            </div>
        </div>
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
            <?php foreach ($list as $p) { ?>
                <tr>
                    <td><?= (int) $p['idproduit'] ?></td>
                    <td><?= htmlspecialchars($p['reference']) ?></td>
                    <td><?= htmlspecialchars($p['designation']) ?></td>
                    <td><?= number_format((float) $p['prix_unitaire'], 3, ',', ' ') ?> TND</td>
                    <td><?= (int) $p['stock'] ?></td>
                    <td><?= htmlspecialchars(trim(($p['vendeur_prenom'] ?? '') . ' ' . ($p['vendeur_nom'] ?? ''))) ?></td>
                    <td><?= (int) $p['actif'] ? 'Oui' : 'Non' ?></td>
                    <td>
                        <a class="btn btn-secondary" href="editProduit.php?id=<?= (int) $p['idproduit'] ?>">Modifier</a>
                        <a class="btn btn-danger js-delete" href="#" data-confirm="Retirer ce produit du catalogue (ou le désactiver s'il a des commandes) ?" data-href="deleteProduit.php?id=<?= (int) $p['idproduit'] ?>">Retirer</a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<script>
document.getElementById('searchInput').addEventListener('input', function(e){
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('#dataTable tbody tr').forEach(r => {
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
</body>
</html>
