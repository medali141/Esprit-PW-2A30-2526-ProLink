    <?php
include '../../Controller/UserP.php';

$userP = new UserP();

if (isset($_GET['id'])) {
    $user = $userP->showUser($_GET['id']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Détail utilisateur</title>
    <style>
        body{ margin:0; font-family: Arial, sans-serif; }
        .card {
            width: 340px;
            margin: 20px auto;
            padding: 20px;
            background: #f4f4f4;
            border-radius: 10px;
            text-align: center;
            box-sizing: border-box;
        }
        p { font-size: 16px; }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<?php include 'sidebar.php'; ?>

<!-- CONTENT -->
<div class="content">
    <div class="topbar">
        <div class="page-title">Détail utilisateur</div>
        <div class="actions">
            <a href="listUsers.php" class="btn btn-secondary">← Retour</a>
        </div>
    </div>

    <div class="card" style="max-width:480px; margin: 0 auto;">
        <h3 style="margin-top:0;">Profil</h3>
        <p><b>ID:</b> <?= htmlspecialchars($user['iduser']) ?></p>
        <p><b>Nom:</b> <?= htmlspecialchars($user['nom']) ?></p>
        <p><b>Prénom:</b> <?= htmlspecialchars($user['prenom']) ?></p>
        <p><b>Email:</b> <?= htmlspecialchars($user['email']) ?></p>
        <p><b>Type:</b> <?= htmlspecialchars($user['type']) ?></p>
        <p><b>Age:</b> <?= htmlspecialchars($user['age']) ?></p>
    </div>
</div>

</body>
</html>