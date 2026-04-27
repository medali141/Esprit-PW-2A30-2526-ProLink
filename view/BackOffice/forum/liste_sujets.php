<?php
require_once __DIR__ . '/forum_bootstrap.php';
require_once __DIR__ . '/../../../controller/ForumController.php';
require_once __DIR__ . '/../_layout/paths.php';

if (empty($_SESSION['forum_csrf'])) {
    $_SESSION['forum_csrf'] = bin2hex(random_bytes(16));
}

$fc = new ForumController();
$catFilter = isset($_GET['cat']) ? (int) $_GET['cat'] : 0;
$catFilter = $catFilter > 0 ? $catFilter : null;

$allowed = ['id_sujet', 'titre', 'created_at', 'epingle', 'verrouille', 'cat_titre'];
$sort = (string) ($_GET['sort'] ?? 'created_at');
$dir  = (string) ($_GET['dir']  ?? 'desc');
if (!in_array($sort, $allowed, true)) {
    $sort = 'created_at';
}
$dir = strtoupper($dir) === 'ASC' ? 'asc' : 'desc';

$__redirQs = static function (bool $withOk = false) use ($catFilter, $sort, $dir) {
    $q = ['sort' => $sort, 'dir' => $dir];
    if ($catFilter) {
        $q['cat'] = $catFilter;
    }
    if ($withOk) {
        $q['ok'] = 1;
    }
    return bo_url('forum/liste_sujets.php?' . http_build_query($q));
};

if (isset($_GET['toggle_ep'], $_GET['token']) && (string) $_GET['token'] === (string) $_SESSION['forum_csrf']) {
    $fc->toggleEpingle((int) $_GET['toggle_ep']);
    header('Location: ' . $__redirQs(false));
    exit;
}
if (isset($_GET['toggle_v'], $_GET['token']) && (string) $_GET['token'] === (string) $_SESSION['forum_csrf']) {
    $fc->toggleVerrou((int) $_GET['toggle_v']);
    header('Location: ' . $__redirQs(false));
    exit;
}
if (isset($_GET['delete'], $_GET['token']) && (string) $_GET['token'] === (string) $_SESSION['forum_csrf']) {
    $fc->deleteSujet((int) $_GET['delete']);
    header('Location: ' . $__redirQs(true));
    exit;
}

$liste = $fc->listSujets($catFilter, $sort, $dir);
$categories = $fc->listCategories();
$t = urlencode((string) $_SESSION['forum_csrf']);

$baseQuery = array_filter(['cat' => $catFilter ?: null, 'sort' => $sort, 'dir' => $dir], static function ($v) {
    return $v !== null && $v !== '';
});
$baseQs = http_build_query($baseQuery);

