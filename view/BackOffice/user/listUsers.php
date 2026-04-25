<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../../controller/AuthController.php';
$__u = (new AuthController())->profile();
if (!$__u || strtolower($__u['type'] ?? '') !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

require_once __DIR__ . '/../../../controller/UserP.php';

$allowed = ['iduser', 'nom', 'prenom', 'email', 'type', 'age'];
$sort = (string) ($_GET['sort'] ?? 'iduser');
$dir  = (string) ($_GET['dir']  ?? 'asc');
if (!in_array($sort, $allowed, true)) {
    $sort = 'iduser';
}
$dir = strtoupper($dir) === 'DESC' ? 'desc' : 'asc';

$userP = new UserP();
$list = $userP->listUsers($sort, $dir);

$qs = static function (array $extra) {
    $b = $extra;
    if (isset($_GET['deleted']) && (string) $_GET['deleted'] === '1') {
        $b['deleted'] = '1';
    }
    if (isset($_GET['error']) && (string) $_GET['error'] === 'hasCommandes') {
        $b['error'] = 'hasCommandes';
    }
    return htmlspecialchars(http_build_query($b), ENT_QUOTES, 'UTF-8');
};

$sortUrl = static function (string $col) use ($sort, $dir, $qs) {
    $is = strtolower($sort) === strtolower($col);
    $next = ($is && strtolower($dir) === 'asc') ? 'desc' : 'asc';
    if (!$is) {
        $next = 'asc';
    }
    return 'listUsers.php?' . $qs(['sort' => $col, 'dir' => $next]);
};

$sortMark = static function (string $col) use ($sort, $dir) {
    if (strtolower($sort) !== strtolower($col)) {
        return '';
    }
    return strtolower($dir) === 'asc' ? ' &uarr;' : ' &darr;';
};

$exportHref = 'exportUsersPdf.php?' . $qs(['sort' => $sort, 'dir' => $dir]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des utilisateurs</title>
    <style>
        .alert{padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600}
        .alert-danger{background:#f8d7da;color:#842029;border:1px solid #f5c2c7}
        .alert-success{background:#d1e7dd;color:#0f5132;border:1px solid #badbcc}
        .table-modern thead th a{color:inherit;text-decoration:none}
        .table-modern thead th a:hover{text-decoration:underline}
    </style>
</head>

<body>

<?php require_once __DIR__ . '/../_layout/sidebar.php'; ?>

<div class="content">
    <div class="container">
        <?php if (isset($_GET['error']) && $_GET['error'] === 'hasCommandes'): ?>
            <div class="alert alert-danger">Cet utilisateur possède des commandes et ne peut pas être supprimé.</div>
        <?php elseif (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Utilisateur supprimé avec succès.</div>
        <?php endif; ?>

        <div class="topbar">
            <div class="page-title">Liste des utilisateurs</div>
            <div class="actions">
                <input class="search-input" placeholder="Rechercher un utilisateur..." id="searchInput">
                <a href="<?= $exportHref ?>" class="btn btn-secondary">Export PDF</a>
                <a href="addUser.php" class="btn btn-primary">+ Ajouter</a>
            </div>
        </div>

        <table class="table-modern" id="usersTable">
            <thead>
            <tr>
                <th><a href="<?= $sortUrl('iduser'); ?>">ID</a><?= $sortMark('iduser') ?></th>
                <th><a href="<?= $sortUrl('nom'); ?>">Nom</a><?= $sortMark('nom') ?></th>
                <th><a href="<?= $sortUrl('prenom'); ?>">Prénom</a><?= $sortMark('prenom') ?></th>
                <th><a href="<?= $sortUrl('email'); ?>">Email</a><?= $sortMark('email') ?></th>
                <th><a href="<?= $sortUrl('type'); ?>">Type</a><?= $sortMark('type') ?></th>
                <th><a href="<?= $sortUrl('age'); ?>">Age</a><?= $sortMark('age') ?></th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($list as $user) { ?>
                <tr>
                    <td><?= htmlspecialchars($user['iduser']); ?></td>
                    <td><?= htmlspecialchars($user['nom']); ?></td>
                    <td><?= htmlspecialchars($user['prenom']); ?></td>
                    <td><?= htmlspecialchars($user['email']); ?></td>
                    <td><?= htmlspecialchars($user['type']); ?></td>
                    <td><?= htmlspecialchars($user['age']); ?></td>
                    <td>
                        <a class="btn btn-secondary" href="detailUser.php?id=<?= $user['iduser']; ?>">Voir</a>
                        <a class="btn btn-secondary" href="updateUser.php?id=<?= $user['iduser']; ?>">Modifier</a>
                        <a class="btn btn-danger js-delete" href="#" data-confirm="Voulez-vous vraiment supprimer cet utilisateur ?" data-href="deleteUser.php?id=<?= $user['iduser']; ?>">Supprimer</a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

    </div>
</div>

<script>
    document.getElementById('searchInput').addEventListener('input', function(e){
        const q = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#usersTable tbody tr');
        rows.forEach(r => {
            r.style.display = Array.from(r.cells).some(c => c.textContent.toLowerCase().includes(q)) ? '' : 'none';
        });
    });
</script>

</body>
</html>
