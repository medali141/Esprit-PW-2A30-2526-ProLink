<?php
require_once __DIR__ . '/../../init.php';
requireLogin('Connectez-vous pour consulter ce projet.');
require_once __DIR__ . '/../../controller/ProjectP.php';
require_once __DIR__ . '/../../controller/UserP.php';
$pp = new ProjectP();
$up = new UserP();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pr = $id ? $pp->get($id) : null;
if (!$pr) {
    header('Location: projects.php');
    exit;
}
$owner = $pr['owner_id'] ? $up->showUser((int)$pr['owner_id']) : null;
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pr['title'] ?? 'Projet') ?> — ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <article class="fo-form-card fo-content-card">
        <h1><?= htmlspecialchars($pr['title']) ?></h1>
        <div class="fo-meta">Par <strong><?= htmlspecialchars(trim(($owner['prenom'] ?? '') . ' ' . ($owner['nom'] ?? ''))) ?></strong> · <?= htmlspecialchars($pr['status']) ?></div>
        <?php if (!empty($pr['description'])): ?>
            <div class="fo-body"><?= nl2br(htmlspecialchars($pr['description'])) ?></div>
        <?php else: ?>
            <p class="hint">Aucune description fournie.</p>
        <?php endif; ?>
        <p><a href="projects.php">← Retour à la liste</a></p>
    </article>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
