<?php
// central init (session, $baseUrl, helpers)
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controller/AuthController.php';
// Load model and helper controller classes used below
require_once __DIR__ . '/../../model/User.php';
require_once __DIR__ . '/../../controller/UserP.php';


$auth = new AuthController();
$user = $auth->profile();
if (!$user) {
    header('Location: ../login.php');
    exit;
}

$error = '';
$success = '';
$generatedTotp = '';

function normalizeTotpSecret(string $secret): string {
    return strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret) ?? '');
}

function generateBase32Secret(int $length = 32): string {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bytes = random_bytes($length);
    $out = '';
    for ($i = 0; $i < $length; $i++) {
        $out .= $alphabet[ord($bytes[$i]) % strlen($alphabet)];
    }
    return $out;
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
        // Fallback sans GD pour eviter une erreur fatale.
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

function normalizeFaceHashInput(string $value): string {
    $value = strtolower(trim($value));
    return preg_match('/^[a-f0-9]{64}$/', $value) ? $value : '';
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

// Handle POST update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $totpSecretInput = normalizeTotpSecret((string) ($_POST['totp_secret'] ?? ''));
    $facePhotoFront = trim((string) ($_POST['face_photo_front'] ?? ''));
    $facePhotoLeft = trim((string) ($_POST['face_photo_left'] ?? ''));
    $facePhotoRight = trim((string) ($_POST['face_photo_right'] ?? ''));
    $faceHashFront = normalizeFaceHashInput((string) ($_POST['face_hash_front'] ?? ''));
    $faceHashLeft = normalizeFaceHashInput((string) ($_POST['face_hash_left'] ?? ''));
    $faceHashRight = normalizeFaceHashInput((string) ($_POST['face_hash_right'] ?? ''));
    $disableFacePhoto = (string) ($_POST['disable_face_photo'] ?? '0') === '1';
    $facePhotoHash = null;

    if (!$nom || !$prenom || !$email || !$type || !$age) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide.';
    } elseif ($totpSecretInput !== '' && strlen($totpSecretInput) < 16) {
        $error = 'Clé Authenticator invalide (minimum 16 caractères).';
    } elseif (!$disableFacePhoto && ($facePhotoFront !== '' || $facePhotoLeft !== '' || $facePhotoRight !== '')) {
        if ($facePhotoFront === '' || $facePhotoLeft === '' || $facePhotoRight === '') {
            $error = 'Capture incomplete. Faites les 3 positions (face, gauche, droite).';
        } else {
            $hFront = $faceHashFront !== '' ? $faceHashFront : computeImageHashFromDataUri($facePhotoFront);
            $hLeft = $faceHashLeft !== '' ? $faceHashLeft : computeImageHashFromDataUri($facePhotoLeft);
            $hRight = $faceHashRight !== '' ? $faceHashRight : computeImageHashFromDataUri($facePhotoRight);
            if ($hFront === null || $hLeft === null || $hRight === null) {
                $error = 'Photos visage invalides. Reprenez la capture live.';
            } else {
                $d1 = hammingHexDistance($hFront, $hLeft);
                $d2 = hammingHexDistance($hFront, $hRight);
                if ($d1 < 8 || $d2 < 8) {
                    $error = 'Mouvements insuffisants. Tournez clairement a gauche puis a droite.';
                } else {
                    $facePhotoHash = $hFront . ':' . hash('sha256', $hFront . '|' . $hLeft . '|' . $hRight);
                }
            }
        }
    } else {
        // Prevent elevating to admin
        if ($type === 'admin') {
            $type = $user['type'];
        }
        if ($totpSecretInput === '') {
            $totpSecretInput = normalizeTotpSecret((string) ($user['totp_secret'] ?? ''));
            if ($totpSecretInput === '') {
                $totpSecretInput = generateBase32Secret(32);
            }
        }
        $updatedUserObj = new User(
            $nom,
            $prenom,
            $email,
            $user['mdp'] ?? '',
            $type,
            $age,
            User::KEEP_VALUE,
            User::KEEP_VALUE,
            User::KEEP_VALUE,
            User::KEEP_VALUE,
            $totpSecretInput,
            User::KEEP_VALUE,
            User::KEEP_VALUE,
            $disableFacePhoto ? '' : (($facePhotoHash !== null && $facePhotoHash !== '') ? $facePhotoHash : User::KEEP_VALUE),
            $disableFacePhoto ? 0 : (($facePhotoHash !== null && $facePhotoHash !== '') ? 1 : User::KEEP_VALUE)
        );
        $userP = new UserP();
        $userP->updateUser($updatedUserObj, $user['iduser']);

        // Refresh session user data
        $fresh = $userP->showUser($user['iduser']);
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['user'] = $fresh;

        header('Location: profile.php?updated=1');
        exit;
    }
}
$existingTotp = normalizeTotpSecret((string) ($user['totp_secret'] ?? ''));
$generatedTotp = $existingTotp !== '' ? $existingTotp : generateBase32Secret(32);
$hasFacePhoto = ((int) ($user['face_photo_enabled'] ?? 0) === 1) && extractPrimaryFaceHash((string) ($user['face_photo_hash'] ?? '')) !== '';
$totpProvisionSecret = normalizeTotpSecret((string) ($_POST['totp_secret'] ?? $generatedTotp));
$otpLabel = rawurlencode('ProLink:' . (string) ($user['email'] ?? 'user'));
$otpIssuer = rawurlencode('ProLink');
$otpUri = 'otpauth://totp/' . $otpLabel
    . '?secret=' . rawurlencode($totpProvisionSecret)
    . '&issuer=' . $otpIssuer
    . '&algorithm=SHA1&digits=6&period=30';
$totpQrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . rawurlencode($otpUri);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le profil - ProLink</title>
    <style>
        body { font-family: Arial; background:#f3f2ef; margin:0; display:flex; flex-direction:column; min-height:100vh }
        .main{ flex:1 }
        .container{
            max-width:900px;
            margin:34px auto;
            background:white;
            padding:22px;
            border-radius:16px;
            box-shadow:0 14px 34px rgba(15,23,42,.08);
            border:1px solid rgba(148,163,184,.25);
        }
        .hero {
            border-radius:14px;
            padding:14px 16px;
            margin-bottom:14px;
            background: linear-gradient(120deg, rgba(14,165,233,.14), rgba(34,197,94,.1));
            border: 1px solid rgba(56,189,248,.26);
        }
        .hero h2 { margin:0 0 5px; color:#0f172a; }
        .hero p { margin:0; color:#475569; font-size:.92rem; font-weight:700; }
        .section-card{
            border:1px solid rgba(148,163,184,.24);
            border-radius:12px;
            padding:12px;
            margin:12px 0;
            background:#f8fafc;
        }
        .section-card h3{ margin:0 0 8px; color:#0f172a; font-size:1rem; }
        .field-grid {
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:10px;
        }
        .field-grid--single { grid-template-columns:1fr; }
        input, select {
            width:100%;
            padding:10px 11px;
            margin:6px 0;
            border-radius:10px;
            border:1px solid rgba(148,163,184,.38);
            background:#fff;
            box-sizing:border-box;
        }
        .btn-row{
            display:flex;
            gap:8px;
            flex-wrap:wrap;
            margin-top:8px;
        }
        button{
            background:#0ea5e9;
            color:white;
            border:none;
            padding:10px 14px;
            border-radius:10px;
            font-weight:700;
            cursor:pointer;
        }
        button:hover{ filter:brightness(.97); }
        .btn-secondary{ background:#475569; }
        .btn-primary-save{ background:linear-gradient(135deg,#0ea5e9,#2563eb); margin-top:8px; }
        .helper{
            display:block;
            color:#475569;
            font-size:.82rem;
            font-weight:700;
            margin-top:4px;
        }
        .status {
            margin:6px 0;
            color:#334155;
            font-weight:700;
            font-size:.9rem;
        }
        .face-live-wrap{
            position:relative;
            width:100%;
            max-width:420px;
            aspect-ratio: 4 / 3;
            margin-bottom:10px;
        }
        .face-live-wrap video{
            width:100%;
            height:100%;
            border-radius:10px;
            border:1px solid #cbd5e1;
            background:#0f172a;
            display:block;
            object-fit:cover;
        }
        .face-guide-overlay{
            position:absolute;
            inset:0;
            pointer-events:none;
            border-radius:10px;
            overflow:hidden;
        }
        .face-oval{
            position:absolute;
            left:50%;
            top:50%;
            width:170px;
            height:210px;
            transform:translate(-50%,-50%);
            border:3px solid rgba(56,189,248,.95);
            border-radius:48% 48% 45% 45%;
            box-shadow:0 0 0 999px rgba(2,6,23,.26);
        }
        .scan-line{
            position:absolute;
            left:50%;
            top:20%;
            width:210px;
            height:3px;
            transform:translateX(-50%);
            background:linear-gradient(90deg, rgba(14,165,233,0), rgba(14,165,233,.95), rgba(14,165,233,0));
            animation:scanMove 2s linear infinite;
            opacity:.95;
        }
        .turn-hint{
            position:absolute;
            left:50%;
            bottom:10px;
            transform:translateX(-50%);
            padding:5px 10px;
            border-radius:999px;
            background:rgba(15,23,42,.72);
            color:#fff;
            font-size:.78rem;
            font-weight:700;
            letter-spacing:.02em;
        }
        .face-guide-overlay.turn-left .turn-hint::before{ content:'\2190 '; }
        .face-guide-overlay.turn-right .turn-hint::before{ content:'\2192 '; }
        .face-guide-overlay.pulse .face-oval{
            animation:pulseFace .55s ease;
        }
        .face-flash{
            position:absolute;
            inset:0;
            border-radius:10px;
            background:rgba(255,255,255,.45);
            opacity:0;
            pointer-events:none;
        }
        .face-flash.on{ animation:flashShot .35s ease; }
        .face-capture-controls{
            max-width:420px;
        }
        @keyframes scanMove{
            0%{ top:22%; }
            50%{ top:72%; }
            100%{ top:22%; }
        }
        @keyframes pulseFace{
            0%{ transform:translate(-50%,-50%) scale(1); }
            50%{ transform:translate(-50%,-50%) scale(1.04); }
            100%{ transform:translate(-50%,-50%) scale(1); }
        }
        @keyframes flashShot{
            0%{ opacity:0; }
            20%{ opacity:1; }
            100%{ opacity:0; }
        }
        .qr-block{
            margin-top:10px;
            padding:10px;
            border:1px solid #cbd5e1;
            border-radius:10px;
            background:#fff;
        }
        .qr-block p{ margin:0 0 8px; font-weight:700; color:#0f172a; }
        .qr-img{
            display:block;
            border-radius:10px;
            border:1px solid #cbd5e1;
            box-shadow:0 8px 20px rgba(15,23,42,.12);
        }
        .back-link{
            margin-top:8px;
            color:#0ea5e9;
            font-weight:700;
            text-decoration:none;
            display:inline-block;
        }
        @media (max-width: 760px) {
            .field-grid { grid-template-columns:1fr; }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/components/navbar.php'; ?>

<main class="main">
<div class="container">
    <div class="hero">
        <h2>Modifier mon profil</h2>
        <p>Gerez vos informations personnelles, votre securite Authenticator et Face ID.</p>
    </div>

    <?php if ($error): ?>
        <div style="color:#b00020"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate data-validate="user-form">
        <section class="section-card">
            <h3>Informations personnelles</h3>
            <div class="field-grid">
                <input type="text" name="nom" placeholder="Nom" value="<?= htmlspecialchars($_POST['nom'] ?? $user['nom']) ?>" required>
                <input type="text" name="prenom" placeholder="Prénom" value="<?= htmlspecialchars($_POST['prenom'] ?? $user['prenom']) ?>" required>
                <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>" required>
                <select name="type" required>
                    <option value="">Type utilisateur</option>
                    <option value="candidat" <?= ((($_POST['type'] ?? $user['type']) === 'candidat') ? 'selected' : '') ?>>Candidat</option>
                    <option value="entrepreneur" <?= ((($_POST['type'] ?? $user['type']) === 'entrepreneur') ? 'selected' : '') ?>>Entrepreneur</option>
                </select>
            </div>
            <div class="field-grid field-grid--single">
                <input type="number" name="age" placeholder="Age" value="<?= htmlspecialchars($_POST['age'] ?? $user['age']) ?>" required>
            </div>
        </section>

        <section class="section-card">
            <h3>Double facteur (Authenticator)</h3>
            <input type="text" name="totp_secret" placeholder="Clé secrète Authenticator (Base32)"
                   value="<?= htmlspecialchars($_POST['totp_secret'] ?? $generatedTotp) ?>">
            <small class="helper">
                Ajoutez cette clé dans Google/Microsoft Authenticator. Elle sera demandée au paiement par carte.
            </small>
        <?php if ($existingTotp === ''): ?>
            <div class="qr-block">
                <p>QR code (affiche une seule fois pour l activation)</p>
                <img class="qr-img" src="<?= htmlspecialchars($totpQrUrl) ?>" alt="QR Authenticator ProLink" width="220" height="220">
                <small class="helper" style="margin-top:8px">
                    Scannez ce QR code dans Google/Microsoft Authenticator puis enregistrez le profil. Ensuite, le QR ne sera plus affiche.
                </small>
            </div>
        <?php else: ?>
            <small class="helper" style="color:#166534;margin-top:6px">
                Authenticator deja configure. Le QR code one-time est masque.
            </small>
        <?php endif; ?>
        </section>

        <section class="section-card">
            <h3>Face ID (video live guide)</h3>
            <p id="faceStatus" class="status">
                Statut: <?= $hasFacePhoto ? 'visage enregistre' : 'non configure' ?>.
            </p>
            <div>
                <div class="face-live-wrap">
                    <video id="faceVideo" autoplay playsinline></video>
                    <div id="faceGuideOverlay" class="face-guide-overlay">
                        <div class="face-oval"></div>
                        <div class="scan-line"></div>
                        <div class="turn-hint" id="turnHint">FACE AVANT</div>
                    </div>
                    <div id="faceFlash" class="face-flash"></div>
                </div>
                <div class="face-capture-controls">
                    <canvas id="faceCanvas" width="320" height="240" style="display:none"></canvas>
                    <input type="hidden" name="face_photo_front" id="face_photo_front" value="">
                    <input type="hidden" name="face_photo_left" id="face_photo_left" value="">
                    <input type="hidden" name="face_photo_right" id="face_photo_right" value="">
                    <input type="hidden" name="face_hash_front" id="face_hash_front" value="">
                    <input type="hidden" name="face_hash_left" id="face_hash_left" value="">
                    <input type="hidden" name="face_hash_right" id="face_hash_right" value="">
                    <input type="hidden" name="disable_face_photo" id="disable_face_photo" value="0">
                    <div class="btn-row">
                        <button type="button" id="faceOpenCamBtn">Activer camera</button>
                        <button type="button" id="faceStartGuideBtn" class="btn-secondary">Demarrer guide live</button>
                        <button type="button" id="faceCaptureStepBtn" class="btn-secondary">Capturer cette position</button>
                        <button type="button" id="faceDisableBtn" class="btn-secondary">Supprimer visage</button>
                    </div>
                    <small class="helper" id="faceInstruction">
                        Guide vocal: face avant, tourner a gauche, tourner a droite.
                    </small>
                </div>
            </div>
        </section>

        <button type="submit" class="btn-primary-save">Enregistrer</button>
    </form>

    <p><a class="back-link" href="profile.php">Retour au profil</a></p>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>

<script src="../assets/forms-validation.js"></script>
<script>
(function () {
    var openCamBtn = document.getElementById('faceOpenCamBtn');
    var startGuideBtn = document.getElementById('faceStartGuideBtn');
    var captureStepBtn = document.getElementById('faceCaptureStepBtn');
    var disableBtn = document.getElementById('faceDisableBtn');
    var statusEl = document.getElementById('faceStatus');
    var instructionEl = document.getElementById('faceInstruction');
    var overlayEl = document.getElementById('faceGuideOverlay');
    var turnHintEl = document.getElementById('turnHint');
    var flashEl = document.getElementById('faceFlash');
    var videoEl = document.getElementById('faceVideo');
    var canvasEl = document.getElementById('faceCanvas');
    var inputFront = document.getElementById('face_photo_front');
    var inputLeft = document.getElementById('face_photo_left');
    var inputRight = document.getElementById('face_photo_right');
    var hashFront = document.getElementById('face_hash_front');
    var hashLeft = document.getElementById('face_hash_left');
    var hashRight = document.getElementById('face_hash_right');
    var disableInput = document.getElementById('disable_face_photo');
    if (!openCamBtn || !startGuideBtn || !captureStepBtn || !disableBtn || !statusEl || !instructionEl || !overlayEl || !turnHintEl || !flashEl || !videoEl || !canvasEl || !inputFront || !inputLeft || !inputRight || !hashFront || !hashLeft || !hashRight || !disableInput) return;

    function setStatus(msg, err) {
        statusEl.textContent = msg;
        statusEl.style.color = err ? '#b91c1c' : '#334155';
    }

    function speak(text) {
        try {
            if (!('speechSynthesis' in window)) return;
            window.speechSynthesis.cancel();
            var u = new SpeechSynthesisUtterance(text);
            u.lang = 'fr-FR';
            u.rate = 0.95;
            u.pitch = 1;
            u.volume = 1;
            window.speechSynthesis.speak(u);
        } catch (e) {}
    }

    function toHex(n) {
        var h = n.toString(16);
        return h.length === 1 ? '0' + h : h;
    }

    function computeFrameHash(ctx, w, h) {
        var img = ctx.getImageData(0, 0, w, h).data;
        var gray = [];
        var sum = 0;
        for (var i = 0; i < img.length; i += 4) {
            var g = Math.round(0.299 * img[i] + 0.587 * img[i + 1] + 0.114 * img[i + 2]);
            gray.push(g);
            sum += g;
        }
        var avg = sum / gray.length;
        var bits = '';
        for (var j = 0; j < gray.length; j++) bits += gray[j] >= avg ? '1' : '0';
        var hex = '';
        for (var k = 0; k < bits.length; k += 8) {
            hex += toHex(parseInt(bits.slice(k, k + 8), 2));
        }
        return hex.toLowerCase();
    }

    function animateGuide(directionClass, hintText) {
        overlayEl.classList.remove('turn-left', 'turn-right');
        if (directionClass) overlayEl.classList.add(directionClass);
        turnHintEl.textContent = hintText;
        overlayEl.classList.remove('pulse');
        void overlayEl.offsetWidth;
        overlayEl.classList.add('pulse');
    }

    function flashShot() {
        flashEl.classList.remove('on');
        void flashEl.offsetWidth;
        flashEl.classList.add('on');
    }

    var steps = [
        { key: 'front', label: 'Regardez la camera en face' },
        { key: 'left', label: 'Tournez la tete a gauche' },
        { key: 'right', label: 'Tournez la tete a droite' }
    ];
    var currentStep = -1;

    function updateInstruction() {
        if (currentStep < 0 || currentStep >= steps.length) {
            instructionEl.textContent = 'Guide vocal: face avant, tourner a gauche, tourner a droite.';
            animateGuide('', 'FACE AVANT');
            return;
        }
        var txt = 'Etape ' + (currentStep + 1) + '/3 - ' + steps[currentStep].label;
        instructionEl.textContent = txt;
        setStatus(txt + '. Puis cliquez sur Capturer cette position.');
        if (currentStep === 0) animateGuide('', 'FACE AVANT');
        if (currentStep === 1) animateGuide('turn-left', 'TOURNEZ A GAUCHE');
        if (currentStep === 2) animateGuide('turn-right', 'TOURNEZ A DROITE');
        speak(txt);
    }

    function clearEnrollment() {
        inputFront.value = '';
        inputLeft.value = '';
        inputRight.value = '';
        hashFront.value = '';
        hashLeft.value = '';
        hashRight.value = '';
    }

    var stream = null;

    openCamBtn.addEventListener('click', async function () {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            setStatus('Camera non supportee par ce navigateur.', true);
            return;
        }
        try {
            if (stream) {
                var tracks = stream.getTracks ? stream.getTracks() : [];
                for (var i = 0; i < tracks.length; i++) tracks[i].stop();
            }
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                audio: false
            });
            videoEl.srcObject = stream;
            setStatus('Camera active. Cliquez sur Demarrer guide live.');
        } catch (e) {
            setStatus('Impossible d activer la camera.', true);
        }
    });

    startGuideBtn.addEventListener('click', function () {
        if (!videoEl.videoWidth || !videoEl.videoHeight) {
            setStatus('Activez d abord la camera.', true);
            return;
        }
        clearEnrollment();
        disableInput.value = '0';
        currentStep = 0;
        updateInstruction();
    });

    captureStepBtn.addEventListener('click', function () {
        if (!videoEl.videoWidth || !videoEl.videoHeight) {
            setStatus('Activez la camera avant la capture.', true);
            return;
        }
        if (currentStep < 0 || currentStep >= steps.length) {
            setStatus('Cliquez sur Demarrer guide live.', true);
            return;
        }
        var w = canvasEl.width;
        var h = canvasEl.height;
        var ctx = canvasEl.getContext('2d');
        ctx.drawImage(videoEl, 0, 0, w, h);
        var data = canvasEl.toDataURL('image/jpeg', 0.92);
        var frameHash = computeFrameHash(ctx, w, h);
        flashShot();
        var stepKey = steps[currentStep].key;
        if (stepKey === 'front') {
            inputFront.value = data;
            hashFront.value = frameHash;
        } else if (stepKey === 'left') {
            inputLeft.value = data;
            hashLeft.value = frameHash;
        } else {
            inputRight.value = data;
            hashRight.value = frameHash;
        }
        disableInput.value = '0';

        currentStep++;
        if (currentStep >= steps.length) {
            currentStep = -1;
            instructionEl.textContent = 'Parcours live termine: 3 positions capturees.';
            setStatus('Capture complete. Cliquez sur Enregistrer.');
            animateGuide('', 'CAPTURE TERMINEE');
            speak('Parcours termine. Vous pouvez enregistrer.');
        } else {
            updateInstruction();
        }
    });

    disableBtn.addEventListener('click', async function () {
        clearEnrollment();
        disableInput.value = '1';
        currentStep = -1;
        animateGuide('', 'FACE AVANT');
        instructionEl.textContent = 'Suppression du visage prete.';
        setStatus('Suppression visage preparee. Cliquez sur Enregistrer.');
    });

    window.addEventListener('beforeunload', function () {
        if (stream) {
            var tracks = stream.getTracks ? stream.getTracks() : [];
            for (var i = 0; i < tracks.length; i++) tracks[i].stop();
        }
    });
})();
</script>

</body>
</html>
