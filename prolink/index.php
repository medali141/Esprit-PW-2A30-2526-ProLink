<?php
// Démarrer la session
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProLink - Plateforme de formation professionnelle</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .hero {
            text-align: center;
            padding: 80px 20px;
            color: white;
        }
        
        .hero h1 {
            font-size: 3.5em;
            margin-bottom: 20px;
            animation: fadeInDown 1s ease;
        }
        
        .hero p {
            font-size: 1.2em;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            margin: 10px;
            transition: transform 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-3px);
        }
        
        .btn-primary {
            background: white;
            color: #667eea;
        }
        
        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            padding: 50px 20px;
        }
        
        .feature {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }
        
        .feature:hover {
            transform: translateY(-10px);
        }
        
        .feature .icon {
            font-size: 3em;
            margin-bottom: 20px;
        }
        
        .feature h3 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .feature p {
            color: #666;
            line-height: 1.6;
        }
        
        .btn-feature {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: opacity 0.3s ease;
        }
        
        .btn-feature:hover {
            opacity: 0.9;
        }
        
        .stats {
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 40px;
            margin: 40px 0;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 30px;
        }
        
        .stat {
            text-align: center;
            color: white;
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
        }
        
        .stat-label {
            font-size: 0.9em;
            opacity: 0.8;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        footer {
            text-align: center;
            padding: 30px;
            color: white;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero">
            <h1>🚀 ProLink</h1>
            <p>La plateforme intelligente qui connecte entrepreneurs et talents</p>
            <div>
                <a href="/FrontOffice/home.php" class="btn btn-primary">Accéder à la plateforme</a>
                <a href="/BackOffice/dashboard.php" class="btn btn-secondary">Espace Admin</a>
            </div>
        </div>
        
        <div class="features">
            <div class="feature">
                <div class="icon">📚</div>
                <h3>Formations</h3>
                <p>Découvrez nos formations en présentiel et en ligne adaptées à vos besoins</p>
                <a href="/FrontOffice/formations/index.php" class="btn-feature">Voir les formations →</a>
            </div>
            <div class="feature">
                <div class="icon">🤝</div>
                <h3>Mise en relation</h3>
                <p>Connectez-vous avec des entrepreneurs et candidats qualifiés</p>
                <a href="/FrontOffice/home.php" class="btn-feature">En savoir plus →</a>
            </div>
            <div class="feature">
                <div class="icon">🎯</div>
                <h3>Scoring IA</h3>
                <p>Classement automatique des profils selon vos compétences</p>
                <a href="/FrontOffice/home.php" class="btn-feature">Découvrir →</a>
            </div>
        </div>
        
        <?php
        // Connexion à la base pour les statistiques
        try {
            // Connexion directe à la base de données
            $host = 'localhost';
            $dbname = 'prolink';
            $username = 'root';
            $password = '';
            
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Compter les formations
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM Formation");
            $nbFormations = $stmt->fetch()['total'];
            
            // Compter les catégories
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM Categorie");
            $nbCategories = $stmt->fetch()['total'];
            
        } catch(Exception $e) {
            $nbFormations = 0;
            $nbCategories = 0;
        }
        ?>
        
        <div class="stats">
            <div class="stat">
                <div class="stat-number"><?= $nbFormations ?></div>
                <div class="stat-label">Formations disponibles</div>
            </div>
            <div class="stat">
                <div class="stat-number"><?= $nbCategories ?></div>
                <div class="stat-label">Catégories</div>
            </div>
            <div class="stat">
                <div class="stat-number">100%</div>
                <div class="stat-label">Satisfaction</div>
            </div>
            <div class="stat">
                <div class="stat-number">24/7</div>
                <div class="stat-label">Support</div>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2025 ProLink - Tous droits réservés</p>
        </footer>
    </div>
</body>
</html>