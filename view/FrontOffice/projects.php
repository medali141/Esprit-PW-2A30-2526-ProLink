<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../../controller/ProjectP.php';
require_once __DIR__ . '/../../controller/UserP.php';
$pp = new ProjectP();
$projects = $pp->listAll();
$up = new UserP();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Projets — ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Projets</h1>
        <p class="fo-lead">Découvrez les projets partagés par la communauté.</p>
    </header>

    <?php if (empty($projects)): ?>
        <div class="fo-empty">
            <p class="hint">Aucun projet pour le moment. Revenez plus tard ou connectez-vous pour en proposer.</p>
            <a href="home.php">Retour à l’accueil</a>
        </div>
    <?php else: ?>
        <div class="fo-product-grid">
            <?php foreach ($projects as $pr):
                $owner = $pr['owner_id'] ? $up->showUser((int)$pr['owner_id']) : null;
                $ownerName = $owner ? trim(($owner['prenom'] ?? '') . ' ' . ($owner['nom'] ?? '')) : '—';
                $short = isset($pr['description']) ? (strlen($pr['description']) > 140 ? substr($pr['description'],0,137) . '…' : $pr['description']) : '';
            ?>
            <article class="fo-product-card">
                <h2><?= htmlspecialchars($pr['title']) ?></h2>
                <div class="fo-meta">Par <strong><?= htmlspecialchars($ownerName) ?></strong> · <?= htmlspecialchars($pr['status']) ?></div>
                <?php if ($short): ?><p class="fo-desc"><?= nl2br(htmlspecialchars($short)) ?></p><?php endif; ?>
                <a class="fo-btn fo-btn--primary" href="project.php?id=<?= (int)$pr['idproject'] ?>">Voir le projet</a>
            </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
