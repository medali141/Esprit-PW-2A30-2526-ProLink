<?php
require_once __DIR__ . '/../../controller/FormationController.php';

$formationC = new FormationController();
$formations = $formationC->afficherToutes();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des formations - ProLink</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fb; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 20px; }
        .btn-add { background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-bottom: 20px; }
        .btn-edit { background: #ffc107; color: #333; padding: 5px 10px; border-radius: 5px; text-decoration: none; margin-right: 5px; }
        .btn-delete { background: #dc3545; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #4a6fdc; color: white; }
        .status { padding: 3px 8px; border-radius: 20px; font-size: 12px; }
        .status.inscrit { background: #28a745; color: white; }
        .status.termine { background: #6c757d; color: white; }
        .status.annule { background: #dc3545; color: white; }
        .btn-back { background: #6c757d; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; float: right; }
        .message { padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <div class="container">
        <a href="../../FrontOffice/home.php" class="btn-back">← Retour au site</a>
        <h1>📚 Gestion des formations</h1>
        <a href="ajouter.php" class="btn-add">➕ Ajouter une formation</a>
        
        <?php if(isset($_GET['success'])): ?>
            <div class="message success">✅ Opération réussie !</div>
        <?php endif; ?>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Titre</th><th>Catégorie</th><th>Type</th>
                    <th>Date début</th><th>Date fin</th><th>Places</th><th>Statut</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $formations->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?= $row['id_formation'] ?></td>
                    <td><?= htmlspecialchars($row['titre']) ?></td>
                    <td><?= htmlspecialchars($row['nom_categorie'] ?? 'Sans catégorie') ?></td>
                    <td><?= $row['type'] == 'presentiel' ? '🏢 Présentiel' : '💻 En ligne' ?></td>
                    <td><?= $row['date_debut'] ?></td>
                    <td><?= $row['date_fin'] ?></td>
                    <td><?= $row['places_max'] ?></td>
                    <td><span class="status <?= $row['statut'] ?>"><?= $row['statut'] ?></span></td>
                    <td>
                        <a href="modifier.php?id=<?= $row['id_formation'] ?>" class="btn-edit">✏️</a>
                        <a href="supprimer.php?id=<?= $row['id_formation'] ?>" class="btn-delete" onclick="return confirm('Supprimer ?')">🗑️</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>