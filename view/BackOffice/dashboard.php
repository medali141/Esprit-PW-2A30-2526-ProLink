<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>

    <style>
        body {
            margin: 0;
            font-family: Arial;
        }

        .content {
            margin-left: 220px;
            padding: 20px;
        }

        .cards {
            display: flex;
            gap: 20px;
        }

        .card {
            flex: 1;
            padding: 20px;
            background: #f4f4f4;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        }

        .card h3 {
            margin: 0;
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<?php include 'sidebar.php'; ?>

<!-- CONTENT -->
<div class="content">
    <h1>Dashboard</h1>

    <div class="cards">
        <div class="card">
            <h3>Users</h3>
            <p>Gestion des utilisateurs</p>
        </div>

        <div class="card">
            <h3>Projets</h3>
            <p>Gestion des projets</p>
        </div>

        <div class="card">
            <h3>Events</h3>
            <p>Gestion des événements</p>
        </div>
    </div>
</div>

</body>
</html>