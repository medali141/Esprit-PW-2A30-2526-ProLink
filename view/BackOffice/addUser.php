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
        body{ margin:0; font-family: Arial, sans-serif; }
        form { width: 320px; margin: 20px auto; }
        input, select { width: 100%; padding: 8px; margin: 8px 0; box-sizing: border-box; }
        button { background: green; color: white; padding: 10px; border: none; border-radius:4px }
    </style>
</head>

    <?php /* sidebar stylesheet will be loaded by the included sidebar.php */ ?>
</head>

<body>

    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <!-- CONTENT -->
    <div class="content">
        <div class="topbar">
            <div class="page-title">Ajouter utilisateur</div>
            <div class="actions">
                <a href="listUsers.php" class="btn btn-secondary">← Retour</a>
            </div>
        </div>

        <div class="card" style="max-width:760px; margin:0 auto;">
            <form method="POST" novalidate data-validate="user-form">
                <div class="form-grid">
                    <div>
                        <input type="text" name="nom" placeholder="Nom">
                    </div>
                    <div>
                        <input type="text" name="prenom" placeholder="Prénom">
                    </div>
                    <div>
                        <input type="text" name="email" placeholder="Email">
                    </div>
                    <div>
                        <input type="password" name="mdp" placeholder="Mot de passe">
                    </div>
                    <div>
                        <select name="type">
                            <option value="admin">Admin</option>
                            <option value="candidat">Candidat</option>
                            <option value="entrepreneur">Entrepreneur</option>
                        </select>
                    </div>
                    <div>
                        <input type="text" name="age" placeholder="Age">
                    </div>
                </div>

                <div style="text-align:right; margin-top:12px;">
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>

            <p style="color:red; text-align:center; margin-top:10px;\"><?= $error ?></p>
        </div>

    </div>

</body>
</html>