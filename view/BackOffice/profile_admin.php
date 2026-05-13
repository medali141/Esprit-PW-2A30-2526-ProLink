<?php
require_once __DIR__ . '/../../controller/AuthController.php';

$auth = new AuthController();
$user = $auth->profile();
if (!$user) {
    header('Location: ../login.php');
    exit;
}
// Only admin allowed
if (strtolower($user['type'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil Admin - BackOffice</title>
    <link rel="stylesheet" href="sidebar.css">
    <style>
        .content{ margin-left:220px; padding:20px }
        .card{ background:white; padding:20px; border-radius:8px; max-width:900px }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="content">
    <div class="topbar">
        <div class="page-title">Mon profil (admin)</div>
    </div>

    <div class="card">
        <h2><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h2>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Type:</strong> <?= htmlspecialchars($user['type']) ?></p>
        <p><strong>Age:</strong> <?= htmlspecialchars($user['age']) ?></p>

        <div style="margin-top:12px">
            <a href="updateProfile_admin.php" class="btn btn-primary">Modifier mon profil</a>
            <a href="../logout.php" class="btn btn-secondary">Se déconnecter</a>
        </div>
    </div>
</div>

</body>
</html>