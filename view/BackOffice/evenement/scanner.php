<?php
session_start();
require_once __DIR__ . '/../../../config.php';

$idEvent = (int)($_GET['id_event'] ?? 0);
if ($idEvent < 1) { header('Location: liste_event.php'); exit; }

$db = Config::getConnexion();
$st = $db->prepare('SELECT `titre_event`, `date_debut`, `date_fin` FROM `evenement` WHERE `id_event` = :id LIMIT 1');
$st->execute(['id' => $idEvent]);
$ev = $st->fetch(PDO::FETCH_ASSOC);
if (!$ev) { header('Location: liste_event.php'); exit; }

$today     = date('Y-m-d');
$dateDebut = substr((string)$ev['date_debut'], 0, 10);
$dateFin   = substr((string)$ev['date_fin'],   0, 10);
$isActive  = ($today >= $dateDebut && $today <= $dateFin);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Scanner — <?= htmlspecialchars($ev['titre_event']) ?></title>
<script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: #0f173c;
        color: #fff;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 30px 16px;
    }
    .header { text-align: center; margin-bottom: 24px; }
    .header h1 { color: #6c4daf; font-size: 1.6rem; margin-bottom: 6px; }
    .header p  { color: #a0a0c0; font-size: 0.9rem; }
    .badge-active   { display:inline-block; background:#065f46; color:#d1fae5; padding:4px 14px; border-radius:20px; font-size:.8rem; font-weight:600; margin-top:8px; }
    .badge-inactive { display:inline-block; background:#7f1d1d; color:#fee2e2; padding:4px 14px; border-radius:20px; font-size:.8rem; font-weight:600; margin-top:8px; }

    .video-wrap {
        position: relative;
        width: 100%;
        max-width: 420px;
        border-radius: 16px;
        overflow: hidden;
        border: 3px solid #6c4daf;
        box-shadow: 0 0 40px #6c4daf44;
    }
    #video { width: 100%; display: block; }
    .scan-line {
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, transparent, #6c4daf, transparent);
        animation: scan 2s linear infinite;
    }
    @keyframes scan {
        0%   { top: 0%; }
        100% { top: 100%; }
    }

    #result {
        margin-top: 20px;
        width: 100%;
        max-width: 420px;
        padding: 18px 20px;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 700;
        text-align: center;
        display: none;
        animation: fadeIn .3s ease;
    }
    @keyframes fadeIn { from { opacity:0; transform:scale(.96); } to { opacity:1; transform:scale(1); } }
    .result-ok   { background: #065f46; color: #d1fae5; border: 2px solid #6ee7b7; }
    .result-err  { background: #7f1d1d; color: #fee2e2; border: 2px solid #fca5a5; }
    .result-warn { background: #78350f; color: #fef3c7; border: 2px solid #fcd34d; }

    .result-icon { font-size: 2.5rem; margin-bottom: 8px; }
    .result-sub  { font-size: .85rem; font-weight: 400; margin-top: 6px; opacity: .85; }

    .btn-link {
        display: inline-block;
        margin-top: 24px;
        padding: 12px 28px;
        background: #6c4daf;
        color: #fff;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        transition: background .2s;
    }
    .btn-link:hover { background: #5a3d9a; }

    .inactive-msg {
        background: #1e1e2e;
        border: 2px solid #ef4444;
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        max-width: 420px;
        width: 100%;
    }
    .inactive-msg h2 { color: #ef4444; margin-bottom: 10px; }
    .inactive-msg p  { color: #a0a0c0; font-size: .9rem; }
</style>
</head>
<body>

<div class="header">
    <h1>📷 Scanner les tickets</h1>
    <p><?= htmlspecialchars($ev['titre_event']) ?></p>
    <p style="margin-top:4px; font-size:.82rem; color:#a0a0c0;">
        <?= date('d/m/Y', strtotime($dateDebut)) ?> → <?= date('d/m/Y', strtotime($dateFin)) ?>
    </p>
    <?php if ($isActive): ?>
        <span class="badge-active">✅ Événement actif aujourd'hui</span>
    <?php else: ?>
        <span class="badge-inactive">⛔ Événement non actif aujourd'hui</span>
    <?php endif; ?>
</div>

<?php if ($isActive): ?>

<div class="video-wrap">
    <video id="video" autoplay muted playsinline></video>
    <div class="scan-line"></div>
</div>

<div id="result"></div>

<a href="presence_list.php?id_event=<?= $idEvent ?>" class="btn-link">📋 Voir la liste de présence</a>

<script>
const idEvent   = <?= $idEvent ?>;
const resultDiv = document.getElementById('result');
let   lastScan  = '';
let   scanning  = true;

// Chemin relatif vers verify_ticket.php depuis scanner.php
// scanner.php est dans view/BackOffice/evenement/
// verify_ticket.php est dans ajax/
const ajaxUrl = '../../../ajax/verify_ticket.php';

const codeReader = new ZXing.BrowserQRCodeReader();
codeReader.decodeFromVideoDevice(null, 'video', (result, err) => {
    if (!result || !scanning) return;

    const qrData = result.getText();
    if (qrData === lastScan) return;
    lastScan = qrData;
    scanning = false;

    fetch(ajaxUrl, {
        method : 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body   : 'qr_data=' + encodeURIComponent(qrData) + '&id_event=' + idEvent
    })
    .then(r => r.json())
    .then(data => {
        resultDiv.style.display = 'block';
        resultDiv.className = '';

        if (data.ok) {
            resultDiv.classList.add('result-ok');
            resultDiv.innerHTML = `
                <div class="result-icon">✅</div>
                <div>${data.msg}</div>
                <div class="result-sub">Ticket validé avec succès</div>`;
        } else if (data.already) {
            resultDiv.classList.add('result-warn');
            resultDiv.innerHTML = `
                <div class="result-icon">⚠️</div>
                <div>${data.msg}</div>
                <div class="result-sub">${data.nom ?? ''}</div>`;
        } else {
            resultDiv.classList.add('result-err');
            resultDiv.innerHTML = `
                <div class="result-icon">❌</div>
                <div>${data.msg}</div>`;
        }

        // Réactiver le scan après 3 secondes
        setTimeout(() => {
            scanning = true;
            lastScan = '';
            resultDiv.style.display = 'none';
        }, 3000);
    })
    .catch(() => {
        resultDiv.style.display = 'block';
        resultDiv.className = 'result-err';
        resultDiv.innerHTML = '<div class="result-icon">❌</div><div>Erreur réseau.</div>';
        setTimeout(() => { scanning = true; lastScan = ''; resultDiv.style.display = 'none'; }, 3000);
    });
});
</script>

<?php else: ?>

<div class="inactive-msg">
    <h2>⛔ Scan non disponible</h2>
    <p>Le scan est disponible uniquement pendant la période de l'événement.<br>
    <strong><?= date('d/m/Y', strtotime($dateDebut)) ?> → <?= date('d/m/Y', strtotime($dateFin)) ?></strong></p>
</div>

<a href="liste_event.php" class="btn-link" style="margin-top:20px;">← Retour</a>

<?php endif; ?>

</body>
</html>