<?php
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../controller/AuthController.php';
require_once __DIR__ . '/../../../model/User.php';
require_once __DIR__ . '/../../../controller/UserP.php';

$auth = new AuthController();
$user = $auth->profile();
if (!$user) {
    header('Location: ../../login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $age = intval($_POST['age'] ?? 0);

    if (!$nom || !$prenom || !$email || !$type || !$age) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide.';
    } else {
        if ($type === 'admin') {
            $type = $user['type'];
        }

        $updatedUserObj = new User($nom, $prenom, $email, $user['mdp'] ?? '', $type, $age);
        $userP = new UserP();
        $userP->updateUser($updatedUserObj, $user['iduser']);

        $fresh = $userP->showUser($user['iduser']);
        session_start();
        $_SESSION['user'] = $fresh;

        header('Location: profile.php?updated=1');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le profil - ProLink</title>
    <style>
        body { font-family: Arial; background:#f3f2ef; margin:0 }
        body { font-family: Arial; background:#f3f2ef; margin:0; display:flex; flex-direction:column; min-height:100vh }
        .main{ flex:1 }
        .container{ max-width:700px; margin:40px auto; background:white; padding:20px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
        input, select { width:100%; padding:10px; margin:8px 0 }
        button{ background:#0073b1; color:white; border:none; padding:10px 14px; border-radius:6px }

        html.dark-mode body.fo-profile-page {
            background: #0c1017 !important;
        }
        html.dark-mode body.fo-profile-page .container {
            background: #151b26 !important;
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.35) !important;
            color: #e2e8f0 !important;
        }
        html.dark-mode body.fo-profile-page .container h2 {
            color: #f1f5f9 !important;
        }
        html.dark-mode body.fo-profile-page .container a:not(.btn) {
            color: #38bdf8 !important;
        }
        html.dark-mode body.fo-profile-page .profile-form-error {
            color: #fca5a5 !important;
        }
    </style>
</head>
<body class="fo-profile-page">

<?php include __DIR__ . '/../components/navbar.php'; ?>

<main class="main">
<div class="container">
    <h2>Modifier mon profil</h2>

    <?php if ($error): ?>
        <div class="profile-form-error" style="color:#b00020"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate data-validate="user-form">
        <input type="text" name="nom" placeholder="Nom" value="<?= htmlspecialchars($_POST['nom'] ?? $user['nom']) ?>" required>
        <input type="text" name="prenom" placeholder="Prénom" value="<?= htmlspecialchars($_POST['prenom'] ?? $user['prenom']) ?>" required>
        <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>" required>

        <select name="type" required>
            <option value="">Type utilisateur</option>
            <option value="candidat" <?= ((($_POST['type'] ?? $user['type']) === 'candidat') ? 'selected' : '') ?>>Candidat</option>
            <option value="entrepreneur" <?= ((($_POST['type'] ?? $user['type']) === 'entrepreneur') ? 'selected' : '') ?>>Entrepreneur</option>
        </select>

        <input type="number" name="age" placeholder="Age" value="<?= htmlspecialchars($_POST['age'] ?? $user['age']) ?>" required>

        <button type="submit">Enregistrer</button>
    </form>

    <p><a href="profile.php">Retour au profil</a></p>
</div>

<?php include __DIR__ . '/../components/footer.php'; ?>

<script src="../../assets/forms-validation.js"></script>

</body>
</html>
