<?php
// projects/detailProject.php — admin-only placeholder for project detail
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
    <title>Détail projet — BackOffice</title>
</head>
<body>
<?php include __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content">
    <div class="container">
        <div class="topbar">
            <div class="page-title">Détail projet</div>
        </div>
        <div class="card">
            <?php
            require_once __DIR__ . '/../../../controller/ProjectP.php';
            $pp = new ProjectP();
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $p = $pp->get($id);
            if (!$p) { echo '<div class="alert alert-danger">Projet introuvable.</div>'; }
            else {
                echo '<h3>' . htmlspecialchars($p['title']) . '</h3>';
                echo '<p>' . nl2br(htmlspecialchars($p['description'])) . '</p>';
                echo '<p><strong>Statut:</strong> ' . htmlspecialchars($p['status']) . '</p>';
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>
