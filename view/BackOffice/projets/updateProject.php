<?php
// projects/updateProject.php — admin-only placeholder for editing a project
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
    <title>Modifier projet — BackOffice</title>
</head>
<body>
<?php include __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content">
    <div class="container">
        <div class="topbar">
            <div class="page-title">Modifier projet</div>
        </div>
        <div class="card">
            <?php
            require_once __DIR__ . '/../../../controller/ProjectP.php';
            $pp = new ProjectP();
            $msg = '';
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $project = $pp->get($id);
            if (!$project) { echo '<div class="alert alert-danger">Projet introuvable.</div>'; }
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $ok = $pp->update($id, ['title'=>$_POST['title'] ?? '', 'description'=>$_POST['description'] ?? '', 'owner_id'=>$_POST['owner_id'] ?? null, 'status'=>$_POST['status'] ?? 'draft']);
                if ($ok) { header('Location: listProjects.php'); exit; }
                $msg = 'Erreur lors de la mise à jour.';
            }
            if ($msg) echo '<div class="alert alert-danger">' . htmlspecialchars($msg) . '</div>';
            ?>
            <form method="POST">
                <label>Titre</label>
                <input name="title" value="<?= htmlspecialchars($project['title'] ?? '') ?>" required style="width:100%;padding:8px;margin:6px 0">
                <label>Description</label>
                <textarea name="description" style="width:100%;padding:8px;margin:6px 0"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
                <label>Statut</label>
                <select name="status"><option value="draft" <?= (isset($project['status']) && $project['status']==='draft')? 'selected':'' ?>>Draft</option><option value="published" <?= (isset($project['status']) && $project['status']==='published')? 'selected':'' ?>>Published</option></select>
                <div style="margin-top:12px"><button class="btn btn-primary" type="submit">Enregistrer</button></div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
