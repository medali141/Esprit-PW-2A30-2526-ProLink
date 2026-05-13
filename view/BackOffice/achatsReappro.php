<?php
require_once __DIR__ . '/../../controller/AuthController.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$auth = new AuthController();
$user = $auth->profile();
if (!$user || strtolower($user['type'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../controller/AchatsStockOffresController.php';
$mc = new AchatsStockOffresController();

$fenetre = isset($_GET['w']) ? (int) $_GET['w'] : 90;
$flashOk = isset($_GET['ok']) ? (string) $_GET['ok'] : '';
$flashErr = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_reappro') {
    try {
        $mc->upsertReapproConfig(
            (int) ($_POST['idproduit'] ?? 0),
            (int) ($_POST['stock_minimum'] ?? 0),
            (int) ($_POST['stock_cible'] ?? 0),
            (int) ($_POST['lead_time_jours'] ?? 0)
        );
        header('Location: achatsReappro.php?w=' . max(7, min(365, $fenetre)) . '&ok=saved');
        exit;
    } catch (Throwable $e) {
        $flashErr = $e->getMessage();
    }
}

$rows = $mc->getReapproDashboard($fenetre);
$counts = ['critique' => 0, 'vigilance' => 0, 'ok' => 0];
foreach ($rows as $r) {
    $n = (string) ($r['niveau_alerte'] ?? 'ok');
    if (isset($counts[$n])) {
        $counts[$n]++;
    }
}

$plReportBanner = [
    'title' => 'Réapprovisionnement',
    'lead' => 'Vue sur ' . (int) $fenetre . ' jours',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Réapprovisionnement — ProLink</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<?php include 'sidebar.php'; ?>
<link rel="stylesheet" href="commerce.css">
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Réapprovisionnement</div>
        <div class="actions">
            <button type="button" id="exportAchatsReapproPdf" class="btn btn-secondary">Exporter PDF</button>
            <a href="gestionAchats.php" class="btn btn-secondary">← Achats</a>
            <a href="achatsSourcing.php" class="btn btn-secondary">Appels d’offres</a>
        </div>
    </div>

    <div class="container commerce-dashboard pl-pdf-export-root" id="achatsReapproPdfRoot" style="max-width:1180px">
        <?php include __DIR__ . '/partials/pl_report_banner.php'; ?>

        <div class="commerce-hub-intro pl-intro-reappro">
            <p>
                Ventes sur une période réglable, couverture en jours, comparaison au délai d’approvisionnement paramétré
                et quantité suggérée pour rejoindre le stock cible.
            </p>
            <p class="hint" style="margin-top:10px;font-weight:600;color:#64748b">
                Effet boutique&nbsp;: pastilles « Priorité réappro » et « Stock surveillé » sur le catalogue ;
                même lecture dans « Mes produits » pour les vendeurs.
            </p>
        </div>

        <?php if ($flashOk === 'saved'): ?>
            <div class="commerce-alert commerce-alert--ok">Paramètres de réappro enregistrés pour ce produit.</div>
        <?php endif; ?>
        <?php if ($flashErr !== ''): ?>
            <div class="commerce-alert commerce-alert--err"><?= htmlspecialchars($flashErr) ?></div>
        <?php endif; ?>

        <form method="get" class="commerce-filters" action="achatsReappro.php">
            <div class="commerce-filters__field pl-pdf-hide">
                <label for="fw">Fenêtre vélocité (jours)</label>
                <select id="fw" name="w" onchange="this.form.submit()">
                    <?php foreach ([30, 60, 90, 180] as $w): ?>
                        <option value="<?= $w ?>" <?= $fenetre === $w ? 'selected' : '' ?>><?= $w ?> j</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <p class="commerce-result-hint" style="margin:0">
                Références actives : <strong><?= count($rows) ?></strong>
                · Critiques : <strong><?= (int) $counts['critique'] ?></strong>
                · Vigilance : <strong><?= (int) $counts['vigilance'] ?></strong>
            </p>
        </form>

        <section class="commerce-kpi-grid" aria-label="Alertes stock">
            <article class="commerce-kpi-card">
                <p class="commerce-kpi-label">Alertes critiques</p>
                <p class="commerce-kpi-value" style="color:#dc2626"><?= (int) $counts['critique'] ?></p>
            </article>
            <article class="commerce-kpi-card">
                <p class="commerce-kpi-label">Vigilance</p>
                <p class="commerce-kpi-value" style="color:#d97706"><?= (int) $counts['vigilance'] ?></p>
            </article>
            <article class="commerce-kpi-card">
                <p class="commerce-kpi-label">OK</p>
                <p class="commerce-kpi-value" style="color:#059669"><?= (int) $counts['ok'] ?></p>
            </article>
        </section>

        <div class="commerce-form-card" style="margin-top:8px">
            <h3 class="commerce-card-title">Articles du catalogue</h3>
            <p class="hint" style="margin:-8px 0 14px;line-height:1.55">
                Les références sans configuration dédiée utilisent des <strong>seuils par défaut</strong> (voir ci-dessous).
                Personnalisez le minimum, le stock cible et le délai fournisseur par SKU, puis validez la ligne.
            </p>
            <p class="hint" style="margin:-6px 0 16px;font-size:0.82rem">
                Défauts automatiques : minimum <strong>10</strong>, cible <strong>40</strong>, délai <strong>14</strong> jours.
            </p>
            <div class="commerce-table-wrap">
                <table class="table-modern pl-reappro-table">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Stock</th>
                            <th>Vélocité <?= (int) $fenetre ?> j</th>
                            <th>Couverture</th>
                            <th>Niveau</th>
                            <th>Suggestion</th>
                            <th class="pl-pdf-hide">Seuils (min / cible / délai)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $r): ?>
                        <?php
                        $lvl = (string) ($r['niveau_alerte'] ?? 'ok');
                        $pill = 'pl-alert-pill--ok';
                        $pillLabel = 'OK';
                        if ($lvl === 'critique') {
                            $pill = 'pl-alert-pill--crit';
                            $pillLabel = 'Critique';
                        } elseif ($lvl === 'vigilance') {
                            $pill = 'pl-alert-pill--warn';
                            $pillLabel = 'Vigilance';
                        }
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars((string) ($r['reference'] ?? '')) ?></strong><br>
                                <span class="pl-ref-sub"><?= htmlspecialchars((string) ($r['designation'] ?? '')) ?></span><br>
                                <span class="commerce-cat-pill"><?= htmlspecialchars((string) ($r['categorie_libelle'] ?? '')) ?></span>
                            </td>
                            <td><?= (int) ($r['stock'] ?? 0) ?></td>
                            <td><?= (int) ($r['qty_vendue_periode'] ?? 0) ?> u.<br><span class="pl-ref-sub"><?= number_format((float) ($r['avg_daily_sales'] ?? 0), 3, ',', ' ') ?> / jour</span></td>
                            <td><?= number_format((float) ($r['couverture_jours'] ?? 0), 1, ',', ' ') ?> j</td>
                            <td><span class="pl-alert-pill <?= $pill ?>"><?= htmlspecialchars($pillLabel) ?></span></td>
                            <td><strong><?= (int) ($r['suggestion_reappro'] ?? 0) ?></strong> u.</td>
                            <td class="pl-pdf-hide">
                                <form method="post" class="pl-reappro-mini-form">
                                    <input type="hidden" name="action" value="save_reappro">
                                    <input type="hidden" name="idproduit" value="<?= (int) ($r['idproduit'] ?? 0) ?>">
                                    <div class="pl-mini-grid">
                                        <input type="number" name="stock_minimum" min="0" max="999999" required value="<?= (int) ($r['stock_minimum'] ?? 0) ?>" title="Minimum">
                                        <input type="number" name="stock_cible" min="1" max="999999" required value="<?= (int) ($r['stock_cible'] ?? 0) ?>" title="Cible">
                                        <input type="number" name="lead_time_jours" min="1" max="365" required value="<?= (int) ($r['lead_time_jours'] ?? 14) ?>" title="Délai j">
                                        <button type="submit" class="btn btn-secondary" style="padding:8px 10px;font-size:0.76rem">OK</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="../assets/backoffice-report-pdf.js"></script>
<script>
prolinkBindReportPdf(<?= json_encode([
    'buttonId' => 'exportAchatsReapproPdf',
    'rootId' => 'achatsReapproPdfRoot',
    'footerLine' => 'ProLink · Réappro · ' . (int) $fenetre . ' j · ' . date('d/m/Y'),
    'fileName' => 'prolink-reappro-' . (int) $fenetre . 'j.pdf',
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
</script>
</body>
</html>
