<?php
require_once __DIR__ . '/../../controller/FormationController.php';
require_once __DIR__ . '/../../model/Formation.php';
require_once __DIR__ . '/../../controller/CategorieController.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formation = new Formation(
        $_POST['id_categorie'],
        $_POST['titre'],
        $_POST['type'],
        $_POST['date_debut'],
        $_POST['date_fin'],
        $_POST['places_max'],
        $_POST['statut'],
        date('Y-m-d')
    );
    
    $formationC = new FormationController();
    if($formationC->ajouter($formation)) {
        $success = "Formation ajoutée avec succès !";
        echo '<script>setTimeout(function(){ window.location.href = "liste.php"; }, 1500);</script>';
    } else {
        $error = "Erreur lors de l'ajout.";
    }
}

$categorieC = new CategorieController();
$categories = $categorieC->afficherToutes();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une formation - ProLink</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 40px 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 15px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 1.8rem; margin-bottom: 5px; }
        .header p { opacity: 0.9; font-size: 0.9rem; }
        .form-container { padding: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .required { color: #dc3545; }
        input, select { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; transition: border-color 0.3s; }
        input:focus, select:focus { outline: none; border-color: #667eea; }
        input.error, select.error { border-color: #dc3545; background-color: #fff0f0; }
        .error-message { color: #dc3545; font-size: 12px; margin-top: 5px; display: none; }
        .error-message.show { display: block; }
        button { width: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 14px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: transform 0.2s; }
        button:hover { transform: translateY(-2px); }
        button:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .alert { padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .btn-back { display: inline-block; margin-bottom: 20px; color: #667eea; text-decoration: none; font-weight: 500; }
        .btn-back:hover { text-decoration: underline; }
        .char-counter { font-size: 12px; color: #888; text-align: right; margin-top: 5px; }
        .char-counter.warning { color: #ffc107; }
        .char-counter.danger { color: #dc3545; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        @media (max-width: 480px) { .form-row { grid-template-columns: 1fr; } .form-container { padding: 20px; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>➕ Ajouter une formation</h1>
            <p>Remplissez tous les champs ci-dessous</p>
        </div>
        
        <div class="form-container">
            <a href="liste.php" class="btn-back">← Retour à la liste</a>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <form id="formationForm" method="POST">
                <div class="form-group">
                    <label>Catégorie <span class="required">*</span></label>
                    <select name="id_categorie" id="id_categorie" required>
                        <option value="">-- Sélectionnez une catégorie --</option>
                        <?php while($cat = $categories->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?= $cat['id_categorie'] ?>"><?= htmlspecialchars($cat['nom_categorie']) ?></option>
                        <?php endwhile; ?>
                    </select>
                    <div class="error-message" id="error-categorie">Veuillez sélectionner une catégorie.</div>
                </div>
                
                <div class="form-group">
                    <label>Titre <span class="required">*</span></label>
                    <input type="text" name="titre" id="titre" maxlength="150" required>
                    <div class="char-counter" id="titre-counter">0/150 caractères</div>
                    <div class="error-message" id="error-titre">Le titre doit contenir entre 3 et 150 caractères.</div>
                </div>
                
                <div class="form-group">
                    <label>Type <span class="required">*</span></label>
                    <select name="type" id="type" required>
                        <option value="">-- Sélectionnez un type --</option>
                        <option value="presentiel">🏢 Présentiel</option>
                        <option value="en_ligne">💻 En ligne</option>
                    </select>
                    <div class="error-message" id="error-type">Veuillez sélectionner un type.</div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Date début <span class="required">*</span></label>
                        <input type="date" name="date_debut" id="date_debut" required>
                        <div class="error-message" id="error-date_debut">La date de début est obligatoire.</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Date fin <span class="required">*</span></label>
                        <input type="date" name="date_fin" id="date_fin" required>
                        <div class="error-message" id="error-date_fin">La date de fin doit être postérieure à la date de début.</div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Places max <span class="required">*</span></label>
                        <input type="number" name="places_max" id="places_max" min="1" max="999" required>
                        <div class="error-message" id="error-places_max">Le nombre de places doit être compris entre 1 et 999.</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Statut <span class="required">*</span></label>
                        <select name="statut" id="statut" required>
                            <option value="">-- Sélectionnez un statut --</option>
                            <option value="inscrit">📝 Inscrit</option>
                            <option value="termine">✅ Terminé</option>
                            <option value="annule">❌ Annulé</option>
                        </select>
                        <div class="error-message" id="error-statut">Veuillez sélectionner un statut.</div>
                    </div>
                </div>
                
                <button type="submit" id="submitBtn">➕ Ajouter la formation</button>
            </form>
        </div>
    </div>
    
    <script src="/BackOffice/formation-validation.js"></script>
</body>
</html>