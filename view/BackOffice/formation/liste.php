<?php
require_once __DIR__ . '/../_layout/paths.php';
require_once __DIR__ . '/../../../controller/AuthController.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$auth = new AuthController();
$user = $auth->profile();
if (!$user || strtolower($user['type'] ?? '') !== 'admin') {
<<<<<<< HEAD
    header('Location: ../login.php');
    exit;
=======
    header('Location: ../login.php'); exit;
>>>>>>> formation
}
require_once __DIR__ . '/../../../controller/FormationP.php';
$fp = new FormationP();
$list = $fp->listAll();
<<<<<<< HEAD
$added = isset($_GET['added']);
=======
>>>>>>> formation
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Formations — BackOffice</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(view_web_base()) ?>assets/style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(bo_url('_layout/sidebar.css')) ?>">
<<<<<<< HEAD
    <link rel="stylesheet" href="formation.css">
</head>
<body>
<?php include __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content formation-page">
    <div class="container">
        <div class="topbar">
            <div>
                <h1 class="page-title">Formations</h1>
                <p class="page-subtitle">Gérez le catalogue des formations, les inscriptions et la délivrance des certificats.</p>
            </div>
            <div class="actions">
                <a href="ajouter.php" class="btn btn-primary">+ Ajouter une formation</a>
            </div>
        </div>

        <?php if ($added): ?>
            <p style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;padding:10px 14px;border-radius:10px;font-weight:600;margin-bottom:14px">
                Formation ajoutée avec succès.
            </p>
        <?php endif; ?>

        <div class="formation-card">
            <?php if (empty($list)): ?>
                <p style="color:#64748b">Aucune formation trouvée. Cliquez « Ajouter une formation » pour commencer.</p>
            <?php else: ?>
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Catégorie</th>
                            <th>Période</th>
                            <th style="text-align:right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($list as $r): ?>
                        <tr>
                            <td><?= (int) $r['id_formation'] ?></td>
                            <td><strong><?= htmlspecialchars((string) $r['titre']) ?></strong></td>
                            <td>
                                <?php if (!empty($r['categorie'])): ?>
                                    <span class="badge-cat"><?= htmlspecialchars((string) $r['categorie']) ?></span>
                                <?php else: ?>
                                    <span style="color:#94a3b8;font-size:0.85rem">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $deb = $r['date_debut'] ?? '';
                                $fin = $r['date_fin'] ?? '';
                                if ($deb && $fin) echo htmlspecialchars($deb) . ' → ' . htmlspecialchars($fin);
                                elseif ($deb) echo htmlspecialchars($deb);
                                else echo '<span style="color:#94a3b8">—</span>';
                                ?>
                            </td>
                            <td style="text-align:right;white-space:nowrap">
                                <a class="btn btn-secondary" href="modifier.php?id=<?= (int) $r['id_formation'] ?>">Modifier</a>
                                <a class="btn btn-secondary" href="inscriptions.php?id=<?= (int) $r['id_formation'] ?>">Inscriptions</a>
                                <a class="btn btn-danger" href="supprimer.php?id=<?= (int) $r['id_formation'] ?>"
                                   onclick="return confirm('Supprimer cette formation et toutes les inscriptions liées ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
=======
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
>>>>>>> formation
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
