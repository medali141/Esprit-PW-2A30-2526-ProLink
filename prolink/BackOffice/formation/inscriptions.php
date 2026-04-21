<?php
$host = 'localhost';
$dbname = 'prolink';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer toutes les inscriptions avec les détails de la formation
    $sql = "SELECT i.*, f.titre as formation_titre, f.date_debut, f.date_fin
            FROM Inscription i
            LEFT JOIN Formation f ON i.id_formation = f.id_formation
            ORDER BY i.date_inscription DESC";
    $stmt = $pdo->query($sql);
    $inscriptions = $stmt->fetchAll();
    
    // Compter le nombre total d'inscriptions
    $totalInscriptions = count($inscriptions);
    
} catch(PDOException $e) {
    $inscriptions = [];
    $totalInscriptions = 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscriptions - ProLink</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fb; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 5px; }
        .subtitle { color: #666; margin-bottom: 20px; font-size: 14px; }
        .btn-back { background: #6c757d; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; display: inline-block; margin-bottom: 20px; }
        .btn-back:hover { background: #5a6268; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #4a6fdc; color: white; font-weight: 600; }
        tr:hover { background: #f8f9fa; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge.confirme { background: #28a745; color: white; }
        .badge.en_attente { background: #ffc107; color: #333; }
        .badge.annule { background: #dc3545; color: white; }
        .stat-total { background: #e9ecef; padding: 10px 15px; border-radius: 8px; display: inline-block; margin-bottom: 20px; }
        .stat-total span { font-weight: bold; color: #4a6fdc; font-size: 18px; }
        @media (max-width: 768px) { th, td { font-size: 12px; padding: 8px; } }
    </style>
</head>
<body>
    <div class="container">
        <a href="liste.php" class="btn-back">← Retour aux formations</a>
        
        <h1>📝 Gestion des inscriptions</h1>
        <p class="subtitle">Liste des candidats inscrits aux formations</p>
        
        <div class="stat-total">
            📊 Total des inscriptions : <span><?= $totalInscriptions ?></span>
        </div>
        
        <?php if(count($inscriptions) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Formation</th>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Date d'inscription</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($inscriptions as $row): ?>
                    <tr>
                        <td><?= $row['id_inscription'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($row['formation_titre'] ?? 'N/A') ?></strong><br>
                            <small style="color:#888;"><?= $row['date_debut'] ?? '' ?> → <?= $row['date_fin'] ?? '' ?></small>
                        </td>
                        <td><?= htmlspecialchars($row['nom']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['telephone'] ?? '-') ?></td>
                        <td><?= date('d/m/Y', strtotime($row['date_inscription'])) ?></td>
                        <td>
                            <span class="badge <?= $row['statut'] ?>"><?= $row['statut'] ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="text-align: center; padding: 60px; color: #888;">
                <p>📭 Aucune inscription pour le moment.</p>
                <p style="margin-top: 10px;">Les inscriptions des candidats apparaîtront ici.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>