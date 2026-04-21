<?php
require_once '../../controller/FormationController.php';
require_once '../../model/Formation.php';

$formationC = new FormationController();
$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formation = new Formation(
        $_POST['id_categorie'],
        $_POST['titre'],
        $_POST['type'],
        $_POST['date_debut'],
        $_POST['date_fin'],
        $_POST['places_max'],
        $_POST['statut'],
        ''
    );
    
    if($formationC->modifier($id, $formation)) {
        header('Location: liste.php');
        exit();
    }
}

$formation = $formationC->afficherUne($id);
$row = $formation->fetch(PDO::FETCH_ASSOC);

require_once '../../controller/CategorieController.php';
$categorieC = new CategorieController();
$categories = $categorieC->afficherToutes();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modifier une formation</title>
</head>
<body>
    <h1>✏️ Modifier la formation</h1>
    
    <form method="POST">
        <label>Catégorie :</label>
        <select name="id_categorie" required>
            <?php while($cat = $categories->fetch(PDO::FETCH_ASSOC)): ?>
                <option value="<?= $cat['id_categorie'] ?>" <?= $cat['id_categorie'] == $row['id_categorie'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nom_categorie']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        
        <label>Titre :</label>
        <input type="text" name="titre" value="<?= htmlspecialchars($row['titre']) ?>" required>
        
        <label>Type :</label>
        <select name="type" required>
            <option value="presentiel" <?= $row['type'] == 'presentiel' ? 'selected' : '' ?>>Présentiel</option>
            <option value="en_ligne" <?= $row['type'] == 'en_ligne' ? 'selected' : '' ?>>En ligne</option>
        </select>
        
        <label>Date début :</label>
        <input type="date" name="date_debut" value="<?= $row['date_debut'] ?>" required>
        
        <label>Date fin :</label>
        <input type="date" name="date_fin" value="<?= $row['date_fin'] ?>" required>
        
        <label>Places max :</label>
        <input type="number" name="places_max" value="<?= $row['places_max'] ?>" required>
        
        <label>Statut :</label>
        <select name="statut" required>
            <option value="inscrit" <?= $row['statut'] == 'inscrit' ? 'selected' : '' ?>>Inscrit</option>
            <option value="termine" <?= $row['statut'] == 'termine' ? 'selected' : '' ?>>Terminé</option>
            <option value="annule" <?= $row['statut'] == 'annule' ? 'selected' : '' ?>>Annulé</option>
        </select>
        
        <button type="submit">Modifier</button>
    </form>
</body>
</html>