<?php
require_once __DIR__ . '/bo_event_bootstrap.php';
require_once __DIR__ . '/../../../controller/eventC.php';
require_once __DIR__ . '/../../../model/event.php';
require_once __DIR__ . '/../_layout/paths.php';

$error = '';
$ec = new EventC();

if (
    isset(
        $_POST['titre_event'], $_POST['description_event'], $_POST['type_event'],
        $_POST['date_debut'], $_POST['date_fin'], $_POST['lieu_event'], $_POST['capacite_max']
    )
) {
    $type_final = $_POST['type_event'] === 'autre' ? trim((string) ($_POST['type_autre'] ?? '')) : (string) $_POST['type_event'];

    if (
        (string) $_POST['titre_event'] !== '' && (string) $_POST['description_event'] !== '' &&
        $type_final !== '' && (string) $_POST['date_debut'] !== '' &&
        (string) $_POST['date_fin'] !== '' && (string) $_POST['lieu_event'] !== '' &&
        (string) $_POST['capacite_max'] !== ''
    ) {
        $event = new Event(
            $_POST['titre_event'],
            $_POST['description_event'],
            $type_final,
            $_POST['date_debut'],
            $_POST['date_fin'],
            $_POST['lieu_event'],
            $_POST['capacite_max']
        );

        $result = $ec->addEvent($event);
        if ($result === true) {
            header('Location: liste_event.php?added=1');
            exit();
        }
        $error = (string) $result;
    } else {
        $error = 'Champs manquants ou invalides.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Ajouter un événement</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="<?= htmlspecialchars(bo_url('commerce.css')) ?>">
    <style>
        .msg { font-size: 0.82em; margin-top: 3px; min-height: 16px; display: block; }
        .msg.success { color: #198754; }
        .msg.error { color: #dc3545; }
        #type_autre_wrap { display: none; margin-top: 6px; }
        .input-date-bo { width: 100%; max-width: 100%; box-sizing: border-box; min-height: 38px; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../_layout/sidebar.php'; ?>

<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Ajouter un événement</div>
        <div class="actions">
            <a href="liste_event.php" class="btn btn-secondary">← Retour</a>
        </div>
    </div>

    <div class="card" style="max-width:760px;margin:0 auto;">
        <form method="POST" id="eventForm" novalidate>
            <div class="form-grid">
                <div>
                    <input type="text" name="titre_event" id="titre_event" placeholder="Titre de l'événement" autocomplete="off">
                    <span class="msg" id="msg-titre"></span>
                </div>
                <div>
                    <select name="lieu_event" id="lieu_event">
                        <option value="">-- Gouvernorat --</option>
                        <?php
                        $govs = ['Ariana','Béja','Ben Arous','Bizerte','Gabès','Gafsa','Jendouba','Kairouan','Kasserine','Kébili','Le Kef','Mahdia','La Manouba','Médenine','Monastir','Nabeul','Sfax','Sidi Bouzid','Siliana','Sousse','Tataouine','Tozeur','Tunis','Zaghouan'];
                        foreach ($govs as $g) {
                            echo '<option value="' . htmlspecialchars($g) . '">' . htmlspecialchars($g) . "</option>\n";
                        }
                        ?>
                    </select>
                    <span class="msg" id="msg-lieu"></span>
                </div>
                <div>
                    <select name="type_event" id="type_event">
                        <option value="">-- Type --</option>
                        <option value="conference">Conférence</option>
                        <option value="atelier">Atelier</option>
                        <option value="seminaire">Séminaire</option>
                        <option value="autre">Autre</option>
                    </select>
                    <div id="type_autre_wrap">
                        <input type="text" name="type_autre" id="type_autre" placeholder="Précisez (min. 3 caractères)">
                        <span class="msg" id="msg-type-autre"></span>
                    </div>
                    <span class="msg" id="msg-type"></span>
                </div>
                <div>
                    <input type="date" name="date_debut" id="date_debut" required
                           min="<?= htmlspecialchars(date('Y-m-d', strtotime('+1 day'))) ?>"
                           title="Date de début"
                           class="input-date-bo" autocomplete="off">
                    <span class="msg" id="msg-date-debut"></span>
                </div>
                <div>
                    <input type="date" name="date_fin" id="date_fin" required
                           min="<?= htmlspecialchars(date('Y-m-d', strtotime('+2 day'))) ?>"
                           title="Date de fin"
                           class="input-date-bo" autocomplete="off">
                    <span class="msg" id="msg-date-fin"></span>
                </div>
                <div>
                    <input type="text" name="capacite_max" id="capacite_max" placeholder="Capacité max">
                    <span class="msg" id="msg-capacite"></span>
                </div>
                <div style="grid-column:1/-1;">
                    <textarea name="description_event" id="description_event" rows="4" style="width:100%;box-sizing:border-box"
                              placeholder="Description"></textarea>
                    <span class="msg" id="msg-description"></span>
                </div>
            </div>
            <div style="text-align:right;margin-top:12px;">
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
        <?php if ($error): ?>
            <p style="color:#dc3545;text-align:center;margin-top:10px;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </div>
</div>
<script>window.PROLINK_EVENT_EDIT = false;</script>
<script src="ajout_event.js"></script>
</body>
</html>
