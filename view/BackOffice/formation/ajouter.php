<?php
require_once __DIR__ . '/../../../controller/AuthController.php';
require_once __DIR__ . '/../_layout/paths.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$auth = new AuthController();
$user = $auth->profile();
if (!$user || strtolower($user['type'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../../controller/FormationP.php';
$fp = new FormationP();
$categories = $fp->categories();
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim((string) ($_POST['titre'] ?? ''));
    $dateDebut = (string) ($_POST['date_debut'] ?? '');
    $dateFin = (string) ($_POST['date_fin'] ?? '');
    if ($titre === '') {
        $err = 'Le titre est requis.';
    } elseif ($dateDebut !== '' && $dateFin !== '' && $dateFin < $dateDebut) {
        $err = 'La date de fin doit être après la date de début.';
    } else {
        $ok = $fp->add($_POST);
        header('Location: liste.php' . ($ok ? '?added=1' : '?error=1'));
        exit;
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Ajouter formation — BackOffice</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(view_web_base()) ?>assets/style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(bo_url('_layout/sidebar.css')) ?>">
    <link rel="stylesheet" href="formation.css">
</head>
<body>
<?php include __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content formation-page">
    <div class="container">
        <div class="topbar">
            <div>
                <h1 class="page-title">Ajouter une formation</h1>
                <p class="page-subtitle">Renseignez les informations puis enregistrez. Vous pourrez ensuite gérer les inscriptions et générer les certificats.</p>
            </div>
            <div class="actions">
                <a href="liste.php" class="btn btn-secondary">← Retour à la liste</a>
            </div>
        </div>

        <div class="formation-card">
            <?php if ($err !== ''): ?>
                <p style="color:#b91c1c;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:10px 14px;margin:0 0 16px;font-weight:600">
                    <?= htmlspecialchars($err) ?>
                </p>
            <?php endif; ?>

            <form method="post" autocomplete="off">
                <div class="form-grid">
                    <div class="form-row full">
                        <label for="titre">Titre de la formation *</label>
                        <input id="titre" name="titre" required maxlength="255" class="form-control"
                               placeholder="Ex. Introduction à PHP / Laravel"
                               value="<?= isset($_POST['titre']) ? htmlspecialchars((string) $_POST['titre']) : '' ?>">
                    </div>

                    <div class="form-row">
                        <label for="categorie">Catégorie</label>
                        <select id="categorie" name="categorie" class="form-control">
                            <option value="">— Choisir une catégorie —</option>
                            <?php $selectedCat = (string) ($_POST['categorie'] ?? ''); ?>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"<?= $selectedCat === $cat ? ' selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="help-text">Sert à regrouper et filtrer les formations sur le site.</span>
                    </div>

                    <div class="form-row">
                        <label for="date_debut">Date de début</label>
                        <input id="date_debut" type="date" name="date_debut" class="form-control"
                               value="<?= htmlspecialchars((string) ($_POST['date_debut'] ?? '')) ?>">
                    </div>

                    <div class="form-row">
                        <label for="date_fin">Date de fin</label>
                        <input id="date_fin" type="date" name="date_fin" class="form-control"
                               value="<?= htmlspecialchars((string) ($_POST['date_fin'] ?? '')) ?>">
                    </div>

                    <div class="form-row full">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="6" class="form-control"
                                  placeholder="Décrivez les objectifs, le programme, le public cible..."><?= htmlspecialchars((string) ($_POST['description'] ?? '')) ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="liste.php" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
