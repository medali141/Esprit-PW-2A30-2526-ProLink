<?php
require_once __DIR__ . '/forum_bootstrap.php';
require_once __DIR__ . '/../../../controller/ForumController.php';
require_once __DIR__ . '/../_layout/paths.php';

$fc = new ForumController();
if (empty($_SESSION['forum_csrf'])) {
    $_SESSION['forum_csrf'] = bin2hex(random_bytes(16));
}
<<<<<<< HEAD

$allowedSort = ['id_categorie', 'titre', 'description', 'ordre'];
$sort = (string) ($_GET['sort'] ?? 'ordre');
$dir  = (string) ($_GET['dir']  ?? 'asc');
if (!in_array($sort, $allowedSort, true)) {
    $sort = 'ordre';
}
$dir = strtoupper($dir) === 'DESC' ? 'desc' : 'asc';

=======
>>>>>>> formation
$err = '';
$ok = isset($_GET['ok']);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['add_cat'])) {
    if ($fc->addCategory(
        (string) ($_POST['titre'] ?? ''),
        (string) ($_POST['description'] ?? ''),
        (int) ($_POST['ordre'] ?? 0)
    )) {
<<<<<<< HEAD
        header('Location: liste_categories.php?ok=1&sort=' . urlencode($sort) . '&dir=' . urlencode($dir));
        exit;
    }
    $err = $fc->getLastPublicError() ?: 'Titre requis.';
=======
        header('Location: liste_categories.php?ok=1');
        exit;
    }
    $err = 'Titre requis.';
>>>>>>> formation
}

if (isset($_GET['delete'], $_GET['token']) && (string) $_GET['token'] === ($_SESSION['forum_csrf'] ?? '')) {
    $fc->deleteCategory((int) $_GET['delete']);
<<<<<<< HEAD
    header('Location: liste_categories.php?ok=1&sort=' . urlencode($sort) . '&dir=' . urlencode($dir));
    exit;
}

$liste = $fc->listCategories($sort, $dir);
$t = urlencode((string) $_SESSION['forum_csrf']);

$sortUrl = static function (string $col) use ($sort, $dir) {
    $isCurrent = strtolower($sort) === strtolower($col);
    $next = $isCurrent && strtolower($dir) === 'asc' ? 'desc' : 'asc';
    if (!$isCurrent) {
        $next = 'asc';
    }
    return 'liste_categories.php?' . http_build_query(['sort' => $col, 'dir' => $next]);
};

$sortMark = static function (string $col) use ($sort, $dir) {
    if (strtolower($sort) !== strtolower($col)) {
        return '';
    }
    return strtolower($dir) === 'asc' ? ' ↑' : ' ↓';
};
=======
    header('Location: liste_categories.php?ok=1');
    exit;
}

