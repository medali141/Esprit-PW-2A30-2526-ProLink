<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/ProduitP.php';

$auth = new AuthController();
$u = $auth->profile();
if (!$u || strtolower($u['type'] ?? '') !== 'entrepreneur') {
    header('Location: ../login.php');
    exit;
}

$pp = new ProduitP();
$vid = (int) $u['iduser'];
$id = (int) ($_GET['id'] ?? 0);
$prod = $id ? $pp->getById($id) : null;
if ($id && (!$prod || (int) $prod['id_vendeur'] !== $vid)) {
    header('Location: mesProduits.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reference = trim($_POST['reference'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = str_replace(',', '.', trim($_POST['prix_unitaire'] ?? '0'));
    $stock = (int) ($_POST['stock'] ?? 0);
    $actif = isset($_POST['actif']) ? 1 : 0;
    if ($reference === '' || $designation === '') {
        $error = 'Référence et désignation obligatoires.';
    } elseif (!is_numeric($prix) || (float) $prix < 0) {
        $error = 'Prix invalide.';
    } else {
        try {
            $data = [
                'reference' => $reference,
                'designation' => $designation,
                'description' => $description !== '' ? $description : null,
                'prix_unitaire' => (float) $prix,
                'stock' => $stock,
                'id_vendeur' => $vid,
                'actif' => $actif,
            ];
            if ($prod) {
                $pp->update($id, $data);
            } else {
                $pp->add($data);
            }
            header('Location: mesProduits.php');
            exit;
        } catch (Exception $e) {
            $error = 'Erreur (référence déjà utilisée ?).';
        }
    }
    $prod = $prod ?: [];
    $prod = array_merge($prod, [
        'reference' => $reference,
        'designation' => $designation,
        'description' => $description,
        'prix_unitaire' => $prix,
        'stock' => $stock,
        'actif' => $actif,
    ]);
} elseif (!$prod) {
    $prod = [
        'reference' => '',
        'designation' => '',
        'description' => '',
        'prix_unitaire' => '0',
        'stock' => 0,
        'actif' => 1,
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $id ? 'Modifier' : 'Nouveau' ?> produit</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>label{display:block;margin-top:12px;font-weight:600} input,textarea{width:100%;max-width:480px;padding:10px;margin-top:6px}</style>
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container">
    <h1><?= $id ? 'Modifier le produit' : 'Nouveau produit' ?></h1>
    <?php if ($error): ?><p style="color:#b00020"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post" class="card" style="margin-top:12px;max-width:560px" novalidate data-validate="produit-form">
        <label>Référence *</label>
        <input type="text" name="reference" required value="<?= htmlspecialchars((string) ($prod['reference'] ?? '')) ?>">
        <label>Désignation *</label>
        <input type="text" name="designation" required value="<?= htmlspecialchars((string) ($prod['designation'] ?? '')) ?>">
        <label>Description</label>
        <textarea name="description" rows="3"><?= htmlspecialchars((string) ($prod['description'] ?? '')) ?></textarea>
        <label>Prix unitaire (TND) *</label>
        <input type="text" name="prix_unitaire" required value="<?= htmlspecialchars((string) ($prod['prix_unitaire'] ?? '0')) ?>">
        <label>Stock *</label>
        <input type="number" name="stock" min="0" required value="<?= (int) ($prod['stock'] ?? 0) ?>">
        <label style="display:flex;align-items:center;gap:8px;margin-top:14px">
            <input type="checkbox" name="actif" value="1" <?= !empty($prod['actif']) ? 'checked' : '' ?>> Visible sur le catalogue
        </label>
        <button type="submit" style="margin-top:16px">Enregistrer</button>
        <a href="mesProduits.php" class="hint" style="margin-left:12px">Annuler</a>
    </form>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
<script src="../assets/forms-validation.js"></script>
</body>
</html>
