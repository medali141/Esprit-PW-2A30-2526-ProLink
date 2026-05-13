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

require_once __DIR__ . '/../../../../controller/AchatsCommerceController.php';
$ac = new AchatsCommerceController();
$scores = $ac->getFournisseurIndicateurs();
$generatedAt = date('d/m/Y à H:i');

$plReportBanner = [
    'title' => 'Indicateurs fournisseurs',
    'lead' => 'Synthèse commandes et délais au ' . date('d/m/Y'),
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fournisseurs — indicateurs — ProLink</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
<link rel="stylesheet" href="../../commerce.css">
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Performance fournisseurs</div>
        <div class="actions">
            <button type="button" id="exportAchatsFournisseursPdf" class="btn btn-secondary">Exporter PDF</button>
            <a href="gestionAchats.php" class="btn btn-secondary">← Achats</a>
            <a href="achatsBudget.php" class="btn btn-secondary">Budget & engagement</a>
        </div>
    </div>

    <div class="container commerce-dashboard pl-pdf-export-root" id="achatsFournisseursPdfRoot" style="max-width:1120px">
        <?php include __DIR__ . '/partials/pl_report_banner.php'; ?>

        <div class="commerce-hub-intro pl-intro-supplier">
            <p>
                Par vendeur&nbsp;: nombre de commandes concernées, chiffre d’affaires lignes,
                respect des dates de livraison (livraisons <strong>livrées</strong> avec date prévue et date effective renseignées).
                Une classification résume la fiabilité des délais.
            </p>
            <p class="hint" style="margin-top:10px;font-weight:600;color:#64748b">
                Indicateurs réservés au back-office ; pas de badge automatique sur les fiches produits pour l’instant.
            </p>
            <p class="hint" style="margin-top:10px"><?= htmlspecialchars($generatedAt) ?></p>
        </div>

        <section class="pl-supplier-legend commerce-form-card">
            <h3 class="commerce-card-title">Signification des niveaux</h3>
            <ul class="pl-legend-list">
                <li><span class="pl-score-badge pl-score-badge--excellent">Excellent</span> Ponctualité ≥ 90&nbsp;% sur livraisons évaluées.</li>
                <li><span class="pl-score-badge pl-score-badge--bon">Bon</span> Entre 70&nbsp;% et 89&nbsp;%.</li>
                <li><span class="pl-score-badge pl-score-badge--watch">À surveiller</span> Sous 70&nbsp;%.</li>
                <li><span class="pl-score-badge pl-score-badge--pending">En cours</span> Pas encore assez de livraisons clôturées avec dates pour mesurer le délai.</li>
            </ul>
        </section>

        <div class="commerce-form-card" style="margin-top:22px">
            <h3 class="commerce-card-title">Classement</h3>
            <div class="commerce-table-wrap">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Fournisseur</th>
                            <th>Commandes</th>
                            <th>CA lignes (TND)</th>
                            <th>Livraisons suivies</th>
                            <th>À temps</th>
                            <th>Retard</th>
                            <th>Indice</th>
                            <th>Niveau</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($scores as $s): ?>
                        <?php
                        $pct = $s['pct_ponctuel'];
                        $badge = (string) ($s['badge'] ?? '');
                        $badgeClass = 'pl-score-badge--pending';
                        if ($badge === 'Excellent') {
                            $badgeClass = 'pl-score-badge--excellent';
                        } elseif ($badge === 'Bon') {
                            $badgeClass = 'pl-score-badge--bon';
                        } elseif ($badge === 'À surveiller') {
                            $badgeClass = 'pl-score-badge--watch';
                        } elseif ($badge === 'En cours') {
                            $badgeClass = 'pl-score-badge--pending';
                        }
                        $scoreRingStyle = '--score:' . (int) ($s['score_global'] ?? 0);
                        ?>
                        <tr>
                            <td>
                                <div class="pl-supplier-name">
                                    <strong><?= htmlspecialchars(trim((string) ($s['prenom'] ?? '') . ' ' . (string) ($s['nom'] ?? ''))) ?></strong>
                                    <span class="pl-supplier-mail"><?= htmlspecialchars((string) ($s['email'] ?? '')) ?></span>
                                </div>
                            </td>
                            <td><?= (int) ($s['nb_commandes'] ?? 0) ?></td>
                            <td><?= number_format((float) ($s['ca_vendeur'] ?? 0), 2, ',', ' ') ?></td>
                            <td><?= (int) ($s['nb_evalues'] ?? 0) ?></td>
                            <td><?= (int) ($s['nb_a_temps'] ?? 0) ?></td>
                            <td><?= (int) ($s['nb_en_retard'] ?? 0) ?></td>
                            <td>
                                <span class="pl-score-ring" style="<?= htmlspecialchars($scoreRingStyle) ?>" aria-label="Indice <?= (int) ($s['score_global'] ?? 0) ?> sur 100">
                                    <span><?= (int) ($s['score_global'] ?? 0) ?></span>
                                </span>
                                <?php if ($pct !== null): ?>
                                    <span class="pl-pct-muted"><?= number_format((float) $pct, 1, ',', ' ') ?> % ponctuel</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="pl-score-badge <?= $badgeClass ?>"><?= htmlspecialchars($badge) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (count($scores) === 0): ?>
                        <tr><td colspan="8" style="text-align:center;padding:28px;color:#64748b">Aucune donnée fournisseur pour le moment.</td></tr>
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
    'buttonId' => 'exportAchatsFournisseursPdf',
    'rootId' => 'achatsFournisseursPdfRoot',
    'footerLine' => 'ProLink · Fournisseurs · ' . date('d/m/Y'),
    'fileName' => 'prolink-fournisseurs.pdf',
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
</script>
</body>
</html>
