<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/controller/AuthController.php';

session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp = trim($_POST['mdp'] ?? '');

    if (empty($email) || empty($mdp)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $auth = new AuthController();
        $user = $auth->login($email, $mdp);
        
        if ($user) {
            $_SESSION['user'] = $user;
            
            $role = strtolower($user['type'] ?? '');
            if ($role === 'admin') {
                header('Location: BackOffice/dashboard.php');
                exit;
            } else {
                header('Location: FrontOffice/home.php');
                exit;
            }
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}

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
        body { font-family: Arial; background: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); width: 350px; text-align: center; }
        h2 { color: #0073b1; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #0073b1; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #005f8d; }
        .error { color: #dc3545; margin-bottom: 10px; padding: 10px; background: #f8d7da; border-radius: 5px; }
        .success { color: #155724; margin-bottom: 10px; padding: 10px; background: #d4edda; border-radius: 5px; }
        .info { background: #e6f3ff; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 12px; text-align: left; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🔐 Connexion ProLink</h2>
        
        <div class="info">
            <strong>Comptes de test :</strong><br>
            Email: admin@prolink.com - Mdp: admin123<br>
            Email: test@test.com - Mdp: test
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="mdp" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>
        
        <p style="margin-top: 15px;">
            <a href="register.php">Créer un compte</a>
        </p>
    </div>
</body>
</html>