$liste = $fc->listCategories();
>>>>>>> formation
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Forum — catégories</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(bo_url('commerce.css')) ?>">
<<<<<<< HEAD
    <style>
        .fc-sort-col { cursor: pointer; user-select: none; }
        .fc-sort-col a { color: inherit; text-decoration: none; }
        .fc-sort-col a:hover { text-decoration: underline; }
        .fc-search { display:flex; gap:10px; align-items:center; max-width:900px; margin: 0 auto 14px; }
        .fc-search input { flex: 1; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; }
        .fc-search .fc-count { color:#64748b; font-size:0.85rem; white-space:nowrap; }
    </style>
=======
>>>>>>> formation
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
<<<<<<< HEAD
        <?php if ($err): ?>
            <p class="error" style="color:#b91c1c;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:8px 12px;margin:0 0 10px"><?= htmlspecialchars($err) ?></p>
        <?php endif; ?>
=======
        <?php if ($err): ?><p class="error" style="color:#b91c1c"><?= htmlspecialchars($err) ?></p><?php endif; ?>
>>>>>>> formation
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
<<<<<<< HEAD

    <div class="fc-search">
        <input type="text" id="fcSearch" placeholder="Rechercher une catégorie (titre, description, ordre)..." autocomplete="off">
        <span class="fc-count" id="fcCount"><?= count($liste) ?> ligne<?= count($liste) > 1 ? 's' : '' ?></span>
    </div>

    <div class="card" style="max-width:900px;margin:0 auto">
        <h2 style="margin-top:0;font-size:1.1rem">Liste</h2>
        <div style="overflow-x:auto">
            <table class="table-modern" style="width:100%;border-collapse:collapse" id="fcTable">
                <thead>
                <tr>
                    <th class="fc-sort-col"><a href="<?= htmlspecialchars($sortUrl('id_categorie')) ?>">#<?= $sortMark('id_categorie') ?></a></th>
                    <th class="fc-sort-col"><a href="<?= htmlspecialchars($sortUrl('ordre')) ?>">Ordre<?= $sortMark('ordre') ?></a></th>
                    <th class="fc-sort-col"><a href="<?= htmlspecialchars($sortUrl('titre')) ?>">Titre<?= $sortMark('titre') ?></a></th>
                    <th class="fc-sort-col"><a href="<?= htmlspecialchars($sortUrl('description')) ?>">Description<?= $sortMark('description') ?></a></th>
=======
    <div class="card" style="max-width:900px;margin:0 auto">
        <h2 style="margin-top:0;font-size:1.1rem">Liste</h2>
        <div style="overflow-x:auto">
            <table class="table-modern" style="width:100%;border-collapse:collapse">
                <thead>
                <tr>
                    <th>Ordre</th>
                    <th>Titre</th>
                    <th>Description</th>
>>>>>>> formation
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($liste as $c): ?>
                    <tr>
<<<<<<< HEAD
                        <td><?= (int) $c['id_categorie'] ?></td>
=======
>>>>>>> formation
                        <td><?= (int) $c['ordre'] ?></td>
                        <td><strong><?= htmlspecialchars((string) $c['titre']) ?></strong></td>
                        <td style="max-width:280px;overflow:hidden;text-overflow:ellipsis"><?= nl2br(htmlspecialchars((string) ($c['description'] ?? ''))) ?></td>
                        <td>
                            <a class="btn btn-sm btn-secondary" href="<?= htmlspecialchars(bo_url('forum/modifier_categorie.php?id=' . (int) $c['id_categorie'])) ?>">Modifier</a>
<<<<<<< HEAD
                            <a class="btn btn-sm btn-danger" href="<?= htmlspecialchars(bo_url('forum/liste_categories.php?delete=' . (int) $c['id_categorie'] . '&token=' . urlencode((string) $_SESSION['forum_csrf']) . '&sort=' . urlencode($sort) . '&dir=' . urlencode($dir))) ?>" onclick="return confirm('Supprimer cette catégorie et tous les sujets associés ?');">Suppr.</a>
=======
                            <a class="btn btn-sm btn-danger" href="<?= htmlspecialchars(bo_url('forum/liste_categories.php?delete=' . (int) $c['id_categorie'] . '&token=' . urlencode((string) $_SESSION['forum_csrf']))) ?>" onclick="return confirm('Supprimer cette catégorie et tous les sujets associés ?');">Suppr.</a>
>>>>>>> formation
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
<<<<<<< HEAD

<script>
(function () {
    var input = document.getElementById('fcSearch');
    var table = document.getElementById('fcTable');
    var count = document.getElementById('fcCount');
    if (!input || !table) return;

    var allRows = Array.prototype.slice.call(table.querySelectorAll('tbody tr'));

    function updateCount(visible) {
        if (!count) return;
        count.textContent = visible + ' ligne' + (visible > 1 ? 's' : '');
    }

    function filter() {
        var q = input.value.trim().toLowerCase();
        var visible = 0;
        allRows.forEach(function (row) {
            var txt = row.innerText.toLowerCase();
            var match = q === '' || txt.indexOf(q) !== -1;
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        updateCount(visible);
    }

    input.addEventListener('input', filter);
})();
</script>
=======
>>>>>>> formation
</body>
</html>
