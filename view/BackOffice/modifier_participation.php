<?php
require_once '../../controller/participationC.php';
$pc = new ParticipationC();
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: liste_participation.php'); exit; }

$participation = $pc->updateParticipation($id);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Modifier une participation</title>
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
        <div class="page-title">Modifier une Participation</div>
        <div class="actions">
            <a href="liste_event.php" class="btn btn-secondary">← Retour</a>
        </div>
    </div>
    <div class="card" style="max-width:700px; margin:0 auto;">
        <form method="POST" id="participationForm" novalidate>
            <div class="form-grid">
                <div>
                    <label>Événement</label>
                    <input type="text" value="<?= htmlspecialchars($participation['titre_event']) ?>" disabled>
                    <!-- id_event non modifiable, on le renvoie en hidden -->
                    <input type="hidden" name="id_event" value="<?= $participation['id_event'] ?>">
                </div>
                <div>
                    <input type="text" name="nom" id="nom" placeholder="Nom"
                           value="<?= htmlspecialchars($participation['nom']) ?>">
                    <span class="msg" id="msg-nom"></span>
                </div>
                <div>
                    <input type="text" name="prenom" id="prenom" placeholder="Prénom"
                           value="<?= htmlspecialchars($participation['prenom']) ?>">
                    <span class="msg" id="msg-prenom"></span>
                </div>
                <div>
                    <input type="text" name="email" id="email" placeholder="Email"
                           value="<?= htmlspecialchars($participation['email']) ?>">
                    <span class="msg" id="msg-email"></span>
                </div>
                <div>
                    <input type="text" name="telephone" id="telephone" placeholder="Téléphone"
                           value="<?= htmlspecialchars($participation['telephone']) ?>">
                    <span class="msg" id="msg-telephone"></span>
                </div>
                <div>
                    <select name="statut" id="statut">
                        <option value="">-- Choisir un statut --</option>
                        <?php foreach (['en attente','confirmé','annulé'] as $s): ?>
                            <option value="<?= $s ?>" <?= $participation['statut'] === $s ? 'selected' : '' ?>>
                                <?= ucfirst($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="msg" id="msg-statut"></span>
                </div>
            </div>
            <div style="text-align:right; margin-top:12px;">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
<script src="participation.js"></script>
</body>
</html>