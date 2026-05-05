<?php
require_once __DIR__ . '/forum_bootstrap.php';
require_once __DIR__ . '/../../../controller/ForumController.php';
require_once __DIR__ . '/../_layout/paths.php';

$fc = new ForumController();
if (empty($_SESSION['forum_csrf'])) {
    $_SESSION['forum_csrf'] = bin2hex(random_bytes(16));
}
$err = '';
$ok = isset($_GET['ok']);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['add_cat'])) {
    if ($fc->addCategory(
        (string) ($_POST['titre'] ?? ''),
        (string) ($_POST['description'] ?? ''),
        (int) ($_POST['ordre'] ?? 0)
    )) {
        header('Location: liste_categories.php?ok=1');
        exit;
    }
    $err = 'Titre requis.';
}

if (isset($_GET['delete'], $_GET['token']) && (string) $_GET['token'] === ($_SESSION['forum_csrf'] ?? '')) {
    $fc->deleteCategory((int) $_GET['delete']);
    header('Location: liste_categories.php?ok=1');
    exit;
}

$allowedSort = ['ordre', 'titre', 'id_categorie'];
$sort = (string) ($_GET['sort'] ?? 'ordre');
$dir = (string) ($_GET['dir'] ?? 'asc');
if (!in_array($sort, $allowedSort, true)) {
    $sort = 'ordre';
}
$dir = strtoupper($dir) === 'ASC' ? 'asc' : 'desc';

$liste = $fc->listCategories($sort, $dir);
$t = urlencode((string) ($_SESSION['forum_csrf'] ?? ''));
$csrf_js = json_encode((string) ($_SESSION['forum_csrf'] ?? ''));
$sort_js = json_encode($sort);
$dir_js = json_encode($dir);

$qval = isset($_GET['q']) ? (string) $_GET['q'] : null;
$baseQuery = array_filter(['q' => $qval, 'sort' => $sort, 'dir' => $dir], static function ($v) {
    return $v !== null && $v !== '';
});
$baseQs = http_build_query($baseQuery);

$sortUrl = static function (string $col) use ($sort, $dir, $qval) {
    $is = strtolower($sort) === strtolower($col);
    $next = ($is && strtolower($dir) === 'asc') ? 'desc' : 'asc';
    if (!$is) {
        $next = 'asc';
    }
    $q = ['sort' => $col, 'dir' => $next];
    if ($qval !== null && $qval !== '') {
        $q['q'] = $qval;
    }
    return 'liste_categories.php?' . http_build_query($q);
};

$sortMark = static function (string $col) use ($sort, $dir) {
    if (strtolower($sort) !== strtolower($col)) {
        return '';
    }
    return strtolower($dir) === 'asc' ? ' ↑' : ' ↓';
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Forum — catégories</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(bo_url('commerce.css')) ?>">
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
        <?php if ($err): ?><p class="error" style="color:#b91c1c"><?= htmlspecialchars($err) ?></p><?php endif; ?>
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
    <div class="card" style="max-width:900px;margin:0 auto">
        <h2 style="margin-top:0;font-size:1.1rem">Liste</h2>
        <div style="margin-top:8px;margin-bottom:12px">
            <label for="q-cat">Recherche</label>
            <input type="search" id="q-cat" name="q" class="search-input" placeholder="Titre, description..." value="<?= htmlspecialchars((string)($_GET['q'] ?? '')) ?>">
        </div>
        <div style="overflow-x:auto">
            <table class="table-modern" style="width:100%;border-collapse:collapse">
                <thead>
                <tr>
                    <th><a href="<?= htmlspecialchars($sortUrl('ordre')) ?>">Ordre<?= $sortMark('ordre') ?></a></th>
                    <th><a href="<?= htmlspecialchars($sortUrl('titre')) ?>">Titre<?= $sortMark('titre') ?></a></th>
                    <th>Description</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($liste as $c): ?>
                    <tr>
                        <td><?= (int) $c['ordre'] ?></td>
                        <td><strong><?= htmlspecialchars((string) $c['titre']) ?></strong></td>
                        <td style="max-width:280px;overflow:hidden;text-overflow:ellipsis"><?= nl2br(htmlspecialchars((string) ($c['description'] ?? ''))) ?></td>
                        <td>
                            <a class="btn btn-sm btn-secondary" href="<?= htmlspecialchars(bo_url('forum/modifier_categorie.php?id=' . (int) $c['id_categorie'])) ?>">Modifier</a>
                            <a class="btn btn-sm btn-danger" href="<?= htmlspecialchars(bo_url('forum/liste_categories.php?delete=' . (int) $c['id_categorie'] . '&token=' . urlencode((string) $_SESSION['forum_csrf']))) ?>" onclick="return confirm('Supprimer cette catégorie et tous les sujets associés ?');">Suppr.</a>
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
        <script>
        (function(){
            var input = document.getElementById('q-cat');
            var tbody = document.querySelector('table.table-modern tbody');
            if (!input || !tbody) return;
            var timer = null;
            var csrf = <?= $csrf_js ?>;
            var sort = <?= $sort_js ?>;
            var dir = <?= $dir_js ?>;
            function escapeHtml(s){
                return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\"/g,'&quot;').replace(/'/g,'&#39;');
            }
            function render(items){
                if (!items || items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" style="color:#64748b;padding:12px">Aucune catégorie.</td></tr>';
                    return;
                }
                tbody.innerHTML = items.map(function(c){
                    var desc = (c.description || '');
                    return '<tr>' +
                        '<td>' + (c.ordre||0) + '</td>' +
                        '<td><strong>' + escapeHtml(c.titre) + '</strong></td>' +
                        '<td style="max-width:280px;overflow:hidden;text-overflow:ellipsis">' + escapeHtml(desc).replace(/\n/g,'<br>') + '</td>' +
                        '<td>' +
                            '<a class="btn btn-sm btn-secondary" href="modifier_categorie.php?id=' + c.id_categorie + '">Modifier</a> ' +
                            '<a class="btn btn-sm btn-danger" href="liste_categories.php?delete=' + c.id_categorie + '&token=' + encodeURIComponent(csrf) + '" onclick="return confirm(\'Supprimer cette catégorie et tous les sujets associés ?\');">Suppr.</a>' +
                        '</td>' +
                    '</tr>';
                }).join('');
            }
            function doSearch(){
                var q = input.value || '';
                var url = 'search_categories.php?q=' + encodeURIComponent(q) + '&sort=' + encodeURIComponent(sort) + '&dir=' + encodeURIComponent(dir);
                fetch(url)
                    .then(function(r){ return r.json(); })
                    .then(function(data){ render(data); })
                    .catch(function(e){ console.error(e); });
            }
            input.addEventListener('input', function(){ clearTimeout(timer); timer = setTimeout(doSearch, 300); });
            if (input.value && input.value.trim() !== '') { doSearch(); }
        })();
        </script>
</body>
</html>
