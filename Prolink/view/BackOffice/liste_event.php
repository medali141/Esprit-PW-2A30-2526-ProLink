<?php
include "../../controller/eventC.php";
include "../../config.php";

$eventC = new EventC();
$liste  = $eventC->listeEvent();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des utilisateurs</title>
    <?php /* Styles are in sidebar.css included by sidebar.php */ ?>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">
    <div class="container">

        <div class="topbar">
            <div class="page-title">Liste Des Evenements</div>
            <div class="actions">
                <input class="search-input" placeholder="Rechercher un utilisateur..." id="searchInput">
                <a href="ajout_event.php" class="btn btn-primary">+ Ajouter</a>
            </div>
        </div>

        <table class="table-modern" id="eventTable">
        <thead>
            <tr>
                <th>Id_Event</th>
                <th>Titre</th>
                <th>Description</th>
                <th>Type</th>
                <th>Date Début</th>
                <th>Date Fin</th>
                <th>Lieu</th>
                <th>Capacité Max</th>
                <th>Statut</th>
                <th>ID Org</th>
                <th>Actions</th>
            </tr>
        </thead>
        
        <tbody>
            <?php foreach ($liste as $event): ?>
                <tr>
                    <td><?= htmlspecialchars($event['id_event']) ?></td>
                    <td><?= htmlspecialchars($event['titre_event']) ?></td>
                    <td><?= htmlspecialchars($event['description_event']) ?></td>
                    <td><?= htmlspecialchars($event['type_event']) ?></td>
                    <td><?= htmlspecialchars($event['date_debut']) ?></td>
                    <td><?= htmlspecialchars($event['date_fin']) ?></td>
                    <td><?= htmlspecialchars($event['lieu_event']) ?></td>
                    <td><?= htmlspecialchars($event['capacite_max']) ?></td>
                    <td><?= htmlspecialchars($event['statut']) ?></td>
                    <td><?= htmlspecialchars($event['id_org']) ?></td>
                    <td>
                        <a href="supprimer_event.php?id=<?= $event['id_event'] ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Supprimer cet événement ?')">Supprimer</a>
                        <a href="modifier_event.php?id=<?= $event['id_event'] ?>" 
                           class="btn btn-secondary" >Modifier</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    </div>
</div>

<script>
    // small client-side search (non-blocking)
    document.getElementById('searchInput').addEventListener('input', function(e){
        const q = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#usersTable tbody tr');
        rows.forEach(r => {
            r.style.display = Array.from(r.cells).some(c => c.textContent.toLowerCase().includes(q)) ? '' : 'none';
        });
    });
</script>

</body>
</html>
