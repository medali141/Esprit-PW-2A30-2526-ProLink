<?php
require_once __DIR__ . '/../../init.php';
requireLogin('Connectez-vous pour publier un nouveau sujet.');
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/ForumController.php';

$auth = new AuthController();
$user = $auth->profile() ?: currentUser();

$fc = new ForumController();
$catId = isset($_GET['cat']) ? (int) $_GET['cat'] : 0;
$cat = $catId > 0 ? $fc->getCategory($catId) : false;
if (!$cat) {
    header('Location: forum.php');
    exit;
}

$err = '';
$uid = (int) ($user['iduser'] ?? 0);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $newId = $fc->createSujetWithFirstMessage(
        $catId,
        $uid,
        (string) ($_POST['titre'] ?? ''),
        (string) ($_POST['contenu'] ?? '')
    );
    if ($newId !== false) {
        header('Location: forum_sujet.php?id=' . (int) $newId);
        exit;
    }
    $le = $fc->getLastPublicError();
    $err = $le !== '' ? $le : 'Remplissez le titre et le message (2 caractères minimum), ou ajoutez une photo.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nouveau sujet — Forum</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero fo-hero--tight">
        <p class="fo-eyebrow"><?= htmlspecialchars((string) $cat['titre']) ?></p>
        <h1>Nouveau sujet</h1>
        <p class="fo-lead">Publiez le premier message du fil de discussion.</p>
    </header>
    <p class="fo-forum-nav"><a class="fo-link-back" href="forum_categorie.php?id=<?= (int) $catId ?>">← Retour</a></p>

    <div class="fo-form-card" style="max-width:640px;margin:0 auto">
        <?php if ($err): ?><p class="fo-banner fo-banner--err"><?= htmlspecialchars($err) ?></p><?php endif; ?>
        <form method="post" enctype="multipart/form-data" novalidate data-validate="forum-new-topic-form" action="forum_nouveau_sujet.php?cat=<?= (int) $catId ?>">
            <div style="margin-bottom:14px">
                <label for="titre" style="display:block;font-size:0.8rem;font-weight:700;margin-bottom:6px;color:var(--sf-muted)">Titre *</label>
                <input type="text" name="titre" id="titre" required maxlength="255" value="<?= isset($_POST['titre']) ? htmlspecialchars((string) $_POST['titre']) : '' ?>"
                    style="width:100%;box-sizing:border-box;border-radius:12px;border:1px solid var(--sf-border);padding:12px">
            </div>
            <div>
                <label for="contenu" style="display:block;font-size:0.8rem;font-weight:700;margin-bottom:6px;color:var(--sf-muted)">Message</label>
                <textarea name="contenu" id="contenu" rows="8" style="width:100%;box-sizing:border-box;border-radius:12px;border:1px solid var(--sf-border);padding:12px;font:inherit" placeholder="Texte, ou seulement une image ci-dessous"><?= isset($_POST['contenu']) ? htmlspecialchars((string) $_POST['contenu']) : '' ?></textarea>
            </div>
            <div style="margin-top:12px">
                <label for="photo" style="display:block;font-size:0.8rem;font-weight:700;margin-bottom:6px;color:var(--sf-muted)">Photo (optionnel) — JPEG, PNG, GIF, WebP, max. 2 Mo</label>
                <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/gif,image/webp" style="max-width:100%;font-size:0.9rem">
            </div>
            <button type="submit" class="fo-btn fo-btn--primary fo-btn--block" style="margin-top:16px">Publier le sujet</button>
        </form>
    </div>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
<script src="../assets/forms-validation.js"></script>
</body>
</html>
