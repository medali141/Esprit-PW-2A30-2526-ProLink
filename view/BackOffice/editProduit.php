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
require_once __DIR__ . '/../../controller/ProduitController.php';
$pp = new ProduitController();
$id = (int) ($_GET['id'] ?? 0);
$prod = $id ? $pp->getById($id) : null;
if (!$prod) {
    header('Location: listProduits.php');
    exit;
}
$vendeurs = $pp->listVendeursForSelect();
$categories = $pp->listCategories();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reference = trim($_POST['reference'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $prix = str_replace(',', '.', trim($_POST['prix_unitaire'] ?? '0'));
    $stockRaw = trim($_POST['stock'] ?? '0');
    $stock = ctype_digit($stockRaw) ? (int) $stockRaw : -1;
    $id_vendeur = (int) ($_POST['id_vendeur'] ?? 0);
    $idcategorie = (int) ($_POST['idcategorie'] ?? 1);
    $actif = isset($_POST['actif']) ? 1 : 0;
    $currentPhoto = $prod['photo'] ?? null;
    $photo = $currentPhoto;

    if ($reference === '' || strlen($reference) > 50 || $designation === '' || strlen($designation) > 200 || $id_vendeur <= 0) {
        $error = 'Champs obligatoires manquants.';
    } elseif (!is_numeric($prix) || (float) $prix < 0) {
        $error = 'Prix invalide.';
    } elseif ($stock < 0) {
        $error = 'Stock invalide (entier positif).';
    } else {
        try {
            if (isset($_POST['remove_photo'])) {
                $photo = null;
                $pp->deletePhotoFile($currentPhoto);
            }
            $photo = $pp->savePhotoUpload($_FILES['photo'] ?? [], $photo);
            $pp->update($id, [
                'reference' => $reference,
                'designation' => $designation,
                'description' => $description !== '' ? $description : null,
                'idcategorie' => $idcategorie,
                'prix_unitaire' => (float) $prix,
                'stock' => $stock,
                'id_vendeur' => $id_vendeur,
                'actif' => $actif,
                'photo' => $photo,
            ]);
            header('Location: listProduits.php');
            exit;
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
    $prod = array_merge($prod, [
        'reference' => $_POST['reference'] ?? $prod['reference'],
        'designation' => $_POST['designation'] ?? $prod['designation'],
        'description' => $_POST['description'] ?? $prod['description'],
        'prix_unitaire' => $_POST['prix_unitaire'] ?? $prod['prix_unitaire'],
        'stock' => $stock,
        'id_vendeur' => $id_vendeur,
        'idcategorie' => $idcategorie,
        'actif' => $actif,
        'photo' => $photo,
    ]);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier produit</title>
</head>
<body>
<?php include 'sidebar.php'; ?>
<link rel="stylesheet" href="commerce.css">
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Modifier produit #<?= $id ?></div>
        <a href="listProduits.php" class="btn btn-secondary">Retour</a>
    </div>
    <div class="container commerce-form-shell">
        <div class="commerce-form-card">
            <h2>Modifier la fiche</h2>
            <p class="form-lead">Référence unique, prix TND, vendeur responsable.</p>
            <?php if ($error): ?>
                <div class="commerce-alert commerce-alert--err"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data" novalidate data-validate="produit-form">
                <label class="field-first">Référence *</label>
                <input type="text" name="reference" value="<?= htmlspecialchars($prod['reference']) ?>">
                <label>Désignation *</label>
                <input type="text" name="designation" value="<?= htmlspecialchars($prod['designation']) ?>">
                <label>Description</label>
                <textarea name="description" rows="3"><?= htmlspecialchars((string) ($prod['description'] ?? '')) ?></textarea>
                <label>Catégorie *</label>
                <select name="idcategorie">
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['idcategorie'] ?>" <?= (int) ($prod['idcategorie'] ?? 1) === (int) $c['idcategorie'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['libelle']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>Prix unitaire (TND) *</label>
                <input type="text" name="prix_unitaire" value="<?= htmlspecialchars((string) $prod['prix_unitaire']) ?>">
                <label>Stock *</label>
                <input type="text" name="stock" inputmode="numeric" value="<?= (int) $prod['stock'] ?>">
                <label>Photo produit</label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp,image/gif">
                <?php if (!empty($prod['photo'])): ?>
                    <div style="margin-top:8px">
                        <img src="../<?= htmlspecialchars((string) $prod['photo']) ?>" alt="Photo produit" style="width:84px;height:84px;object-fit:cover;border-radius:10px;border:1px solid #d8dee9">
                    </div>
                    <label class="form-row-check" style="margin-top:8px">
                        <input type="checkbox" name="remove_photo" value="1">
                        <span>Supprimer la photo actuelle</span>
                    </label>
                <?php endif; ?>
                <label>Vendeur *</label>
                <select name="id_vendeur">
                    <?php foreach ($vendeurs as $v): ?>
                        <option value="<?= (int) $v['iduser'] ?>" <?= (int) $prod['id_vendeur'] === (int) $v['iduser'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($v['prenom'] . ' ' . $v['nom'] . ' — ' . ($v['type'] ?? '') . ' (' . ($v['email'] ?? '') . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label class="form-row-check">
                    <input type="checkbox" name="actif" value="1" <?= (int) $prod['actif'] ? 'checked' : '' ?>>
                    <span>Actif (visible catalogue)</span>
                </label>
                <div class="btn-submit-wrap">
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="../assets/forms-validation.js"></script>
</body>
</html>
