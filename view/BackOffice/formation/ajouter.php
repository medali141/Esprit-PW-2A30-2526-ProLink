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
require_once __DIR__ . '/../../../controller/NotificationP.php';
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
        $newId = $fp->add($_POST);
        if ($newId !== false) {
            $titreNotif = trim((string) ($_POST['titre'] ?? ''));
            (new NotificationP())->broadcastToAllUsers(
                'formation_ajoutee',
                'Nouvelle formation sur ProLink',
                $titreNotif !== '' ? ('La formation « ' . $titreNotif . ' » est ouverte aux inscriptions.') : 'Une nouvelle formation est disponible.',
                'view/FrontOffice/formation_detail.php?id=' . $newId
            );
        }
        header('Location: liste.php' . ($newId !== false ? '?added=1' : '?error=1'));
        exit;
    }
=======
if (!$user || strtolower($user['type'] ?? '') !== 'admin') { header('Location: ../login.php'); exit; }

require_once __DIR__ . '/../../../controller/FormationP.php';
$fp = new FormationP();
$categories = $fp->getAllCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ok = $fp->add($_POST);
    header('Location: liste.php' . ($ok ? '?added=1' : '?error=1'));
    exit;
>>>>>>> formation
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Ajouter formation — BackOffice</title>
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
=======
    <style>
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .form-row {
            display: flex;
            flex-direction: column;
        }
        .form-row label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }
        .form-row input, .form-row select, .form-row textarea {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        .form-row.full-width {
            grid-column: 1 / -1;
        }
        .form-actions {
            margin-top: 20px;
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #0073b1;
            color: white;
        }
        .btn-primary:hover {
            background: #005f8d;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .page-title {
            font-size: 24px;
            font-weight: bold;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        .certif-section {
            background: #fef9e6;
            border: 1px solid #f5af19;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }
        .certif-section h4 {
            color: #f5af19;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content">
    <div class="container">
        <div class="topbar">
            <div class="page-title">➕ Ajouter une formation</div>
            <div class="actions"><a href="liste.php" class="btn btn-secondary">← Retour</a></div>
        </div>

        <div class="card">
            <form method="post">
                <div class="form-grid">
                    <div class="form-row">
                        <label for="titre">Titre *</label>
                        <input id="titre" name="titre" required placeholder="Ex: Python pour débutants">
                    </div>
                    
                    <div class="form-row">
                        <label for="type">Type *</label>
                        <select id="type" name="type" required>
                            <option value="en_ligne">💻 En ligne</option>
                            <option value="presentiel">🏢 Présentiel</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <label for="id_categorie">Catégorie</label>
                        <select id="id_categorie" name="id_categorie">
                            <option value="">-- Sélectionner une catégorie --</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?= $cat['id_categorie'] ?>"><?= htmlspecialchars($cat['nom_categorie']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <label for="places_max">Places disponibles</label>
                        <input id="places_max" type="number" name="places_max" value="30" min="1" max="500">
                    </div>
                    
                    <div class="form-row">
                        <label for="date_debut">Date début *</label>
                        <input id="date_debut" type="date" name="date_debut" required>
                    </div>
                    
                    <div class="form-row">
                        <label for="date_fin">Date fin *</label>
                        <input id="date_fin" type="date" name="date_fin" required>
                    </div>
                    
                    <div class="form-row">
                        <label for="statut">Statut</label>
                        <select id="statut" name="statut">
                            <option value="inscrit">Inscription ouverte</option>
                            <option value="termine">Terminé</option>
                            <option value="annule">Annulé</option>
                        </select>
                    </div>
                    
                    <div class="form-row full-width">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="5" placeholder="Description détaillée de la formation..."></textarea>
                    </div>
                </div>
                
                <!-- Section Certification -->
                <div class="certif-section">
                    <h4>🎓 Certification associée (optionnel)</h4>
                    <div class="form-grid">
                        <div class="form-row full-width">
                            <label for="certification">Nom de la certification</label>
                            <input id="certification" type="text" name="certification" placeholder="Ex: Certification Python Officielle">
                        </div>
                        <div class="form-row full-width">
                            <label for="niveau">Niveau</label>
                            <select id="niveau" name="niveau">
                                <option value="debutant">Débutant</option>
                                <option value="intermediaire">Intermédiaire</option>
                                <option value="avance">Avancé</option>
                                <option value="expert">Expert</option>
                            </select>
                        </div>
                        <div class="form-row full-width">
                            <label for="duree_heures">Durée de la certification (heures)</label>
                            <input id="duree_heures" type="number" name="duree_heures" value="20" min="1" max="200">
                        </div>
                        <div class="form-row full-width">
                            <label for="certification_description">Description de la certification</label>
                            <textarea id="certification_description" name="certification_description" rows="3" placeholder="Description de la certification..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">✅ Enregistrer</button>
                    <a href="liste.php" class="btn btn-secondary">❌ Annuler</a>
>>>>>>> formation
                </div>
            </form>
        </div>
    </div>
</div>
</body>
<<<<<<< HEAD
</html>
=======
</html>
>>>>>>> formation
