<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/CommandeController.php';
require_once __DIR__ . '/../../config/mail.php';
require_once __DIR__ . '/../../lib/MailOtpService.php';

function normalizeTotpSecret(string $secret): string {
    return strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret) ?? '');
}

function base32Decode(string $encoded): string {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $clean = strtoupper(str_replace('=', '', preg_replace('/\s+/', '', $encoded) ?? ''));
    $bits = '';
    $out = '';
    $len = strlen($clean);
    for ($i = 0; $i < $len; $i++) {
        $pos = strpos($alphabet, $clean[$i]);
        if ($pos === false) {
            continue;
        }
        $bits .= str_pad(decbin((int) $pos), 5, '0', STR_PAD_LEFT);
    }
    $bitsLen = strlen($bits);
    for ($i = 0; $i + 8 <= $bitsLen; $i += 8) {
        $out .= chr(bindec(substr($bits, $i, 8)));
    }
    return $out;
}

function verifyTotpCode(string $secret, string $code, int $window = 1): bool {
    $secretBin = base32Decode($secret);
    if ($secretBin === '' || !preg_match('/^\d{6}$/', $code)) {
        return false;
    }
    $timeStep = 30;
    $counter = (int) floor(time() / $timeStep);
    for ($offset = -$window; $offset <= $window; $offset++) {
        $ctr = $counter + $offset;
        $binCounter = pack('N*', 0) . pack('N*', $ctr);
        $hash = hash_hmac('sha1', $binCounter, $secretBin, true);
        $idx = ord(substr($hash, -1)) & 0x0F;
        $part = substr($hash, $idx, 4);
        $val = unpack('N', $part)[1] & 0x7fffffff;
        $totp = str_pad((string) ($val % 1000000), 6, '0', STR_PAD_LEFT);
        if (hash_equals($totp, $code)) {
            return true;
        }
    }
    return false;
}

function computeImageHashFromDataUri(string $dataUri): ?string {
    if (!preg_match('/^data:image\/[a-zA-Z0-9.+-]+;base64,/', $dataUri)) {
        return null;
    }
    $parts = explode(',', $dataUri, 2);
    if (count($parts) !== 2) {
        return null;
    }
    $raw = base64_decode($parts[1], true);
    if ($raw === false || $raw === '') {
        return null;
    }
    if (!function_exists('imagecreatefromstring') || !function_exists('imagecopyresampled')) {
        return hash('sha256', $raw);
    }
    $im = @imagecreatefromstring($raw);
    if (!$im) {
        return null;
    }
    $w = 16;
    $h = 16;
    $small = imagecreatetruecolor($w, $h);
    imagecopyresampled($small, $im, 0, 0, 0, 0, $w, $h, imagesx($im), imagesy($im));
    imagefilter($small, IMG_FILTER_GRAYSCALE);
    $vals = [];
    $sum = 0;
    for ($y = 0; $y < $h; $y++) {
        for ($x = 0; $x < $w; $x++) {
            $rgb = imagecolorat($small, $x, $y);
            $gray = $rgb & 0xFF;
            $vals[] = $gray;
            $sum += $gray;
        }
    }
    imagedestroy($small);
    imagedestroy($im);
    $avg = $sum / count($vals);
    $bits = '';
    foreach ($vals as $v) {
        $bits .= ($v >= $avg) ? '1' : '0';
    }
    $hex = '';
    for ($i = 0; $i < strlen($bits); $i += 4) {
        $hex .= dechex(bindec(substr($bits, $i, 4)));
    }
    return strtolower($hex);
}

function isLocalDevRequest(): bool {
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    return strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false;
}

function hammingHexDistance(string $a, string $b): int {
    $a = strtolower(trim($a));
    $b = strtolower(trim($b));
    $len = min(strlen($a), strlen($b));
    if ($len === 0) {
        return PHP_INT_MAX;
    }
    $dist = 0;
    for ($i = 0; $i < $len; $i++) {
        $x = hexdec($a[$i]) ^ hexdec($b[$i]);
        $dist += (($x >> 0) & 1) + (($x >> 1) & 1) + (($x >> 2) & 1) + (($x >> 3) & 1);
    }
    return $dist;
}

function extractPrimaryFaceHash(string $stored): string {
    $stored = trim($stored);
    if ($stored === '') return '';
    $parts = explode(':', $stored, 2);
    return trim((string) ($parts[0] ?? ''));
}

