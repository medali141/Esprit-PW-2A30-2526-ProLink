<?php
<<<<<<< HEAD
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../../../controller/AuthController.php';
=======
require_once __DIR__ . '/../../../../controller/AuthController.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
>>>>>>> formation
$auth = new AuthController();
$user = $auth->profile();
if (!$user || strtolower($user['type'] ?? '') !== 'admin') {
    header('Location: ../../../login.php');
    exit;
}
require_once __DIR__ . '/../../../../controller/ProduitController.php';
$pp = new ProduitController();
$vendeurs = $pp->listVendeursForSelect();
<<<<<<< HEAD
$categories = $pp->listCategories();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $validated = $pp->validateProduitPayload($_POST, $categories);
    if ($validated['error'] !== null) {
        $error = $validated['error'];
    } else {
        $photo = null;
        try {
            $photo = $pp->savePhotoUpload($_FILES['photo'] ?? []);
            $pp->add(array_merge($validated['data'], ['photo' => $photo]));
            header('Location: listProduits.php');
            exit;
        } catch (Throwable $e) {
            $pp->deletePhotoFile($photo);
            $error = $e->getMessage();
=======
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
>>>>>>> formation
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ajouter un produit</title>
<<<<<<< HEAD
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
<link rel="stylesheet" href="../../commerce.css">
=======
    <link rel="stylesheet" href="../../commerce.css">
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
>>>>>>> formation
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Nouveau produit</div>
        <a href="listProduits.php" class="btn btn-secondary">Retour liste</a>
    </div>
    <div class="container commerce-form-shell">
        <div class="commerce-form-card">
            <h2>Nouvelle fiche produit</h2>
            <p class="form-lead">Prix en TND, stock entier, visibilité catalogue.</p>
            <?php if ($error): ?>
                <div class="commerce-alert commerce-alert--err"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (empty($vendeurs)): ?>
                <p>Aucun utilisateur en base.</p>
            <?php else: ?>
<<<<<<< HEAD
            <form method="post" enctype="multipart/form-data" novalidate data-validate="produit-form">
                <label class="field-first">Référence *</label>
                <input type="text" name="reference" value="<?= htmlspecialchars($_POST['reference'] ?? '') ?>">
                <label>Désignation *</label>
                <input type="text" name="designation" value="<?= htmlspecialchars($_POST['designation'] ?? '') ?>">
                <label>Description</label>
                <textarea name="description" rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                <label>Catégorie *</label>
                <select name="idcategorie">
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['idcategorie'] ?>" <?= (int)($_POST['idcategorie'] ?? 1) === (int) $c['idcategorie'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['libelle']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>Prix unitaire (TND) *</label>
                <input type="text" name="prix_unitaire" value="<?= htmlspecialchars($_POST['prix_unitaire'] ?? '0') ?>">
                <label>Stock *</label>
                <input type="text" name="stock" inputmode="numeric" value="<?= htmlspecialchars($_POST['stock'] ?? '0') ?>">
                <label>Photo produit</label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp,image/gif">
                <label>Vendeur *</label>
                <select name="id_vendeur">
=======
            <form method="post" novalidate data-validate="produit-form">
                <label class="field-first">Référence *</label>
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
>>>>>>> formation
                    <option value="">— Choisir —</option>
                    <?php foreach ($vendeurs as $v): ?>
                        <option value="<?= (int) $v['iduser'] ?>" <?= (string)($_POST['id_vendeur'] ?? '') === (string)$v['iduser'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($v['prenom'] . ' ' . $v['nom'] . ' — ' . $v['type'] . ' (' . $v['email'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label class="form-row-check">
                    <input type="checkbox" name="actif" value="1" <?= !isset($_POST['reference']) || isset($_POST['actif']) ? 'checked' : '' ?>>
                    <span>Visible catalogue (actif)</span>
                </label>
                <div class="btn-submit-wrap">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<<<<<<< HEAD
<script src="../../../assets/forms-validation.js"></script>
=======
>>>>>>> formation
</body>
</html>
