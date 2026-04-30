<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/CommandeController.php';
require_once __DIR__ . '/../../model/CommerceMetier.php';

$auth = new AuthController();
$u = $auth->profile();
if (!$u) {
    header('Location: ../login.php');
    exit;
}

$cp = new CommandeController();
$orders = $cp->listByAcheteur((int) $u['iduser']);

$labels = [
    'brouillon' => 'Brouillon',
    'en_attente_paiement' => 'En attente paiement',
    'payee' => 'Payée',
    'en_preparation' => 'En préparation',
    'expediee' => 'En cours de livraison',
    'livree' => 'Livrée',
    'annulee' => 'Annulée',
];
$paymentLabels = [
    'card' => 'Carte bancaire',
    'cash_on_delivery' => 'Cash livraison',
];

$statusCount = [];
$statusAmount = [];
foreach (array_keys($labels) as $k) {
    $statusCount[$k] = 0;
    $statusAmount[$k] = 0.0;
}
$paymentCount = ['card' => 0, 'cash_on_delivery' => 0, 'other' => 0];
$monthBuckets = [];
for ($i = 5; $i >= 0; $i--) {
    $monthBuckets[date('Y-m', strtotime("-$i month"))] = 0.0;
}

$monthLabelFr = static function (string $ym): string {
    $fr = [
        '01' => 'janv.', '02' => 'févr.', '03' => 'mars', '04' => 'avr.', '05' => 'mai', '06' => 'juin',
        '07' => 'juil.', '08' => 'août', '09' => 'sept.', '10' => 'oct.', '11' => 'nov.', '12' => 'déc.',
    ];
    $p = explode('-', $ym);
    if (count($p) === 2 && isset($fr[$p[1]])) {
        return $fr[$p[1]] . ' ' . $p[0];
    }
    return $ym;
};

$totalAmount = 0.0;
$amountExcludingCancelled = 0.0;
$loyaltyPoints = 0;
$otherStatusCount = 0;
$otherStatusAmount = 0.0;

foreach ($orders as $o) {
    $st = (string) ($o['statut'] ?? '');
    $mt = (float) ($o['montant_total'] ?? 0);
    $pm = (string) ($o['mode_paiement'] ?? 'cash_on_delivery');
    if (isset($statusCount[$st])) {
        $statusCount[$st]++;
        $statusAmount[$st] += $mt;
    } else {
        $otherStatusCount++;
        $otherStatusAmount += $mt;
    }
    if (isset($paymentCount[$pm])) {
        $paymentCount[$pm]++;
    } else {
        $paymentCount['other']++;
    }
    $dk = substr((string) ($o['date_commande'] ?? ''), 0, 7);
    if (isset($monthBuckets[$dk])) {
        $monthBuckets[$dk] += $mt;
    }
    $totalAmount += $mt;
    if ($st !== 'annulee') {
        $amountExcludingCancelled += $mt;
        $loyaltyPoints += CommerceMetier::pointsFromAmount($mt);
    }
}

$chartStatusLabels = array_values($labels);
$chartStatusCounts = [];
foreach (array_keys($labels) as $statusKey) {
    $chartStatusCounts[] = (int) ($statusCount[$statusKey] ?? 0);
}
$statusColors = ['#94a3b8', '#f59e0b', '#10b981', '#6366f1', '#06b6d4', '#22c55e', '#f43f5e'];
if ($otherStatusCount > 0) {
    $chartStatusLabels[] = 'Autre / inconnu';
    $chartStatusCounts[] = $otherStatusCount;
    $statusColors[] = '#a855f7';
}

$chartMonthLabels = [];
foreach (array_keys($monthBuckets) as $ym) {
    $chartMonthLabels[] = $monthLabelFr($ym);
}
$chartMonthAmounts = [];
foreach (array_values($monthBuckets) as $bucketValue) {
    $chartMonthAmounts[] = round((float) $bucketValue, 3);
}

$chartPayLabels = [$paymentLabels['card'], $paymentLabels['cash_on_delivery']];
$chartPayCounts = [(int) $paymentCount['card'], (int) $paymentCount['cash_on_delivery']];
if ($paymentCount['other'] > 0) {
    $chartPayLabels[] = 'Autre';
    $chartPayCounts[] = (int) $paymentCount['other'];
}
$payColors = ['#22c55e', '#f59e0b', '#8b5cf6'];

$tableRows = [];
foreach (array_keys($labels) as $sk) {
    $c = (int) ($statusCount[$sk] ?? 0);
    if ($c === 0 && ($statusAmount[$sk] ?? 0) <= 0) {
        continue;
    }
    $tableRows[] = [
        'label' => $labels[$sk],
        'count' => $c,
        'amount' => (float) ($statusAmount[$sk] ?? 0),
    ];
}
if ($otherStatusCount > 0) {
    $tableRows[] = [
        'label' => 'Autre / inconnu',
        'count' => $otherStatusCount,
        'amount' => $otherStatusAmount,
    ];
}

