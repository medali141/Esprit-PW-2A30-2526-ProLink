<?php
require_once __DIR__ . '/../controller/AuthController.php';
require_once __DIR__ . '/../model/User.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mdp = trim($_POST['mdp'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $age = intval($_POST['age'] ?? 0);

    // Server-side validation
    if (!$nom || !$prenom || !$email || !$mdp || !$type || !$age) {
        $error = 'Veuillez remplir tous les champs correctement.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } elseif (strlen($mdp) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } else {
        // Prevent creating an admin via the public form
        if ($type === 'admin') {
            $type = 'candidat';
        }

        $userObj = new User($nom, $prenom, $email, $mdp, $type, $age);
        $auth = new AuthController();
        $auth->register($userObj);
        header('Location: login.php?registered=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Register - ProLink</title>
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

        html.dark-mode body { background: #0b1017 !important; }
        html.dark-mode .register-box { background: #151b26 !important; color: #e2e8f0; box-shadow: 0 8px 32px rgba(0,0,0,0.45); }
        html.dark-mode .register-box h2 { color: #38bdf8; }
        html.dark-mode .register-box input, html.dark-mode .register-box select { background: #1e293b; border: 1px solid rgba(148,163,184,0.25); color: #f8fafc; }
        html.dark-mode .register-box a { color: #7dd3fc; }
    </style>
</head>

<body>

<!-- NAVBAR -->
<?php include 'FrontOffice/components/navbar.php'; ?>

<div class="container">
    <div class="register-box">
        <h2>Inscription</h2>

        <?php if ($error): ?>
            <div style="color: #b00020; margin-bottom:10px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate data-validate="user-form">
            <input type="text" name="nom" placeholder="Nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
            <input type="text" name="prenom" placeholder="Prénom" required value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
            <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <input type="password" name="mdp" placeholder="Mot de passe" required>

            <select name="type" required>
                <option value="">Type utilisateur</option>
                <!-- Admin is intentionally hidden from public registration -->
                <option value="candidat" <?= (($_POST['type'] ?? '') === 'candidat') ? 'selected' : '' ?>>Candidat</option>
                <option value="entrepreneur" <?= (($_POST['type'] ?? '') === 'entrepreneur') ? 'selected' : '' ?>>Entrepreneur</option>
            </select>

            <input type="number" name="age" placeholder="Age" required value="<?= htmlspecialchars($_POST['age'] ?? '') ?>">

            <button type="submit">S'inscrire</button>
        </form>

        <a href="login.php">Déjà un compte ? Se connecter</a>
    </div>
</div>

<!-- FOOTER -->
<?php include 'FrontOffice/components/footer.php'; ?>

<script src="assets/forms-validation.js"></script>

</body>
</html>