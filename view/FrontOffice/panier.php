<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
<<<<<<< HEAD
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
=======
>>>>>>> formation
require_once __DIR__ . '/../../controller/ProduitController.php';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

<<<<<<< HEAD
$allowedPanierTri = ['designation', 'prix_asc', 'prix_desc', 'ligne_asc', 'ligne_desc'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qty'])) {
    if (empty($_POST['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], (string) $_POST['csrf_token'])) {
        header('Location: panier.php?csrf=1');
        exit;
    }
    $triRedirect = (string) ($_POST['sort_tri'] ?? $_GET['tri'] ?? 'designation');
    if (!in_array($triRedirect, $allowedPanierTri, true)) {
        $triRedirect = 'designation';
    }
=======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qty'])) {
>>>>>>> formation
    foreach ($_POST['qty'] as $pid => $q) {
        $pid = (int) $pid;
        $q = max(0, (int) $q);
        if ($q === 0) {
            unset($_SESSION['cart'][$pid]);
        } else {
            $_SESSION['cart'][$pid] = $q;
        }
    }
<<<<<<< HEAD
    header('Location: panier.php?tri=' . rawurlencode($triRedirect));
    exit;
}

$triPanier = (string) ($_GET['tri'] ?? 'designation');
if (!in_array($triPanier, $allowedPanierTri, true)) {
    $triPanier = 'designation';
=======
>>>>>>> formation
}

$pp = new ProduitController();
$lines = [];
$total = 0.0;
foreach ($_SESSION['cart'] as $pid => $qte) {
    $pid = (int) $pid;
    $qte = (int) $qte;
    if ($pid <= 0 || $qte <= 0) {
        continue;
    }
    $p = $pp->getById($pid);
    if (!$p || !(int) $p['actif']) {
        unset($_SESSION['cart'][$pid]);
        continue;
    }
    $qte = min($qte, (int) $p['stock']);
    if ($qte <= 0) {
        unset($_SESSION['cart'][$pid]);
        continue;
    }
    $_SESSION['cart'][$pid] = $qte;
    $sub = (float) $p['prix_unitaire'] * $qte;
    $total += $sub;
    $lines[] = ['p' => $p, 'qte' => $qte, 'sub' => $sub];
}
<<<<<<< HEAD

if (!empty($lines)) {
    usort($lines, static function (array $a, array $b) use ($triPanier): int {
        switch ($triPanier) {
            case 'prix_asc':
                $cmp = (float) $a['p']['prix_unitaire'] <=> (float) $b['p']['prix_unitaire'];
                return $cmp !== 0 ? $cmp : strcasecmp((string) $a['p']['designation'], (string) $b['p']['designation']);
            case 'prix_desc':
                $cmp = (float) $b['p']['prix_unitaire'] <=> (float) $a['p']['prix_unitaire'];
                return $cmp !== 0 ? $cmp : strcasecmp((string) $a['p']['designation'], (string) $b['p']['designation']);
            case 'ligne_asc':
                return ($a['sub'] <=> $b['sub']) ?: strcmp((string) $a['p']['designation'], (string) $b['p']['designation']);
            case 'ligne_desc':
                return ($b['sub'] <=> $a['sub']) ?: strcmp((string) $a['p']['designation'], (string) $b['p']['designation']);
            case 'designation':
            default:
                return strcasecmp((string) $a['p']['designation'], (string) $b['p']['designation']);
        }
    });
}
=======
>>>>>>> formation
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panier — ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Panier</h1>
        <p class="fo-lead">Ajustez les quantités puis passez commande ou continuez vos achats sur le catalogue.</p>
    </header>
<<<<<<< HEAD
    <?php if (isset($_GET['csrf'])): ?>
        <p class="fo-banner fo-banner--err">Session expirée. Rechargez la page puis réessayez.</p>
    <?php endif; ?>
    <?php if (empty($lines)): ?>
        <div class="fo-empty fo-empty--cart">
=======
    <?php if (empty($lines)): ?>
        <div class="fo-empty">
>>>>>>> formation
            <p class="hint" style="margin:0 0 12px">Votre panier est vide.</p>
            <a href="catalogue.php">Voir le catalogue</a>
        </div>
    <?php else: ?>
<<<<<<< HEAD
        <form method="get" class="fo-filters fo-filters--inline" action="panier.php" aria-label="Tri du panier">
            <div class="fo-filters__field">
                <label for="pan-tri">Trier l’affichage</label>
                <select name="tri" id="pan-tri" onchange="this.form.submit()">
                    <option value="designation" <?= $triPanier === 'designation' ? 'selected' : '' ?>>Produit (A–Z)</option>
                    <option value="prix_asc" <?= $triPanier === 'prix_asc' ? 'selected' : '' ?>>Prix unitaire ↑</option>
                    <option value="prix_desc" <?= $triPanier === 'prix_desc' ? 'selected' : '' ?>>Prix unitaire ↓</option>
                    <option value="ligne_desc" <?= $triPanier === 'ligne_desc' ? 'selected' : '' ?>>Sous-total (plus cher d’abord)</option>
                    <option value="ligne_asc" <?= $triPanier === 'ligne_asc' ? 'selected' : '' ?>>Sous-total (moins cher d’abord)</option>
                </select>
            </div>
        </form>
        <form method="post" class="fo-form-card fo-form-card--wide" novalidate data-validate="panier-form" action="panier.php?tri=<?= rawurlencode($triPanier) ?>">
            <input type="hidden" name="sort_tri" value="<?= htmlspecialchars($triPanier) ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $_SESSION['csrf_token']) ?>">
=======
        <form method="post" class="fo-form-card" novalidate data-validate="panier-form" style="max-width:none">
>>>>>>> formation
            <div class="fo-table-wrap">
            <table class="table-modern">
                <thead><tr><th>Produit</th><th>Prix unitaire</th><th>Qté</th><th>Sous-total</th></tr></thead>
                <tbody>
                <?php foreach ($lines as $row): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($row['p']['designation']) ?></strong><br><span class="hint"><?= htmlspecialchars($row['p']['reference']) ?></span></td>
                        <td><?= number_format((float) $row['p']['prix_unitaire'], 3, ',', ' ') ?> TND</td>
<<<<<<< HEAD
                        <td><input type="text" name="qty[<?= (int) $row['p']['idproduit'] ?>]" data-min="0" data-max="<?= (int) $row['p']['stock'] ?>" inputmode="numeric" value="<?= (int) $row['qte'] ?>" style="width:88px"></td>
=======
                        <td><input type="number" name="qty[<?= (int) $row['p']['idproduit'] ?>]" min="0" max="<?= (int) $row['p']['stock'] ?>" value="<?= (int) $row['qte'] ?>" style="width:88px"></td>
>>>>>>> formation
                        <td><strong><?= number_format($row['sub'], 3, ',', ' ') ?> TND</strong></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
<<<<<<< HEAD
            <div class="fo-cart-summary">
                Sous-total articles : <?= number_format($total, 3, ',', ' ') ?> TND<br>
                Livraison : 0,000 TND (offerte)<br>
                Total à payer : <?= number_format($total, 3, ',', ' ') ?> TND
            </div>
            <div class="fo-actions fo-actions--cart">
                <button type="submit" class="fo-btn fo-btn--secondary">Mettre à jour le panier</button>
                <a href="checkout.php" class="fo-btn fo-btn--primary">Commander</a>
                <a href="catalogue.php" class="fo-link-soft">Continuer les achats</a>
=======
            <div class="fo-cart-summary">Total : <?= number_format($total, 3, ',', ' ') ?> TND</div>
            <div class="fo-actions">
                <button type="submit" class="fo-btn fo-btn--secondary">Mettre à jour le panier</button>
                <a href="checkout.php" class="fo-btn fo-btn--primary">Commander</a>
                <a href="catalogue.php" class="hint" style="align-self:center">Continuer les achats</a>
>>>>>>> formation
            </div>
        </form>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
<script src="../assets/forms-validation.js"></script>
</body>
</html>
