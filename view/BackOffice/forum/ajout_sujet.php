<?php
require_once __DIR__ . '/forum_bootstrap.php';
require_once __DIR__ . '/../../../controller/ForumController.php';
require_once __DIR__ . '/../_layout/paths.php';

$fc = new ForumController();
$idUser = (int) ($__forumUser['iduser'] ?? 0);
$categories = $fc->listCategories();
$err = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $newId = $fc->createSujetWithFirstMessage(
        (int) ($_POST['id_categorie'] ?? 0),
        $idUser,
        (string) ($_POST['titre'] ?? ''),
        (string) ($_POST['contenu'] ?? '')
    );
    if ($newId !== false) {
        header('Location: ' . bo_url('forum/sujet_messages.php?id=' . $newId . '&ok=1'));
        exit;
    }
<<<<<<< HEAD
    $err = $fc->getLastPublicError() ?: 'Remplissez tous les champs et choisissez une catégorie.';
=======
    $err = 'Remplissez tous les champs et choisissez une catégorie.';
>>>>>>> formation
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouveau sujet (admin)</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(bo_url('commerce.css')) ?>">
</head>
<body>
<?php require_once __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Nouveau sujet</div>
        <div class="actions">
            <a href="<?= htmlspecialchars(bo_url('forum/liste_sujets.php')) ?>" class="btn btn-secondary">← Sujets</a>
        </div>
    </div>
    <div class="card" style="max-width:640px;margin:0 auto">
        <p style="color:#64748b;font-size:0.9rem;margin-top:0">Publié au nom de votre compte admin : <strong><?= htmlspecialchars((string) ($__forumUser['email'] ?? '')) ?></strong></p>
<<<<<<< HEAD
        <?php if ($err): ?>
            <p style="color:#b91c1c;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:8px 12px;margin:0 0 12px"><?= htmlspecialchars($err) ?></p>
        <?php endif; ?>
=======
        <?php if ($err): ?><p style="color:#b91c1c"><?= htmlspecialchars($err) ?></p><?php endif; ?>
>>>>>>> formation
        <?php if (empty($categories)): ?>
            <p style="color:#b91c1c">Créez d’abord une <a href="<?= htmlspecialchars(bo_url('forum/liste_categories.php')) ?>">catégorie</a>.</p>
        <?php else: ?>
        <form method="post" style="display:grid;gap:14px">
            <div>
                <label for="id_categorie">Catégorie *</label>
                <select name="id_categorie" id="id_categorie" required style="width:100%;box-sizing:border-box">
                    <option value="">— Choisir —</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id_categorie'] ?>"><?= htmlspecialchars((string) $c['titre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="titre">Titre du sujet *</label>
                <input type="text" name="titre" id="titre" required maxlength="255" style="width:100%;box-sizing:border-box">
            </div>
            <div>
                <label for="contenu">Premier message *</label>
                <textarea name="contenu" id="contenu" rows="8" required style="width:100%;box-sizing:border-box"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Publier</button>
        </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
