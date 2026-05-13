<?php
require_once __DIR__ . '/../../init.php';
requireLogin('Connectez-vous pour finaliser votre commande.');
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/ProduitController.php';
require_once __DIR__ . '/../../controller/CommandeController.php';

$auth = new AuthController();
$u = $auth->profile() ?: currentUser();

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || !array_sum($_SESSION['cart'])) {
    header('Location: panier.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adr = trim($_POST['adresse_livraison'] ?? '');
    $cp = trim($_POST['code_postal'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $pays = trim($_POST['pays'] ?? 'Tunisie');
    $notes = trim($_POST['notes'] ?? '');
    if ($adr === '' || $cp === '' || $ville === '') {
        $error = 'Adresse, code postal et ville sont obligatoires.';
    } else {
        try {
            $cmdP = new CommandeController();
            $idCmd = $cmdP->createFromCart((int) $u['iduser'], $_SESSION['cart'], [
                'adresse_livraison' => $adr,
                'code_postal' => $cp,
                'ville' => $ville,
                'pays' => $pays !== '' ? $pays : 'Tunisie',
                'notes' => $notes !== '' ? $notes : null,
            ]);
            $_SESSION['cart'] = [];
            header('Location: mesCommandes.php?new=' . $idCmd);
            exit;
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$pp = new ProduitController();
$total = 0.0;
foreach ($_SESSION['cart'] as $pid => $qte) {
    $p = $pp->getById((int) $pid);
    if ($p && (int) $p['actif']) {
        $total += (float) $p['prix_unitaire'] * (int) $qte;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Commande — ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Valider la commande</h1>
        <p class="fo-lead">Montant estimé <strong><?= number_format($total, 3, ',', ' ') ?> TND</strong> — statut « en attente de paiement » jusqu’au traitement par l’administrateur.</p>
    </header>
    <?php if ($error): ?>
        <p class="fo-banner fo-banner--err"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" class="fo-checkout-card" novalidate data-validate="checkout-form">
        <label>Adresse de livraison *</label>
        <input type="text" name="adresse_livraison" required value="<?= htmlspecialchars($_POST['adresse_livraison'] ?? '') ?>">
        <label>Code postal *</label>
        <input type="text" name="code_postal" required value="<?= htmlspecialchars($_POST['code_postal'] ?? '') ?>">
        <label>Ville *</label>
        <input type="text" name="ville" required value="<?= htmlspecialchars($_POST['ville'] ?? '') ?>">
        <label>Pays</label>
        <input type="text" name="pays" value="<?= htmlspecialchars($_POST['pays'] ?? 'Tunisie') ?>">
        <label>Notes (optionnel)</label>
        <textarea name="notes" rows="2"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
        <div class="fo-actions" style="margin-top:20px">
            <button type="submit" class="fo-btn fo-btn--primary">Confirmer la commande</button>
            <a href="panier.php" class="fo-btn fo-btn--secondary" style="text-decoration:none">Retour panier</a>
        </div>
    </form>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
<script src="../assets/forms-validation.js"></script>
</body>
</html>