$auth = new AuthController();
$u = $auth->profile();
if (!$u) {
    header('Location: ../login.php');
    exit;
}

$orderId = isset($_GET['order']) ? (int) $_GET['order'] : (int) ($_POST['order_id'] ?? 0);
if ($orderId <= 0) {
    header('Location: mesCommandes.php');
    exit;
}

$cmdP = new CommandeController();
$cmd = $cmdP->getById($orderId);
if (!$cmd || (int) ($cmd['id_acheteur'] ?? 0) !== (int) $u['iduser']) {
    header('Location: mesCommandes.php');
    exit;
}

$paymentMode = (string) ($cmd['mode_paiement'] ?? 'card');
if ($paymentMode !== 'card') {
    header('Location: mesCommandes.php?new=' . $orderId);
    exit;
}

if (!isset($_SESSION['checkout_faceid_ok']) || !is_array($_SESSION['checkout_faceid_ok'])) {
    $_SESSION['checkout_faceid_ok'] = [];
}
if (!isset($_SESSION['checkout_email_otp']) || !is_array($_SESSION['checkout_email_otp'])) {
    $_SESSION['checkout_email_otp'] = [];
}
if (!isset($_SESSION['pending_card_verification']) || !is_array($_SESSION['pending_card_verification'])) {
    $_SESSION['pending_card_verification'] = [];
}

