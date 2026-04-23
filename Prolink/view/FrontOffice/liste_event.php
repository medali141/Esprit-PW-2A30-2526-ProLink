<?php
include "../../controller/eventC.php";
include "../../config.php";
$eventC = new EventC();
$liste  = $eventC->listeEvent();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ProLink - evenements </title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <!-- Project stylesheet (relative to this file) -->
    <link rel="stylesheet" href="../assets/style.css">

    <style>
   .table-modern {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    border-radius: 10px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* HEADER BLEU */
.table-modern thead {
    background: #085dfa;
    color: white;
}

.table-modern th {
    padding: 14px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
}

/* BODY */
.table-modern td {
    padding: 12px;
    font-size: 14px;
    color: #333;
}

/* lignes alternées */
.table-modern tbody tr:nth-child(even) {
    background: #f4f6f8;
}

/* hover */
.table-modern tbody tr:hover {
    background: #e3f2fd;
    transition: 0.2s;
}

/* bordures douces */
.table-modern td, .table-modern th {
    border-bottom: 1px solid #eee;
}
        :root{
            --bg: #0b1220;
            --muted: #9aa6b2;
            --card: #0f1724;
            --accent-1: #00a7ff;
            --accent-2: #00d4ff;
            --glass: rgba(255,255,255,0.04);
            --shadow: 0 12px 40px rgba(2,12,27,0.55);
            --radius-lg: 16px;
        }

        /* Page layout */
        body{
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
            background: radial-gradient(1200px 400px at 10% 10%, rgba(0,167,255,0.06), transparent 10%), linear-gradient(180deg,#061022 0%, #07162a 100%);
            color:#e6f0f6; margin:0; -webkit-font-smoothing:antialiased; display:flex; flex-direction:column; min-height:100vh;
        }

        .container{ max-width:1100px; margin:0 auto; padding:28px 20px; }
        .main{ flex:1; display:block }

        /* Hero */
        .hero{
            display:flex; gap:28px; align-items:center; justify-content:space-between; padding:56px; background: linear-gradient(90deg,var(--accent-1), var(--accent-2)); color:#042031; border-radius:var(--radius-lg); margin:28px 0; box-shadow: var(--shadow);
            position:relative; overflow:hidden;
        }

        /* subtle animated accent shape */
        .hero::after{
            content:''; position:absolute; right:-10%; top:-20%; width:380px; height:380px; background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.06), transparent 30%);
            transform: rotate(25deg); opacity:0.9; pointer-events:none;
        }

        .hero-left{ max-width:62%; }
        .hero h1{ font-size:44px; margin:0 0 12px; line-height:1.02; color:#00151b; letter-spacing:-0.5px }
        .hero p{ margin:0 0 18px; color:rgba(0,21,26,0.85); font-weight:600 }

        .cta{ display:inline-block; background:linear-gradient(90deg,#00151b, #012a35); color:var(--accent-2); padding:14px 22px; border-radius:12px; font-weight:800; text-decoration:none; box-shadow: 0 10px 30px rgba(0,167,255,0.12); transition: transform .18s ease, box-shadow .18s ease; }
        .cta:hover{ transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,212,255,0.14); }
        .cta:focus{ outline: 3px solid rgba(0,167,255,0.14); outline-offset:4px }

        .hero-visual{ flex:1; display:flex; justify-content:flex-end }
        .glass-card{ background:linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01)); border-radius:12px; padding:14px; backdrop-filter: blur(6px); box-shadow: 0 10px 30px rgba(2,12,27,0.5); color:#dff6ff }

        /* Features */
        .features{ display:grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap:18px; margin:28px 0 }
        .feature-card{ background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:22px; text-align:left; box-shadow: 0 8px 26px rgba(2,12,27,0.55); display:flex; gap:14px; align-items:flex-start; transition: transform .18s ease, box-shadow .18s ease }
        .feature-card:hover{ transform: translateY(-6px); box-shadow: 0 18px 40px rgba(2,12,27,0.6) }

        .feature-icon{ width:64px; height:64px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:26px; background: linear-gradient(135deg,var(--accent-1),var(--accent-2)); color:#00151b; box-shadow: 0 8px 20px rgba(2,12,27,0.5) }
        .feature-card h3{ color:#e6f0f6; margin:0 0 6px; font-size:18px }
        .feature-card p{ margin:0; color:var(--muted) }

        /* subtle entrance animation */
        .feature-card, .stat, .glass-card{ opacity:0; transform: translateY(8px); animation: fadeUp .48s ease forwards; }
        .feature-card:nth-child(1){ animation-delay: 0.06s }
        .feature-card:nth-child(2){ animation-delay: 0.12s }
        .feature-card:nth-child(3){ animation-delay: 0.18s }


        @keyframes fadeUp{ to { opacity:1; transform:none } }

        @media (max-width:900px){ .hero{ flex-direction:column; text-align:left } .hero-left{ max-width:100% } .hero-visual{ justify-content:flex-start; width:100% } }
        @media (max-width:520px){ .hero{ padding:26px } .hero h1{ font-size:28px } .feature-icon{ width:52px; height:52px } }
    </style>
    <meta charset="UTF-8">
    <title>Liste des utilisateurs</title>
    <?php /* Styles are in sidebar.css included by sidebar.php */ ?>
</head>

<body>
<!-- NAVBAR -->
<?php include 'components/navbar.php'; ?>

<!-- MAIN (fills available vertical space so footer stays at bottom) -->
<main class="main container">

<div class="content">
    <div class="container">

        <div class="topbar">
            <div class="page-title">Liste Des Evenements </div>
            <div class="actions">
                <input class="search-input" placeholder="Rechercher un utilisateur..." id="searchInput">
            </div>
        </div>

        <table class="table-modern" id="eventTable">
        <thead>
            <tr>
                <th>Id_Event</th>
                <th>Titre</th>
                <th>Description</th>
                <th>Type</th>
                <th>Date Début</th>
                <th>Date Fin</th>
                <th>Lieu</th>
                <th>Capacité Max</th>
                <th>Statut</th>
                <th>ID Org</th>
                <th>Actions</th>
            </tr>
        </thead>
        
        <tbody>
            <?php foreach ($liste as $event): ?>
                <tr>
                    <td><?= htmlspecialchars($event['id_event']) ?></td>
                    <td><?= htmlspecialchars($event['titre_event']) ?></td>
                    <td><?= htmlspecialchars($event['description_event']) ?></td>
                    <td><?= htmlspecialchars($event['type_event']) ?></td>
                    <td><?= htmlspecialchars($event['date_debut']) ?></td>
                    <td><?= htmlspecialchars($event['date_fin']) ?></td>
                    <td><?= htmlspecialchars($event['lieu_event']) ?></td>
                    <td><?= htmlspecialchars($event['capacite_max']) ?></td>
                    <td><?= htmlspecialchars($event['statut']) ?></td>
                    <td><?= htmlspecialchars($event['id_org']) ?></td>
                    <td>
                        <a href="#" class="btn btn-secondary" >participer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    </div>
</div>

<script>
    // small client-side search (non-blocking)
    document.getElementById('searchInput').addEventListener('input', function(e){
        const q = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#usersTable tbody tr');
        rows.forEach(r => {
            r.style.display = Array.from(r.cells).some(c => c.textContent.toLowerCase().includes(q)) ? '' : 'none';
        });
    });
</script>
</main>
<!-- FOOTER -->
<?php include 'components/footer.php'; ?>
</body>
</html>
