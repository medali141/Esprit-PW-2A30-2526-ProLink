<?php
include "../../controller/eventC.php";
include "../../model/event.php";
include "../../config.php";

$ec = new EventC();

if (isset($_GET['id'])) {
    $event = $ec->updateEvent($_GET['id']);
}

if (
    isset($_POST["titre_event"], $_POST["description_event"], $_POST["type_event"],
          $_POST["date_debut"], $_POST["date_fin"], $_POST["lieu_event"],
          $_POST["capacite_max"], $_POST["statut"], $_POST["id_org"])
) {
    $newEvent = new Event(
        $_POST['titre_event'],
        $_POST['description_event'],
        $_POST['type_event'],
        $_POST['date_debut'],
        $_POST['date_fin'],
        $_POST['lieu_event'],
        $_POST['capacite_max'],
        $_POST['statut'],
        $_POST['id_org']
    );

    $ec->saveEvent($newEvent, $_GET['id']);
    header('Location: listEvents.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Modifier un Événement</title>
     <style>
        .msg { font-size: 0.9em; margin-top: 5px; display: block; }
        .error { color: red; }
        .success { color: green; }
    </style>
    <meta charset="utf-8">
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">
    <div class="topbar">
        <div class="page-title">Modifier un Événement</div>
        <div class="actions">
            <a href="listEvents.php" class="btn btn-secondary">← Retour</a>
        </div>
    </div>

    <div class="card" style="max-width:760px; margin:0 auto;">
        <form method="POST" id="eventForm" novalidate>
            <div class="form-grid">

                <div>
                    <input type="text" name="titre_event" id="titre_event" placeholder="Titre"
                           value="<?= htmlspecialchars($event['titre_event']) ?>">
                    <div class="msg" id="msg-titre"></div>
                </div>

                <div>
                    <textarea name="description_event" id="description_event" placeholder="Description" rows="3"><?= htmlspecialchars($event['description_event']) ?></textarea>
                    <div class="msg" id="msg-description"></div>
                </div>

                <div>
                    <select name="type_event" id="type_event">
                        <option value="">-- Choisir un type --</option>
                        <?php foreach (['conference', 'formation', 'atelier'] as $type): ?>
                            <option value="<?= $type ?>" <?= $event['type_event'] === $type ? 'selected' : '' ?>>
                                <?= ucfirst($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="msg" id="msg-type"></div>
                </div>

                <div>
                    <input type="text" name="date_debut" id="date_debut" placeholder="AAAA-MM-JJ"
                           value="<?= htmlspecialchars($event['date_debut']) ?>">
                    <div class="msg" id="msg-date-debut"></div>
                </div>

                <div>
                    <input type="text" name="date_fin" id="date_fin" placeholder="AAAA-MM-JJ"
                           value="<?= htmlspecialchars($event['date_fin']) ?>">
                    <div class="msg" id="msg-date-fin"></div>
                </div>

                <div>
                    <input type="text" name="lieu_event" id="lieu_event" placeholder="Lieu"
                           value="<?= htmlspecialchars($event['lieu_event']) ?>">
                    <div class="msg" id="msg-lieu"></div>
                </div>

                <div>
                    <input type="text" name="capacite_max" id="capacite_max" placeholder="Capacité Max"
                           value="<?= htmlspecialchars($event['capacite_max']) ?>">
                    <div class="msg" id="msg-capacite"></div>
                </div>

                <div>
                    <select name="statut" id="statut">
                        <option value="">-- Choisir un statut --</option>
                        <?php foreach (['en attente', 'confirme', 'annule'] as $s): ?>
                            <option value="<?= $s ?>" <?= $event['statut'] === $s ? 'selected' : '' ?>>
                                <?= ucfirst($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="msg" id="msg-statut"></div>
                </div>

                <div>
                    <input type="text" name="id_org" id="id_org" placeholder="ID Organisateur"
                           value="<?= htmlspecialchars($event['id_org']) ?>">
                    <div class="msg" id="msg-idorg"></div>
                </div>

            </div>

            <div style="text-align:right; margin-top:12px;">
                <button type="submit" class="btn btn-primary">Modifier</button>
            </div>
        </form>
    </div>

</div>

    <script src="ajout_event.js"></script>
</body>
</html>