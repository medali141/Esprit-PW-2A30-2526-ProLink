<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../controller/AuthController.php';

function maskEmailForDisplay($email) {
    $email = (string) $email;
    if (strpos($email, '@') === false) {
        return 'votre adresse';
    }
    [$local, $domain] = explode('@', $email, 2);
    $keep = $local !== '' ? $local[0] : '';
    return $keep . '•••@' . $domain;
}

$error = '';
$success = '';
$codeSent = !empty($_SESSION['forgot_pwd_user_id']);
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController();

    if ($action === 'send_otp') {
        $email = trim($_POST['email'] ?? '');
        if ($email === '') {
            $error = 'Veuillez saisir votre adresse e-mail.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Adresse e-mail invalide.';
        } else {
            $userId = $auth->requestPasswordResetOtp($email);
            if ($userId !== null) {
                $_SESSION['forgot_pwd_user_id'] = $userId;
                $_SESSION['forgot_pwd_email'] = $email;
            }
            // Message identique que l’e-mail existe ou non (énumération)
            $success = 'Si un compte ProLink est associé à cette adresse, un code de vérification vient d’y être envoyé. Il est valable 15 minutes.';
            if ($userId !== null) {
                $codeSent = true;
            }
        }
    } elseif ($action === 'reset') {
        $otp = trim($_POST['otp'] ?? '');
        $mdp = trim($_POST['mdp'] ?? '');
        $uid = (int) ($_SESSION['forgot_pwd_user_id'] ?? 0);

        if ($uid <= 0) {
            $error = 'Aucune demande de code en cours. Demandez d’abord un code pour votre e-mail.';
        } elseif ($otp === '' || !ctype_digit($otp) || strlen($otp) !== 6) {
            $error = 'Saisissez le code à 6 chiffres reçu par e-mail.';
        } elseif (strlen($mdp) < 6) {
            $error = 'Le mot de passe doit contenir au moins 6 caractères.';
        } else {
            if ($auth->resetPasswordWithOtp($uid, $otp, $mdp)) {
                unset($_SESSION['forgot_pwd_user_id'], $_SESSION['forgot_pwd_email']);
                $success = 'Mot de passe mis à jour. Vous pouvez vous connecter.';
                $codeSent = false;
            } else {
                $error = 'Code incorrect ou expiré. Vous pouvez demander un nouveau code.';
            }
        }
    } elseif ($action === 'clear') {
        unset($_SESSION['forgot_pwd_user_id'], $_SESSION['forgot_pwd_email']);
        header('Location: forgotpwd.php');
        exit;
    }
} elseif (isset($_GET['new']) && $_GET['new'] === '1') {
    unset($_SESSION['forgot_pwd_user_id'], $_SESSION['forgot_pwd_email']);
    header('Location: forgotpwd.php');
    exit;
}
?>
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

        button, .btn-link {
            width: 100%;
            padding: 10px;
            background: #0073b1;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover, .btn-link:hover {
            background: #005f8d;
        }

        .btn-secondary {
            background: #6b7280;
            margin-top: 8px;
        }
        .btn-secondary:hover { background: #4b5563; }

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
            <?php if (!empty($_SESSION['forgot_pwd_user_id']) && !empty($_SESSION['forgot_pwd_email'])): ?>
                Un code a été demandé pour <?= htmlspecialchars(maskEmailForDisplay($_SESSION['forgot_pwd_email'])) ?>.
            <?php else: ?>
                Saisissez votre e-mail pour recevoir un code (valable 15 minutes), puis choisissez un nouveau mot de passe.
            <?php endif; ?>
        </p>

        <?php if ($error): ?>
            <div style="color: #b00020; margin-bottom:10px; font-size:14px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div style="color: #0a7f2a; margin-bottom:10px; font-size:14px;"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" action="forgotpwd.php" novalidate data-validate="forgot-send-form">
            <input type="hidden" name="action" value="send_otp">
            <input type="email" name="email" placeholder="Votre e-mail" autocomplete="email"
                value="<?= htmlspecialchars($_SESSION['forgot_pwd_email'] ?? ($_POST['email'] ?? '')) ?>"
                <?= !empty($_SESSION['forgot_pwd_user_id']) ? 'readonly style="opacity:0.9"' : '' ?>
            >

            <button type="submit">Recevoir le code</button>
        </form>

        <?php if (!empty($_SESSION['forgot_pwd_user_id'])): ?>
            <form method="post" action="forgotpwd.php" novalidate data-validate="forgot-reset-form" style="margin-top: 16px; border-top: 1px solid #e5e7eb; padding-top: 16px;">
                <input type="hidden" name="action" value="reset">
                <p class="info" style="text-align:left;">Code à 6 chiffres (e-mail) + nouveau mot de passe :</p>
                <input type="text" name="otp" placeholder="Code à 6 chiffres" inputmode="numeric" pattern="[0-9]*" maxlength="6" autocomplete="one-time-code">
                <input type="password" name="mdp" placeholder="Nouveau mot de passe" autocomplete="new-password">
                <button type="submit">Valider le nouveau mot de passe</button>
            </form>

            <form method="post" action="forgotpwd.php" style="margin-top: 8px;">
                <input type="hidden" name="action" value="send_otp">
                <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['forgot_pwd_email'] ?? '') ?>">
                <button type="submit" class="btn-secondary" title="Génère un nouveau code (l’ancien ne sera plus valable)">Renvoyer un code</button>
            </form>

            <a href="forgotpwd.php?new=1">Changer d’adresse e-mail</a>
        <?php endif; ?>

        <a href="login.php">Retour à la connexion</a>
    </div>
</div>

<!-- FOOTER -->
<?php include 'FrontOffice/components/footer.php'; ?>
<script src="assets/forms-validation.js"></script>

</body>
</html>
