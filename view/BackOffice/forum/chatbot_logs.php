<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../init.php';

$logFile = __DIR__ . '/../../../logs/chatbot.log';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    if ($_POST['action'] === 'clear') {
        @file_put_contents($logFile, "");
        $msg = 'Logs vidés.';
    }
}

if (isset($_GET['download'])) {
    if (is_file($logFile)) {
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="chatbot.log"');
        readfile($logFile);
        exit;
    }
    http_response_code(404);
    echo 'Fichier de log introuvable.';
    exit;
}

function tailLines(string $file, int $lines = 200): array {
    if (!is_file($file)) return [];
    $f = fopen($file, 'rb');
    if (!$f) return [];
    $buffer = '';
    $chunkSize = 4096;
    fseek($f, 0, SEEK_END);
    $pos = ftell($f);
    $data = '';
    while ($pos > 0 && substr_count($data, "\n") <= $lines) {
        $read = ($pos - $chunkSize) > 0 ? $chunkSize : $pos;
        $pos -= $read;
        fseek($f, $pos);
        $data = fread($f, $read) . $data;
        if ($pos === 0) break;
    }
    fclose($f);
    $arr = explode("\n", trim($data));
    if (count($arr) > $lines) $arr = array_slice($arr, -$lines);
    return $arr;
}

$lines = tailLines($logFile, 300);

?><!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Logs chatbot — BackOffice</title>
    <link rel="stylesheet" href="../../assets/style.css">
    <link rel="stylesheet" href="../../assets/storefront.css">
</head>
<body class="fo-store-page">
<?php if (file_exists(__DIR__ . '/../_layout/paths.php')) {
    // try to include BO navbar if present
    @include __DIR__ . '/../_layout/paths.php';
}
?>
<main class="container">
    <div class="fo-form-card">
        <h2>Logs du chatbot</h2>
        <p class="hint">Fichier : <strong><?php echo htmlspecialchars($logFile); ?></strong></p>
        <?php if ($msg): ?><p class="fo-banner--ok" style="padding:10px"><?php echo htmlspecialchars($msg); ?></p><?php endif; ?>
        <div style="display:flex;gap:12px;margin-bottom:12px">
            <form method="get" style="margin:0"><button class="fo-btn fo-btn--secondary" type="submit" name="download" value="1">Télécharger</button></form>
            <form method="post" style="margin:0" onsubmit="return confirm('Vider le fichier de logs ?');">
                <input type="hidden" name="action" value="clear">
                <button class="fo-btn" type="submit">Vider le log</button>
            </form>
        </div>

        <?php if (empty($lines)): ?>
            <p class="hint">Aucun log trouvé.</p>
        <?php else: ?>
            <pre style="white-space:pre-wrap;background:#0f1724;color:#fff;padding:14px;border-radius:10px;overflow:auto;max-height:480px;"><?php
                foreach ($lines as $l) {
                    echo htmlspecialchars($l) . "\n";
                }
            ?></pre>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
