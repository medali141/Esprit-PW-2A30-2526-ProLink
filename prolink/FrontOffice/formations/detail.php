<?php
session_start();
$host = 'localhost';
$dbname = 'prolink';
$username = 'root';
$password = '';
$message = '';
$messageType = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $id = isset($_GET['id']) ? $_GET['id'] : 0;
    
    $sql = "SELECT f.*, c.nom_categorie 
            FROM Formation f
            LEFT JOIN Categorie c ON f.id_categorie = c.id_categorie
            WHERE f.id_formation = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $formation = $stmt->fetch();
    
    if(!$formation) {
        header('Location: index.php');
        exit();
    }
    
    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['inscrire'])) {
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $age = trim($_POST['age'] ?? '');
        $ville = trim($_POST['ville'] ?? '');
        $niveau = trim($_POST['niveau'] ?? '');
        $motivation = trim($_POST['motivation'] ?? '');
        
        if(empty($nom) || empty($email)) {
            $message = "Veuillez remplir au moins votre nom et votre email.";
            $messageType = "error";
        } else {
            $check = $pdo->prepare("SELECT * FROM Inscription WHERE id_formation = :id_formation AND email = :email");
            $check->execute([':id_formation' => $id, ':email' => $email]);
            
            if($check->rowCount() > 0) {
                $message = "Vous êtes déjà inscrit à cette formation !";
                $messageType = "error";
            } else {
                $insert = $pdo->prepare("INSERT INTO Inscription (id_formation, nom, email, telephone, age, ville, niveau, motivation, date_inscription) 
                                         VALUES (:id_formation, :nom, :email, :telephone, :age, :ville, :niveau, :motivation, CURDATE())");
                $result = $insert->execute([
                    ':id_formation' => $id,
                    ':nom' => $nom,
                    ':email' => $email,
                    ':telephone' => $telephone,
                    ':age' => $age,
                    ':ville' => $ville,
                    ':niveau' => $niveau,
                    ':motivation' => $motivation
                ]);
                
                if($result) {
                    $message = "✅ Félicitations $nom ! Vous êtes inscrit à la formation \"" . htmlspecialchars($formation['titre']) . "\"";
                    $messageType = "success";
                } else {
                    $message = "❌ Erreur lors de l'inscription.";
                    $messageType = "error";
                }
            }
        }
    }
    
} catch(PDOException $e) {
    $error = $e->getMessage();
}

