<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../../controller/AuthController.php';
require_once __DIR__ . '/../../../controller/ForumController.php';
$__dashUser = (new AuthController())->profile();
if (!$__dashUser || strtolower($__dashUser['type'] ?? '') !== 'admin') {
    header('Location: ../../login.php');
    exit;
}
$nu = $np = $nc = 0;
$nFCat = $nFSuj = $nFMsg = 0;
$dateKeys = [];
$labelsFr = [];
$seriesOrders = [];
$seriesRevenue = [];
$seriesUsers = [];
$seriesForumMsg = [];
$dataOrders = [];
$dataRevenue = [];
$dataUsers = [];
$dataForumMsg = [];
try {
    require_once __DIR__ . '/../../../config.php';
    $__db = Config::getConnexion();
    $nu = (int) $__db->query('SELECT COUNT(*) FROM user')->fetchColumn();
    $np = (int) $__db->query('SELECT COUNT(*) FROM produit WHERE actif = 1')->fetchColumn();
    $nc = (int) $__db->query('SELECT COUNT(*) FROM commande')->fetchColumn();

    $__fc = new ForumController();
    $nFCat = $__fc->countCategories();
    $nFSuj = $__fc->countSujets();
    $nFMsg = $__fc->countMessages();

    $days = 14;
    $endD = new DateTimeImmutable('today');
    $startD = $endD->modify('-' . ($days - 1) . ' days');
    $dateKeys = [];
    for ($d = $startD; $d <= $endD; $d = $d->modify('+1 day')) {
        $k = $d->format('Y-m-d');
        $dateKeys[] = $k;
        $labelsFr[] = $d->format('d/m');
    }
    $seriesOrders = array_fill_keys($dateKeys, 0);
    $seriesRevenue = array_fill_keys($dateKeys, 0.0);
    $seriesUsers = array_fill_keys($dateKeys, 0);
    $seriesForumMsg = array_fill_keys($dateKeys, 0);

    $a = $startD->format('Y-m-d');
    $b = $endD->format('Y-m-d');
    $st = $__db->prepare(
        'SELECT DATE(date_commande) AS d, COUNT(*) AS cnt, COALESCE(SUM(montant_total), 0) AS rev
         FROM commande WHERE DATE(date_commande) BETWEEN :a AND :b GROUP BY DATE(date_commande)'
    );
    $st->execute(['a' => $a, 'b' => $b]);
    while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
        $dk = $row['d'] ?? '';
        if ($dk !== '' && array_key_exists($dk, $seriesOrders)) {
            $seriesOrders[$dk] = (int) $row['cnt'];
            $seriesRevenue[$dk] = (float) $row['rev'];
        }
    }
    $st2 = $__db->prepare(
        'SELECT DATE(created_at) AS d, COUNT(*) AS cnt FROM user
         WHERE DATE(created_at) BETWEEN :a AND :b GROUP BY DATE(created_at)'
    );
    $st2->execute(['a' => $a, 'b' => $b]);
    while ($row = $st2->fetch(PDO::FETCH_ASSOC)) {
        $dk = $row['d'] ?? '';
        if ($dk !== '' && array_key_exists($dk, $seriesUsers)) {
            $seriesUsers[$dk] = (int) $row['cnt'];
        }
    }
    $st3 = $__db->prepare(
        'SELECT DATE(created_at) AS d, COUNT(*) AS cnt FROM `forum_message`
         WHERE DATE(created_at) BETWEEN :a AND :b GROUP BY DATE(created_at)'
    );
    $st3->execute(['a' => $a, 'b' => $b]);
    while ($row = $st3->fetch(PDO::FETCH_ASSOC)) {
        $dk = $row['d'] ?? '';
        if ($dk !== '' && array_key_exists($dk, $seriesForumMsg)) {
            $seriesForumMsg[$dk] = (int) $row['cnt'];
        }
    }
} catch (Throwable $e) {
}
foreach ($dateKeys as $k) {
    $dataOrders[] = (int) ($seriesOrders[$k] ?? 0);
    $dataRevenue[] = round((float) ($seriesRevenue[$k] ?? 0), 3);
    $dataUsers[] = (int) ($seriesUsers[$k] ?? 0);
    $dataForumMsg[] = (int) ($seriesForumMsg[$k] ?? 0);
}
$chartJson = json_encode([
    'labels' => $labelsFr,
    'orders' => $dataOrders,
    'revenue' => $dataRevenue,
    'users' => $dataUsers,
    'forumMsg' => $dataForumMsg,
], JSON_UNESCAPED_UNICODE);
if ($chartJson === false) {
    $chartJson = '{"labels":[],"orders":[],"revenue":[],"users":[],"forumMsg":[]}';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{
            --vibrant-1: #00b4d8;
            --vibrant-2: #0077b6;
            --vibrant-3: #90e0ef;
            --glass: rgba(255,255,255,0.06);
        }
        html,body{ height:100%; }
        body{ margin:0; font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, Arial; background: linear-gradient(180deg, #f6fbff 0%, #eef7ff 100%); }

        /* content is provided by sidebar.css (uses .content with margin-left) */
        .content{ padding:28px; }

        .dashboard-header{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:20px }
        .page-title{ font-size:28px; font-weight:800; color:#073b4c }
        .subtitle{ color:#376a79; font-weight:600 }

        .stats-grid{ display:grid; grid-template-columns: repeat(auto-fit,minmax(200px,1fr)); gap:18px }
        .stat-card{ background: linear-gradient(135deg,var(--vibrant-1), var(--vibrant-2)); color: white; padding:20px; border-radius:12px; box-shadow: 0 12px 30px rgba(3,37,65,0.08); transition: transform .22s cubic-bezier(.2,.9,.3,1), box-shadow .22s; display:flex; align-items:center; gap:14px }
        .stat-card .icon{ width:56px; height:56px; border-radius:10px; background: rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; font-size:22px }
        .stat-card h3{ margin:0; font-size:20px; font-weight:700 }
        .stat-card p{ margin:4px 0 0 0; opacity:0.92 }

        .stat-card:hover{ transform: translateY(-8px) scale(1.01); box-shadow: 0 20px 40px rgba(3,37,65,0.12) }

        /* secondary cards (light) */
        .card-light{ background: white; color: #073b4c; padding:18px; border-radius:10px; box-shadow: 0 8px 22px rgba(3,37,65,0.06); transition: transform .18s; }
        .card-light:hover{ transform: translateY(-6px) }

        .grid-3{ display:grid; grid-template-columns: repeat(3,1fr); gap:18px }
        @media (max-width:900px){ .grid-3{ grid-template-columns: repeat(auto-fit,minmax(220px,1fr)) } .dashboard-header{ flex-direction:column; align-items:flex-start } }

        .charts-section{ margin-top: 22px; }
        .charts-section > h3{ margin: 0 0 14px; font-size: 1.05rem; font-weight: 800; color: #073b4c; letter-spacing: -0.02em; }
        .charts-grid{ display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 18px; }
        .chart-card{
            background: #fff;
            color: #073b4c;
            padding: 18px 18px 12px;
            border-radius: 14px;
            box-shadow: 0 10px 28px rgba(3, 37, 65, 0.07);
            border: 1px solid rgba(0, 119, 182, 0.08);
        }
        .chart-card h4{ margin: 0 0 12px; font-size: 0.95rem; font-weight: 700; color: #0c4a6e; }
        .chart-card .chart-wrap{ position: relative; height: 240px; width: 100%; }
        .chart-card canvas{ max-height: 240px; }
    </style>
</head>

<body>

<?php require_once __DIR__ . '/../_layout/sidebar.php'; ?>

<div class="content">
    <div class="dashboard-header">
        <div>
            <div class="page-title">Tableau de bord</div>
            <div class="subtitle">Vue d'ensemble rapide — statistiques et actions</div>
        </div>
        <div class="actions">
            <a class="btn btn-primary" href="../achat/vente/gestionAchats.php">Achat / vente</a>
            <a class="btn btn-secondary" href="../user/listUsers.php">Utilisateurs</a>
        </div>
    </div>

    <section class="stats-grid">
        <div class="stat-card">
            <div class="icon">👥</div>
            <div>
                <h3><?= (int) $nu ?></h3>
                <p>Utilisateurs</p>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg,#ff7a18,#ff3d67);">
            <div class="icon">📦</div>
            <div>
                <h3><?= (int) $np ?></h3>
                <p>Produits actifs (catalogue)</p>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg,#8e2de2,#4a00e0);">
            <div class="icon">🛒</div>
            <div>
                <h3><?= (int) $nc ?></h3>
                <p>Commandes</p>
            </div>
        </div>

        <a class="stat-card stat-card--link" href="../forum/liste_categories.php" style="background: linear-gradient(135deg,#06b6d4,#0e7490); text-decoration:none;">
            <div class="icon">🗂️</div>
            <div>
                <h3><?= (int) $nFCat ?></h3>
                <p>Catégories forum</p>
            </div>
        </a>

        <a class="stat-card stat-card--link" href="../forum/liste_sujets.php" style="background: linear-gradient(135deg,#10b981,#047857); text-decoration:none;">
            <div class="icon">💬</div>
            <div>
                <h3><?= (int) $nFSuj ?></h3>
                <p>Sujets forum</p>
            </div>
        </a>

        <a class="stat-card stat-card--link" href="../forum/forum_index.php" style="background: linear-gradient(135deg,#f59e0b,#b45309); text-decoration:none;">
            <div class="icon">✉️</div>
            <div>
                <h3><?= (int) $nFMsg ?></h3>
                <p>Messages forum</p>
            </div>
        </a>
    </section>

    <div style="height:18px"></div>

    <section class="grid-3">
        <div class="card-light">
            <h4>Activité récente</h4>
            <p style="margin-top:8px; color:#5b6b72">Dernières actions réalisées par les utilisateurs et changements système.</p>
        </div>
        <div class="card-light">
            <h4>Commerce</h4>
            <p style="margin-top:8px; color:#5b6b72">Produits, stocks, commandes et livraison.</p>
            <p style="margin-top:10px"><a href="../achat/vente/gestionAchats.php">Ouvrir le hub achat / vente</a></p>
        </div>
        <div class="card-light">
            <h4>Analyses</h4>
            <p style="margin-top:8px; color:#5b6b72">Courbes sur 14 jours : commandes, chiffre d’affaires et inscriptions.</p>
        </div>
    </section>

    <section class="charts-section" aria-labelledby="dash-charts-title">
        <h3 id="dash-charts-title">Tendances (14 derniers jours)</h3>
        <div class="charts-grid">
            <div class="chart-card">
                <h4>Commandes par jour</h4>
                <div class="chart-wrap"><canvas id="chartOrders" aria-label="Graphique commandes"></canvas></div>
            </div>
            <div class="chart-card">
                <h4>Chiffre d’affaires (TND)</h4>
                <div class="chart-wrap"><canvas id="chartRevenue" aria-label="Graphique chiffre d’affaires"></canvas></div>
            </div>
            <div class="chart-card">
                <h4>Nouveaux utilisateurs</h4>
                <div class="chart-wrap"><canvas id="chartUsers" aria-label="Graphique inscriptions"></canvas></div>
            </div>
            <div class="chart-card">
                <h4>Messages du forum par jour</h4>
                <div class="chart-wrap"><canvas id="chartForumMsg" aria-label="Graphique messages du forum"></canvas></div>
            </div>
        </div>
    </section>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script>
(function () {
    var data = <?= $chartJson ?>;
    if (typeof Chart === 'undefined' || !data.labels || !data.labels.length) return;

    var curve = { tension: 0.38, cubicInterpolationMode: 'monotone' };
    var grid = { color: 'rgba(7, 59, 76, 0.08)' };
    var ticks = { color: '#5b6b72', maxTicksLimit: 7 };

    new Chart(document.getElementById('chartOrders'), {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Commandes',
                data: data.orders,
                borderColor: '#0077b6',
                backgroundColor: 'rgba(0, 180, 216, 0.18)',
                fill: true,
                ...curve,
                borderWidth: 2.5,
                pointRadius: 3,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: ticks },
                y: { beginAtZero: true, grid: grid, ticks: ticks }
            }
        }
    });

    new Chart(document.getElementById('chartRevenue'), {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'TND',
                data: data.revenue,
                borderColor: '#8e2de2',
                backgroundColor: 'rgba(142, 45, 226, 0.12)',
                fill: true,
                ...curve,
                borderWidth: 2.5,
                pointRadius: 3,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: ticks },
                y: { beginAtZero: true, grid: grid, ticks: ticks }
            }
        }
    });

    new Chart(document.getElementById('chartUsers'), {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Inscriptions',
                data: data.users,
                borderColor: '#ff7a18',
                backgroundColor: 'rgba(255, 122, 24, 0.15)',
                fill: true,
                ...curve,
                borderWidth: 2.5,
                pointRadius: 3,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: ticks },
                y: { beginAtZero: true, grid: grid, ticks: { ...ticks, precision: 0 } }
            }
        }
    });

    var forumCanvas = document.getElementById('chartForumMsg');
    if (forumCanvas) {
        new Chart(forumCanvas, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Messages forum',
                    data: data.forumMsg || [],
                    borderColor: '#06b6d4',
                    backgroundColor: 'rgba(6, 182, 212, 0.18)',
                    fill: true,
                    ...curve,
                    borderWidth: 2.5,
                    pointRadius: 3,
                    pointHoverRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: ticks },
                    y: { beginAtZero: true, grid: grid, ticks: { ...ticks, precision: 0 } }
                }
            }
        });
    }
})();
</script>

</body>
</html>
