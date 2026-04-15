<?php
require_once __DIR__ . '/../../controller/AuthController.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$auth = new AuthController();
$user = $auth->profile();
if (!$user || strtolower($user['type'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require_once __DIR__ . '/../../controller/ProduitP.php';
$pp = new ProduitP();
$id = (int) ($_GET['id'] ?? 0);
$prod = $id ? $pp->getById($id) : null;
if (!$prod) {
    header('Location: listProduits.php');
    exit;
}
$vendeurs = $pp->listVendeursForSelect();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reference = trim($_POST['reference'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = str_replace(',', '.', trim($_POST['prix_unitaire'] ?? '0'));
    $stock = (int) ($_POST['stock'] ?? 0);
    $id_vendeur = (int) ($_POST['id_vendeur'] ?? 0);
    $actif = isset($_POST['actif']) ? 1 : 0;

    if ($reference === '' || $designation === '' || $id_vendeur <= 0) {
        $error = 'Champs obligatoires manquants.';
    } elseif (!is_numeric($prix) || (float) $prix < 0) {
        $error = 'Prix invalide.';
    } else {
        try {
            $pp->update($id, [
                'reference' => $reference,
                'designation' => $designation,
                'description' => $description !== '' ? $description : null,
                'prix_unitaire' => (float) $prix,
                'stock' => $stock,
                'id_vendeur' => $id_vendeur,
                'actif' => $actif,
            ]);
            header('Location: listProduits.php');
            exit;
        } catch (Exception $e) {
            $error = 'Erreur à l’enregistrement (référence dupliquée ?).';
        }
    }
    $prod = array_merge($prod, [
        'reference' => $_POST['reference'] ?? $prod['reference'],
        'designation' => $_POST['designation'] ?? $prod['designation'],
        'description' => $_POST['description'] ?? $prod['description'],
        'prix_unitaire' => $_POST['prix_unitaire'] ?? $prod['prix_unitaire'],
        'stock' => $stock,
        'id_vendeur' => $id_vendeur,
        'actif' => $actif,
    ]);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier produit</title>
    <link rel="stylesheet" href="sidebar.css">
    <style>.form-box{max-width:520px;background:#fff;padding:22px;border-radius:10px;box-shadow:0 6px 18px rgba(35,47,86,0.06)} label{display:block;margin-top:12px;font-weight:600} input,select,textarea{width:100%;padding:10px;margin-top:6px;box-sizing:border-box}</style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="content">
    <div class="topbar">
        <div class="page-title">Modifier produit #<?= $id ?></div>
        <a href="listProduits.php" class="btn btn-secondary">Retour</a>
    </div>
    <div class="form-box">
        <?php if ($error): ?><p style="color:#c0392b"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="post" novalidate data-validate="produit-form">
            <label>Référence *</label>
            <input type="text" name="reference" required value="<?= htmlspecialchars($prod['reference']) ?>">
            <label>Désignation *</label>
            <input type="text" name="designation" required value="<?= htmlspecialchars($prod['designation']) ?>">
            <label>Description</label>
            <textarea name="description" rows="3"><?= htmlspecialchars((string) ($prod['description'] ?? '')) ?></textarea>
            <label>Prix unitaire (TND) *</label>
            <input type="text" name="prix_unitaire" required value="<?= htmlspecialchars((string) $prod['prix_unitaire']) ?>">
            <label>Stock *</label>
            <input type="number" name="stock" min="0" required value="<?= (int) $prod['stock'] ?>">
            <label>Vendeur *</label>
            <select name="id_vendeur" required>
                <?php foreach ($vendeurs as $v): ?>
                    <option value="<?= (int) $v['iduser'] ?>" <?= (int) $prod['id_vendeur'] === (int) $v['iduser'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($v['prenom'] . ' ' . $v['nom'] . ' — ' . ($v['type'] ?? '') . ' (' . ($v['email'] ?? '') . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label style="display:flex;align-items:center;gap:8px;margin-top:16px">
                <input type="checkbox" name="actif" value="1" <?= (int) $prod['actif'] ? 'checked' : '' ?>> Actif
            </label>
            <button type="submit" class="btn btn-primary" style="margin-top:18px">Mettre à jour</button>
        </form>
    </div>
</div>
</body>
</html>
