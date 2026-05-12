<?php
// projects/listProjects.php — admin-only listing placeholder
require_once __DIR__ . '/../../../controller/AuthController.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$auth = new AuthController();
$user = $auth->profile();
if (!$user || strtolower($user['type'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Projets — BackOffice</title>
</head>
<body>
<?php include __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content">
    <div class="container">
        <div class="topbar">
            <div class="page-title">Projets</div>
            <div class="actions">
                <a href="addProject.php" class="btn btn-primary">+ Ajouter</a>
            </div>
        </div>

        <div class="card">
            <?php
            require_once __DIR__ . '/../../../controller/ProjectP.php';
            $pp = new ProjectP();
            $projects = $pp->listAll();
            if (empty($projects)) {
                echo '<p>Aucun projet trouvé. Utilisez + Ajouter pour en créer un.</p>';
            } else {
                echo '<table class="table-modern"><thead><tr><th>ID</th><th>Titre</th><th>Statut</th><th>Actions</th></tr></thead><tbody>';
                foreach ($projects as $pr) {
                    echo '<tr>' .
                        '<td>' . (int)$pr['idproject'] . '</td>' .
                        '<td>' . htmlspecialchars($pr['title']) . '</td>' .
                        '<td>' . htmlspecialchars($pr['status']) . '</td>' .
                        '<td><a class="btn btn-secondary" href="detailProject.php?id=' . (int)$pr['idproject'] . '">Voir</a> '
                        . '<a class="btn btn-secondary" href="updateProject.php?id=' . (int)$pr['idproject'] . '">Modifier</a> '
                        . '<a class="btn btn-danger" href="deleteProject.php?id=' . (int)$pr['idproject'] . '">Supprimer</a></td>' .
                        '</tr>';
                }
                echo '</tbody></table>';
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>
