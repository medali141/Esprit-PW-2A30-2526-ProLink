<?php
require_once __DIR__ . '/../controller/AuthController.php';
<<<<<<< HEAD
require_once __DIR__ . '/../lib/MailOtpService.php';
=======
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5

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
<<<<<<< HEAD
        // but for admin we require a second factor code first)
        if ($user) {
            $role = strtolower($user['type'] ?? '');
            if ($role === 'admin') {
                // remove any session user set by AuthController::login to avoid granting access
                if (session_status() !== PHP_SESSION_ACTIVE) {
                    session_start();
                }
                unset($_SESSION['user']);

                // generate a one-time 6-digit code and store it in session with expiry (5 minutes)
                $code = random_int(100000, 999999);
                $_SESSION['admin_mfa'] = [
                    'id' => (int) $user['iduser'],
                    'code' => (string) $code,
                    'expires' => time() + 300,
                    'email' => $user['email'] ?? '',
                    'attempts' => 0,
                    'resend_after' => time() + 60, // wait 60s before allowing resend
                    'locked_until' => 0 // timestamp until which login is blocked
                ];

                // Send admin MFA code through PHPMailer / SMTP pipeline
                MailOtpService::sendAdminMfaCode(
                    (string) ($_SESSION['admin_mfa']['email'] ?? ''),
                    (string) $code,
                    (int) $_SESSION['admin_mfa']['expires']
                );

                // redirect to verification form
                header('Location: BackOffice/verify_admin.php');
                exit;
            }

            // Non-admin: finalize session and redirect as before
=======
        // but be defensive in case it's changed)
        if ($user) {
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            $_SESSION['user'] = $user;

<<<<<<< HEAD
            $next = (string) ($_GET['next'] ?? $_POST['next'] ?? '');
            if ($next !== '' && strpos($next, '..') === false && preg_match('#^FrontOffice/forum/#', $next)) {
                header('Location: ' . $next);
                exit;
            }
            header('Location: FrontOffice/home.php');
            exit;
=======
            // redirect admin to backoffice, others to frontoffice
            $role = strtolower($user['type'] ?? '');
            if ($role === 'admin') {
                header('Location: BackOffice/dashboard.php');
                exit;
            } else {
                header('Location: FrontOffice/home.php');
                exit;
            }
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
        }
    }
}

// show message after successful registration
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $success = 'Inscription réussie. Vous pouvez vous connecter.';
}
<<<<<<< HEAD

$nextParam = '';
if (isset($_GET['next'])) {
    $cand = (string) $_GET['next'];
    if ($cand !== '' && strpos($cand, '..') === false && preg_match('#^FrontOffice/forum/#', $cand)) {
        $nextParam = $cand;
    }
}
=======
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
?>







<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Login - ProLink</title>
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

        html.dark-mode body { background: #0b1017 !important; }
        html.dark-mode .login-box { background: #151b26 !important; color: #e2e8f0; box-shadow: 0 8px 32px rgba(0,0,0,0.45); }
        html.dark-mode .login-box h2 { color: #38bdf8; }
        html.dark-mode .login-box input { background: #1e293b; border: 1px solid rgba(148,163,184,0.25); color: #f8fafc; }
        html.dark-mode .links a { color: #7dd3fc; }
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

        <form method="POST" novalidate data-validate="login-form">
<<<<<<< HEAD
            <?php if ($nextParam !== ''): ?>
                <input type="hidden" name="next" value="<?= htmlspecialchars($nextParam) ?>">
            <?php endif; ?>
=======
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
            <input type="email" name="email" placeholder="Email" autocomplete="username">
            <input type="password" name="mdp" placeholder="Mot de passe" autocomplete="current-password">

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

<!-- FOOTER -->
<?php include 'FrontOffice/components/footer.php'; ?>
<script src="assets/forms-validation.js"></script>

</body>
</html>