<?php
require_once __DIR__ . '/../../init.php';
requireLogin('Connectez-vous pour accéder aux formations.');
require_once __DIR__ . '/../../controller/FormationP.php';
$fp = new FormationP();
$list = $fp->listAll();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formations — ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Formations</h1>
        <p class="fo-lead">Parcourez et inscrivez-vous aux prochaines formations.</p>
    </header>

    <?php if (empty($list)): ?>
        <div class="fo-empty">
            <p class="hint">Aucune formation programmée pour le moment.</p>
            <a href="home.php">Retour à l’accueil</a>
        </div>
    <?php else: ?>
        <div class="fo-event-grid">
            <?php foreach ($list as $f): ?>
                <article class="fo-event-card">
                    <div class="fo-event-card__head">
                        <h3 class="fo-event-title"><?= htmlspecialchars($f['titre']) ?></h3>
                    </div>
                    <div class="fo-event-excerpt"><?= nl2br(htmlspecialchars(substr($f['description'] ?? '', 0, 240))) ?></div>
                    <div class="fo-event-actions">
                        <a class="fo-btn fo-btn--primary" href="formation_detail.php?id=<?= (int)$f['id_formation'] ?>">Voir / S'inscrire</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
