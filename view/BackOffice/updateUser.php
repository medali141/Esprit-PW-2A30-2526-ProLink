<?php
include '../../Controller/UserP.php';

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
        "", // mdp non modifié ici
        $_POST['type'],
        $_POST['age']
    );

    $userP->updateUser($newUser, $_GET['id']);
    header('Location:listUsers.php');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modifier utilisateur</title>
    <style>
        /* local fallbacks; shared styles live in sidebar.css */
        body{ margin:0; font-family: Arial, sans-serif; }
    </style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">
    <div class="topbar">
        <div class="page-title">Modifier utilisateur</div>
        <div class="actions">
            <a href="listUsers.php" class="btn btn-secondary">← Retour</a>
        </div>
    </div>

    <div class="card" style="max-width:760px; margin:0 auto;">
        <form method="POST">
            <div class="form-grid">
                <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>">
                <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>">
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">

                <select name="type">
                    <option value="admin" <?= $user['type']=="admin"?"selected":"" ?>>Admin</option>
                    <option value="candidat" <?= $user['type']=="candidat"?"selected":"" ?>>Candidat</option>
                    <option value="entrepreneur" <?= $user['type']=="entrepreneur"?"selected":"" ?>>Entrepreneur</option>
                </select>

                <input type="number" name="age" value="<?= htmlspecialchars($user['age']) ?>">
            </div>

            <div style="text-align:right; margin-top:12px;">
                <button type="submit" class="btn btn-primary">Modifier</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>