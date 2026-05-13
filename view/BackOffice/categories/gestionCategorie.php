<?php
session_start();
require_once 'C:/xampp/htdocs/prolink/config.php';

// Vérifier si l'utilisateur est admin
if(!isset($_SESSION['user']) || $_SESSION['user']['type'] !== 'admin') {
    header('Location: /prolink/view/login.php');
    exit();
}

$pdo = Config::getConnexion();
$message = '';
$error = '';

// Ajouter une catégorie
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter'])) {
    $nom = trim($_POST['nom_categorie']);
    $description = trim($_POST['description'] ?? '');
    
    if(empty($nom)) {
        $error = "Le nom de la catégorie est obligatoire.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categorie (nom_categorie, description) VALUES (:nom, :desc)");
            $stmt->execute([':nom' => $nom, ':desc' => $description]);
            $message = "Catégorie ajoutée avec succès !";
        } catch(PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    }
}

// Modifier une catégorie
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modifier'])) {
    $id = $_POST['id_categorie'];
    $nom = trim($_POST['nom_categorie']);
    $description = trim($_POST['description'] ?? '');
    
    if(empty($nom)) {
        $error = "Le nom de la catégorie est obligatoire.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE categorie SET nom_categorie = :nom, description = :desc WHERE id_categorie = :id");
            $stmt->execute([':id' => $id, ':nom' => $nom, ':desc' => $description]);
            $message = "Catégorie modifiée avec succès !";
        } catch(PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    }
}

// Supprimer une catégorie
if(isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    try {
        // Vérifier si des formations utilisent cette catégorie
        $check = $pdo->prepare("SELECT COUNT(*) FROM formation WHERE id_categorie = :id");
        $check->execute([':id' => $id]);
        $count = $check->fetchColumn();
        
        if($count > 0) {
            $error = "Impossible de supprimer : $count formation(s) utilisent cette catégorie.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM categorie WHERE id_categorie = :id");
            $stmt->execute([':id' => $id]);
            $message = "Catégorie supprimée avec succès !";
        }
    } catch(PDOException $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

// Récupérer toutes les catégories
$categories = $pdo->query("SELECT * FROM categorie ORDER BY id_categorie DESC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des catégories - ProLink</title>
    <link rel="stylesheet" href="../../assets/backoffice.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .page-header h1 {
            font-size: 28px;
            color: #1a2a3a;
        }
        .form-section {
            background: white;
            padding: 25px;
            border-radius: 16px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .form-section h3 {
            margin-bottom: 20px;
            color: #1a2a3a;
            border-left: 4px solid #0073b1;
            padding-left: 12px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        textarea {
            min-height: 80px;
            resize: vertical;
        }
        .btn {
            padding: 8px 20px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #0073b1;
            color: white;
        }
        .btn-primary:hover {
            background: #005f8d;
        }
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        .btn-warning:hover {
            background: #d97706;
        }
        .btn-danger {
            background: #dc2626;
            color: white;
        }
        .btn-danger:hover {
            background: #b91c1c;
        }
        .btn-sm {
            padding: 4px 12px;
            font-size: 12px;
        }
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
        }
        .btn-back:hover {
            background: #5a6268;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc2626;
        }
        .inline-form {
            display: inline;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        @media (max-width: 768px) {
            .container { padding: 15px; }
            th, td { padding: 8px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>📁 Gestion des catégories</h1>
            <a href="../formation/liste.php" class="btn-back">← Retour aux formations</a>
        </div>
        
        <?php if($message): ?>
            <div class="alert-success">✅ <?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert-error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <!-- Formulaire d'ajout -->
        <div class="form-section">
            <h3>➕ Ajouter une catégorie</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Nom de la catégorie *</label>
                    <input type="text" name="nom_categorie" required placeholder="Ex: Informatique, Marketing, Design...">
                </div>
                <div class="form-group">
                    <label>Description (optionnelle)</label>
                    <textarea name="description" placeholder="Description de la catégorie"></textarea>
                </div>
                <button type="submit" name="ajouter" class="btn btn-primary">➕ Ajouter</button>
            </form>
        </div>
        
        <!-- Liste des catégories -->
        <h3 style="margin-bottom: 15px;">📋 Liste des catégories</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Créée le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $categories->fetch(PDO::FETCH_ASSOC)): ?>
                    <form method="POST" class="inline-form">
                        <input type="hidden" name="id_categorie" value="<?= $row['id_categorie'] ?>">
                        <tr>
                            <td style="width: 50px;"><?= $row['id_categorie'] ?></td>
                            <td>
                                <input type="text" name="nom_categorie" value="<?= htmlspecialchars($row['nom_categorie']) ?>" style="width: 100%;" required>
                            </td>
                            <td>
                                <input type="text" name="description" value="<?= htmlspecialchars($row['description'] ?? '') ?>" style="width: 100%;">
                            </td>
                            <td style="width: 100px;"><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                            <td style="width: 140px;">
                                <div class="action-buttons">
                                    <button type="submit" name="modifier" class="btn btn-warning btn-sm">✏️ Modifier</button>
                                    <a href="?delete_id=<?= $row['id_categorie'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer cette catégorie ?')">🗑️ Supprimer</a>
                                </div>
                            </td>
                        </tr>
                    </form>
                <?php endwhile; ?>
                <?php if($categories->rowCount() == 0): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px;">
                            📭 Aucune catégorie. Ajoutez-en une !
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>