$error = '';
$info = '';
$faceCheckState = '';
$faceCheckText = '';
$verificationMethod = (string) ($_POST['verification_method'] ?? 'authenticator');
if (!in_array($verificationMethod, ['authenticator', 'face_id'], true)) {
    $verificationMethod = 'authenticator';
}
$paymentEmail = trim((string) ($_POST['payment_email'] ?? ($u['payment_email'] ?? $u['email'] ?? '')));
$paymentEmailOtp = trim((string) ($_POST['payment_email_otp'] ?? ''));
$totpCode = trim((string) ($_POST['totp_code'] ?? ''));
$facePhotoData = trim((string) ($_POST['face_photo_verify_data'] ?? ''));
$totpSecret = normalizeTotpSecret((string) ($u['totp_secret'] ?? ''));
$hasAuthenticator = $totpSecret !== '';
$savedFaceHash = extractPrimaryFaceHash((string) ($u['face_photo_hash'] ?? ''));
$hasFaceId = ((int) ($u['face_photo_enabled'] ?? 0) === 1) && $savedFaceHash !== '';
$statut = (string) ($cmd['statut'] ?? '');
$availableMethods = [];
if ($hasAuthenticator) {
    $availableMethods[] = 'authenticator';
}
if ($hasFaceId) {
    $availableMethods[] = 'face_id';
}
if (empty($availableMethods)) {
    $availableMethods = ['authenticator'];
}
if (!in_array($verificationMethod, $availableMethods, true)) {
    $verificationMethod = $availableMethods[0];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['payment_action'] ?? '');
    if (empty($_POST['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Session expirée. Rechargez la page puis recommencez.';
    } elseif (!filter_var($paymentEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email de confirmation invalide.';
    } elseif ($statut !== 'en_attente_paiement' && $statut !== 'payee') {
        $error = 'Cette commande n est pas eligible a la verification paiement.';
    } elseif ($action === 'send_email_code') {
        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $_SESSION['checkout_email_otp'][(int) $orderId] = [
            'hash' => hash('sha256', $otpCode),
            'expires_at' => time() + 600,
            'email' => $paymentEmail,
            'id_user' => (int) $u['iduser'],
        ];
        $sent = MailOtpService::sendPaymentVerificationOtp($paymentEmail, $otpCode, 600);
        if ($sent) {
            $info = 'Code Gmail envoye. Verifiez votre boite de reception.';
        } else {
            if (isLocalDevRequest()) {
                $_SESSION['checkout_email_otp'][(int) $orderId]['dev_code'] = $otpCode;
                $info = 'SMTP indisponible en local. Code OTP de test: ' . $otpCode;
            } else {
                $error = 'Impossible d envoyer le code Gmail (verifiez la configuration SMTP).';
            }
        }
    } elseif ($action === 'check_face') {
        if (!$hasFaceId || $savedFaceHash === '') {
            $faceCheckState = 'non';
            $faceCheckText = 'NON';
            unset($_SESSION['checkout_faceid_ok'][(int) $orderId]);
            $error = 'Face ID: NON (profil non configure).';
        } else {
            $currentHash = computeImageHashFromDataUri($facePhotoData);
            if ($currentHash === null) {
                $faceCheckState = 'non';
                $faceCheckText = 'NON';
                unset($_SESSION['checkout_faceid_ok'][(int) $orderId]);
                $error = 'Face ID: NON (photo absente).';
            } else {
                $distance = hammingHexDistance($savedFaceHash, $currentHash);
                if ($distance > 70) {
                    $faceCheckState = 'non';
                    $faceCheckText = 'NON';
                    unset($_SESSION['checkout_faceid_ok'][(int) $orderId]);
                    $error = 'Face ID: NON';
                } else {
                    $faceCheckState = 'ok';
                    $faceCheckText = 'CONFIRME';
                    $_SESSION['checkout_faceid_ok'][(int) $orderId] = [
                        'ok' => true,
                        'at' => time(),
                    ];
                    $info = 'Face ID: CONFIRME';
                }
            }
        }
    } elseif ($action === 'verify_payment') {
        if ($statut === 'payee') {
            header('Location: mesCommandes.php?paid=' . $orderId);
            exit;
        }
        $otpPayload = $_SESSION['checkout_email_otp'][(int) $orderId] ?? null;
        if (!$otpPayload || !is_array($otpPayload)) {
            $error = 'Envoyez d abord le code Gmail.';
        } elseif ((int) ($otpPayload['id_user'] ?? 0) !== (int) $u['iduser']) {
            $error = 'Session OTP invalide.';
        } elseif ((int) ($otpPayload['expires_at'] ?? 0) < time()) {
            $error = 'Code Gmail expire. Demandez un nouveau code.';
        } elseif (!hash_equals((string) ($otpPayload['email'] ?? ''), $paymentEmail)) {
            $error = 'Email modifie. Renvoyez un nouveau code.';
        } elseif (!preg_match('/^\d{6}$/', $paymentEmailOtp) || !hash_equals((string) ($otpPayload['hash'] ?? ''), hash('sha256', $paymentEmailOtp))) {
            $error = 'Code Gmail invalide.';
        } elseif ($verificationMethod === 'authenticator') {
            if ($totpSecret === '') {
                $error = 'Authenticator non configure. Activez-le depuis votre profil.';
            } elseif (!preg_match('/^\d{6}$/', $totpCode)) {
                $error = 'Code Authenticator invalide.';
            } elseif (!verifyTotpCode($totpSecret, $totpCode, 1)) {
                $error = 'Code Authenticator invalide.';
            }
        } else {
            if (!$hasFaceId) {
                $error = 'Face ID non configure.';
            } elseif ($savedFaceHash === '') {
                $error = 'Mode Face ID indisponible.';
            } else {
                $faceProof = $_SESSION['checkout_faceid_ok'][(int) $orderId] ?? null;
                if (!$faceProof || !is_array($faceProof) || empty($faceProof['ok'])) {
                    $faceCheckState = 'non';
                    $faceCheckText = 'NON';
                    $error = 'Cliquez d abord sur Verifier Face ID.';
                } elseif ((int) ($faceProof['at'] ?? 0) + 300 < time()) {
                    $faceCheckState = 'non';
                    $faceCheckText = 'NON';
                    unset($_SESSION['checkout_faceid_ok'][(int) $orderId]);
                    $error = 'Verification Face ID expiree. Reprenez la photo.';
                } else {
                    $faceCheckState = 'ok';
                    $faceCheckText = 'CONFIRME';
                }
            }
        }

        if ($error === '') {
            $ok = $cmdP->confirmCardPayment($orderId, (int) $u['iduser']);
            if (!$ok) {
                $error = 'Impossible de confirmer le paiement pour cette commande.';
            } else {
                unset($_SESSION['checkout_faceid_ok'][(int) $u['iduser']]);
                unset($_SESSION['checkout_email_otp'][(int) $orderId]);
                unset($_SESSION['checkout_faceid_ok'][(int) $orderId]);
                unset($_SESSION['pending_card_verification'][(int) $orderId]);
                header('Location: mesCommandes.php?paid=' . $orderId);
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verification paiement carte - ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
    <style>
        .verify-card { max-width: 760px; margin: 0 auto; }
        .verify-card label { font-weight: 700; }
        .verify-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:8px; }
        .face-secure-box{
            margin-top:10px;
            border:1px solid rgba(148,163,184,.3);
            border-radius:14px;
            padding:12px;
            background:linear-gradient(180deg, rgba(15,23,42,.28), rgba(15,23,42,.18));
        }
        .face-badge{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            min-width:108px;
            padding:6px 10px;
            border-radius:999px;
            font-weight:800;
            letter-spacing:.02em;
            background:rgba(51,65,85,.6);
            color:#e2e8f0;
            border:1px solid rgba(148,163,184,.45);
        }
        .face-badge--ok{
            background:rgba(22,163,74,.2);
            border-color:rgba(34,197,94,.5);
            color:#86efac;
        }
        .face-badge--non{
            background:rgba(185,28,28,.2);
            border-color:rgba(248,113,113,.5);
            color:#fca5a5;
        }
        .face-help{
            margin-top:8px;
            color:#cbd5e1;
            font-size:.9rem;
            font-weight:700;
        }
        .face-hidden-video{
            position:absolute;
            width:1px;
            height:1px;
            opacity:0;
            pointer-events:none;
            overflow:hidden;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Verification paiement carte</h1>
        <p class="fo-lead">Commande #<?= (int) $orderId ?> — Montant: <strong><?= number_format((float) ($cmd['montant_total'] ?? 0), 3, ',', ' ') ?> TND</strong></p>
        <p class="fo-lead">Validation requise: <strong>Code Gmail + (Authenticator ou Face ID photo)</strong>.</p>
    </header>

    <?php if ($error): ?><p class="fo-banner fo-banner--err"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($info): ?><p class="fo-banner"><?= htmlspecialchars($info) ?></p><?php endif; ?>

    <form method="post" class="fo-checkout-card verify-card" novalidate autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $_SESSION['csrf_token']) ?>">
        <input type="hidden" name="order_id" value="<?= (int) $orderId ?>">
        <input type="hidden" id="face_photo_verify_data" name="face_photo_verify_data" value="<?= htmlspecialchars((string) ($_POST['face_photo_verify_data'] ?? '')) ?>">

        <label>Email de confirmation Gmail</label>
        <input type="email" name="payment_email" value="<?= htmlspecialchars($paymentEmail) ?>" required>

        <label>Code Gmail (6 chiffres)</label>
        <input type="text" name="payment_email_otp" placeholder="Code Gmail" inputmode="numeric" pattern="\d{6}" maxlength="6" autocomplete="one-time-code" value="<?= htmlspecialchars($paymentEmailOtp) ?>">

        <?php if ($hasAuthenticator): ?>
        <label>Code Authenticator (si mode App Authenticator)</label>
        <input type="text" name="totp_code" id="totp_code" placeholder="Code Authenticator (6 chiffres)" inputmode="numeric" value="<?= htmlspecialchars($totpCode) ?>">
        <?php endif; ?>

        <?php if (count($availableMethods) > 1): ?>
        <div class="verify-grid-2">
            <label class="fo-form-check" style="margin:0">
                <input type="radio" name="verification_method" value="authenticator" <?= $verificationMethod === 'authenticator' ? 'checked' : '' ?>>
                App Authenticator
            </label>
            <label class="fo-form-check" style="margin:0">
                <input type="radio" name="verification_method" value="face_id" <?= $verificationMethod === 'face_id' ? 'checked' : '' ?>>
                Face ID / Biometrie
            </label>
        </div>
        <?php elseif ($verificationMethod === 'authenticator'): ?>
        <input type="hidden" name="verification_method" value="authenticator">
        <p class="hint" style="margin-top:8px">Mode actif: App Authenticator.</p>
        <?php elseif ($verificationMethod === 'face_id'): ?>
        <input type="hidden" name="verification_method" value="face_id">
        <p class="hint" style="margin-top:8px">Mode actif: Face ID / Biometrie.</p>
        <?php endif; ?>

        <div id="faceIdActions" class="face-secure-box" style="display:<?= $verificationMethod === 'face_id' ? '' : 'none' ?>">
            <video id="faceVideo" autoplay playsinline class="face-hidden-video"></video>
            <canvas id="faceCanvas" width="320" height="240" style="display:none"></canvas>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <span class="face-badge <?= $faceCheckState === 'ok' ? 'face-badge--ok' : ($faceCheckState === 'non' ? 'face-badge--non' : '') ?>" id="faceBadge">
                    <?= htmlspecialchars($faceCheckText !== '' ? $faceCheckText : 'EN ATTENTE') ?>
                </span>
                <span class="hint" id="faceIdStatus"></span>
            </div>
            <p class="face-help">Capture securisee sans affichage camera: prenez une photo puis cliquez Verifier Face ID.</p>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px">
                <button type="button" id="faceOpenCamBtn" class="fo-btn fo-btn--secondary">Activer camera</button>
                <button type="button" id="faceIdVerifyBtn" class="fo-btn fo-btn--secondary">Prendre photo verification</button>
                <button type="submit" name="payment_action" value="check_face" class="fo-btn fo-btn--secondary">Verifier Face ID</button>
            </div>
        </div>

        <div class="fo-actions" style="margin-top:18px">
            <button type="submit" name="payment_action" value="send_email_code" class="fo-btn fo-btn--secondary">Envoyer code Gmail</button>
            <button type="submit" name="payment_action" value="verify_payment" class="fo-btn fo-btn--primary">Valider le paiement</button>
            <a href="mesCommandes.php" class="fo-btn fo-btn--secondary" style="text-decoration:none">Retour commandes</a>
        </div>
    </form>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
<script>
(function () {
    var verificationRadios = document.querySelectorAll('input[name="verification_method"]');
    var faceActions = document.getElementById('faceIdActions');
    var faceOpenCamBtn = document.getElementById('faceOpenCamBtn');
    var faceBtn = document.getElementById('faceIdVerifyBtn');
    var faceStatus = document.getElementById('faceIdStatus');
    var faceInput = document.getElementById('face_photo_verify_data');
    var faceBadge = document.getElementById('faceBadge');
    var totpInput = document.getElementById('totp_code');
    var faceVideo = document.getElementById('faceVideo');
    var faceCanvas = document.getElementById('faceCanvas');
    var stream = null;

    function refreshMethodUI() {
        var vm = document.querySelector('input[name="verification_method"]:checked');
        var useFace = vm ? vm.value === 'face_id' : <?= $verificationMethod === 'face_id' ? 'true' : 'false' ?>;
        if (faceActions) faceActions.style.display = useFace ? '' : 'none';
        if (totpInput) totpInput.disabled = !!useFace;
    }

    for (var i = 0; i < verificationRadios.length; i++) {
        verificationRadios[i].addEventListener('change', function () {
            if (faceInput) faceInput.value = '';
            if (faceStatus) faceStatus.textContent = '';
            if (faceBadge) {
                faceBadge.textContent = 'EN ATTENTE';
                faceBadge.classList.remove('face-badge--ok', 'face-badge--non');
            }
            refreshMethodUI();
        });
    }

    if (faceOpenCamBtn) {
        faceOpenCamBtn.addEventListener('click', async function () {
            try {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error('camera non supportee');
                }
                if (stream) {
                    var oldTracks = stream.getTracks ? stream.getTracks() : [];
                    for (var i = 0; i < oldTracks.length; i++) oldTracks[i].stop();
                }
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                    audio: false
                });
                if (faceVideo) faceVideo.srcObject = stream;
                if (faceStatus) faceStatus.textContent = 'Camera securisee active.';
            } catch (e) {
                if (faceStatus) faceStatus.textContent = 'Camera indisponible.';
            }
        });
    }

    if (faceBtn) {
        faceBtn.addEventListener('click', function () {
            if (!faceVideo || !faceCanvas || !faceVideo.videoWidth || !faceVideo.videoHeight) {
                if (faceStatus) faceStatus.textContent = 'Activez la camera puis prenez la photo.';
                return;
            }
            var ctx = faceCanvas.getContext('2d');
            ctx.drawImage(faceVideo, 0, 0, faceCanvas.width, faceCanvas.height);
            var data = faceCanvas.toDataURL('image/jpeg', 0.92);
            if (faceInput) faceInput.value = data;
            if (stream) {
                var tracks = stream.getTracks ? stream.getTracks() : [];
                for (var i = 0; i < tracks.length; i++) tracks[i].stop();
                stream = null;
            }
            if (faceStatus) faceStatus.textContent = 'Photo capturee. Cliquez sur Verifier Face ID.';
        });
    }

    window.addEventListener('beforeunload', function () {
        if (stream) {
            var tracks = stream.getTracks ? stream.getTracks() : [];
            for (var i = 0; i < tracks.length; i++) tracks[i].stop();
        }
    });

    refreshMethodUI();
})();
</script>
</body>
</html>
