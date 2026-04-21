<?php
$host = 'localhost';
$dbname = 'prolink';
$username = 'root';
$password = '';

$error = '';
$success = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ajout d'une catégorie
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
        $nom = trim($_POST['nom_categorie']);
        $description = trim($_POST['description']);
        
        if(empty($nom)) {
            $error = "Le nom de la catégorie est obligatoire.";
        } else {
            $sql = "INSERT INTO Categorie (nom_categorie, description) VALUES (:nom, :desc)";
            $stmt = $pdo->prepare($sql);
            if($stmt->execute([':nom' => $nom, ':desc' => $description])) {
                $success = "Catégorie ajoutée avec succès !";
            } else {
                $error = "Erreur lors de l'ajout.";
            }
        }
    }
    
    // Modification d'une catégorie
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier') {
        $id = $_POST['id_categorie'];
        $nom = trim($_POST['nom_categorie']);
        $description = trim($_POST['description']);
        
        if(empty($nom)) {
            $error = "Le nom de la catégorie est obligatoire.";
        } else {
            $sql = "UPDATE Categorie SET nom_categorie = :nom, description = :desc WHERE id_categorie = :id";
            $stmt = $pdo->prepare($sql);
            if($stmt->execute([':id' => $id, ':nom' => $nom, ':desc' => $description])) {
                $success = "Catégorie modifiée avec succès !";
            } else {
                $error = "Erreur lors de la modification.";
            }
        }
    }
    
    // Suppression d'une catégorie
    if (isset($_GET['supprimer'])) {
        $id = $_GET['supprimer'];
        
        // Vérifier si la catégorie a des formations associées
        $check = $pdo->prepare("SELECT COUNT(*) FROM Formation WHERE id_categorie = :id");
        $check->execute([':id' => $id]);
        $count = $check->fetchColumn();
        
        if($count > 0) {
            $error = "Impossible de supprimer cette catégorie car elle contient $count formation(s).";
        } else {
            $sql = "DELETE FROM Categorie WHERE id_categorie = :id";
            $stmt = $pdo->prepare($sql);
            if($stmt->execute([':id' => $id])) {
                $success = "Catégorie supprimée avec succès !";
            } else {
                $error = "Erreur lors de la suppression.";
            }
        }
    }
    
    // Récupérer toutes les catégories
    $stmt = $pdo->query("SELECT * FROM Categorie ORDER BY id_categorie DESC");
    $categories = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $error = "Erreur base de données : " . $e->getMessage();
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des catégories - ProLink</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fb; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 5px; }
        .subtitle { color: #666; margin-bottom: 20px; font-size: 14px; }
        .btn-back { background: #6c757d; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; display: inline-block; margin-bottom: 20px; }
        .btn-back:hover { background: #5a6268; }
        .form-section { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 30px; }
        .form-section h3 { margin-bottom: 15px; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; }
        input[type="text"], textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
        textarea { resize: vertical; min-height: 60px; }
        button { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-size: 14px; }
        button:hover { background: #218838; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #4a6fdc; color: white; font-weight: 600; }
        tr:hover { background: #f8f9fa; }
        .btn-edit { background: #ffc107; color: #333; padding: 5px 10px; border-radius: 5px; text-decoration: none; border: none; cursor: pointer; }
        .btn-edit:hover { background: #e0a800; }
        .btn-delete { background: #dc3545; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; margin-left: 5px; }
        .btn-delete:hover { background: #c82333; }
        .alert { padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .actions { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="container">
        <a href="../formation/liste.php" class="btn-back">← Retour aux formations</a>
        
        <h1>📁 Gestion des catégories</h1>
        <p class="subtitle">Ajoutez, modifiez ou supprimez les catégories de formations</p>
        
        <?php if($success): ?>
            <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <!-- Formulaire d'ajout -->
        <div class="form-section">
            <h3>➕ Ajouter une catégorie</h3>
            <form method="POST">
                <input type="hidden" name="action" value="ajouter">
                <div class="form-group">
                    <label>Nom de la catégorie *</label>
                    <input type="text" name="nom_categorie" required placeholder="Ex: Informatique, Marketing, Design...">
                </div>
                <div class="form-group">
                    <label>Description (optionnelle)</label>
                    <textarea name="description" placeholder="Description de la catégorie..."></textarea>
                </div>
                <button type="submit">➕ Ajouter</button>
            </form>
        </div>
        
        <!-- Liste des catégories -->
        <h3>📋 Liste des catégories</h3>
        
        <?php if(count($categories) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($categories as $row): ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="modifier">
                            <input type="hidden" name="id_categorie" value="<?= $row['id_categorie'] ?>">
                            <tr>
                                <td style="width: 50px;"><?= $row['id_categorie'] ?></td>
                                <td>
                                    <input type="text" name="nom_categorie" value="<?= htmlspecialchars($row['nom_categorie']) ?>" style="width: 100%;" required>
                                </td>
                                <td>
                                    <textarea name="description" style="width: 100%; min-height: 40px;"><?= htmlspecialchars($row['description'] ?? '') ?></textarea>
                                </td>
                                <td class="actions">
                                    <button type="submit" class="btn-edit">✏️ Modifier</button>
                                    <a href="?supprimer=<?= $row['id_categorie'] ?>" class="btn-delete" onclick="return confirm('Supprimer cette catégorie ? Les formations associées ne seront pas supprimées.')">🗑️ Supprimer</a>
                                </td>
                            </tr>
                        </form>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; padding: 40px; color: #888;">Aucune catégorie trouvée. Ajoutez votre première catégorie !</p>
        <?php endif; ?>
    </div>
</body>
</html>