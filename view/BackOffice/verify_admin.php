<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../lib/MailOtpService.php';

$error = '';
$info = '';
$sent = false;

if (!isset($_SESSION['admin_mfa'])) {
    header('Location: ../login.php');
    exit;
}

$mfa = &$_SESSION['admin_mfa'];

// Backfill defaults in case session was created before these fields existed
if (!isset($mfa['attempts']))     $mfa['attempts'] = 0;
if (!isset($mfa['resend_after'])) $mfa['resend_after'] = 0;
if (!isset($mfa['locked_until'])) $mfa['locked_until'] = 0;

// Auto-unlock once the lock window has elapsed
if ((int) $mfa['locked_until'] > 0 && time() >= (int) $mfa['locked_until']) {
    $mfa['locked_until'] = 0;
    $mfa['attempts'] = 0;
}

$now           = time();
$isLocked      = ((int) $mfa['locked_until'] > $now);
$lockRemaining = $isLocked ? ((int) $mfa['locked_until'] - $now) : 0;

// Lock duration (seconds) applied after 3 wrong attempts
const ADMIN_MFA_MAX_ATTEMPTS = 3;
const ADMIN_MFA_LOCK_SECONDS = 900;   // 15 minutes
const ADMIN_MFA_RESEND_COOLDOWN = 60; // seconds between resends

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLocked) {
    // While locked, refuse any submission outright
    $minutes = (int) ceil($lockRemaining / 60);
    $error = 'Compte temporairement verrouillé après plusieurs tentatives. Réessayez dans ' . $minutes . ' minute(s).';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend'])) {
    // Resend: enforce cooldown
    $resendAfter = (int) $mfa['resend_after'];
    if ($resendAfter > $now) {
        $remaining = $resendAfter - $now;
        $error = 'Veuillez patienter ' . $remaining . ' seconde(s) avant de demander un nouveau code.';
    } else {
        $code = random_int(100000, 999999);
        $mfa['code']         = (string) $code;
        $mfa['expires']      = $now + 300;
        $mfa['resend_after'] = $now + ADMIN_MFA_RESEND_COOLDOWN;
        // Note: attempts counter is NOT reset on resend so that lockout cannot be bypassed.

        $delivered = MailOtpService::sendAdminMfaCode(
            (string) ($mfa['email'] ?? ''),
            (string) $code,
            (int) $mfa['expires']
        );

        if ($delivered) {
            $sent = true;
        } else {
            $error = "Impossible d'envoyer l'e-mail pour le moment. Veuillez réessayer plus tard.";
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $input = trim((string) ($_POST['code'] ?? ''));
    if ($input === '') {
        $error = 'Veuillez entrer le code.';
    } elseif ($now > (int) ($mfa['expires'] ?? 0)) {
        $error = 'Le code a expiré. Veuillez demander un nouveau code.';
    } elseif ($input !== ($mfa['code'] ?? '')) {
        $mfa['attempts'] = (int) $mfa['attempts'] + 1;
        $remaining = max(0, ADMIN_MFA_MAX_ATTEMPTS - (int) $mfa['attempts']);

        if ((int) $mfa['attempts'] >= ADMIN_MFA_MAX_ATTEMPTS) {
            $mfa['locked_until'] = $now + ADMIN_MFA_LOCK_SECONDS;
            $isLocked = true;
            $lockRemaining = ADMIN_MFA_LOCK_SECONDS;
            // Invalidate the active code so it cannot be used during the lock window
            $mfa['code'] = '';
            $minutes = (int) ceil(ADMIN_MFA_LOCK_SECONDS / 60);
            $error = 'Trop de tentatives incorrectes. Compte verrouillé pour ' . $minutes . ' minute(s).';
        } else {
            $error = 'Code invalide. Tentative(s) restante(s) : ' . $remaining . '.';
        }
    } else {
        // code ok, finalize login
        require_once __DIR__ . '/../../controller/UserP.php';
        $up = new UserP();
        $userRow = $up->showUser((int) $mfa['id']);
        if ($userRow) {
            $auth = new AuthController();
            // store full user in session
            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
            $_SESSION['user'] = $userRow;
            unset($_SESSION['admin_mfa']);
            header('Location: ../BackOffice/dashboard/dashboard.php');
            exit;
        }
        $error = 'Utilisateur introuvable.';
    }
}

// Recompute UI helpers after handling the POST
$now            = time();
$isLocked       = ((int) $mfa['locked_until'] > $now);
$lockRemaining  = $isLocked ? ((int) $mfa['locked_until'] - $now) : 0;
$resendCooldown = max(0, (int) $mfa['resend_after'] - $now);
$canResend      = !$isLocked && $resendCooldown === 0;
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Vérification administrateur — ProLink</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .mfa-box { max-width:420px; margin:48px auto; background:white; padding:22px; border-radius:10px }
        .mfa-box h2{margin-top:0}
        .mfa-note{font-size:14px;color:#6b7280}
        .mfa-actions{display:flex;gap:12px;margin-top:12px}
        .mfa-meta{margin-top:10px;font-size:13px;color:#6b7280}
        .mfa-meta strong{color:#0f172a}
        .mfa-locked{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px;border-radius:8px;margin-top:12px}
        button[disabled]{opacity:.6;cursor:not-allowed}
    </style>
</head>
<body>
<?php require_once __DIR__ . '/_layout/sidebar.php'; ?>
<div class="content">
    <div class="container page-full">
        <div class="card mfa-box">
            <h2>Code administrateur</h2>
            <p class="mfa-note">Nous avons envoyé un code à l'adresse <strong><?= htmlspecialchars($mfa['email']) ?></strong>. Entrez-le ci-dessous pour terminer la connexion.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($sent): ?>
                <div class="alert alert-success">Code renvoyé.</div>
            <?php endif; ?>

            <?php if ($isLocked): ?>
                <div class="mfa-locked">
                    Compte temporairement verrouillé.
                    Réessayez dans <strong id="lockCountdown" data-seconds="<?= (int) $lockRemaining ?>"><?= (int) ceil($lockRemaining / 60) ?> minute(s)</strong>.
                </div>
                <div class="mfa-actions">
                    <a class="btn btn-secondary" href="../login.php">Retour à la connexion</a>
                </div>
            <?php else: ?>
                <form method="POST" autocomplete="off">
                    <input name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" placeholder="123456" style="padding:10px;width:100%;border-radius:8px;border:1px solid #ddd;margin-top:12px">
                    <div class="mfa-actions">
                        <button type="submit" class="btn btn-primary">Valider</button>
                        <button name="resend" value="1" class="btn btn-secondary" <?= $canResend ? '' : 'disabled' ?>>
                            <?php if ($canResend): ?>
                                Renvoyer
                            <?php else: ?>
                                Renvoyer (<span id="resendCountdown" data-seconds="<?= (int) $resendCooldown ?>"><?= (int) $resendCooldown ?></span>s)
                            <?php endif; ?>
                        </button>
                    </div>
                </form>
                <div class="mfa-meta">
                    Tentatives utilisées : <strong><?= (int) $mfa['attempts'] ?>/<?= (int) ADMIN_MFA_MAX_ATTEMPTS ?></strong>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    (function () {
        function tick(elId, btn, onZero) {
            var el = document.getElementById(elId);
            if (!el) return;
            var remaining = parseInt(el.getAttribute('data-seconds') || '0', 10);
            if (!(remaining > 0)) { if (typeof onZero === 'function') onZero(); return; }
            var timer = setInterval(function () {
                remaining -= 1;
                if (remaining <= 0) {
                    clearInterval(timer);
                    if (typeof onZero === 'function') onZero();
                    return;
                }
                el.textContent = remaining + (elId === 'lockCountdown' ? 's restantes' : '');
                if (elId === 'lockCountdown') {
                    var mins = Math.ceil(remaining / 60);
                    el.textContent = mins + ' minute(s)';
                }
            }, 1000);
        }
        tick('resendCountdown', null, function () {
            // Reload to re-enable the resend button server-side once cooldown elapsed
            window.location.reload();
        });
        tick('lockCountdown', null, function () {
            window.location.reload();
        });
    })();
</script>
</body>
</html>
