<?php
session_start();
$host = 'localhost';
$dbname = 'prolink';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Option 1: Afficher TOUTES les formations (sans filtre)
    $sql = "SELECT f.*, c.nom_categorie 
            FROM Formation f
            LEFT JOIN Categorie c ON f.id_categorie = c.id_categorie
            ORDER BY f.date_debut DESC";
    $stmt = $pdo->query($sql);
    $formations = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $formations = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nos formations - ProLink</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .formations-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .page-title { text-align: center; margin-bottom: 40px; }
        .page-title h1 { font-size: 2.5rem; color: #0073b1; }
        .formations-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; }
        .formation-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .formation-card:hover { transform: translateY(-5px); }
        .card-badge { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin: 15px 0 0 15px; }
        .card-badge.presentiel { background: #e3f2fd; color: #1976d2; }
        .card-badge.en_ligne { background: #e8f5e9; color: #388e3c; }
        .formation-card h3 { font-size: 1.25rem; margin: 15px 15px 10px; color: #333; }
        .categorie { color: #0073b1; font-size: 0.85rem; margin: 0 15px 10px; }
        .dates { color: #888; font-size: 0.8rem; margin: 0 15px 10px; }
        .places { color: #28a745; font-weight: bold; margin: 0 15px 15px; }
        .card-footer { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-top: 1px solid #eee; background: #fafafa; }
        .status { padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
        .status.inscrit { background: #28a745; color: white; }
        .status.termine { background: #6c757d; color: white; }
        .status.annule { background: #dc3545; color: white; }
        .btn-detail { background: #0073b1; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 0.8rem; }
        .btn-detail:hover { background: #005f8d; }
        .btn-search { background: #17a2b8; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 0.8rem; margin-left: 10px; }
        .no-results { text-align: center; padding: 60px; color: #888; }
        .header-actions { text-align: right; margin-bottom: 20px; }
    </style>
</head>
<body>

<?php include '../components/navbar.php'; ?>

<div class="formations-container">
    <div class="page-title">
        <h1>📚 Nos formations</h1>
        <p>Découvrez nos formations en présentiel et en ligne</p>
    </div>
    
    <div class="header-actions">
        <a href="search.php" class="btn-search">🔍 Rechercher par catégorie</a>
    </div>

    <div class="formations-grid">
        <?php if(count($formations) > 0): ?>
            <?php foreach($formations as $row): ?>
                <div class="formation-card">
                    <div class="card-badge <?= $row['type'] ?>">
                        <?= $row['type'] == 'presentiel' ? '🏢 Présentiel' : '💻 En ligne' ?>
                    </div>
                    <h3><?= htmlspecialchars($row['titre']) ?></h3>
                    <p class="categorie">📁 <?= htmlspecialchars($row['nom_categorie'] ?? 'Non catégorisé') ?></p>
                    <div class="dates">📅 Du <?= date('d/m/Y', strtotime($row['date_debut'])) ?> au <?= date('d/m/Y', strtotime($row['date_fin'])) ?></div>
                    <div class="places">👥 Places disponibles : <?= $row['places_max'] ?></div>
                    <div class="card-footer">
                        <span class="status <?= $row['statut'] ?>"><?= $row['statut'] ?></span>
                        <a href="detail.php?id=<?= $row['id_formation'] ?>" class="btn-detail">Voir détails →</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">
                <p>Aucune formation disponible pour le moment.</p>
                <p style="margin-top: 10px;">👨‍💻 Connectez-vous en tant qu'admin pour ajouter des formations.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../components/footer.php'; ?>
</body>
</html>