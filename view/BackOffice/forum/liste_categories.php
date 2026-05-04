<?php
require_once __DIR__ . '/forum_bootstrap.php';
require_once __DIR__ . '/../../../controller/ForumController.php';
require_once __DIR__ . '/../_layout/paths.php';

$fc = new ForumController();
if (empty($_SESSION['forum_csrf'])) {
    $_SESSION['forum_csrf'] = bin2hex(random_bytes(16));
}
$err = '';
$ok = isset($_GET['ok']);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['add_cat'])) {
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

if (isset($_GET['delete'], $_GET['token']) && (string) $_GET['token'] === ($_SESSION['forum_csrf'] ?? '')) {
    $fc->deleteCategory((int) $_GET['delete']);
    header('Location: liste_categories.php?ok=1');
    exit;
}

$liste = $fc->listCategories();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Forum — catégories</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(bo_url('commerce.css')) ?>">
</head>
<body>
<?php require_once __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Catégories du forum</div>
        <div class="actions">
            <a href="<?= htmlspecialchars(bo_url('forum/forum_index.php')) ?>" class="btn btn-secondary">← Forum</a>
        </div>
    </div>
    <div class="card" style="max-width: 900px; margin: 0 auto 20px;">
        <h2 style="margin-top:0;font-size:1.1rem">Ajouter une catégorie</h2>
        <?php if ($err): ?><p class="error" style="color:#b91c1c"><?= htmlspecialchars($err) ?></p><?php endif; ?>
        <form method="post" class="form-grid" style="display:grid;gap:10px;grid-template-columns:1fr 1fr auto;align-items:end">
            <div>
                <label for="titre">Titre</label>
                <input type="text" name="titre" id="titre" required maxlength="200" style="width:100%;box-sizing:border-box">
            </div>
            <div>
                <label for="ordre">Ordre d’affichage</label>
                <input type="number" name="ordre" id="ordre" value="0" style="width:100%;box-sizing:border-box">
            </div>
            <div>
                <button type="submit" name="add_cat" class="btn btn-primary">Ajouter</button>
            </div>
            <div style="grid-column:1/-1">
                <label for="description">Description (optionnel)</label>
                <textarea name="description" id="description" rows="2" style="width:100%;box-sizing:border-box"></textarea>
            </div>
        </form>
    </div>
    <?php if ($ok): ?>
        <p class="alert" style="max-width:900px;margin:0 auto 16px;padding:10px 14px;border-radius:8px;background:#ecfdf5;color:#047857;font-weight:600">Enregistré.</p>
    <?php endif; ?>
    <div class="card" style="max-width:900px;margin:0 auto">
        <h2 style="margin-top:0;font-size:1.1rem">Liste</h2>
        <div style="overflow-x:auto">
            <table class="table-modern" style="width:100%;border-collapse:collapse">
                <thead>
                <tr>
                    <th>Ordre</th>
                    <th>Titre</th>
                    <th>Description</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($liste as $c): ?>
                    <tr>
                        <td><?= (int) $c['ordre'] ?></td>
                        <td><strong><?= htmlspecialchars((string) $c['titre']) ?></strong></td>
                        <td style="max-width:280px;overflow:hidden;text-overflow:ellipsis"><?= nl2br(htmlspecialchars((string) ($c['description'] ?? ''))) ?></td>
                        <td>
                            <a class="btn btn-sm btn-secondary" href="<?= htmlspecialchars(bo_url('forum/modifier_categorie.php?id=' . (int) $c['id_categorie'])) ?>">Modifier</a>
                            <a class="btn btn-sm btn-danger" href="<?= htmlspecialchars(bo_url('forum/liste_categories.php?delete=' . (int) $c['id_categorie'] . '&token=' . urlencode((string) $_SESSION['forum_csrf']))) ?>" onclick="return confirm('Supprimer cette catégorie et tous les sujets associés ?');">Suppr.</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (empty($liste)): ?>
            <p style="color:#64748b">Aucune catégorie. Ajoutez-en une ci-dessus.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