$inscriptionAutorisee = ($formation['statut'] == 'inscrit');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($formation['titre']) ?> - ProLink</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fb; color: #333; }
        
        .navbar { background: #0073b1; color: white; padding: 15px 30px; display: flex; justify-content: space-between; }
        .navbar a { color: white; text-decoration: none; margin-left: 20px; }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        
        .detail-card { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .card-header { padding: 30px; border-bottom: 1px solid #eee; }
        .badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; margin-bottom: 15px; }
        .badge.presentiel { background: #e3f2fd; color: #1976d2; }
        .badge.en_ligne { background: #e8f5e9; color: #388e3c; }
        .card-header h1 { font-size: 1.8rem; margin-bottom: 10px; }
        .categorie { color: #0073b1; }
        
        .info-section { padding: 30px; background: #f8f9fa; }
        .info-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee; }
        .info-label { font-weight: 600; color: #555; }
        .info-value { color: #333; }
        
        .inscription-section { padding: 30px; border-top: 1px solid #eee; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; font-family: inherit;
        }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .btn-inscrire { background: #28a745; color: white; border: none; padding: 15px 40px; border-radius: 50px; font-size: 1.1rem; font-weight: 600; cursor: pointer; width: 100%; }
        .btn-inscrire:hover { background: #218838; }
        
        .btn-back { display: inline-block; margin-top: 20px; color: #0073b1; text-decoration: none; }
        .message { padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .statut-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .statut-inscrit { background: #28a745; color: white; }
        .statut-termine { background: #6c757d; color: white; }
        .statut-annule { background: #dc3545; color: white; }
        
        .info-important { background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        
        footer { background: #333; color: white; text-align: center; padding: 20px; margin-top: 40px; }
        
        @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="logo">🚀 ProLink</div>
    <div>
        <a href="../home.php">Accueil</a>
        <a href="index.php">Formations</a>
    </div>
</nav>

<div class="container">
    <div class="detail-card">
        <div class="card-header">
            <div class="badge <?= $formation['type'] ?>">
                <?= $formation['type'] == 'presentiel' ? '🏢 Formation présentielle' : '💻 Formation en ligne' ?>
            </div>
            <h1><?= htmlspecialchars($formation['titre']) ?></h1>
            <p class="categorie">📁 Catégorie : <?= htmlspecialchars($formation['nom_categorie'] ?? 'Non catégorisé') ?></p>
        </div>
        
        <div class="info-section">
            <div class="info-item"><span class="info-label">📅 Date de début :</span><span class="info-value"><?= date('d/m/Y', strtotime($formation['date_debut'])) ?></span></div>
            <div class="info-item"><span class="info-label">📅 Date de fin :</span><span class="info-value"><?= date('d/m/Y', strtotime($formation['date_fin'])) ?></span></div>
            <div class="info-item"><span class="info-label">👥 Places disponibles :</span><span class="info-value"><?= $formation['places_max'] ?></span></div>
            <div class="info-item">
                <span class="info-label">📝 Statut :</span>
                <span class="info-value">
                    <span class="statut-badge statut-<?= $formation['statut'] ?>">
                        <?php 
                        switch($formation['statut']) {
                            case 'inscrit': echo '✅ Inscription ouverte'; break;
                            case 'termine': echo '🏁 Formation terminée'; break;
                            case 'annule': echo '❌ Formation annulée'; break;
                            default: echo $formation['statut'];
                        }
                        ?>
                    </span>
                </span>
            </div>
        </div>
        
        <?php if($message): ?>
            <div class="message <?= $messageType ?>"><?= $message ?></div>
        <?php endif; ?>
        
        <?php if(!$inscriptionAutorisee): ?>
            <div class="info-important">
                <?php if($formation['statut'] == 'termine'): ?>
                    🏁 Cette formation est terminée. Les inscriptions ne sont plus disponibles.
                <?php elseif($formation['statut'] == 'annule'): ?>
                    ❌ Cette formation a été annulée. Veuillez consulter d'autres formations.
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="inscription-section">
            <?php if($inscriptionAutorisee): ?>
                <h3 style="margin-bottom: 20px;">📝 Formulaire d'inscription</h3>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nom complet *</label>
                            <input type="text" name="nom" required placeholder="Votre nom et prénom">
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" required placeholder="votre@email.com">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Téléphone</label>
                            <input type="tel" name="telephone" placeholder="Votre numéro de téléphone">
                        </div>
                        <div class="form-group">
                            <label>Âge</label>
                            <input type="number" name="age" min="16" max="99" placeholder="Ex: 25">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Ville</label>
                            <input type="text" name="ville" placeholder="Votre ville">
                        </div>
                        <div class="form-group">
                            <label>Niveau d'études</label>
                            <select name="niveau">
                                <option value="">-- Sélectionnez --</option>
                                <option value="Bac">Bac</option>
                                <option value="Bac+2">Bac+2</option>
                                <option value="Bac+3">Bac+3 (Licence)</option>
                                <option value="Bac+5">Bac+5 (Master)</option>
                                <option value="Bac+8">Bac+8 (Doctorat)</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Motivation (pourquoi cette formation ?)</label>
                        <textarea name="motivation" placeholder="Décrivez brièvement votre motivation..."></textarea>
                    </div>
                    
                    <button type="submit" name="inscrire" class="btn-inscrire">📝 S'inscrire à cette formation</button>
                </form>
            <?php else: ?>
                <p style="text-align: center; color: #6c757d; padding: 20px;">
                    🔒 Les inscriptions ne sont pas disponibles pour cette formation.
                </p>
                <a href="index.php" style="display: block; text-align: center; color: #0073b1;">← Voir d'autres formations</a>
            <?php endif; ?>
        </div>
    </div>
    
    <div style="text-align: center;">
        <a href="index.php" class="btn-back">← Retour à la liste des formations</a>
    </div>
</div>

<footer>
    <p>&copy; 2025 ProLink - Plateforme de formation professionnelle</p>
</footer>

</body>
</html>