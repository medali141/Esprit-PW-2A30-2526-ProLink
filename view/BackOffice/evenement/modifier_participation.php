<?php
require_once __DIR__ . '/bo_event_bootstrap.php';
require_once __DIR__ . '/../../../controller/participationC.php';
require_once __DIR__ . '/../_layout/paths.php';

$pc = new ParticipationC();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$participation = $pc->updateParticipation($id);
if (!is_array($participation)) {
    header('Location: liste_event.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Modifier une participation</title>
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
        <div class="page-title">Modifier une participation</div>
        <div class="actions">
            <a href="liste_event.php" class="btn btn-secondary">← Retour</a>
        </div>
    </div>
    <div class="card" style="max-width:700px;margin:0 auto;">
        <form method="POST" id="participationForm" novalidate>
            <?php
            foreach (['esort' => 'bo_esort', 'edir' => 'bo_edir', 'psort' => 'bo_psort', 'pdir' => 'bo_pdir'] as $gkey => $field) {
                if (isset($_GET[$gkey]) && (string) $_GET[$gkey] !== '') {
                    echo '<input type="hidden" name="' . htmlspecialchars($field) . '" value="' . htmlspecialchars((string) $_GET[$gkey]) . '">' . "\n            ";
                }
            }
            ?>
            <div class="form-grid">
                <div>
                    <label>Événement</label>
                    <input type="text" value="<?= htmlspecialchars((string) $participation['titre_event']) ?>" disabled>
                    <input type="hidden" name="id_event" value="<?= (int) $participation['id_event'] ?>">
                </div>
                <div>
                    <input type="text" name="nom" id="nom" placeholder="Nom" value="<?= htmlspecialchars((string) $participation['nom']) ?>">
                    <span class="msg" id="msg-nom"></span>
                </div>
                <div>
                    <input type="text" name="prenom" id="prenom" placeholder="Prénom" value="<?= htmlspecialchars((string) $participation['prenom']) ?>">
                    <span class="msg" id="msg-prenom"></span>
                </div>
                <div>
                    <input type="text" name="email" id="email" placeholder="Email" value="<?= htmlspecialchars((string) $participation['email']) ?>">
                    <span class="msg" id="msg-email"></span>
                </div>
                <div>
                    <input type="text" name="telephone" id="telephone" placeholder="Téléphone" value="<?= htmlspecialchars((string) $participation['telephone']) ?>">
                    <span class="msg" id="msg-telephone"></span>
                </div>
                <div>
                    <select name="statut" id="statut">
                        <option value="">-- Statut --</option>
                        <?php foreach (['en attente','confirmé','annulé'] as $s): ?>
                            <option value="<?= htmlspecialchars($s) ?>"<?= ($participation['statut'] === $s) ? ' selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($s)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="msg" id="msg-statut"></span>
                </div>
            </div>
            <div style="text-align:right;margin-top:12px;">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
<script src="participation.js"></script>
</body>
</html>
