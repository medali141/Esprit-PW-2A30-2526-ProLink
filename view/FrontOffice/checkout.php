<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/ProduitP.php';
require_once __DIR__ . '/../../controller/CommandeP.php';

$auth = new AuthController();
$u = $auth->profile();
if (!$u) {
    header('Location: ../login.php');
    exit;
}

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
            $cmdP = new CommandeP();
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

$pp = new ProduitP();
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
    <link rel="stylesheet" href="../assets/style.css">
    <style>label{display:block;margin-top:12px;font-weight:600} input,textarea{width:100%;max-width:480px;padding:10px;margin-top:6px}</style>
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container">
    <h1>Valider la commande</h1>
    <p class="hint">Montant estimé : <strong><?= number_format($total, 3, ',', ' ') ?> TND</strong> (statut « en attente de paiement » jusqu’à traitement admin).</p>
    <?php if ($error): ?><p style="color:#b00020"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post" class="card" style="margin-top:16px;max-width:560px" novalidate data-validate="checkout-form">
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
        <button type="submit" style="margin-top:16px">Confirmer la commande</button>
        <a href="panier.php" class="hint" style="margin-left:14px">Retour panier</a>
    </form>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
<script src="../assets/forms-validation.js"></script>
</body>
</html>
