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
require_once __DIR__ . '/../../../../controller/CommandeController.php';
$pp = new ProduitController();
$cp = new CommandeController();
$nProd = count($pp->listAllAdmin());
$nCmd = $cp->countAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion achat / vente — ProLink</title>
    <link rel="stylesheet" href="../../commerce.css">
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Gestion achat / vente</div>
    </div>
    <div class="container" style="max-width:980px">
        <div class="commerce-hub-intro">
            <h1>Tableau commerce</h1>
            <p>Catalogue produits (TND), stocks, commandes clients et suivi livraison — tout au même endroit.</p>
        </div>
        <div class="commerce-hub-grid">
            <article class="commerce-hub-card commerce-hub-card--products">
                <div class="hub-icon" aria-hidden="true">📦</div>
                <h2>Produits</h2>
                <div class="commerce-hub-stat"><?= (int) $nProd ?></div>
                <p class="hub-hint">Références en base (actives et inactives)</p>
                <div class="commerce-hub-actions">
                    <a class="btn btn-primary" href="listProduits.php">Liste des produits</a>
                    <a class="btn btn-secondary" href="addProduit.php">+ Ajouter</a>
                </div>
            </article>
            <article class="commerce-hub-card commerce-hub-card--orders">
                <div class="hub-icon" aria-hidden="true">🛒</div>
                <h2>Commandes</h2>
                <div class="commerce-hub-stat"><?= (int) $nCmd ?></div>
                <p class="hub-hint">Suivi des statuts et des livraisons</p>
                <div class="commerce-hub-actions">
                    <a class="btn btn-primary" href="listCommandes.php">Liste des commandes</a>
                </div>
            </article>
        </div>
    </div>
</div>
</body>
</html>
