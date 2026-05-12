<?php
require_once __DIR__ . '/../_layout/paths.php';
require_once __DIR__ . '/../../../controller/AuthController.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$auth = new AuthController();
$user = $auth->profile();
if (!$user || strtolower($user['type'] ?? '') !== 'admin') {
    header('Location: ../login.php'); exit;
}
require_once __DIR__ . '/../../../controller/FormationP.php';
$fp = new FormationP();
$list = $fp->listAll();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Formations — BackOffice</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(view_web_base()) ?>assets/style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(bo_url('_layout/sidebar.css')) ?>">
</head>
<body>
<?php include __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content">
    <div class="container">
        <div class="topbar">
            <div class="page-title">Formations</div>
            <div class="actions"><a href="ajouter.php" class="btn btn-primary">+ Ajouter</a></div>
        </div>

        <div class="card">
            <?php if (empty($list)): ?>
                <p>Aucune formation trouvée.</p>
            <?php else: ?>
                <table class="table-modern"><thead><tr><th>ID</th><th>Titre</th><th>Date</th><th>Actions</th></tr></thead><tbody>
                <?php foreach ($list as $r): ?>
                    <tr>
                        <td><?= (int)$r['id_formation'] ?></td>
                        <td><?= htmlspecialchars($r['titre']) ?></td>
                        <td><?= htmlspecialchars($r['date_debut'] ?? '') ?></td>
                        <td>
                            <a class="btn btn-secondary" href="modifier.php?id=<?= (int)$r['id_formation'] ?>">Modifier</a>
                            <a class="btn btn-secondary" href="inscriptions.php?id=<?= (int)$r['id_formation'] ?>">Inscriptions</a>
                            <a class="btn btn-danger" href="supprimer.php?id=<?= (int)$r['id_formation'] ?>">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody></table>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
