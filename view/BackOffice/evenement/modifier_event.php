<?php
require_once __DIR__ . '/bo_event_bootstrap.php';
require_once __DIR__ . '/../../../controller/eventC.php';
require_once __DIR__ . '/../_layout/paths.php';

$ec = new EventC();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id < 1) {
    header('Location: liste_event.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['type_event']) && (string) $_POST['type_event'] === 'autre') {
        $_POST['type_event'] = trim((string) ($_POST['type_autre'] ?? ''));
    }
    $ec->updateEvent($id);
}

$event = $ec->getEvent($id);
if (!$event) {
    header('Location: liste_event.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Modifier un événement</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="<?= htmlspecialchars(bo_url('commerce.css')) ?>">
    <style>
        .msg { font-size: 0.9em; margin-top: 5px; display: block; }
        .error { color: #dc3545; } .success { color: #198754; }
        #type_autre_wrap { display: none; margin-top: 6px; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Modifier un événement</div>
        <div class="actions">
            <a href="liste_event.php" class="btn btn-secondary">← Retour</a>
        </div>
    </div>

    <div class="card" style="max-width:760px;margin:0 auto;">
        <form method="POST" id="eventForm" novalidate>
            <?php
            foreach (['esort' => 'bo_esort', 'edir' => 'bo_edir', 'psort' => 'bo_psort', 'pdir' => 'bo_pdir'] as $gkey => $field) {
                if (isset($_GET[$gkey]) && (string) $_GET[$gkey] !== '') {
                    echo '<input type="hidden" name="' . htmlspecialchars($field) . '" value="' . htmlspecialchars((string) $_GET[$gkey]) . '">' . "\n            ";
                }
            }
            ?>
            <div class="form-grid">
                <div>
                    <input type="text" name="titre_event" id="titre_event" placeholder="Titre"
                           value="<?= htmlspecialchars((string) $event['titre_event']) ?>">
                    <div class="msg" id="msg-titre"></div>
                </div>
                <div>
                    <textarea name="description_event" id="description_event" rows="3" placeholder="Description"
                    ><?= htmlspecialchars((string) $event['description_event']) ?></textarea>
                    <div class="msg" id="msg-description"></div>
                </div>
                <?php
                $typesConnus = ['conference', 'formation', 'atelier', 'seminaire'];
                $typeActuel = (string) $event['type_event'];
                $isAutre = !in_array($typeActuel, $typesConnus, true);
                ?>
                <div>
                    <select name="type_event" id="type_event">
                        <option value="">-- Type --</option>
                        <option value="conference"<?= $typeActuel === 'conference' ? ' selected' : '' ?>>Conférence</option>
                        <option value="formation"<?= $typeActuel === 'formation' ? ' selected' : '' ?>>Formation</option>
                        <option value="atelier"<?= $typeActuel === 'atelier' ? ' selected' : '' ?>>Atelier</option>
                        <option value="seminaire"<?= $typeActuel === 'seminaire' ? ' selected' : '' ?>>Séminaire</option>
                        <option value="autre"<?= $isAutre ? ' selected' : '' ?>>Autre</option>
                    </select>
                    <div id="type_autre_wrap"<?= $isAutre ? ' style="display:block;"' : '' ?>>
                        <input type="text" name="type_autre" id="type_autre" placeholder="Précisez (min. 3 caractères)"
                               value="<?= $isAutre ? htmlspecialchars($typeActuel) : '' ?>">
                        <div class="msg" id="msg-type-autre"></div>
                    </div>
                    <div class="msg" id="msg-type"></div>
                </div>
                <div>
                    <select name="lieu_event" id="lieu_event">
                        <option value="">-- Gouvernorat --</option>
                        <?php
                        $gouvernorats = [
                            'Ariana','Béja','Ben Arous','Bizerte','Gabès','Gafsa',
                            'Jendouba','Kairouan','Kasserine','Kébili','Le Kef','Mahdia',
                            'La Manouba','Médenine','Monastir','Nabeul','Sfax','Sidi Bouzid',
                            'Siliana','Sousse','Tataouine','Tozeur','Tunis','Zaghouan'
                        ];
                        $lieu = (string) $event['lieu_event'];
                        foreach ($gouvernorats as $g): ?>
                            <option value="<?= htmlspecialchars($g) ?>"<?= $lieu === $g ? ' selected' : '' ?>><?= htmlspecialchars($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="msg" id="msg-lieu"></div>
                </div>
                <div>
                    <input type="text" name="date_debut" id="date_debut" placeholder="AAAA-MM-JJ"
                           value="<?= htmlspecialchars((string) $event['date_debut']) ?>">
                    <div class="msg" id="msg-date-debut"></div>
                </div>
                <div>
                    <input type="text" name="date_fin" id="date_fin" placeholder="AAAA-MM-JJ"
                           value="<?= htmlspecialchars((string) $event['date_fin']) ?>">
                    <div class="msg" id="msg-date-fin"></div>
                </div>
                <div>
                    <input type="text" name="capacite_max" id="capacite_max" placeholder="Capacité"
                           value="<?= htmlspecialchars((string) $event['capacite_max']) ?>">
                    <div class="msg" id="msg-capacite"></div>
                </div>
                <div>
                    <input type="text" value="Statut : <?= htmlspecialchars((string)($event['statut'] ?? 'Ouvert')) ?>" disabled>
                    <small style="display:block;margin-top:6px;color:#64748b;">Mis à jour selon les inscriptions (capacité).</small>
                </div>
            </div>
            <div style="text-align:right;margin-top:12px;">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
<script>window.PROLINK_EVENT_EDIT = true;</script>
<script src="ajout_event.js"></script>
</body>
</html>
