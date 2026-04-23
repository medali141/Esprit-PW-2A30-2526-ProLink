<?php
declare(strict_types=1);

require_once __DIR__ . '/../_layout/paths.php';
require_once __DIR__ . '/../../../controller/AuthController.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$auth = new AuthController();
$user = $auth->profile();
if (!$user || strtolower($user['type'] ?? '') !== 'admin') {
    header('Location: ' . view_web_base() . 'login.php');
    exit;
}

$labels = [
    'projets' => 'Gestion des projets',
    'evenements' => 'Gestion des événements',
    'formations' => 'Gestion des formations',
];
$k = preg_replace('/[^a-z_]/', '', (string) ($_GET['module'] ?? ''));
$title = $labels[$k] ?? 'Module administration';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?> — ProLink</title>
</head>
<body>
<?php require_once __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content">
    <div class="topbar">
        <div class="page-title"><?= htmlspecialchars($title) ?></div>
    </div>
    <div class="container" style="max-width:640px">
        <div class="card">
            <p>Cette section est prévue dans l’architecture mais pas encore implémentée.</p>
            <p class="hint">Fichiers et notes associés : dossier <code>view/BackOffice/_TODO/<?= htmlspecialchars($k ?: '…') ?></code></p>
            <p style="margin-top:16px"><a class="btn btn-secondary" href="<?= htmlspecialchars(bo_url('dashboard/dashboard.php')) ?>">Retour au tableau de bord</a></p>
        </div>
    </div>
</div>
</body>
</html>
