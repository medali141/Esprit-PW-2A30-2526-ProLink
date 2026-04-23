<?php
require_once '../../controller/participationC.php';
$pc = new ParticipationC();

if (isset($_GET['delete'])) {
    $pc->deleteParticipation($_GET['delete']);
    header('Location: liste_participation.php');
    exit();
}

$participations = $pc->listeParticipation();
?>
<!DOCTYPE html>
<html lang="fr">
<head><title>Participations</title><meta charset="utf-8"></head>
<body>
<?php include 'sidebar.php'; ?>
<div class="content">
    <div class="topbar">
        <div class="page-title">Liste des Participations</div>
        <div class="actions">
            <a href="ajout_participation.php" class="btn btn-primary">+ Ajouter</a>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Événement</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Date inscription</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($participations as $p): ?>
            <tr>
                <td><?= $p['id_participation'] ?></td>
                <td><?= htmlspecialchars($p['titre_event']) ?></td>
                <td><?= htmlspecialchars($p['nom']) ?></td>
                <td><?= htmlspecialchars($p['prenom']) ?></td>
                <td><?= htmlspecialchars($p['email']) ?></td>
                <td><?= htmlspecialchars($p['telephone']) ?></td>
                <td><?= $p['date_inscription'] ?></td>
                <td><?= htmlspecialchars($p['statut']) ?></td>
                <td>
                    <a href="modifier_participation.php?id=<?= $p['id_participation'] ?>">✏️</a>
                    <a href="?delete=<?= $p['id_participation'] ?>"
                       onclick="return confirm('Supprimer cette participation ?')">🗑️</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>