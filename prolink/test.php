<?php
// Connexion directe à la base de données
$host = 'localhost';
$dbname = 'prolink';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer les formations avec jointure
    $sql = "SELECT f.*, c.nom_categorie 
            FROM Formation f
            LEFT JOIN Categorie c ON f.id_categorie = c.id_categorie
            ORDER BY f.id_formation DESC";
    $stmt = $pdo->query($sql);
    $formations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des formations - ProLink</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 10px; padding: 20px; }
        h1 { color: #333; margin-bottom: 20px; }
        .btn-add { background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #4a6fdc; color: white; }
        .btn-edit { background: #ffc107; color: #333; padding: 5px 10px; border-radius: 5px; text-decoration: none; margin-right: 5px; }
        .btn-delete { background: #dc3545; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; }
        .status { padding: 3px 8px; border-radius: 20px; font-size: 12px; }
        .status.inscrit { background: #28a745; color: white; }
        .status.termine { background: #17a2b8; color: white; }
        .status.annule { background: #dc3545; color: white; }
        .empty { text-align: center; padding: 40px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📚 Gestion des formations</h1>
        <a href="ajouter_simple.php" class="btn-add">➕ Ajouter une formation</a>
        
        <?php if(count($formations) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Titre</th><th>Catégorie</th><th>Type</th>
                        <th>Date début</th><th>Date fin</th><th>Places</th><th>Statut</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($formations as $row): ?>
                    <tr>
                        <td><?= $row['id_formation'] ?></td>
                        <td><?= htmlspecialchars($row['titre']) ?></td>
                        <td><?= htmlspecialchars($row['nom_categorie'] ?? 'Non défini') ?></td>
                        <td><?= $row['type'] == 'presentiel' ? '🏢 Présentiel' : '💻 En ligne' ?></td>
                        <td><?= $row['date_debut'] ?></td>
                        <td><?= $row['date_fin'] ?></td>
                        <td><?= $row['places_max'] ?></td>
                        <td><span class="status <?= $row['statut'] ?>"><?= $row['statut'] ?></span></td>
                        <td>
                            <a href="modifier_simple.php?id=<?= $row['id_formation'] ?>" class="btn-edit">✏️</a>
                            <a href="supprimer_simple.php?id=<?= $row['id_formation'] ?>" class="btn-delete" onclick="return confirm('Supprimer ?')">🗑️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="empty">Aucune formation trouvée. <a href="ajouter_simple.php">Ajoutez-en une</a></p>
        <?php endif; ?>
    </div>
</body>
</html>