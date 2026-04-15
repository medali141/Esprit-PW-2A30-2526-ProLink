<?php
include '../../Controller/UserP.php';

$error = "";
$userP = new UserP();

if (
    isset($_POST["nom"], $_POST["prenom"], $_POST["email"],
          $_POST["mdp"], $_POST["type"], $_POST["age"])
) {
    if (
        !empty($_POST["nom"]) && !empty($_POST["prenom"]) &&
        !empty($_POST["email"]) && !empty($_POST["mdp"]) &&
        !empty($_POST["type"]) && !empty($_POST["age"])
    ) {
        $user = new User(
            $_POST['nom'],
            $_POST['prenom'],
            $_POST['email'],
            $_POST['mdp'],
            $_POST['type'],
            $_POST['age']
        );

        $userP->addUser($user);
        header('Location:listUsers.php');
    } else {
        $error = "Champs manquants";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ajouter utilisateur</title>
    <style>
        form { width: 300px; margin: auto; }
        input, select { width: 100%; padding: 8px; margin: 5px 0; }
        button { background: green; color: white; padding: 10px; border: none; }
    </style>
</head>

<body>
<h2 align="center">Ajouter utilisateur</h2>

<form method="POST">
    <input type="text" name="nom" placeholder="Nom">
    <input type="text" name="prenom" placeholder="Prénom">
    <input type="email" name="email" placeholder="Email">
    <input type="password" name="mdp" placeholder="Mot de passe">

    <select name="type">
        <option value="admin">Admin</option>
        <option value="candidat">Candidat</option>
        <option value="entrepreneur">Entrepreneur</option>
    </select>

    <input type="number" name="age" placeholder="Age">

    <button type="submit">Ajouter</button>
</form>

<p style="color:red; text-align:center;"><?= $error ?></p>

</body>
</html>