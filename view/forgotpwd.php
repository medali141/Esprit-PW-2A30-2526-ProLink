<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié - ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="assets/style.css">

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
            height: 80vh;
        }

        .box {
            background: white;
            padding: 30px;
            width: 320px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        h2 {
            color: #0073b1;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #0073b1;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #005f8d;
        }

        a {
            display: block;
            margin-top: 10px;
            color: #0073b1;
            text-decoration: none;
        }

        .info {
            font-size: 14px;
            color: #555;
            margin-bottom: 10px;
        }

        html.dark-mode body { background: #0b1017 !important; }
        html.dark-mode .box { background: #151b26 !important; color: #e2e8f0; box-shadow: 0 8px 32px rgba(0,0,0,0.45); }
        html.dark-mode .box h2 { color: #38bdf8; }
        html.dark-mode .box input { background: #1e293b; border-color: rgba(148,163,184,0.25); color: #f8fafc; }
        html.dark-mode .box a { color: #7dd3fc; }
        html.dark-mode .info { color: #94a3b8; }
    </style>
</head>

<body>

<!-- NAVBAR -->
<?php include 'FrontOffice/components/navbar.php'; ?>

<div class="container">
    <div class="box">
        <h2>Mot de passe oublié</h2>

        <p class="info">
            Entrez votre email pour réinitialiser votre mot de passe
        </p>

        <form method="post" action="#" novalidate data-validate="forgot-form">
            <input type="email" name="email" placeholder="Votre email" autocomplete="email">

            <input type="password" name="mdp" placeholder="Nouveau mot de passe" autocomplete="new-password">

            <button type="submit">Réinitialiser</button>
        </form>

        <a href="login.php">Retour à la connexion</a>
    </div>
</div>

<!-- FOOTER -->
<?php include 'FrontOffice/components/footer.php'; ?>
<script src="assets/forms-validation.js"></script>

</body>
</html>