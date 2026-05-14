<?php
require_once __DIR__ . '/../../init.php';
<<<<<<< HEAD
requireLogin('Connectez-vous pour consulter ce sujet du forum.');
=======
>>>>>>> formation
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/ForumController.php';

$fc = new ForumController();
$auth = new AuthController();
<<<<<<< HEAD
$user = $auth->profile() ?: currentUser();
=======
$user = $auth->profile();
>>>>>>> formation

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$sujet = $id > 0 ? $fc->getSujet($id) : false;
if (!$sujet) {
    header('Location: forum.php');
    exit;
}

$error = '';
$ok = isset($_GET['ok']);

if ($user && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['reponse'])) {
    $uid = (int) ($user['iduser'] ?? 0);
    $res = $fc->addMessagePublic($id, $uid, (string) $_POST['reponse']);
    if ($res === true) {
        header('Location: forum_sujet.php?id=' . $id . '&ok=1');
        exit;
    }
    $error = (string) $res;
}

$msgs = $fc->listMessagesBySujet($id);
$catId = (int) $sujet['id_categorie'];
$nextLogin = 'FrontOffice/forum/forum_sujet.php?id=' . $id;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars((string) $sujet['titre']) ?> — Forum</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
    <style>
        .fo-forum-post { max-width: 800px; margin: 0 auto; }
        .fo-forum-post__item { background: var(--sf-card); border: 1px solid rgba(255,255,255,0.9); border-radius: var(--sf-radius); padding: 16px 18px; margin-bottom: 14px; box-shadow: 0 4px 6px rgba(15,23,42,0.04); }
        .fo-forum-post__meta { font-size: 0.82rem; color: var(--sf-muted); margin-bottom: 8px; }
        .fo-forum-post__body { white-space: pre-wrap; word-break: break-word; line-height: 1.55; color: var(--sf-text); }
        .fo-forum-post__img { margin-top: 12px; max-width: 100%; border-radius: 12px; border: 1px solid var(--sf-border, rgba(0,0,0,.08)); }
    </style>
</head>
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero fo-hero--tight">
        <p class="fo-eyebrow"><?= htmlspecialchars((string) $sujet['cat_titre']) ?></p>
        <h1><?= htmlspecialchars((string) $sujet['titre']) ?></h1>
        <p class="fo-lead">
            <?php if ((int) $sujet['epingle']): ?>Épinglé · <?php endif; ?>
            <?php if ((int) $sujet['verrouille']): ?>Verrouillé · <?php endif; ?>
            Dernière activité <?= htmlspecialchars((string) ($sujet['updated_at'] ?? $sujet['created_at'])) ?>
        </p>
    </header>
    <p class="fo-forum-nav">
        <a class="fo-link-back" href="forum_categorie.php?id=<?= (int) $catId ?>">← <?= htmlspecialchars((string) $sujet['cat_titre']) ?></a>
        &nbsp;·&nbsp; <a class="fo-link-back" style="font-weight:500" href="forum.php">Forum</a>
    </p>

    <div class="fo-forum-post">
        <?php foreach ($msgs as $m): ?>
            <article class="fo-forum-post__item">
                <div class="fo-forum-post__meta">
                    <strong><?= htmlspecialchars(trim(($m['prenom'] ?? '') . ' ' . ($m['nom'] ?? ''))) ?></strong>
                    · <?= htmlspecialchars((string) $m['created_at']) ?>
                </div>
                <div class="fo-forum-post__body"><?= strlen(trim((string) $m['contenu'])) ? htmlspecialchars((string) $m['contenu']) : '' ?></div>
                <?php if (!empty($m['image_fichier'])): ?>
                    <p style="margin:0"><img class="fo-forum-post__img" src="../<?= htmlspecialchars((string) $m['image_fichier'], ENT_QUOTES) ?>" alt=""></p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="fo-form-card" style="max-width:800px;margin:0 auto">
        <h2 class="fo-event-aside__title" style="border:0;padding:0">Répondre</h2>
        <?php if ($ok): ?>
            <p class="fo-banner fo-banner--ok" role="status">Message publié.</p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="fo-banner fo-banner--err" role="alert"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if ((int) ($sujet['verrouille'] ?? 0) === 1): ?>
            <p class="hint" style="margin:0">Ce sujet est verrouillé. Plus de nouvelles réponses.</p>
        <?php elseif ($user): ?>
            <form method="post" enctype="multipart/form-data" action="forum_sujet.php?id=<?= (int) $id ?>" novalidate data-validate="forum-reply-form">
                <div>
                    <label for="reponse" style="display:block;font-size:0.8rem;font-weight:700;margin-bottom:6px;color:var(--sf-muted)">Votre message</label>
                    <textarea name="reponse" id="reponse" rows="5" style="width:100%;box-sizing:border-box;border-radius:12px;border:1px solid var(--sf-border);padding:12px;font:inherit" placeholder="Texte, ou seulement une image ci-dessous"><?= isset($_POST['reponse']) ? htmlspecialchars((string) $_POST['reponse']) : '' ?></textarea>
                </div>
                <div style="margin-top:12px">
                    <label for="photo" style="display:block;font-size:0.8rem;font-weight:700;margin-bottom:6px;color:var(--sf-muted)">Photo (optionnel) — JPEG, PNG, GIF, WebP, max. 2 Mo</label>
                    <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/gif,image/webp" style="max-width:100%;font-size:0.9rem">
                </div>
                <button type="submit" class="fo-btn fo-btn--primary fo-btn--block" style="margin-top:12px">Publier</button>
            </form>
        <?php else: ?>
            <p class="hint" style="margin:0 0 12px">Connectez-vous pour participer.</p>
            <a class="fo-btn fo-btn--primary" href="../login.php?next=<?= urlencode($nextLogin) ?>">Se connecter</a>
        <?php endif; ?>
    </div>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
<script src="../assets/forms-validation.js"></script>
</body>
</html>
