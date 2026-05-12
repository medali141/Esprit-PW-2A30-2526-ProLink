<?php
require_once __DIR__ . '/../../../controller/AuthController.php';
require_once __DIR__ . '/../_layout/paths.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$auth = new AuthController();
$user = $auth->profile();
if (!$user || strtolower($user['type'] ?? '') !== 'admin') { header('Location: ../login.php'); exit; }
require_once __DIR__ . '/../../../controller/FormationP.php';
$fp = new FormationP();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$row = $id ? $fp->get($id) : null;
if (!$row) { header('Location: liste.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fp->update($id, $_POST);
    header('Location: liste.php'); exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Modifier formation — BackOffice</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(view_web_base()) ?>assets/style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(bo_url('_layout/sidebar.css')) ?>">
</head>
<body>
<?php include __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content">
    <div class="container">
        <div class="topbar">
            <div class="page-title">Modifier la formation</div>
            <div class="actions"><a href="liste.php" class="btn btn-secondary">← Retour</a></div>
        </div>

        <div class="card">
            <form method="post">
                <div class="form-grid">
                    <div class="form-row">
                        <label for="titre">Titre</label>
                        <input id="titre" name="titre" value="<?= htmlspecialchars($row['titre']) ?>" required class="form-control">
                    </div>
                    <div class="form-row">
                        <label for="date_debut">Date début</label>
                        <input id="date_debut" type="date" name="date_debut" value="<?= htmlspecialchars($row['date_debut'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="form-row">
                        <label for="date_fin">Date fin</label>
                        <input id="date_fin" type="date" name="date_fin" value="<?= htmlspecialchars($row['date_fin'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="form-row" style="grid-column:1/-1">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="6"><?= htmlspecialchars($row['description'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary">Mettre à jour</button>
                    <a href="liste.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
