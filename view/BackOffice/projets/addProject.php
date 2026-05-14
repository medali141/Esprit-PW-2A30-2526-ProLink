<?php
// projects/addProject.php — admin-only placeholder for adding a project
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
    <title>Ajouter projet — BackOffice</title>
</head>
<body>
<?php include __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content">
    <div class="container">
        <div class="topbar">
            <div class="page-title">Ajouter projet</div>
        </div>
        <div class="card">
            <?php
            require_once __DIR__ . '/../../../controller/ProjectP.php';
<<<<<<< HEAD
            require_once __DIR__ . '/../../../controller/NotificationP.php';
            $pp = new ProjectP();
            $msg = '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $newId = $pp->add(['title'=>$_POST['title'] ?? '', 'description'=>$_POST['description'] ?? '', 'owner_id'=>$_POST['owner_id'] ?? null, 'status'=>$_POST['status'] ?? 'draft']);
                if ($newId !== false) {
                    $title = trim((string) ($_POST['title'] ?? ''));
                    (new NotificationP())->broadcastToAllUsers(
                        'projet_publie',
                        'Nouveau projet sur ProLink',
                        $title !== '' ? ('« ' . $title . ' » est disponible. Découvrez-le dans le catalogue.') : 'Un nouveau projet vient d’être ajouté au catalogue.',
                        'view/FrontOffice/project.php?id=' . $newId
                    );
=======
            $pp = new ProjectP();
            $msg = '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $ok = $pp->add(['title'=>$_POST['title'] ?? '', 'description'=>$_POST['description'] ?? '', 'owner_id'=>$_POST['owner_id'] ?? null, 'status'=>$_POST['status'] ?? 'draft']);
                if ($ok) {
>>>>>>> formation
                    header('Location: listProjects.php'); exit;
                }
                $msg = 'Erreur lors de l\'ajout.';
            }
            if ($msg) echo '<div class="alert alert-danger">' . htmlspecialchars($msg) . '</div>';
            ?>
            <form method="POST">
                <label>Titre</label>
                <input name="title" required style="width:100%;padding:8px;margin:6px 0">
                <label>Description</label>
                <textarea name="description" style="width:100%;padding:8px;margin:6px 0"></textarea>
                <label>Statut</label>
                <select name="status"><option value="draft">Draft</option><option value="published">Published</option></select>
                <div style="margin-top:12px"><button class="btn btn-primary" type="submit">Créer</button></div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
