<?php
require_once __DIR__ . '/../../../controller/AuthController.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$auth = new AuthController();
$user = $auth->profile();
if (!$user || strtolower($user['type'] ?? '') !== 'admin') { header('Location: ../login.php'); exit; }

require_once __DIR__ . '/../../../controller/FormationP.php';
require_once __DIR__ . '/../_layout/paths.php';
$fp = new FormationP();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
	if ($id) { $fp->delete($id); }
	header('Location: liste.php'); exit;
}

// show confirmation UI
?><!doctype html>
<html lang="fr"><head>
	<meta charset="utf-8">
	<title>Supprimer formation — BackOffice</title>
	<link rel="stylesheet" href="<?= htmlspecialchars(view_web_base()) ?>assets/style.css">
	<link rel="stylesheet" href="<?= htmlspecialchars(bo_url('_layout/sidebar.css')) ?>">
</head><body>
<?php include __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content"><div class="container">
	<div class="topbar"><div class="page-title">Supprimer la formation</div><div class="actions"><a href="liste.php" class="btn btn-secondary">← Retour</a></div></div>
	<div class="card">
		<p>Voulez-vous vraiment supprimer cette formation ? Cette action est irréversible.</p>
		<form method="post">
			<button class="btn btn-danger" name="confirm" value="yes">Oui, supprimer</button>
			<a href="liste.php" class="btn btn-secondary">Annuler</a>
		</form>
	</div>
</div></div>
</body></html>
