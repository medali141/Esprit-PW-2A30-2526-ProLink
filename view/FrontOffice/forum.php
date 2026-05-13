<?php
require_once __DIR__ . '/../../init.php';
requireLogin('Connectez-vous pour accéder au forum.');
require_once __DIR__ . '/../../controller/ForumController.php';

$fc = new ForumController();
$categories = $fc->listCategoriesWithStats();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forum — ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Forum</h1>
        <p class="fo-lead">Échangez avec la communauté ProLink : questions, annonces et discussions par thème.</p>
    </header>

    <div class="fo-product-grid fo-forum-grid">
        <?php foreach ($categories as $c):
            $id = (int) $c['id_categorie'];
            $nb = (int) ($c['nb_sujets'] ?? 0);
        ?>
            <article class="fo-product-card">
                <h2 class="fo-forum-cat__title"><?= htmlspecialchars((string) $c['titre']) ?></h2>
                <span class="fo-ref">Rubrique</span>
                <?php if (!empty($c['description'])): ?>
                    <p class="fo-desc"><?= nl2br(htmlspecialchars((string) $c['description'])) ?></p>
                <?php else: ?>
                    <p class="fo-desc" style="color:var(--sf-muted)">Aucune description.</p>
                <?php endif; ?>
                <div class="fo-price fo-forum-cat__stat"><?= $nb ?> <span class="fo-event-places__label" style="display:inline">sujet<?= $nb > 1 ? 's' : '' ?></span></div>
                <div class="fo-meta">Discussions et réponses de la communauté</div>
                <a class="fo-btn fo-btn--primary" href="forum_categorie.php?id=<?= $id ?>">Entrer</a>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (empty($categories)): ?>
        <div class="fo-empty">
            <p class="hint" style="margin:0 0 12px">Aucune catégorie pour le moment.</p>
            <a href="catalogue.php">Retour à la boutique</a>
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
