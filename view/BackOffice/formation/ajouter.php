<?php
require_once __DIR__ . '/../../../controller/AuthController.php';
require_once __DIR__ . '/../_layout/paths.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$auth = new AuthController();
$user = $auth->profile();
if (!$user || strtolower($user['type'] ?? '') !== 'admin') { header('Location: ../login.php'); exit; }

require_once __DIR__ . '/../../../controller/FormationP.php';
$fp = new FormationP();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ok = $fp->add($_POST);
    header('Location: liste.php' . ($ok ? '?added=1' : '?error=1'));
    exit;
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Ajouter formation — BackOffice</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(view_web_base()) ?>assets/style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(bo_url('_layout/sidebar.css')) ?>">
</head>
<body>
<?php include __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content">
    <div class="container">
        <div class="topbar">
            <div class="page-title">Ajouter une formation</div>
            <div class="actions"><a href="liste.php" class="btn btn-secondary">← Retour</a></div>
        </div>

        <div class="card">
            <form method="post">
                <div class="form-grid">
                    <div class="form-row">
                        <label for="titre">Titre</label>
                        <input id="titre" name="titre" required class="form-control">
                    </div>
                    <div class="form-row">
                        <label for="date_debut">Date début</label>
                        <input id="date_debut" type="date" name="date_debut" class="form-control">
                    </div>
                    <div class="form-row">
                        <label for="date_fin">Date fin</label>
                        <input id="date_fin" type="date" name="date_fin" class="form-control">
                    </div>
                    <div class="form-row" style="grid-column:1/-1">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="6"></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary">Enregistrer</button>
                    <a href="liste.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