$nOrders = count($orders);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Statistiques commandes — ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Statistiques de mes commandes</h1>
        <p class="fo-lead">Répartition par statut, montants sur les six derniers mois et modes de paiement.</p>
    </header>

    <?php if ($nOrders === 0): ?>
        <div class="fo-empty fo-form-card" style="max-width:520px">
            <p>Vous n’avez pas encore de commande. Les graphiques apparaîtront dès votre premier achat.</p>
            <p style="margin-top:12px"><a href="catalogue.php" class="fo-btn fo-btn--primary" style="text-decoration:none">Parcourir le catalogue</a></p>
        </div>
        <div class="fo-actions">
            <a href="mesCommandes.php" class="fo-btn fo-btn--secondary" style="text-decoration:none">← Retour mes commandes</a>
        </div>
    <?php else: ?>
        <div class="fo-stats-summary" aria-label="Synthèse">
            <div class="fo-stats-summary__item">
                <strong><?= (int) $nOrders ?></strong>
                <span>Commandes</span>
            </div>
            <div class="fo-stats-summary__item">
                <strong><?= number_format($totalAmount, 3, ',', ' ') ?></strong>
                <span>Montant total (TND)</span>
            </div>
            <div class="fo-stats-summary__item">
                <strong><?= number_format($amountExcludingCancelled, 3, ',', ' ') ?></strong>
                <span>Hors annulées (TND)</span>
            </div>
            <div class="fo-stats-summary__item">
                <strong><?= (int) $loyaltyPoints ?></strong>
                <span>Points fidélité (estim.)</span>
            </div>
        </div>

        <section class="fo-form-card" style="max-width:none;margin-top:0">
            <div class="fo-stats-grid">
                <div class="fo-table-wrap fo-stats-chart">
                    <p class="fo-stats-chart__title">Répartition par statut</p>
                    <div class="fo-stats-chart__panel">
                        <canvas id="statStatus" aria-label="Graphique répartition par statut"></canvas>
                    </div>
                </div>
                <div class="fo-table-wrap fo-stats-chart">
                    <p class="fo-stats-chart__title">Montant par mois (6 derniers mois)</p>
                    <div class="fo-stats-chart__panel">
                        <canvas id="statMonth" aria-label="Graphique montants mensuels"></canvas>
                    </div>
                </div>
                <div class="fo-table-wrap fo-stats-chart">
                    <p class="fo-stats-chart__title">Modes de paiement</p>
                    <div class="fo-stats-chart__panel">
                        <canvas id="statPay" aria-label="Graphique modes de paiement"></canvas>
                    </div>
                </div>
            </div>

            <?php if ($tableRows !== []): ?>
            <div class="fo-stats-table-wrap fo-table-wrap">
                <table class="table-modern">
                    <caption>Détail par statut</caption>
                    <thead>
                        <tr>
                            <th scope="col">Statut</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Montant cumulé (TND)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tableRows as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= (int) $row['count'] ?></td>
                            <td><?= number_format($row['amount'], 3, ',', ' ') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <div class="fo-actions">
                <a href="mesCommandes.php" class="fo-btn fo-btn--secondary" style="text-decoration:none">← Retour mes commandes</a>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
<?php if ($nOrders > 0): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') return;
    var dark = document.documentElement.classList.contains('dark-mode');
    var tick = dark ? '#94a3b8' : '#64748b';
    var grid = dark ? 'rgba(148,163,184,0.12)' : 'rgba(100,116,139,0.18)';
    var doughnutBorder = dark ? '#1e293b' : '#ffffff';

    var statusLabels = <?= json_encode($chartStatusLabels, JSON_UNESCAPED_UNICODE) ?>;
    var statusCounts = <?= json_encode($chartStatusCounts) ?>;
    var statusColors = <?= json_encode($statusColors) ?>;
    var monthLabels = <?= json_encode($chartMonthLabels, JSON_UNESCAPED_UNICODE) ?>;
    var monthAmounts = <?= json_encode($chartMonthAmounts) ?>;
    var payLabels = <?= json_encode($chartPayLabels, JSON_UNESCAPED_UNICODE) ?>;
    var payCounts = <?= json_encode($chartPayCounts) ?>;
    var payColors = <?= json_encode(array_slice($payColors, 0, count($chartPayLabels))) ?>;

    var s = document.getElementById('statStatus');
    var m = document.getElementById('statMonth');
    var p = document.getElementById('statPay');
    if (!s || !m || !p) return;

    var sumStatus = statusCounts.reduce(function (a, b) { return a + b; }, 0);
    var sumPay = payCounts.reduce(function (a, b) { return a + b; }, 0);

    new Chart(s, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusCounts,
                backgroundColor: statusColors,
                borderWidth: 2,
                borderColor: doughnutBorder,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { color: tick, boxWidth: 12 } },
                tooltip: {
                    callbacks: {
                        footer: function (items) {
                            if (!items.length || sumStatus <= 0) return '';
                            var v = Number(items[0].raw);
                            if (!Number.isFinite(v)) return '';
                            return Math.round((v / sumStatus) * 1000) / 10 + ' % des commandes';
                        }
                    }
                }
            }
        }
    });

    new Chart(m, {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Montant (TND)',
                data: monthAmounts,
                borderRadius: 8,
                backgroundColor: dark ? 'rgba(14,165,233,0.65)' : '#0ea5e9'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            var v = ctx.raw;
                            return (typeof v === 'number' ? v.toFixed(3) : v) + ' TND';
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: { color: tick, maxRotation: 45, minRotation: 0 },
                    grid: { color: grid }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: tick,
                        callback: function (v) {
                            return v + ' TND';
                        }
                    },
                    grid: { color: grid }
                }
            }
        }
    });

    new Chart(p, {
        type: 'pie',
        data: {
            labels: payLabels,
            datasets: [{
                data: payCounts,
                backgroundColor: payColors,
                borderWidth: 2,
                borderColor: doughnutBorder,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { color: tick, boxWidth: 12 } },
                tooltip: {
                    callbacks: {
                        footer: function (items) {
                            if (!items.length || sumPay <= 0) return '';
                            var v = Number(items[0].raw);
                            if (!Number.isFinite(v)) return '';
                            return Math.round((v / sumPay) * 1000) / 10 + ' %';
                        }
                    }
                }
            }
        }
    });
})();
</script>
<?php endif; ?>
</body>
</html>
