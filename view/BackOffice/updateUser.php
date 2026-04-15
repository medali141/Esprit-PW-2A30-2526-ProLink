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
        form { width: 300px; margin: auto; }
        input, select { width: 100%; padding: 8px; margin: 5px 0; }
        button { background: orange; color: white; padding: 10px; border: none; }
    </style>
</head>

<body>

<h2 align="center">Modifier utilisateur</h2>

<form method="POST">
    <input type="text" name="nom" value="<?= $user['nom'] ?>">
    <input type="text" name="prenom" value="<?= $user['prenom'] ?>">
    <input type="email" name="email" value="<?= $user['email'] ?>">

    <select name="type">
        <option value="admin" <?= $user['type']=="admin"?"selected":"" ?>>Admin</option>
        <option value="candidat" <?= $user['type']=="candidat"?"selected":"" ?>>Candidat</option>
        <option value="entrepreneur" <?= $user['type']=="entrepreneur"?"selected":"" ?>>Entrepreneur</option>
    </select>

    <input type="number" name="age" value="<?= $user['age'] ?>">

    <button type="submit">Modifier</button>
</form>

</body>
</html>