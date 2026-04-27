<?php
require_once __DIR__ . '/forum_bootstrap.php';
require_once __DIR__ . '/../../../controller/ForumController.php';
require_once __DIR__ . '/../_layout/paths.php';

$fc = new ForumController();
$err = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if ($fc->addCategory(
        (string) ($_POST['titre'] ?? ''),
        (string) ($_POST['description'] ?? ''),
        (int) ($_POST['ordre'] ?? 0)
    )) {
        header('Location: liste_categories.php?ok=1');
        exit;
    }
    $err = 'Titre requis.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvelle catégorie</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(bo_url('commerce.css')) ?>">
</head>
<body>
<?php require_once __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Nouvelle catégorie</div>
        <div class="actions">
            <a href="<?= htmlspecialchars(bo_url('forum/liste_categories.php')) ?>" class="btn btn-secondary">← Liste</a>
        </div>
    </div>
    <div class="card" style="max-width:560px;margin:0 auto">
        <?php if ($err): ?><p style="color:#b91c1c"><?= htmlspecialchars($err) ?></p><?php endif; ?>
        <form method="post" style="display:grid;gap:12px">
            <div>
                <label for="titre">Titre *</label>
                <input type="text" name="titre" id="titre" required maxlength="200" style="width:100%;box-sizing:border-box">
            </div>
            <div>
                <label for="ordre">Ordre</label>
                <input type="number" name="ordre" id="ordre" value="0" style="width:100%;box-sizing:border-box">
            </div>
            <div>
                <label for="description">Description</label>
                <textarea name="description" id="description" rows="4" style="width:100%;box-sizing:border-box"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    </div>
</div>
</body>
</html>
