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
require_once __DIR__ . '/../../controller/ProduitController.php';
require_once __DIR__ . '/../../controller/CommandeController.php';
$pp = new ProduitController();
$cp = new CommandeController();
$nProd = count($pp->listAllAdmin());
$nCmd = $cp->countAll();
$kpi = $cp->getCommerceKpis();
$topProduits = $cp->topProduitsVendus(6);
$generatedAt = date('d/m/Y à H:i');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Statistiques commerce — ProLink</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        #commerceStatsPdfRoot.pl-report-root {
            font-family: "Inter", system-ui, sans-serif;
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid rgba(15, 23, 42, 0.06);
            border-radius: 20px;
            padding: 22px 22px 26px;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
        }
        .pl-report-hero {
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 22px;
            background: linear-gradient(125deg, #0f172a 0%, #1e3a5f 42%, #0b66c3 118%);
            color: #fff;
            box-shadow: 0 18px 44px rgba(11, 102, 195, 0.22);
        }
        .pl-report-hero-inner {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            padding: 22px 24px;
        }
        .pl-report-brand { display: flex; align-items: center; gap: 14px; }
        .pl-report-mark {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.22);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.95rem;
            letter-spacing: -0.02em;
        }
        .pl-report-kicker {
            margin: 0;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            opacity: 0.82;
        }
        .pl-report-title {
            margin: 6px 0 4px;
            font-size: 1.45rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.2;
        }
        .pl-report-lead {
            margin: 0;
            font-size: 0.88rem;
            opacity: 0.88;
            line-height: 1.45;
            max-width: 520px;
        }
        .pl-report-meta {
            font-size: 0.78rem;
            font-weight: 600;
            padding: 10px 14px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.16);
            white-space: nowrap;
        }
        #commerceStatsPdfRoot .commerce-kpi-card {
            border-radius: 14px;
            border: 1px solid rgba(15, 23, 42, 0.06);
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
        }
        #commerceStatsPdfRoot .commerce-form-card {
            border-radius: 16px;
            border: 1px solid rgba(15, 23, 42, 0.07);
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.04);
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<link rel="stylesheet" href="commerce.css">
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Statistiques commerce</div>
        <div class="actions">
            <a href="gestionAchats.php" class="btn btn-secondary">← Achats</a>
            <button type="button" id="exportDashboardPdf" class="btn btn-secondary">Exporter PDF</button>
        </div>
    </div>
    <div class="container commerce-dashboard commerce-stats-pdf-root pl-report-root pl-pdf-export-root" id="commerceStatsPdfRoot" style="max-width:1120px">
        <div class="pl-report-hero">
            <div class="pl-report-hero-inner">
                <div class="pl-report-brand">
                    <span class="pl-report-mark" aria-hidden="true">PL</span>
                    <div>
                        <p class="pl-report-kicker">ProLink · Commerce</p>
                        <h1 class="pl-report-title">Activité commerce</h1>
                        <p class="pl-report-lead">Chiffre d’affaires, commandes et produits les plus vendus. Export PDF en mode clair pour impression.</p>
                    </div>
                </div>
                <time class="pl-report-meta" datetime="<?= htmlspecialchars(date('c')) ?>">Généré le <?= htmlspecialchars($generatedAt) ?></time>
            </div>
        </div>
        <section class="commerce-kpi-grid" aria-label="Indicateurs clés">
            <article class="commerce-kpi-card">
                <p class="commerce-kpi-label">CA total</p>
                <p class="commerce-kpi-value"><?= number_format((float) $kpi['ca_total'], 3, ',', ' ') ?> TND</p>
            </article>
            <article class="commerce-kpi-card">
                <p class="commerce-kpi-label">CA du mois</p>
                <p class="commerce-kpi-value"><?= number_format((float) $kpi['ca_mois'], 3, ',', ' ') ?> TND</p>
            </article>
            <article class="commerce-kpi-card">
                <p class="commerce-kpi-label">Panier moyen</p>
                <p class="commerce-kpi-value"><?= number_format((float) $kpi['panier_moyen'], 3, ',', ' ') ?> TND</p>
            </article>
            <article class="commerce-kpi-card">
                <p class="commerce-kpi-label">Commandes</p>
                <p class="commerce-kpi-value"><?= (int) $kpi['total_commandes'] ?></p>
            </article>
        </section>
        <div class="commerce-form-card" style="margin-bottom:18px">
            <h3 class="commerce-card-title">Récapitulatif</h3>
            <p class="hint" style="margin:0;line-height:1.6">
                Produits en catalogue : <strong><?= (int) $nProd ?></strong> · Commandes en base : <strong><?= (int) $nCmd ?></strong><br>
                À payer : <strong><?= (int) $kpi['a_payer'] ?></strong> · En cours : <strong><?= (int) $kpi['en_cours'] ?></strong> · Livrées : <strong><?= (int) $kpi['livrees'] ?></strong>
            </p>
        </div>
        <section class="commerce-charts-grid" aria-label="Graphiques ventes">
            <article class="commerce-form-card commerce-chart-card">
                <h3 class="commerce-card-title">Volumes CA (TND)</h3>
                <canvas id="chartRevenue" height="110"></canvas>
            </article>
            <article class="commerce-form-card commerce-chart-card">
                <h3 class="commerce-card-title">Répartition statuts commandes</h3>
                <canvas id="chartStatus" height="110"></canvas>
            </article>
        </section>
        <div class="commerce-form-card" style="margin-top:20px">
            <h3 class="commerce-card-title">Top produits vendus</h3>
            <div class="commerce-table-wrap">
                <table class="table-modern">
                    <thead><tr><th>Réf.</th><th>Désignation</th><th>Qté vendue</th><th>CA produit</th></tr></thead>
                    <tbody>
                    <?php foreach ($topProduits as $tp): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars((string) $tp['reference']) ?></strong></td>
                            <td><?= htmlspecialchars((string) $tp['designation']) ?></td>
                            <td><?= (int) $tp['qte_vendue'] ?></td>
                            <td><?= number_format((float) $tp['ca_produit'], 3, ',', ' ') ?> TND</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="../assets/backoffice-report-pdf.js"></script>
