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
require_once __DIR__ . '/../../../../controller/CommandeP.php';
$pp = new ProduitP();
$cp = new CommandeP();
$nProd = count($pp->listAllAdmin());
$nCmd = $cp->countAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion achat / vente</title>
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
<div class="content">
    <div class="topbar">
        <div class="page-title">Gestion achat / vente Pro</div>
    </div>
    <div class="container" style="max-width:900px">
        <p class="text-muted" style="margin-bottom:20px">Catalogue, stocks, commandes et livraison (module Chames).</p>
        <div class="form-grid" style="grid-template-columns: repeat(auto-fit,minmax(240px,1fr)); gap:18px">
            <div class="card">
                <h3 style="margin-top:0">Produits</h3>
                <p class="hint"><?= (int) $nProd ?> référence(s) en base</p>
                <a class="btn btn-primary" href="listProduits.php" style="margin-top:12px;display:inline-block">Liste des produits</a>
                <a class="btn btn-secondary" href="addProduit.php" style="margin-top:8px;display:inline-block">+ Ajouter un produit</a>
            </div>
            <div class="card">
                <h3 style="margin-top:0">Commandes</h3>
                <p class="hint"><?= (int) $nCmd ?> commande(s)</p>
                <a class="btn btn-primary" href="listCommandes.php" style="margin-top:12px;display:inline-block">Liste des commandes</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
