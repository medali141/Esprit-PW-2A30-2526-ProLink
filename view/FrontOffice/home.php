<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>ProLink - Accueil</title>

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: #f3f2ef;
        }

        .hero {
            text-align: center;
            padding: 80px 20px;
            background: linear-gradient(to right, #0073b1, #00a0dc);
            color: white;
        }

        .hero h1 {
            font-size: 40px;
        }

        .hero p {
            font-size: 18px;
        }

        .hero .btn {
            padding: 12px 25px;
            background: white;
            color: #0073b1;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
            display: inline-block;
        }

        .features {
            display: flex;
            justify-content: center;
            gap: 20px;
            padding: 50px;
        }

        .card {
            background: white;
            padding: 20px;
            width: 250px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .card h3 {
            color: #0073b1;
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<?php include 'components/navbar.php'; ?>

<!-- HERO -->
<section class="hero">
    <h1>Bienvenue sur ProLink</h1>
    <p>Connectez-vous avec des professionnels, partagez vos projets et développez votre réseau</p>
    <a href="../register.php" class="btn">Commencer</a>
</section>

<!-- FEATURES -->
<section class="features">
    <div class="card">
        <h3>👤 Réseau</h3>
        <p>Ajoutez et interagissez avec des professionnels</p>
    </div>

    <div class="card">
        <h3>📁 Projets</h3>
        <p>Publiez et collaborez sur des projets</p>
    </div>

    <div class="card">
        <h3>📅 Événements</h3>
        <p>Participez à des événements professionnels</p>
    </div>
</section>

<!-- FOOTER -->
<?php include 'components/footer.php'; ?>

</body>
</html>