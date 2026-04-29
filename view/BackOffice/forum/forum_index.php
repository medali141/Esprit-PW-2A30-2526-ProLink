<?php
require_once __DIR__ . '/forum_bootstrap.php';
require_once __DIR__ . '/../../../controller/ForumController.php';
require_once __DIR__ . '/../_layout/paths.php';

$fc = new ForumController();
$nCat = $fc->countCategories();
$nSuj = $fc->countSujets();
$nMsg = $fc->countMessages();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forum — Administration</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(bo_url('commerce.css')) ?>">
</head>
<body>
<?php require_once __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Gestion du forum</div>
    </div>
    <div class="container" style="max-width:980px">
        <div class="commerce-hub-intro">
            <h1>Modération &amp; structure</h1>
            <p>Catégories, sujets et messages. Les suppressions de sujets effacent aussi les réponses (cascade).</p>
        </div>
        <div class="commerce-hub-grid">
            <article class="commerce-hub-card commerce-hub-card--products">
                <div class="hub-icon" aria-hidden="true">📁</div>
                <h2>Catégories</h2>
                <div class="commerce-hub-stat"><?= (int) $nCat ?></div>
                <p class="hub-hint">Rubriques du forum</p>
                <div class="commerce-hub-actions">
                    <a class="btn btn-primary" href="<?= htmlspecialchars(bo_url('forum/liste_categories.php')) ?>">Liste &amp; édition</a>
                    <a class="btn btn-secondary" href="<?= htmlspecialchars(bo_url('forum/ajout_categorie.php')) ?>">+ Ajouter</a>
                </div>
            </article>
            <article class="commerce-hub-card commerce-hub-card--orders">
                <div class="hub-icon" aria-hidden="true">💬</div>
                <h2>Sujets</h2>
                <div class="commerce-hub-stat"><?= (int) $nSuj ?></div>
                <p class="hub-hint">Épinglage, verrou, suppression</p>
                <div class="commerce-hub-actions">
                    <a class="btn btn-primary" href="<?= htmlspecialchars(bo_url('forum/liste_sujets.php')) ?>">Liste des sujets</a>
                    <a class="btn btn-secondary" href="<?= htmlspecialchars(bo_url('forum/ajout_sujet.php')) ?>">+ Nouveau sujet</a>
                </div>
            </article>
            <article class="commerce-hub-card commerce-hub-card--products" style="grid-column: 1 / -1; max-width: 100%;">
                <div class="hub-icon" aria-hidden="true">📝</div>
                <h2>Messages</h2>
                <div class="commerce-hub-stat"><?= (int) $nMsg ?></div>
                <p class="hub-hint">Modérez réponse par réponse depuis la fiche sujet (au moins un message reste par sujet).</p>
                <div class="commerce-hub-actions">
                    <a class="btn btn-secondary" href="<?= htmlspecialchars(bo_url('forum/liste_sujets.php')) ?>">Ouvrir un sujet…</a>
                </div>
            </article>
        </div>
    </div>
</div>
</body>
</html>
