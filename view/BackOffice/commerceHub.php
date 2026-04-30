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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion achat / vente — ProLink</title>
</head>
<body>
<?php include 'sidebar.php'; ?>
<link rel="stylesheet" href="commerce.css">
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Gestion achat / vente</div>
        <div class="actions">
            <button type="button" id="exportDashboardPdf" class="btn btn-secondary">Exporter PDF</button>
        </div>
    </div>
    <div class="container commerce-dashboard" style="max-width:1120px">
        <div class="commerce-hub-intro" id="dashboardToExport">
            <h1>Tableau commerce</h1>
            <p>Catalogue produits (TND), stocks, commandes clients et suivi livraison — tout au même endroit.</p>
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
        <div class="commerce-hub-grid">
            <article class="commerce-hub-card commerce-hub-card--products">
                <div class="hub-icon" aria-hidden="true">📦</div>
                <h2>Produits</h2>
                <div class="commerce-hub-stat"><?= (int) $nProd ?></div>
                <p class="hub-hint">Références en base (actives et inactives)</p>
                <div class="commerce-hub-actions">
                    <a class="btn btn-primary" href="listProduits.php">Liste des produits</a>
                    <a class="btn btn-secondary" href="addProduit.php">+ Ajouter</a>
                </div>
            </article>
            <article class="commerce-hub-card commerce-hub-card--orders">
                <div class="hub-icon" aria-hidden="true">🛒</div>
                <h2>Commandes</h2>
                <div class="commerce-hub-stat"><?= (int) $nCmd ?></div>
                <p class="hub-hint">Suivi des statuts et des livraisons</p>
                <div class="commerce-hub-actions">
                    <a class="btn btn-primary" href="listCommandes.php">Liste des commandes</a>
                </div>
            </article>
            <article class="commerce-hub-card commerce-hub-card--orders">
                <div class="hub-icon" aria-hidden="true">💰</div>
                <h2>Indicateurs ventes</h2>
                <div class="hub-hint">CA total: <strong><?= number_format((float) $kpi['ca_total'], 3, ',', ' ') ?> TND</strong></div>
                <div class="hub-hint">CA du mois: <strong><?= number_format((float) $kpi['ca_mois'], 3, ',', ' ') ?> TND</strong></div>
                <div class="hub-hint">Panier moyen: <strong><?= number_format((float) $kpi['panier_moyen'], 3, ',', ' ') ?> TND</strong></div>
                <div class="hub-hint">À payer: <strong><?= (int) $kpi['a_payer'] ?></strong> · En cours: <strong><?= (int) $kpi['en_cours'] ?></strong> · Livrées: <strong><?= (int) $kpi['livrees'] ?></strong></div>
            </article>
        </div>
        <section class="commerce-charts-grid" aria-label="Graphiques ventes">
            <article class="commerce-form-card commerce-chart-card">
                <h3 class="commerce-card-title">Synthèse CA (TND)</h3>
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

        new Chart(revenueCanvas, {
            type: 'bar',
            data: {
                labels: ['CA total', 'CA du mois', 'Panier moyen'],
                datasets: [{
                    data: [caTotal, caMois, panier],
                    borderRadius: 10,
                    backgroundColor: ['#0891b2', '#6366f1', '#10b981']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {legend: {display: false}},
                scales: {
                    y: {beginAtZero: true, ticks: {callback: function(v){ return v + ' TND'; }}}
                }
            }
        });

        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: ['À payer', 'En cours', 'Livrées'],
                datasets: [{
                    data: [aPayer, enCours, livrees],
                    backgroundColor: ['#f59e0b', '#0ea5e9', '#22c55e'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {legend: {position: 'bottom'}}
            }
        });
    }

    var btnPdf = document.getElementById('exportDashboardPdf');
    var target = document.querySelector('.commerce-dashboard');
    if (!btnPdf || !target || typeof html2canvas === 'undefined' || !window.jspdf) return;

    btnPdf.addEventListener('click', function () {
        var oldText = btnPdf.textContent;
        btnPdf.disabled = true;
        btnPdf.textContent = 'Génération PDF...';
        html2canvas(target, {scale: 2, backgroundColor: '#ffffff'}).then(function (canvas) {
            var jsPDF = window.jspdf.jsPDF;
            var pdf = new jsPDF('p', 'mm', 'a4');
            var pageWidth = 210;
            var pageHeight = 297;
            var margin = 10;
            var innerW = pageWidth - margin * 2;
            var imgW = innerW;
            var imgH = canvas.height * imgW / canvas.width;
            var imgData = canvas.toDataURL('image/png');
            var y = margin;
            var remaining = imgH;

            pdf.setFontSize(13);
            pdf.text('ProLink - Dashboard Gestion Achats/Ventes', margin, y);
            y += 6;

            if (imgH <= pageHeight - y - margin) {
                pdf.addImage(imgData, 'PNG', margin, y, imgW, imgH);
            } else {
                var sliceHeightPx = Math.floor((pageHeight - y - margin) * canvas.width / imgW);
                var offsetPx = 0;
                while (remaining > 0) {
                    var pageCanvas = document.createElement('canvas');
                    pageCanvas.width = canvas.width;
                    pageCanvas.height = Math.min(sliceHeightPx, canvas.height - offsetPx);
                    var ctx = pageCanvas.getContext('2d');
                    if (!ctx) break;
                    ctx.drawImage(canvas, 0, offsetPx, canvas.width, pageCanvas.height, 0, 0, canvas.width, pageCanvas.height);
                    var pageImg = pageCanvas.toDataURL('image/png');
                    var pageImgH = pageCanvas.height * imgW / pageCanvas.width;
                    pdf.addImage(pageImg, 'PNG', margin, y, imgW, pageImgH);
                    remaining -= pageImgH;
                    offsetPx += pageCanvas.height;
                    if (remaining > 0) {
                        pdf.addPage();
                        y = margin;
                    }
                }
            }
            pdf.save('prolink-dashboard-achats.pdf');
        }).finally(function () {
            btnPdf.disabled = false;
            btnPdf.textContent = oldText;
        });
    });
})();
</script>
</body>
</html>
