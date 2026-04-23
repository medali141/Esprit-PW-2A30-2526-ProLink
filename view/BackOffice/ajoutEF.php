<!DOCTYPE html>
<html>
<head>
    <title>Ajouter Événement</title>

    <style>

        body{
            margin:0;
            font-family: Arial, sans-serif;
        }

        input, select, textarea{
            width:100%;
            padding:8px;
            margin:8px 0;
            box-sizing:border-box;
        }

        button{
            background:green;
            color:white;
            padding:10px;
            border:none;
            border-radius:4px;
        }

        .field{
            margin-bottom:10px;
        }

    </style>

</head>

<body>

<!-- SIDEBAR -->
<?php include 'sidebar.php'; ?>

<!-- CONTENT -->
<div class="content">

    <div class="topbar">
        <div class="page-title">Ajouter Événement</div>

        <div class="actions">
            <a href="liste_event.php">← Retour</a>
        </div>
    </div>

    <div class="card" style="max-width:600px; margin:0 auto;">

        <form id="eventForm" action="ajoutE.php" method="POST">

            <div class="field">
                <input type="text" name="titre_event" placeholder="Titre de l'événement">
                <span id="msg-titre"></span>
            </div>

            <div class="field">
                <textarea name="description_event" placeholder="Description"></textarea>
                <span id="msg-description"></span>
            </div>

            <div class="field">
                <select name="type_event">
                    <option value="">Type événement</option>
                    <option value="conference">Conférence</option>
                    <option value="formation">Formation</option>
                    <option value="atelier">Atelier</option>
                </select>
                <span id="msg-type"></span>
            </div>

            <div class="field">
                <input type="date" name="date_debut">
                <span id="msg-date-debut"></span>
            </div>

            <div class="field">
                <input type="date" name="date_fin">
                <span id="msg-date-fin"></span>
            </div>

            <div class="field">
                <input type="text" name="lieu_event" placeholder="Lieu">
                <span id="msg-lieu"></span>
            </div>

            <div class="field">
                <input type="text" name="capacite_max" placeholder="Capacité maximale">
                <span id="msg-capacite"></span>
            </div>

            <div class="field">
                <select name="statut">
                    <option value="">Statut</option>
                    <option value="en attente">En attente</option>
                    <option value="confirme">Confirmé</option>
                    <option value="annule">Annulé</option>
                </select>
                <span id="msg-statut"></span>
            </div>

            <div style="text-align:right; margin-top:12px;">
                <button type="button" onclick="validerFormulaire()">Valider</button>
                <button type="submit">Ajouter</button>
            </div>

        </form>

    </div>

</div>

<script src="ajout_event.js"></script>

</body>
</html>