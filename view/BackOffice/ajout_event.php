<?php
include '../../controller/eventC.php';
include '../../model/event.php';
include '../../config.php';

$error = "";
$ec = new EventC();

if (
    isset($_POST['titre_event'], $_POST['description_event'], $_POST['type_event'],
          $_POST['date_debut'], $_POST['date_fin'], $_POST['lieu_event'],
          $_POST['capacite_max'], $_POST['statut'])
) {
    // Si type = "autre", on prend la valeur du champ texte personnalisé
    $type_final = $_POST['type_event'] === 'autre' ? trim($_POST['type_autre'] ?? '') : $_POST['type_event'];

    if (
        !empty($_POST['titre_event']) && !empty($_POST['description_event']) &&
        !empty($type_final) && !empty($_POST['date_debut']) &&
        !empty($_POST['date_fin']) && !empty($_POST['lieu_event']) &&
        !empty($_POST['capacite_max']) && !empty($_POST['statut'])
    ) {
        $event = new Event(
            $_POST['titre_event'],
            $_POST['description_event'],
            $type_final,
            $_POST['date_debut'],
            $_POST['date_fin'],
            $_POST['lieu_event'],
            $_POST['capacite_max'],
            $_POST['statut']
        );

        $ec->addEvent($event);
        header('Location: liste_event.php');
        exit();
    } else {
        $error = "Champs manquants ou invalides.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Ajouter un événement</title>
    <meta charset="utf-8">
    <?php /* sidebar stylesheet will be loaded by the included sidebar.php */ ?>
    <style>
        .msg {
            font-size: 0.82em;
            margin-top: 3px;
            min-height: 16px;
            display: block;
        }
        .msg.success { color: #198754; }
        .msg.error   { color: #dc3545; }
        #type_autre_wrap {
            display: none;
            margin-top: 6px;
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <!-- CONTENT -->
    <div class="content">
        <div class="topbar">
            <div class="page-title">Ajouter un événement</div>
            <div class="actions">
                <a href="liste_event.php" class="btn btn-secondary">← Retour</a>
            </div>
        </div>

        <div class="card" style="max-width:760px; margin:0 auto;">
            <form method="POST" id="eventForm" novalidate>
                <div class="form-grid">

                    <div>
                        <input type="text" name="titre_event" id="titre_event" placeholder="Titre de l'événement">
                        <span class="msg" id="msg-titre"></span>
                    </div>

                    <!-- LIEU : select 24 gouvernorats -->
                    <div>
                        <select name="lieu_event" id="lieu_event">
                            <option value="">-- Choisir un gouvernorat --</option>
                            <option value="Ariana">Ariana</option>
                            <option value="Béja">Béja</option>
                            <option value="Ben Arous">Ben Arous</option>
                            <option value="Bizerte">Bizerte</option>
                            <option value="Gabès">Gabès</option>
                            <option value="Gafsa">Gafsa</option>
                            <option value="Jendouba">Jendouba</option>
                            <option value="Kairouan">Kairouan</option>
                            <option value="Kasserine">Kasserine</option>
                            <option value="Kébili">Kébili</option>
                            <option value="Le Kef">Le Kef</option>
                            <option value="Mahdia">Mahdia</option>
                            <option value="La Manouba">La Manouba</option>
                            <option value="Médenine">Médenine</option>
                            <option value="Monastir">Monastir</option>
                            <option value="Nabeul">Nabeul</option>
                            <option value="Sfax">Sfax</option>
                            <option value="Sidi Bouzid">Sidi Bouzid</option>
                            <option value="Siliana">Siliana</option>
                            <option value="Sousse">Sousse</option>
                            <option value="Tataouine">Tataouine</option>
                            <option value="Tozeur">Tozeur</option>
                            <option value="Tunis">Tunis</option>
                            <option value="Zaghouan">Zaghouan</option>
                        </select>
                        <span class="msg" id="msg-lieu"></span>
                    </div>

                    <!-- TYPE : avec option "Autre" + champ texte conditionnel -->
                    <div>
                        <select name="type_event" id="type_event">
                            <option value="">-- Choisir un type --</option>
                            <option value="conference">Conférence</option>
                            <option value="atelier">Atelier</option>
                            <option value="seminaire">Séminaire</option>
                            <option value="autre">Autre</option>
                        </select>
                        <div id="type_autre_wrap">
                            <input type="text" name="type_autre" id="type_autre" placeholder="Précisez le type (min. 3 caractères)">
                            <span class="msg" id="msg-type-autre"></span>
                        </div>
                        <span class="msg" id="msg-type"></span>
                    </div>

                    <div>
                        <select name="statut" id="statut">
                            <option value="">-- Choisir un statut --</option>
                            <option value="planifie">Planifié</option>
                            <option value="en_cours">En cours</option>
                            <option value="termine">Terminé</option>
                            <option value="annule">Annulé</option>
                        </select>
                        <span class="msg" id="msg-statut"></span>
                    </div>

                    <div>
                        <input type="text" name="date_debut" id="date_debut" placeholder="Date de début (AAAA-MM-JJ)">
                        <span class="msg" id="msg-date-debut"></span>
                    </div>

                    <div>
                        <input type="text" name="date_fin" id="date_fin" placeholder="Date de fin (AAAA-MM-JJ)">
                        <span class="msg" id="msg-date-fin"></span>
                    </div>

                    <div>
                        <input type="text" name="capacite_max" id="capacite_max" placeholder="Capacité maximale">
                        <span class="msg" id="msg-capacite"></span>
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <textarea name="description_event" id="description_event"
                                  placeholder="Description" rows="4"
                                  style="width:100%; padding:8px; box-sizing:border-box;"></textarea>
                        <span class="msg" id="msg-description"></span>
                    </div>

                </div>

                <div style="text-align:right; margin-top:12px;">
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>

            <p style="color:red; text-align:center; margin-top:10px;"><?= $error ?></p>
        </div>
    </div>

    <!-- JS externe : toute la validation est dans ce fichier -->
    <script src="ajout_event.js"></script>

</body>
</html>