<?php
require_once __DIR__ . '/../../controller/AuthController.php';

$auth = new AuthController();
// logout handler
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_start();
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit;
}

$user = $auth->profile();
if (!$user) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil - ProLink</title>
    <style>
        body { font-family: Arial; background:#f3f2ef; margin:0; }
        .container{ max-width:900px; margin:40px auto; background:white; padding:20px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.05);} 
        .profile-header{ display:flex; gap:20px; align-items:center }
        .avatar{ width:90px; height:90px; border-radius:50%; background:#ddd; display:flex; align-items:center; justify-content:center; font-size:32px; color:#666 }
        .meta{ flex:1 }
        .actions a{ margin-right:10px; text-decoration:none; padding:8px 12px; border-radius:6px }
        .edit{ background:#0073b1; color:white }
        .logout{ border:1px solid #ccc; color:#333 }
        dl { margin-top:20px }
        dt{ font-weight:600 }
        dd{ margin:0 0 10px 0 }
    </style>
</head>
<body>

<?php include __DIR__ . '/components/navbar.php'; ?>

<div class="container">
    <div class="profile-header">
        <div class="avatar"><?= strtoupper(substr($user['nom'],0,1) . substr($user['prenom'],0,1)) ?></div>
        <div class="meta">
            <h2><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h2>
            <div><?= htmlspecialchars($user['email']) ?> • <?= htmlspecialchars($user['type']) ?></div>
        </div>
        <div class="actions">
            <a class="edit" href="updateprofile.php">Modifier le profil</a>
            <a class="logout" href="profile.php?action=logout">Se déconnecter</a>
        </div>
    </div>

    <dl>
        <dt>Nom</dt>
        <dd><?= htmlspecialchars($user['nom']) ?></dd>

        <dt>Prénom</dt>
        <dd><?= htmlspecialchars($user['prenom']) ?></dd>

        <dt>Email</dt>
        <dd><?= htmlspecialchars($user['email']) ?></dd>

        <dt>Type</dt>
        <dd><?= htmlspecialchars($user['type']) ?></dd>

        <dt>Âge</dt>
        <dd><?= htmlspecialchars($user['age']) ?></dd>
    </dl>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>

</body>
</html>
