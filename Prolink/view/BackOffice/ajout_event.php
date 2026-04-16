<?php
include '../../controller/eventC.php';
include '../../Model/Event.php';  
$error = "";
$eventP = new  EventC();

if (
    isset($_POST["titre_event"], $_POST["description_event"], $_POST["type_event"],
          $_POST["date_debut"], $_POST["date_fin"], $_POST["lieu_event"],
          $_POST["capacite_max"], $_POST["statut"], $_POST["id_org"])
) {
    if (
        !empty($_POST["titre_event"]) && !empty($_POST["description_event"]) &&
        !empty($_POST["type_event"]) && !empty($_POST["date_debut"]) &&
        !empty($_POST["date_fin"]) && !empty($_POST["lieu_event"]) &&
        !empty($_POST["capacite_max"]) && !empty($_POST["statut"]) &&
        !empty($_POST["id_org"])
    ) {
        $event = new Event(
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

        $eventP->addEvent($event);
        header('Location: liste_event.php');
    } else {
        $error = "Champs manquants";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ajouter un événement</title>
    <style>
        .msg { font-size: 0.9em; margin-top: 5px; display: block; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
    <?php /* sidebar stylesheet will be loaded by the included sidebar.php */ ?>
</head>

<body>

    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <!-- CONTENT -->
    <div class="content">
        <div class="topbar">
            <div class="page-title">Ajouter un événement</div>
            <div class="actions">
                <a href="listEvents.php" class="btn btn-secondary">← Retour</a>
            </div>
        </div>

        <div class="card" style="max-width:760px; margin:0 auto;">
            <form id="eventForm" method="POST" novalidate>
                <div class="form-grid">

                    <div>
                        <input type="text" id="titre_event" name="titre_event" placeholder="Titre de l'événement">
                        <span id="msg-titre" class="msg"></span>
                    </div>

                    <div>
                        <input type="text" id="lieu_event" name="lieu_event" placeholder="Lieu">
                        <span id="msg-lieu" class="msg"></span>
                    </div>

                    <div>
                        <select id="type_event" name="type_event">
                            <option value="">-- Type d'événement --</option>
                    <option value="conference">Conférence</option>
                    <option value="formation">Formation</option>
                    <option value="atelier">Atelier</option>
                        </select>
                        <span id="msg-type" class="msg"></span>
                    </div>

                    <div>
                        <select id="statut" name="statut">
                    <option value="">Statut</option>
                    <option value="en attente">En attente</option>
                    <option value="confirme">Confirmé</option>
                    <option value="annule">Annulé</option>
                        </select>
                        <span id="msg-statut" class="msg"></span>
                    </div>

                    <div>
                        <input type="date" id="date_debut" name="date_debut" placeholder="Date de début">
                        <span id="msg-date-debut" class="msg"></span>
                    </div>

                    <div>
                        <input type="date" id="date_fin" name="date_fin" placeholder="Date de fin">
                        <span id="msg-date-fin" class="msg"></span>
                    </div>

                    <div>
                        <input type="number" id="capacite_max" name="capacite_max" placeholder="Capacité maximale" min="1">
                        <span id="msg-capacite" class="msg"></span>
                    </div>

                    <div>
                        <input type="number" id="id_org" name="id_org" placeholder="ID Organisateur" min="1">
                        <span id="msg-idorg" class="msg"></span>
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <textarea id="description_event" name="description_event" placeholder="Description de l'événement" rows="4" style="width:100%; padding:8px; box-sizing:border-box;"></textarea>
                        <span id="msg-description" class="msg"></span>
                    </div>

                </div>

                <div style="text-align:right; margin-top:12px;">
                    <button type="button" class="btn btn-secondary" onclick="validerFormulaire()" style="margin-right:8px;">Vérifier</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>

            <p style="color:red; text-align:center; margin-top:10px;"><?= $error ?></p>
        </div>

    </div>

    <script src="ajout_event.js"></script>

</body>
</html>