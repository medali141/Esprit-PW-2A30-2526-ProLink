<?php
require_once '../../controller/participationC.php';
require_once '../../model/participation.php';
$pc     = new ParticipationC();
$events = $pc->listeEvents();
$error  = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p = new Participation(
        $_POST['id_event'],
        $_POST['nom'],
        $_POST['prenom'],
        $_POST['email'],
        $_POST['telephone'],
        $_POST['statut']
    );
    $result = $pc->addParticipation($p);
    if ($result === true) {
        header('Location: liste_event.php');
        exit();
    } else {
        $error = $result;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Ajouter une participation</title>
    <meta charset="utf-8">
    <style>
        .msg { font-size:0.85em; margin-top:4px; display:block; }
        .success { color: green; } .error { color: red; }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="content">
    <div class="topbar">
        <div class="page-title">Ajouter une Participation</div>
        <div class="actions">
            <a href="liste_event.php" class="btn btn-secondary">← Retour</a>
        </div>
    </div>
    <div class="card" style="max-width:700px; margin:0 auto;">
        <?php if ($error): ?>
            <p style="color:red;text-align:center;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" id="participationForm" novalidate>
            <div class="form-grid">
                <div>
                    <select name="id_event" id="id_event">
                        <option value="">-- Choisir un événement --</option>
                        <?php foreach ($events as $e): ?>
                            <option value="<?= $e['id_event'] ?>"><?= htmlspecialchars($e['titre_event']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="msg" id="msg-event"></span>
                </div>
                <div>
                    <input type="text" name="nom" id="nom" placeholder="Nom">
                    <span class="msg" id="msg-nom"></span>
                </div>
                <div>
                    <input type="text" name="prenom" id="prenom" placeholder="Prénom">
                    <span class="msg" id="msg-prenom"></span>
                </div>
                <div>
                    <input type="text" name="email" id="email" placeholder="Email">
                    <span class="msg" id="msg-email"></span>
                </div>
                <div>
                    <input type="text" name="telephone" id="telephone" placeholder="Téléphone (8 chiffres)">
                    <span class="msg" id="msg-telephone"></span>
                </div>
                <div>
                    <select name="statut" id="statut">
                        <option value="">-- Choisir un statut --</option>
                        <option value="en attente">En attente</option>
                        <option value="confirmé">Confirmé</option>
                        <option value="annulé">Annulé</option>
                    </select>
                    <span class="msg" id="msg-statut"></span>
                </div>
            </div>
            <div style="text-align:right; margin-top:12px;">
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>
</div>
<script src="participation.js"></script>
</body>
</html>