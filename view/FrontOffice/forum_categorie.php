<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controller/ForumController.php';

$fc = new ForumController();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$cat = $id > 0 ? $fc->getCategory($id) : false;
if (!$cat) {
    header('Location: forum.php');
    exit;
}
$sujets = $fc->listSujets($id, 'created_at', 'desc');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars((string) $cat['titre']) ?> — Forum</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
    <style>
        .fo-forum-list { max-width: 900px; margin: 0 auto; }
        .fo-forum-list__row { display: block; padding: 14px 16px; border-bottom: 1px solid var(--sf-border); text-decoration: none; color: inherit; border-radius: 0; }
        .fo-forum-list__row:hover { background: rgba(6, 182, 212, 0.06); }
        .fo-forum-list__row--pinned { background: linear-gradient(90deg, rgba(6, 182, 212, 0.08), transparent); }
        .fo-forum-list__h { font-weight: 800; font-size: 0.95rem; margin: 0 0 4px; color: var(--sf-text); }
        .fo-forum-list__meta { font-size: 0.82rem; color: var(--sf-muted); }
    </style>
</head>
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero fo-hero--tight">
        <p class="fo-eyebrow">Catégorie</p>
        <h1><?= htmlspecialchars((string) $cat['titre']) ?></h1>
        <p class="fo-lead"><?= !empty($cat['description']) ? nl2br(htmlspecialchars((string) $cat['description'])) : 'Sujets de discussion.' ?></p>
    </header>
    <p class="fo-forum-nav"><a class="fo-link-back" href="forum.php">← Toutes les catégories</a></p>

    <div class="fo-form-card fo-forum-list">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:8px">
            <h2 class="fo-forum-cat__title" style="margin:0;font-size:1rem">Sujets</h2>
            <a class="fo-btn fo-btn--primary" href="forum_nouveau_sujet.php?cat=<?= (int) $id ?>">Nouveau sujet</a>
        </div>
        <?php foreach ($sujets as $s):
            $sid = (int) $s['id_sujet'];
            $pin = (int) ($s['epingle'] ?? 0);
        ?>
            <a class="fo-forum-list__row<?= $pin ? ' fo-forum-list__row--pinned' : '' ?>" href="forum_sujet.php?id=<?= $sid ?>">
                <p class="fo-forum-list__h"><?= $pin ? '📌 ' : '' ?><?= htmlspecialchars((string) $s['titre']) ?></p>
                <p class="fo-forum-list__meta">
                    <?= htmlspecialchars(trim(($s['prenom'] ?? '') . ' ' . ($s['nom'] ?? ''))) ?>
                    · <?= htmlspecialchars((string) $s['created_at']) ?>
                    <?php if ((int)($s['verrouille'] ?? 0)): ?> · <span>Verrouillé</span><?php endif; ?>
                </p>
            </a>
        <?php endforeach; ?>
        <?php if (empty($sujets)): ?>
            <p class="fo-meta" style="padding:20px 16px;margin:0">Aucun sujet. <a href="forum_nouveau_sujet.php?cat=<?= (int) $id ?>">Créer le premier</a></p>
        <?php endif; ?>
    </div>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
