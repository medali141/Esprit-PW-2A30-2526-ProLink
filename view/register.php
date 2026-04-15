<?php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Register - ProLink</title>

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: #f3f2ef;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 85vh;
        }

        .register-box {
            background: white;
            padding: 30px;
            width: 350px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #0073b1;
            color: white;
            border: none;
        }

        a {
            display: block;
            margin-top: 10px;
            color: #0073b1;
            text-decoration: none;
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<?php include 'FrontOffice/components/navbar.php'; ?>

<div class="container">
    <div class="register-box">
        <h2>Inscription</h2>

        <form>
            <input type="text" placeholder="Nom" required>
            <input type="text" placeholder="Prénom" required>
            <input type="email" placeholder="Email" required>
            <input type="password" placeholder="Mot de passe" required>

            <select required>
                <option value="">Type utilisateur</option>
                <option value="admin">Admin</option>
                <option value="candidat">Candidat</option>
                <option value="entrepreneur">Entrepreneur</option>
            </select>

            <input type="number" placeholder="Age" required>

            <button type="submit">S'inscrire</button>
        </form>

        <a href="login.php">Déjà un compte ? Se connecter</a>
    </div>
</div>

<!-- FOOTER -->
<?php include 'FrontOffice/components/footer.php'; ?>

</body>
</html>