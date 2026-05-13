<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../controller/AuthController.php';
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../lib/MailOtpService.php';

if (!isset($_SESSION['pwd_reset_otp']) || !is_array($_SESSION['pwd_reset_otp'])) {
    $_SESSION['pwd_reset_otp'] = [];
}

$error = '';
$success = '';
$step = 'request';
$emailInput = trim((string) ($_POST['email'] ?? ''));
$newPwdInput = (string) ($_POST['mdp'] ?? '');
$otpInput = trim((string) ($_POST['otp_code'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['forgot_action'] ?? 'send_otp');
    if (!filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide.';
    } elseif ($newPwdInput === '' || strlen($newPwdInput) < 6) {
        $error = 'Nouveau mot de passe: au moins 6 caracteres.';
    } elseif ($action === 'send_otp') {
        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $_SESSION['pwd_reset_otp'][$emailInput] = [
            'hash' => hash('sha256', $otpCode),
            'expires_at' => time() + (int) PROLINK_PWD_RESET_OTP_TTL,
            'new_password' => $newPwdInput,
        ];
        $sent = MailOtpService::sendPasswordResetOtp($emailInput, $otpCode, (int) PROLINK_PWD_RESET_OTP_TTL);
        if ($sent) {
            $success = 'Code de verification envoye sur votre email.';
            $step = 'verify';
        } else {
            $error = 'Impossible d envoyer le code email. Verifiez la config SMTP.';
        }
    } elseif ($action === 'verify_otp') {
        $step = 'verify';
        $payload = $_SESSION['pwd_reset_otp'][$emailInput] ?? null;
        if (!$payload || !is_array($payload)) {
            $error = 'Aucun code actif. Cliquez sur "Envoyer le code".';
        } elseif ((int) ($payload['expires_at'] ?? 0) < time()) {
            $error = 'Code expire. Demandez un nouveau code.';
        } elseif (!preg_match('/^\d{6}$/', $otpInput) || !hash_equals((string) ($payload['hash'] ?? ''), hash('sha256', $otpInput))) {
            $error = 'Code OTP invalide.';
        } else {
            $auth = new AuthController();
            $auth->forgotPassword($emailInput, (string) ($payload['new_password'] ?? $newPwdInput));
            unset($_SESSION['pwd_reset_otp'][$emailInput]);
            $success = 'Mot de passe reinitialise avec succes. Vous pouvez vous connecter.';
            $step = 'done';
        }
    }
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

        <?php if ($error !== ''): ?>
            <p style="color:#b00020; font-size:13px; margin:0 0 8px;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($success !== ''): ?>
            <p style="color:#166534; font-size:13px; margin:0 0 8px;"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="post" action="#" novalidate data-validate="forgot-form">
            <input type="email" name="email" placeholder="Votre email" required maxlength="150" autocomplete="email"
                   value="<?= htmlspecialchars($emailInput) ?>">

            <input type="password" name="mdp" placeholder="Nouveau mot de passe" required minlength="6" maxlength="128" autocomplete="new-password"
                   value="<?= htmlspecialchars($newPwdInput) ?>">

            <?php if ($step === 'verify' || $step === 'done'): ?>
                <input type="text" name="otp_code" placeholder="Code OTP (6 chiffres)" inputmode="numeric"
                       value="<?= htmlspecialchars($otpInput) ?>">
                <?php if ($step !== 'done'): ?>
                    <button type="submit" name="forgot_action" value="verify_otp">Valider le code OTP</button>
                <?php endif; ?>
            <?php else: ?>
                <button type="submit" name="forgot_action" value="send_otp">Envoyer le code OTP</button>
            <?php endif; ?>
        </form>

        <a href="login.php">Retour à la connexion</a>
    </div>
</div>

<!-- FOOTER -->
<?php include 'FrontOffice/components/footer.php'; ?>
<script src="assets/forms-validation.js"></script>

</body>
</html>