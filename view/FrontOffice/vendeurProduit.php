<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/ProduitController.php';

$auth = new AuthController();
$u = $auth->profile();
if (!$u || strtolower($u['type'] ?? '') !== 'entrepreneur') {
    header('Location: ../login.php');
    exit;
}

$pp = new ProduitController();
$categories = $pp->listCategories();
$vid = (int) $u['iduser'];
$id = (int) ($_GET['id'] ?? 0);
$prod = $id ? $pp->getById($id) : null;
if ($id && (!$prod || (int) $prod['id_vendeur'] !== $vid)) {
    header('Location: mesProduits.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $photo = $prod['photo'] ?? null;
    $validated = $pp->validateProduitPayload(array_merge($_POST, ['id_vendeur' => $vid]), $categories);
    $stockRaw = trim((string) ($_POST['stock'] ?? '0'));
    $stock = ctype_digit($stockRaw) ? (int) $stockRaw : -1;

    if ($validated['error'] !== null) {
        $error = $validated['error'];
    } else {
        $d = $validated['data'];
        $d['id_vendeur'] = $vid;
        $currentPhoto = $prod['photo'] ?? null;
        $photo = $currentPhoto;
        try {
            if (isset($_POST['remove_photo'])) {
                $photo = null;
                $pp->deletePhotoFile($currentPhoto);
            }
            $photo = $pp->savePhotoUpload($_FILES['photo'] ?? [], $photo);
            $payload = array_merge($d, ['photo' => $photo]);
            if ($prod) {
                $pp->update($id, $payload);
            } else {
                $pp->add($payload);
            }
            header('Location: mesProduits.php');
            exit;
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
    $prod = $prod ?: [];
    $prod = array_merge($prod, [
        'reference' => $_POST['reference'] ?? ($prod['reference'] ?? ''),
        'designation' => $_POST['designation'] ?? ($prod['designation'] ?? ''),
        'description' => $_POST['description'] ?? ($prod['description'] ?? ''),
        'prix_unitaire' => $_POST['prix_unitaire'] ?? ($prod['prix_unitaire'] ?? '0'),
        'stock' => $stock,
        'idcategorie' => (int) ($_POST['idcategorie'] ?? ($prod['idcategorie'] ?? 1)),
        'actif' => isset($_POST['actif']) ? 1 : 0,
        'photo' => $photo,
    ]);
} elseif (!$prod) {
    $prod = [
        'reference' => '',
        'designation' => '',
        'description' => '',
        'prix_unitaire' => '0',
        'stock' => 0,
        'idcategorie' => 1,
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
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1><?= $id ? 'Modifier le produit' : 'Nouveau produit' ?></h1>
        <p class="fo-lead">Référence unique, prix en TND, visibilité sur la boutique.</p>
    </header>
    <?php if ($error): ?>
        <p class="fo-banner fo-banner--err"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="fo-form-card" novalidate data-validate="produit-form">
        <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
        <label>Référence *</label>
        <input type="text" name="reference" value="<?= htmlspecialchars((string) ($prod['reference'] ?? '')) ?>">
        <label>Désignation *</label>
        <input type="text" name="designation" value="<?= htmlspecialchars((string) ($prod['designation'] ?? '')) ?>">
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
        <input type="text" name="prix_unitaire" value="<?= htmlspecialchars((string) ($prod['prix_unitaire'] ?? '0')) ?>">
        <label>Stock *</label>
        <input type="text" name="stock" inputmode="numeric" value="<?= (int) ($prod['stock'] ?? 0) ?>">
        <label>Photo produit</label>
        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp,image/gif">
        <?php if (!empty($prod['photo'])): ?>
            <div style="margin-top:8px">
                <img src="../<?= htmlspecialchars((string) $prod['photo']) ?>" alt="Photo produit" style="width:88px;height:88px;object-fit:cover;border-radius:12px;border:1px solid #d8dee9">
            </div>
            <label class="fo-form-check" style="margin-top:8px">
                <input type="checkbox" name="remove_photo" value="1">
                <span>Supprimer la photo actuelle</span>
            </label>
        <?php endif; ?>
        <label class="fo-form-check">
            <input type="checkbox" name="actif" value="1" <?= !empty($prod['actif']) ? 'checked' : '' ?>>
            <span>Visible sur le catalogue</span>
        </label>
        <div class="fo-actions" style="margin-top:20px">
            <button type="submit" class="fo-btn fo-btn--primary">Enregistrer</button>
            <a href="mesProduits.php" class="fo-btn fo-btn--secondary" style="text-decoration:none">Annuler</a>
        </div>
    </form>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
<script src="../assets/forms-validation.js"></script>
</body>
</html>
