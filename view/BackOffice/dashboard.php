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
    </style>
</head>

<body>

<!-- SIDEBAR -->
<?php include 'sidebar.php'; ?>

<!-- CONTENT -->
<div class="content">
    <div class="dashboard-header">
        <div>
            <div class="page-title">Tableau de bord</div>
            <div class="subtitle">Vue d'ensemble rapide — statistiques et actions</div>
        </div>
        <div class="actions">
            <a class="btn btn-primary" href="listUsers.php">Gérer les utilisateurs</a>
        </div>
    </div>

    <section class="stats-grid">
        <div class="stat-card">
            <div class="icon">👥</div>
            <div>
                <h3>1,248</h3>
                <p>Utilisateurs actifs</p>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg,#ff7a18,#ff3d67);">
            <div class="icon">📁</div>
            <div>
                <h3>312</h3>
                <p>Projets publiés</p>
            </div>
        </div>

        <div class="stat-card" style="background: linear-gradient(135deg,#8e2de2,#4a00e0);">
            <div class="icon">📅</div>
            <div>
                <h3>24</h3>
                <p>Événements à venir</p>
            </div>
        </div>
    </section>

    <div style="height:18px"></div>

    <section class="grid-3">
        <div class="card-light">
            <h4>Activité récente</h4>
            <p style="margin-top:8px; color:#5b6b72">Dernières actions réalisées par les utilisateurs et changements système.</p>
        </div>
        <div class="card-light">
            <h4>Ressources</h4>
            <p style="margin-top:8px; color:#5b6b72">Liens rapides: import/export, sauvegarde, paramètres.(todo)</p>
        </div>
        <div class="card-light">
            <h4>Support</h4>
            <p style="margin-top:8px; color:#5b6b72">espace des courbes et des analyses.(todo)</p>
        </div>
    </section>

</div>

</body>
</html>