<?php
require_once __DIR__ . '/../../../controller/UserP.php';

$userP = new UserP();

if (isset($_GET['id'])) {
    $user = $userP->showUser($_GET['id']);
}

if (
    isset($_POST["nom"], $_POST["prenom"], $_POST["email"],
          $_POST["type"], $_POST["age"])
) {
    $newUser = new User(
        $_POST['nom'],
        $_POST['prenom'],
        $_POST['email'],
        "",
        $_POST['type'],
        $_POST['age']
    );

    $userP->updateUser($newUser, $_GET['id']);
    header('Location: listUsers.php');
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier utilisateur</title>
    <style>
        body{ margin:0; font-family: Arial, sans-serif; }
    </style>
</head>

<body>

<?php require_once __DIR__ . '/../_layout/sidebar.php'; ?>

<div class="content">
    <div class="topbar">
        <div class="page-title">Modifier utilisateur</div>
        <div class="actions">
            <a href="listUsers.php" class="btn btn-secondary">← Retour</a>
        </div>
    </div>

    <div class="card" style="max-width:760px; margin:0 auto;">
        <form method="POST" novalidate data-validate="user-form">
            <div class="form-grid">
                <div>
                    <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>">
                </div>
                <div>
                    <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>">
                </div>
                <div>
                    <input type="text" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                </div>

                <div>
                    <select name="type">
                        <option value="admin" <?= $user['type']=="admin"?"selected":"" ?>>Admin</option>
                        <option value="candidat" <?= $user['type']=="candidat"?"selected":"" ?>>Candidat</option>
                        <option value="entrepreneur" <?= $user['type']=="entrepreneur"?"selected":"" ?>>Entrepreneur</option>
                    </select>
                </div>

                <div>
                    <input type="text" name="age" value="<?= htmlspecialchars($user['age']) ?>">
                </div>
            </div>

            <div style="text-align:right; margin-top:12px;">
                <button type="submit" class="btn btn-primary">Modifier</button>
            </div>
        </form>
    </div>

</div>

</body>
</html>
