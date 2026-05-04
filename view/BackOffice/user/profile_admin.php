<?php
require_once __DIR__ . '/../../../controller/AuthController.php';

$auth = new AuthController();
$user = $auth->profile();
if (!$user) {
    header('Location: ../../login.php');
    exit;
}
if (strtolower($user['type'] ?? '') !== 'admin') {
    header('Location: ../../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil Admin - BackOffice</title>
    <style>
        .content{ margin-left:var(--sidebar-width,288px); padding:20px }
        .card{ background:white; padding:20px; border-radius:8px; max-width:900px }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/../_layout/sidebar.php'; ?>

<div class="content">
    <div class="topbar">
        <div class="page-title">Mon profil (admin)</div>
    </div>

    <div class="card">
        <?php
        $__p = trim((string) ($user['photo'] ?? ''));
        if ($__p !== '') {
            $__src = '../../' . htmlspecialchars(str_replace('\\', '/', $__p));
            echo '<p style="margin:0 0 16px"><img src="' . $__src . '" alt="" style="width:96px;height:96px;object-fit:cover;border-radius:12px;border:1px solid #e5e7eb"></p>';
        }
        ?>
        <h2><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h2>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Type:</strong> <?= htmlspecialchars($user['type']) ?></p>
        <p><strong>Age:</strong> <?= htmlspecialchars($user['age']) ?></p>

        <div style="margin-top:12px">
            <a href="updateProfile_admin.php" class="btn btn-primary">Modifier mon profil</a>
            <a href="../../logout.php" class="btn btn-secondary">Se déconnecter</a>
        </div>
    </div>
</div>

</body>
</html>
