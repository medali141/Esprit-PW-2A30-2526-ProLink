<?php
require_once __DIR__ . '/../../../controller/AuthController.php';
require_once __DIR__ . '/../_layout/paths.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$auth = new AuthController();
$user = $auth->profile();
<<<<<<< HEAD
if (!$user || strtolower($user['type'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../../controller/FormationP.php';
$fp = new FormationP();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$row = $id ? $fp->get($id) : null;
if (!$row) {
    header('Location: liste.php');
    exit;
}
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
        $fp->update($id, $_POST);
        header('Location: liste.php');
        exit;
    }
}

$currentCat = $err !== '' ? (string) ($_POST['categorie'] ?? '') : (string) ($row['categorie'] ?? '');
$currentTitre = $err !== '' ? (string) ($_POST['titre'] ?? '') : (string) ($row['titre'] ?? '');
$currentDeb = $err !== '' ? (string) ($_POST['date_debut'] ?? '') : (string) ($row['date_debut'] ?? '');
$currentFin = $err !== '' ? (string) ($_POST['date_fin'] ?? '') : (string) ($row['date_fin'] ?? '');
$currentDesc = $err !== '' ? (string) ($_POST['description'] ?? '') : (string) ($row['description'] ?? '');
=======
if (!$user || strtolower($user['type'] ?? '') !== 'admin') { header('Location: ../login.php'); exit; }
require_once __DIR__ . '/../../../controller/FormationP.php';
$fp = new FormationP();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$row = $id ? $fp->get($id) : null;
if (!$row) { header('Location: liste.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fp->update($id, $_POST);
    header('Location: liste.php'); exit;
}
>>>>>>> formation
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Modifier formation — BackOffice</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(view_web_base()) ?>assets/style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(bo_url('_layout/sidebar.css')) ?>">
<<<<<<< HEAD
    <link rel="stylesheet" href="formation.css">
</head>
<body>
<?php include __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content formation-page">
    <div class="container">
        <div class="topbar">
            <div>
                <h1 class="page-title">Modifier la formation</h1>
                <p class="page-subtitle">Mettez à jour les informations puis enregistrez. Les inscriptions existantes sont conservées.</p>
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
                               value="<?= htmlspecialchars($currentTitre) ?>">
                    </div>

                    <div class="form-row">
                        <label for="categorie">Catégorie</label>
                        <select id="categorie" name="categorie" class="form-control">
                            <option value="">— Choisir une catégorie —</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"<?= $currentCat === $cat ? ' selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if ($currentCat !== '' && !in_array($currentCat, $categories, true)): ?>
                                <option value="<?= htmlspecialchars($currentCat) ?>" selected>
                                    <?= htmlspecialchars($currentCat) ?> (existant)
                                </option>
                            <?php endif; ?>
                        </select>
                        <span class="help-text">Sert à regrouper et filtrer les formations sur le site.</span>
                    </div>

                    <div class="form-row">
                        <label for="date_debut">Date de début</label>
                        <input id="date_debut" type="date" name="date_debut" class="form-control"
                               value="<?= htmlspecialchars($currentDeb) ?>">
                    </div>

                    <div class="form-row">
                        <label for="date_fin">Date de fin</label>
                        <input id="date_fin" type="date" name="date_fin" class="form-control"
                               value="<?= htmlspecialchars($currentFin) ?>">
                    </div>

                    <div class="form-row full">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="6" class="form-control"><?= htmlspecialchars($currentDesc) ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="liste.php" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
=======
</head>
<body>
<?php include __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content">
    <div class="container">
        <div class="topbar">
            <div class="page-title">Modifier la formation</div>
            <div class="actions"><a href="liste.php" class="btn btn-secondary">← Retour</a></div>
        </div>

        <div class="card">
            <form method="post">
                <div class="form-grid">
                    <div class="form-row">
                        <label for="titre">Titre</label>
                        <input id="titre" name="titre" value="<?= htmlspecialchars($row['titre']) ?>" required class="form-control">
                    </div>
                    <div class="form-row">
                        <label for="date_debut">Date début</label>
                        <input id="date_debut" type="date" name="date_debut" value="<?= htmlspecialchars($row['date_debut'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="form-row">
                        <label for="date_fin">Date fin</label>
                        <input id="date_fin" type="date" name="date_fin" value="<?= htmlspecialchars($row['date_fin'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="form-row" style="grid-column:1/-1">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="6"><?= htmlspecialchars($row['description'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary">Mettre à jour</button>
                    <a href="liste.php" class="btn btn-secondary">Annuler</a>
>>>>>>> formation
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