$sortUrl = static function (string $col) use ($catFilter, $sort, $dir) {
    $is = strtolower($sort) === strtolower($col);
    $next = ($is && strtolower($dir) === 'asc') ? 'desc' : 'asc';
    if (!$is) {
        $next = 'asc';
    }
    $q = ['sort' => $col, 'dir' => $next];
    if ($catFilter) {
        $q['cat'] = $catFilter;
    }
    return 'liste_sujets.php?' . http_build_query($q);
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
    <title>Forum — sujets</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(bo_url('commerce.css')) ?>">
</head>
<body>
<?php require_once __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Sujets du forum</div>
        <div class="actions">
            <a href="<?= htmlspecialchars(bo_url('forum/ajout_sujet.php')) ?>" class="btn btn-primary">+ Sujet</a>
            <a href="<?= htmlspecialchars(bo_url('forum/forum_index.php')) ?>" class="btn btn-secondary">← Forum</a>
        </div>
    </div>
    <?php if (isset($_GET['ok'])): ?>
        <p class="alert" style="max-width:1100px;margin:0 auto 16px;padding:10px 14px;border-radius:8px;background:#ecfdf5;color:#047857;font-weight:600">Supprimé.</p>
    <?php endif; ?>
    <div class="card" style="max-width:1100px;margin:0 auto 16px">
        <form method="get" style="display:flex;flex-wrap:wrap;gap:10px;align-items:end">
            <div>
                <label for="cat">Catégorie</label>
                <select name="cat" id="cat" onchange="this.form.submit()" style="min-width:200px">
                    <option value="0">— Toutes —</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id_categorie'] ?>"<?= $catFilter === (int) $c['id_categorie'] ? ' selected' : '' ?>>
                            <?= htmlspecialchars((string) $c['titre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <input type="hidden" name="dir" value="<?= htmlspecialchars($dir) ?>">
        </form>
    </div>
    <div class="card" style="max-width:1100px;margin:0 auto;overflow-x:auto">
        <table class="table-modern" style="width:100%;border-collapse:collapse;font-size:0.9rem">
            <thead>
            <tr>
                <th><a href="<?= htmlspecialchars($sortUrl('id_sujet')) ?>">#<?= $sortMark('id_sujet') ?></a></th>
                <th><a href="<?= htmlspecialchars($sortUrl('titre')) ?>">Titre<?= $sortMark('titre') ?></a></th>
                <th><a href="<?= htmlspecialchars($sortUrl('cat_titre')) ?>">Catégorie<?= $sortMark('cat_titre') ?></a></th>
                <th>Auteur</th>
                <th><a href="<?= htmlspecialchars($sortUrl('created_at')) ?>">Date<?= $sortMark('created_at') ?></a></th>
                <th><a href="<?= htmlspecialchars($sortUrl('epingle')) ?>">Épinglé<?= $sortMark('epingle') ?></a></th>
                <th><a href="<?= htmlspecialchars($sortUrl('verrouille')) ?>">Verrou<?= $sortMark('verrouille') ?></a></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($liste as $s): ?>
                <tr>
                    <td><?= (int) $s['id_sujet'] ?></td>
                    <td><a href="<?= htmlspecialchars(bo_url('forum/sujet_messages.php?id=' . (int) $s['id_sujet'])) ?>"><strong><?= htmlspecialchars((string) $s['titre']) ?></strong></a></td>
                    <td><?= htmlspecialchars((string) $s['cat_titre']) ?></td>
                    <td><?= htmlspecialchars(trim(($s['prenom'] ?? '') . ' ' . ($s['nom'] ?? ''))) ?></td>
                    <td><?= htmlspecialchars((string) $s['created_at']) ?></td>
                    <td><?= (int) $s['epingle'] ? 'Oui' : '—' ?></td>
                    <td><?= (int) $s['verrouille'] ? 'Oui' : '—' ?></td>
                    <td style="white-space:nowrap">
                        <a class="btn btn-sm btn-secondary" href="liste_sujets.php?toggle_ep=<?= (int) $s['id_sujet'] ?>&amp;token=<?= $t ?><?= $baseQs !== '' ? '&amp;' . htmlspecialchars($baseQs, ENT_QUOTES, 'UTF-8') : '' ?>">Épingler</a>
                        <a class="btn btn-sm btn-secondary" href="liste_sujets.php?toggle_v=<?= (int) $s['id_sujet'] ?>&amp;token=<?= $t ?><?= $baseQs !== '' ? '&amp;' . htmlspecialchars($baseQs, ENT_QUOTES, 'UTF-8') : '' ?>">Verrou</a>
                        <a class="btn btn-sm btn-danger" href="liste_sujets.php?delete=<?= (int) $s['id_sujet'] ?>&amp;token=<?= $t ?><?= $baseQs !== '' ? '&amp;' . htmlspecialchars($baseQs, ENT_QUOTES, 'UTF-8') : '' ?>" onclick="return confirm('Supprimer ce sujet et tous les messages ?');">Suppr.</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (empty($liste)): ?>
            <p style="color:#64748b;padding:12px">Aucun sujet.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
