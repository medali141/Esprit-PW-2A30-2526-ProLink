<?php
require_once __DIR__ . '/../../../controller/AuthController.php';
require_once __DIR__ . '/../../../controller/UserP.php';
require_once __DIR__ . '/../../../model/User.php';

$auth = new AuthController();
$user = $auth->profile();
if (!$user) {
    header('Location: ../../login.php');
    exit;
}
if (strtolower($user['type'] ?? '') !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$userP = new UserP();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $age = intval($_POST['age'] ?? 0);

    if (!$nom || !$prenom || !$email || !$age) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide.';
    } else {
        $updatedUser = new User($nom, $prenom, $email, $user['mdp'] ?? '', 'admin', $age);
        $userP->updateUser($updatedUser, $user['iduser']);

        $fresh = $userP->showUser($user['iduser']);
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION['user'] = $fresh;

        header('Location: profile_admin.php?updated=1');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier mon profil (admin)</title>
    <style>
        .content{ margin-left:var(--sidebar-width,288px); padding:20px }
        .card{ background:white; padding:20px; border-radius:8px; max-width:900px }
        input{ width:100%; padding:8px; margin:6px 0 }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/../_layout/sidebar.php'; ?>

<div class="content">
    <div class="topbar">
        <div class="page-title">Modifier mon profil (admin)</div>
    </div>

    <div class="card">
        <?php if ($error): ?>
            <div style="color:#b00020"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate data-validate="user-form">
            <label>Nom</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? $user['nom']) ?>">

            <label>Prénom</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($_POST['prenom'] ?? $user['prenom']) ?>">

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>">

            <label>Age</label>
            <input type="number" name="age" value="<?= htmlspecialchars($_POST['age'] ?? $user['age']) ?>">

            <div style="margin-top:12px">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="profile_admin.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
