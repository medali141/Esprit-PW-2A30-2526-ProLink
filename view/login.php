<?php
require_once __DIR__ . '/../controller/AuthController.php';

error_reporting(E_ALL);
$error = '';
$success = '';
// Handle POST login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp = trim($_POST['mdp'] ?? '');

    if (empty($email) || empty($mdp)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $auth = new AuthController();
        $user = $auth->login($email, $mdp);
        if ($user) {
            // user found — continue to session/redirect handling below
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
        // ensure session stores the logged user (AuthController::login already does this,
        // but be defensive in case it's changed)
        if ($user) {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            $_SESSION['user'] = $user;

            // redirect admin to backoffice, others to frontoffice
            $role = strtolower($user['type'] ?? '');
            if ($role === 'admin') {
                header('Location: BackOffice/dashboard.php');
                exit;
            } else {
                header('Location: FrontOffice/home.php');
                exit;
            }
        }
    }
}

// show message after successful registration
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $success = 'Inscription réussie. Vous pouvez vous connecter.';
}
?>







<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Login - ProLink</title>

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

        .login-box {
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

        .links {
            margin-top: 10px;
        }

        .links a {
            display: block;
            margin-top: 8px;
            text-decoration: none;
            color: #0073b1;
            font-size: 14px;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<?php include 'FrontOffice/components/navbar.php'; ?>

<div class="container">
    <div class="login-box">
        <h2>Connexion</h2>

        <?php if ($error): ?>
            <div style="color: #b00020; margin-bottom:10px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" onsubmit="return validateLogin(this)">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="mdp" placeholder="Mot de passe" required>

            <button type="submit">Se connecter</button>
        </form>

        <div class="links">
            <!-- 🔹 Forgot password -->
            <a href="forgotpwd.php">Mot de passe oublié ?</a>

            <!-- 🔹 Register -->
            <a href="register.php">Créer un compte</a>
        </div>
        </div>
    </div>
</div>

<?php if ($success): ?>
    <div style="text-align:center; margin-top:10px; color: #0a7f2a;"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<script>
    function validateLogin(form){
        clearFormErrors(form);
        const emailEl = form.querySelector('input[name="email"]');
        const mdpEl = form.querySelector('input[name="mdp"]');
        let ok = true;
        if(!emailEl || !emailEl.value.trim()){ setFieldError(emailEl, 'Email requis.'); ok = false; }
        if(!mdpEl || !mdpEl.value){ setFieldError(mdpEl, 'Mot de passe requis.'); ok = false; }
        else if(mdpEl.value.length < 6){ setFieldError(mdpEl, 'Le mot de passe doit contenir au moins 6 caractères.'); ok = false; }
        if(!ok){ if(typeof focusFirstInvalid === 'function') focusFirstInvalid(form); return false; }
        return true;
    }
</script>

<!-- FOOTER -->
<?php include 'FrontOffice/components/footer.php'; ?>

</body>
</html>