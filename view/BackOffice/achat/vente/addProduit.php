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
        $error = 'Référence, désignation et vendeur sont obligatoires.';
    } elseif (!is_numeric($prix) || (float) $prix < 0) {
        $error = 'Prix invalide.';
    } else {
        try {
            $pp->add([
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
            $error = 'Erreur : référence peut-être déjà utilisée, ou vendeur invalide.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un produit</title>
    <style>.form-box{max-width:520px;background:#fff;padding:22px;border-radius:10px;box-shadow:0 6px 18px rgba(35,47,86,0.06)} label{display:block;margin-top:12px;font-weight:600} input,select,textarea{width:100%;padding:10px;margin-top:6px;box-sizing:border-box}</style>
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
<div class="content">
    <div class="topbar">
        <div class="page-title">Nouveau produit</div>
        <a href="listProduits.php" class="btn btn-secondary">Retour liste</a>
    </div>
    <div class="form-box">
        <?php if ($error): ?><p style="color:#c0392b"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <?php if (empty($vendeurs)): ?>
            <p>Aucun utilisateur en base.</p>
        <?php else: ?>
        <form method="post" novalidate data-validate="produit-form">
            <label>Référence *</label>
            <input type="text" name="reference" required value="<?= htmlspecialchars($_POST['reference'] ?? '') ?>">
            <label>Désignation *</label>
            <input type="text" name="designation" required value="<?= htmlspecialchars($_POST['designation'] ?? '') ?>">
            <label>Description</label>
            <textarea name="description" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            <label>Prix unitaire (TND) *</label>
            <input type="text" name="prix_unitaire" required value="<?= htmlspecialchars($_POST['prix_unitaire'] ?? '0') ?>">
            <label>Stock *</label>
            <input type="number" name="stock" min="0" required value="<?= htmlspecialchars($_POST['stock'] ?? '0') ?>">
            <label>Vendeur *</label>
            <select name="id_vendeur" required>
                <option value="">— Choisir —</option>
                <?php foreach ($vendeurs as $v): ?>
                    <option value="<?= (int) $v['iduser'] ?>" <?= (string)($_POST['id_vendeur'] ?? '') === (string)$v['iduser'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($v['prenom'] . ' ' . $v['nom'] . ' — ' . $v['type'] . ' (' . $v['email'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label style="display:flex;align-items:center;gap:8px;margin-top:16px">
                <input type="checkbox" name="actif" value="1" <?= !isset($_POST['reference']) || isset($_POST['actif']) ? 'checked' : '' ?>> Visible catalogue (actif)
            </label>
            <button type="submit" class="btn btn-primary" style="margin-top:18px">Enregistrer</button>
        </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