<script>
(function () {
    var revenueCanvas = document.getElementById('chartRevenue');
    var statusCanvas = document.getElementById('chartStatus');
    if (typeof Chart !== 'undefined' && revenueCanvas && statusCanvas) {
        var caTotal = <?= json_encode((float) $kpi['ca_total']) ?>;
        var caMois = <?= json_encode((float) $kpi['ca_mois']) ?>;
        var panier = <?= json_encode((float) $kpi['panier_moyen']) ?>;
        var aPayer = <?= json_encode((int) $kpi['a_payer']) ?>;
        var enCours = <?= json_encode((int) $kpi['en_cours']) ?>;
        var livrees = <?= json_encode((int) $kpi['livrees']) ?>;

        var tickCol = '#64748b';
        var gridCol = 'rgba(100, 116, 139, 0.12)';

        new Chart(revenueCanvas, {
            type: 'bar',
            data: {
                labels: ['CA total', 'CA du mois', 'Panier moyen'],
                datasets: [{
                    data: [caTotal, caMois, panier],
                    borderRadius: 8,
                    backgroundColor: ['#0ea5e9', '#6366f1', '#14b8a6'],
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {legend: {display: false}},
                scales: {
                    x: {
                        ticks: { color: tickCol, font: { size: 11, weight: '600' } },
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { color: tickCol, font: { size: 11 }, callback: function(v){ return v + ' TND'; } },
                        grid: { color: gridCol }
                    }
                }
            }
        });

        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: ['À payer', 'En cours', 'Livrées'],
                datasets: [{
                    data: [aPayer, enCours, livrees],
                    backgroundColor: ['#fb923c', '#38bdf8', '#4ade80'],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '58%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 14, font: { size: 11, weight: '600' } }
                    }
                }
            }
        });
    }

    if (typeof prolinkBindReportPdf === 'function') {
        prolinkBindReportPdf(<?= json_encode([
            'buttonId' => 'exportDashboardPdf',
            'rootId' => 'commerceStatsPdfRoot',
            'loadingText' => 'Génération PDF...',
            'footerLine' => 'ProLink · Statistiques commerce · ' . date('d/m/Y'),
            'fileName' => 'prolink-stats-commerce.pdf',
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
    }
})();
</script>
</body>
</html>
