<?php
session_start();
$host = 'localhost';
$dbname = 'prolink';
$username = 'root';
$password = '';
$formations = [];
$categorieNom = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmtCat = $pdo->query("SELECT * FROM Categorie ORDER BY nom_categorie");
    $categories = $stmtCat->fetchAll();
    
    if(isset($_GET['categorie']) && !empty($_GET['categorie'])) {
        $id_categorie = $_GET['categorie'];
        $sql = "SELECT f.*, c.nom_categorie FROM Formation f LEFT JOIN Categorie c ON f.id_categorie = c.id_categorie WHERE f.id_categorie = :id_categorie AND f.statut = 'inscrit' ORDER BY f.date_debut DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id_categorie' => $id_categorie]);
        $formations = $stmt->fetchAll();
        
        $stmtCatName = $pdo->prepare("SELECT nom_categorie FROM Categorie WHERE id_categorie = :id");
        $stmtCatName->execute([':id' => $id_categorie]);
        $cat = $stmtCatName->fetch();
        $categorieNom = $cat ? $cat['nom_categorie'] : '';
    }
} catch(PDOException $e) { $categories = []; }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche formations - ProLink</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .search-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .search-box { background: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; text-align: center; }
        .search-form { display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; }
        .search-form select { padding: 12px 20px; border: 1px solid #ddd; border-radius: 8px; width: 250px; }
        .btn-search { background: #0073b1; color: white; border: none; padding: 12px 30px; border-radius: 8px; cursor: pointer; }
        .results-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px; margin-top: 20px; }
        .formation-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .formation-card h3 { margin-bottom: 10px; color: #333; }
        .no-results { text-align: center; padding: 60px; background: white; border-radius: 12px; }
    </style>
</head>
<body>
<?php include '../components/navbar.php'; ?>

<div class="search-container">
    <div class="search-box">
        <h2>🔍 Rechercher une formation</h2>
        <form method="GET" action="" class="search-form">
            <select name="categorie" required>
                <option value="">-- Sélectionnez une catégorie --</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id_categorie'] ?>"><?= htmlspecialchars($cat['nom_categorie']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-search">🔎 Rechercher</button>
        </form>
    </div>

    <?php if(isset($_GET['categorie']) && !empty($_GET['categorie'])): ?>
        <h2>Résultats pour : <?= htmlspecialchars($categorieNom) ?></h2>
        <?php if(count($formations) > 0): ?>
            <div class="results-grid">
                <?php foreach($formations as $row): ?>
                    <div class="formation-card">
                        <h3><?= htmlspecialchars($row['titre']) ?></h3>
                        <p>📅 Du <?= $row['date_debut'] ?> au <?= $row['date_fin'] ?></p>
                        <p>👥 Places : <?= $row['places_max'] ?></p>
                        <a href="detail.php?id=<?= $row['id_formation'] ?>" class="btn-detail">Voir détails →</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">Aucune formation trouvée dans cette catégorie.</div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include '../components/footer.php'; ?>
</body>
</html>