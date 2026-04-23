<?php
require_once '../../controller/participationC.php';
require_once '../../model/participation.php';
$pc    = new ParticipationC();
$error = "";
$success = "";
$id_event = $_GET['id_event'] ?? null;

// Charger l'event pour affichage
$event = null;
if ($id_event) {
    $db = config::getConnexion();
    $req = $db->prepare("SELECT * FROM event WHERE id_event = :id");
    $req->execute(['id' => $id_event]);
    $event = $req->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p = new Participation(
        $_POST['id_event'],
        $_POST['nom'],
        $_POST['prenom'],
        $_POST['email'],
        $_POST['telephone']
    );
    $result = $pc->addParticipation($p);
    if ($result === true) {
        $success = "Inscription réussie ! Vous recevrez une confirmation.";
    } else {
        $error = $result;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Inscription à l'événement</title>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 0 20px; }
        input, select { width:100%; padding:10px; margin-bottom:4px; box-sizing:border-box;
                        border:1px solid #ccc; border-radius:6px; font-size:1em; }
        button { background:#0d6efd; color:#fff; padding:10px 24px;
                 border:none; border-radius:6px; cursor:pointer; font-size:1em; }
        .msg { font-size:0.85em; margin-bottom:10px; display:block; }
        .success { color: green; } .error { color: red; }
        .event-info { background:#f0f4ff; border-radius:8px; padding:16px; margin-bottom:24px; }
    </style>
</head>
<body>
    <h2>S'inscrire à un événement</h2>

    <?php if ($event): ?>
    <div class="event-info">
        <strong><?= htmlspecialchars($event['titre_event']) ?></strong><br>
        📅 <?= $event['date_debut'] ?> → <?= $event['date_fin'] ?><br>
        📍 <?= htmlspecialchars($event['lieu_event']) ?>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="msg success"><?= $success ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="msg error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" id="participationForm" novalidate>
        <input type="hidden" name="id_event" value="<?= htmlspecialchars($id_event) ?>">

        <input type="text" name="nom" id="nom" placeholder="Nom">
        <span class="msg" id="msg-nom"></span>

        <input type="text" name="prenom" id="prenom" placeholder="Prénom">
        <span class="msg" id="msg-prenom"></span>

        <input type="text" name="email" id="email" placeholder="Email">
        <span class="msg" id="msg-email"></span>

        <input type="text" name="telephone" id="telephone" placeholder="Téléphone (8 chiffres)">
        <span class="msg" id="msg-telephone"></span>

        <br>
        <button type="submit">S'inscrire</button>
    </form>

    <script src="participation.js"></script>
</body>
</html>