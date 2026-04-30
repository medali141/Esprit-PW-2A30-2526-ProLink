<?php
require_once __DIR__ . '/../../controller/UserP.php';

$userP = new UserP();

if (isset($_GET['id'])) {
    $user = $userP->showUser($_GET['id']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail utilisateur — BackOffice</title>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="content">
    <div class="container page-full">
        <div class="topbar">
            <div class="page-title">Détail utilisateur</div>
            <div class="actions">
                <a href="listUsers.php" class="btn btn-secondary">← Retour</a>
            </div>
        </div>

        <div class="card" style="max-width:760px; margin: 0 auto; padding:18px">
            <h3 style="margin-top:0;">Profil</h3>

            <table class="table-modern" style="width:100%; max-width:720px; margin: 12px auto;">
                <tbody>
                <tr>
                    <th style="width:180px; background:transparent; color:var(--accent-2);">ID</th>
                    <td><?= htmlspecialchars($user['iduser']) ?></td>
                </tr>
                <tr>
                    <th style="background:transparent; color:var(--accent-2);">Nom</th>
                    <td><?= htmlspecialchars($user['nom']) ?></td>
                </tr>
                <tr>
                    <th style="background:transparent; color:var(--accent-2);">Prénom</th>
                    <td><?= htmlspecialchars($user['prenom']) ?></td>
                </tr>
                <tr>
                    <th style="background:transparent; color:var(--accent-2);">Email</th>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                </tr>
                <tr>
                    <th style="background:transparent; color:var(--accent-2);">Type</th>
                    <td><?= htmlspecialchars($user['type']) ?></td>
                </tr>
                <tr>
                    <th style="background:transparent; color:var(--accent-2);">Age</th>
                    <td><?= htmlspecialchars($user['age']) ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>