<?php
// Variables expected: $code (string), $expires (int UNIX timestamp), $siteName (string), $helpEmail (string)
if (!isset($siteName)) $siteName = 'ProLink';
if (!isset($helpEmail)) $helpEmail = 'support@prolink.local';
if (!isset($expires)) $expires = time() + 300;
$expiryMinutes = max(1, ceil(($expires - time())/60));
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($siteName) ?> — Code de connexion</title>
  <style>
    /* Inline-friendly simple styles */
    body{background:#f4f6fb;margin:0;padding:20px;font-family:Inter,Helvetica,Arial,sans-serif;color:#0f1724}
    .card{max-width:640px;margin:0 auto;background:#ffffff;border-radius:12px;padding:24px;border:1px solid #eef2f7}
    h1{font-size:20px;margin:0 0 8px;color:#0b66c3}
    p{margin:0 0 12px;color:#334155}
    .code{display:block;margin:18px auto;padding:18px 22px;font-size:22px;letter-spacing:6px;text-align:center;border-radius:10px;background:linear-gradient(90deg,#00151b,#012a35);color:#8be9ff;font-weight:700;max-width:260px}
    .btn{display:inline-block;padding:10px 14px;background:#06b6d4;color:white;border-radius:8px;text-decoration:none}
    .muted{font-size:13px;color:#6b7280}
    @media (max-width:480px){.card{padding:18px}.code{font-size:20px}}
  </style>
</head>
<body>
  <div class="card">
    <h1>Code de connexion administrateur</h1>
    <p>Bonjour,</p>
    <p>Utilisez le code ci-dessous pour terminer votre connexion sur <strong><?= htmlspecialchars($siteName) ?></strong>. Ce code expire dans <?= $expiryMinutes ?> minute(s).</p>
    <div class="code" aria-label="Code de connexion"><?= htmlspecialchars($code) ?></div>
    <p class="muted">Si vous n'avez pas demandé ce code, ignorez ce message ou contactez <a href="mailto:<?= htmlspecialchars($helpEmail) ?>"><?= htmlspecialchars($helpEmail) ?></a>.</p>
    <p style="margin-top:14px"><a class="btn" href="#">Retour à l'application</a></p>
  </div>
</body>
</html>
