<?php
require_once '../../controller/participationC.php';
include "../../controller/eventC.php";
include "../../config.php";

// Suppression participation
if (isset($_GET['delete_p'])) {
    $pc = new ParticipationC();
    $pc->deleteParticipation($_GET['delete_p']);
    header('Location: liste_event.php');
    exit();
}

// Suppression event
if (isset($_GET['delete_e'])) {
    $ec = new EventC();
    // si vous avez une méthode deleteEvent dans eventC.php
    // $ec->deleteEvent($_GET['delete_e']);
    header('Location: liste_event.php');
    exit();
}

$eventC        = new EventC();
$liste         = $eventC->listeEvent();
$pc            = new ParticipationC();
$participations = $pc->listeParticipation();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Événements & Participations</title>
    <?php /* Styles are in sidebar.css included by sidebar.php */ ?>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">
    <div class="container">

        <!-- ========== TOPBAR ÉVÉNEMENTS ========== -->
        <div class="topbar">
            <div class="page-title">Liste Des Evenements</div>
            <div class="actions">
                <input class="search-input" placeholder="Rechercher un événement..." id="searchEventInput">
                <a href="ajout_event.php" class="btn btn-primary">+ Ajouter</a>
            </div>
        </div>

        <!-- ========== TABLE ÉVÉNEMENTS ========== -->
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
                        <td>
                            <a href="supprimer_event.php?id=<?= $event['id_event'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Supprimer cet événement ?')">Supprimer</a>
                            <a href="modifier_event.php?id=<?= $event['id_event'] ?>"
                               class="btn btn-secondary">Modifier</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- ========== TOPBAR PARTICIPATIONS ========== -->
        <div class="topbar" style="margin-top: 48px;">
            <div class="page-title">Liste Des Participations</div>
            <div class="actions">
                <input class="search-input" placeholder="Rechercher une participation..." id="searchPartInput">
                <a href="ajout_participation.php" class="btn btn-primary">+ Ajouter</a>
            </div>
        </div>

        <!-- ========== TABLE PARTICIPATIONS ========== -->
        <?php if (empty($participations)): ?>
            <p style="text-align:center; color:#888; padding:20px;">Aucune participation enregistrée.</p>
        <?php else: ?>
        <table class="table-modern" id="partTable">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Événement</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Date Inscription</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($participations as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['id_participation']) ?></td>
                        <td><?= htmlspecialchars($p['titre_event']) ?></td>
                        <td><?= htmlspecialchars($p['nom']) ?></td>
                        <td><?= htmlspecialchars($p['prenom']) ?></td>
                        <td><?= htmlspecialchars($p['email']) ?></td>
                        <td><?= htmlspecialchars($p['telephone']) ?></td>
                        <td><?= htmlspecialchars($p['date_inscription']) ?></td>
                        <td><?= htmlspecialchars($p['statut']) ?></td>
                        <td>
                            <a href="modifier_participation.php?id=<?= $p['id_participation'] ?>"
                               class="btn btn-secondary">Modifier</a>
                            <a href="?delete_p=<?= $p['id_participation'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Supprimer cette participation ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

    </div><!-- /container -->
</div><!-- /content -->

<script>
    // Recherche événements
    document.getElementById('searchEventInput').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#eventTable tbody tr').forEach(function(row) {
            row.style.display = Array.from(row.cells).some(function(c) {
                return c.textContent.toLowerCase().includes(q);
            }) ? '' : 'none';
        });
    });

    // Recherche participations
    document.getElementById('searchPartInput').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#partTable tbody tr').forEach(function(row) {
            row.style.display = Array.from(row.cells).some(function(c) {
                return c.textContent.toLowerCase().includes(q);
            }) ? '' : 'none';
        });
    });
</script>

</body>
</html>