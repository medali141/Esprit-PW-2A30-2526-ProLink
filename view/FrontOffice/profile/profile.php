<?php
require_once __DIR__ . '/../../../controller/AuthController.php';

$auth = new AuthController();
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_start();
    session_unset();
    session_destroy();
    header('Location: ../../login.php');
    exit;
}

$user = $auth->profile();
if (!$user) {
    header('Location: ../../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil - ProLink</title>
    <style>
        body { font-family: Arial; background:#f3f2ef; margin:0; display:flex; flex-direction:column; min-height:100vh }
        .main{ flex:1 }
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

        /* Mode sombre : éviter texte blanc sur carte blanche (style.css impose body * { color:#fff }) */
        html.dark-mode body.fo-profile-page {
            background: #0c1017 !important;
        }
        html.dark-mode body.fo-profile-page .container {
            background: #151b26 !important;
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.35) !important;
            color: #e2e8f0 !important;
        }
        html.dark-mode body.fo-profile-page .container h2,
        html.dark-mode body.fo-profile-page .container .meta div {
            color: #f1f5f9 !important;
        }
        html.dark-mode body.fo-profile-page .container dt {
            color: #94a3b8 !important;
        }
        html.dark-mode body.fo-profile-page .container dd {
            color: #e2e8f0 !important;
        }
        html.dark-mode body.fo-profile-page .avatar {
            background: #334155 !important;
            color: #cbd5e1 !important;
        }
        html.dark-mode body.fo-profile-page .logout {
            border-color: rgba(148, 163, 184, 0.35) !important;
            color: #e2e8f0 !important;
        }
    </style>
</head>
<body class="fo-profile-page">

<?php include __DIR__ . '/../components/navbar.php'; ?>

<main class="main">

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

</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

</body>
</html>
