<?php
// Ensure session is active before any output (navbar may rely on session)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Handle password reset submission
$error = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../controller/AuthController.php';

    $email = trim($_POST['email'] ?? '');
    $newpwd = trim($_POST['newpwd'] ?? '');

    if (!$email || !$newpwd) {
        $error = 'Veuillez renseigner l\'email et le nouveau mot de passe.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide.';
    } elseif (strlen($newpwd) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } else {
        $auth = new AuthController();
        $auth->forgotPassword($email, $newpwd);
        $success = true;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié - ProLink</title>

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
        /* Toast / inline notification */
        .toast {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            min-width: 300px;
            max-width: 90%;
            padding: 14px 16px;
            border-radius: 8px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.12);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            z-index: 2000;
            opacity: 0;
            transform-origin: top center;
            transition: opacity .28s ease, transform .28s ease;
        }
        .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
        .toast.success { background: #e6ffef; color: #064e2a; border: 1px solid #b7f3d1 }
        .toast .actions { display:flex; gap:8px }
        .toast a, .toast button { background: #065f46; color: white; border: none; padding: 8px 10px; border-radius:6px; text-decoration:none; cursor:pointer }
        .toast button.secondary { background: transparent; color: inherit; border: 1px solid rgba(0,0,0,0.06) }
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

        <?php if ($error): ?>
            <div style="color:#b00020; margin-bottom:10px"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Votre email" required>

            <input type="password" name="newpwd" placeholder="Nouveau mot de passe" required>

            <button type="submit">Réinitialiser</button>
        </form>

        <a href="login.php">Retour à la connexion</a>
    </div>
</div>

<!-- FOOTER -->
<?php include 'FrontOffice/components/footer.php'; ?>

</body>
</html>
<?php if ($success): ?>
    <div id="reset-toast" class="toast success">
        <div>
            <strong>Mot de passe réinitialisé</strong>
            <div style="font-size:13px; margin-top:4px">Votre mot de passe a été mis à jour avec succès.</div>
        </div>
        <div class="actions">
            <a href="login.php">Se connecter</a>
            <button id="dismiss-toast" class="secondary">Fermer</button>
        </div>
    </div>

    <script>
        (function(){
            const toast = document.getElementById('reset-toast');
            const btn = document.getElementById('dismiss-toast');
            // show with small animation
            requestAnimationFrame(()=> toast.classList.add('show'));

            // auto-redirect after 3.5s
            const timer = setTimeout(()=>{
                window.location = 'login.php';
            }, 3500);

            btn.addEventListener('click', ()=>{
                toast.classList.remove('show');
                clearTimeout(timer);
            });
        })();
    </script>
<?php endif; ?>

</body>
</html>