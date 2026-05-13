<?php
require_once __DIR__ . '/../../../../controller/AuthController.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$auth = new AuthController();
$user = $auth->profile();
if (!$user || strtolower($user['type'] ?? '') !== 'admin') {
    header('Location: ../../../login.php');
    exit;
}

require_once __DIR__ . '/../../../../controller/AchatsAuditLog.php';

$logs = AchatsAuditLog::listRecent(200);

$plReportBanner = [
    'title' => 'Journal des événements',
    'lead' => 'Extractions liste (consultation)',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Journal des événements — ProLink</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
<link rel="stylesheet" href="../../commerce.css">
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Journal des événements</div>
        <div class="actions">
            <button type="button" id="exportAchatsJournalPdf" class="btn btn-secondary">Exporter PDF</button>
            <a href="gestionAchats.php" class="btn btn-secondary">← Achats</a>
        </div>
    </div>

    <div class="container commerce-dashboard pl-pdf-export-root" id="achatsJournalPdfRoot" style="max-width:1180px">
        <?php include __DIR__ . '/partials/pl_report_banner.php'; ?>

        <header class="commerce-hub-intro pl-intro-supplier">
            <p>
                Historique horodaté des opérations enregistrées par l’application (attribution d’AO, contrats prix, dossiers demande d’achat).
                Détails en JSON dans la colonne ci-dessous.
            </p>
        </header>

        <div class="commerce-form-card" style="margin-top:22px">
            <h3 class="commerce-card-title">Entrées récentes</h3>
            <div class="commerce-table-wrap">
                <table class="table-modern table-modern--dense">
                    <thead>
                        <tr>
                            <th>Date / heure</th>
                            <th>Action</th>
                            <th>Entité</th>
                            <th>Id</th>
                            <th>Acteur</th>
                            <th>Détail (JSON)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($logs as $L): ?>
                        <?php
                        $payload = $L['payload'] ?? null;
                        $pretty = '—';
                        if (is_string($payload) && $payload !== '') {
                            $dec = json_decode($payload, true);
                            $pretty = $dec !== null
                                ? json_encode($dec, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                                : $payload;
                        }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($L['created_at'] ?? '')) ?></td>
                            <td><code class="pl-audit-code"><?= htmlspecialchars((string) ($L['action'] ?? '')) ?></code></td>
                            <td><?= htmlspecialchars((string) ($L['entity'] ?? '')) ?></td>
                            <td><?= $L['entity_id'] !== null ? (int) $L['entity_id'] : '—' ?></td>
                            <td><?= htmlspecialchars(trim(($L['prenom'] ?? '') . ' ' . ($L['nom'] ?? ''))) ?></td>
                            <td class="pl-audit-payload"><pre><?= htmlspecialchars((string) $pretty) ?></pre></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($logs === []): ?>
                        <tr><td colspan="6" class="hint">Aucun événement — effectuez une attribution AO ou un contrat cadre.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="../../../assets/backoffice-report-pdf.js"></script>
<script>
prolinkBindReportPdf(<?= json_encode([
    'buttonId' => 'exportAchatsJournalPdf',
    'rootId' => 'achatsJournalPdfRoot',
    'footerLine' => 'ProLink · Journal achats · ' . date('d/m/Y'),
    'fileName' => 'prolink-journal-achats.pdf',
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
</script>
</body>
</html>
