<?php
require_once __DIR__ . "/../../controller/eventC.php";

$ec = new EventC();
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: liste_event.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si type = "autre", on prend la valeur du champ texte personnalisé
    if ($_POST['type_event'] === 'autre') {
        $_POST['type_event'] = trim($_POST['type_autre'] ?? '');
    }
    $ec->updateEvent($id);
}

$event = $ec->getEvent($id);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Modifier un Événement</title>
    <style>
        .msg { font-size: 0.9em; margin-top: 5px; display: block; }
        .error { color: red; }
        .success { color: green; }
        #type_autre_wrap {
            display: none;
            margin-top: 6px;
        }
    </style>
    <meta charset="utf-8">
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">
    <div class="topbar">
        <div class="page-title">Modifier un Événement</div>
        <div class="actions">
            <a href="liste_event.php" class="btn btn-secondary">← Retour</a>
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

                <!-- TYPE : avec option "Autre" + champ texte conditionnel -->
                <?php
                    $typesConnus = ['conference', 'formation', 'atelier', 'seminaire'];
                    $typeActuel  = $event['type_event'];
                    $isAutre     = !in_array($typeActuel, $typesConnus);
                ?>
                <div>
                    <select name="type_event" id="type_event">
                        <option value="">-- Choisir un type --</option>
                        <option value="conference" <?= $typeActuel === 'conference' ? 'selected' : '' ?>>Conférence</option>
                        <option value="formation"  <?= $typeActuel === 'formation'  ? 'selected' : '' ?>>Formation</option>
                        <option value="atelier"    <?= $typeActuel === 'atelier'    ? 'selected' : '' ?>>Atelier</option>
                        <option value="seminaire"  <?= $typeActuel === 'seminaire'  ? 'selected' : '' ?>>Séminaire</option>
                        <option value="autre"      <?= $isAutre ? 'selected' : '' ?>>Autre</option>
                    </select>
                    <div id="type_autre_wrap" <?= $isAutre ? 'style="display:block;"' : '' ?>>
                        <input type="text" name="type_autre" id="type_autre"
                               placeholder="Précisez le type (min. 3 caractères)"
                               value="<?= $isAutre ? htmlspecialchars($typeActuel) : '' ?>">
                        <div class="msg" id="msg-type-autre"></div>
                    </div>
                    <div class="msg" id="msg-type"></div>
                </div>

                <!-- LIEU : select 24 gouvernorats -->
                <div>
                    <select name="lieu_event" id="lieu_event">
                        <option value="">-- Choisir un gouvernorat --</option>
                        <?php
                        $gouvernorats = [
                            'Ariana','Béja','Ben Arous','Bizerte','Gabès','Gafsa',
                            'Jendouba','Kairouan','Kasserine','Kébili','Le Kef','Mahdia',
                            'La Manouba','Médenine','Monastir','Nabeul','Sfax','Sidi Bouzid',
                            'Siliana','Sousse','Tataouine','Tozeur','Tunis','Zaghouan'
                        ];
                        foreach ($gouvernorats as $g): ?>
                            <option value="<?= $g ?>" <?= $event['lieu_event'] === $g ? 'selected' : '' ?>>
                                <?= $g ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="msg" id="msg-lieu"></div>
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

            </div>

            <div style="text-align:right; margin-top:12px;">
                <button type="submit" class="btn btn-primary">Modifier</button>
            </div>
        </form>
    </div>

</div>

<!-- JS externe dédié à la modification -->
<script src="ajout_event.js"></script>
</body>
</html>