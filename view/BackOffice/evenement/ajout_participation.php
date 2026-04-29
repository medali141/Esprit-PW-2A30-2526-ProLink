<?php
require_once __DIR__ . '/bo_event_bootstrap.php';
require_once __DIR__ . '/../../../controller/participationC.php';
require_once __DIR__ . '/../../../model/participation.php';
require_once __DIR__ . '/../_layout/paths.php';

$pc = new ParticipationC();
$events = $pc->listeEvents();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p = new Participation(
        $_POST['id_event'] ?? '',
        $_POST['nom'] ?? '',
        $_POST['prenom'] ?? '',
        $_POST['email'] ?? '',
        $_POST['telephone'] ?? '',
        $_POST['statut'] ?? ''
    );
    $result = $pc->addParticipation($p);
    if ($result === true) {
        header('Location: liste_event.php?part_ok=1');
        exit();
    }
    $error = (string) $result;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Ajouter une participation</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="<?= htmlspecialchars(bo_url('commerce.css')) ?>">
    <style>
        .msg { font-size:0.85em; margin-top:4px; display:block; }
        .success { color: #198754; } .error { color: #dc3545; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Ajouter une participation</div>
        <div class="actions">
            <a href="liste_event.php" class="btn btn-secondary">← Retour</a>
        </div>
    </div>
    <div class="card" style="max-width:700px;margin:0 auto;">
        <?php if ($error): ?>
            <p style="color:#dc3545;text-align:center;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" id="participationForm" novalidate>
            <div class="form-grid">
                <div>
                    <select name="id_event" id="id_event">
                        <option value="">-- Choisir un événement --</option>
                        <?php foreach ($events as $e): ?>
                            <option value="<?= (int) $e['id_event'] ?>"><?= htmlspecialchars((string) $e['titre_event']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="msg" id="msg-event"></span>
                </div>
                <div>
                    <input type="text" name="nom" id="nom" placeholder="Nom" autocomplete="family-name">
                    <span class="msg" id="msg-nom"></span>
                </div>
                <div>
                    <input type="text" name="prenom" id="prenom" placeholder="Prénom" autocomplete="given-name">
                    <span class="msg" id="msg-prenom"></span>
                </div>
                <div>
                    <input type="email" name="email" id="email" placeholder="Email" autocomplete="email">
                    <span class="msg" id="msg-email"></span>
                </div>
                <div>
                    <input type="text" name="telephone" id="telephone" placeholder="Téléphone (8 chiffres)" inputmode="numeric">
                    <span class="msg" id="msg-telephone"></span>
                </div>
                <div>
                    <select name="statut" id="statut">
                        <option value="">-- Statut --</option>
                        <option value="en attente">En attente</option>
                        <option value="confirmé">Confirmé</option>
                        <option value="annulé">Annulé</option>
                    </select>
                    <span class="msg" id="msg-statut"></span>
                </div>
            </div>
            <div style="text-align:right;margin-top:12px;">
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>
<script src="participation.js"></script>
</body>
</html>
