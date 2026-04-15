<?php
require_once __DIR__ . '/../../controller/AuthController.php';
// Load model and helper controller classes used below
require_once __DIR__ . '/../../model/User.php';
require_once __DIR__ . '/../../controller/UserP.php';


$auth = new AuthController();
$user = $auth->profile();
if (!$user) {
    header('Location: ../login.php');
    exit;
}

$error = '';
$success = '';

// Handle POST update
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
        // Prevent elevating to admin
        if ($type === 'admin') {
            $type = $user['type'];
        }

        $updatedUserObj = new User($nom, $prenom, $email, $user['mdp'] ?? '', $type, $age);
        $userP = new UserP();
        $userP->updateUser($updatedUserObj, $user['iduser']);

        // Refresh session user data
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
    </style>
</head>
<body>

<?php include __DIR__ . '/components/navbar.php'; ?>

<main class="main">
<div class="container">
    <h2>Modifier mon profil</h2>

    <?php if ($error): ?>
        <div style="color:#b00020"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" onsubmit="return validateUpdate()">
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

<?php include __DIR__ . '/components/footer.php'; ?>

<script>
    function validateUpdate(){
        const nom = document.querySelector('input[name="nom"]').value.trim();
        const prenom = document.querySelector('input[name="prenom"]').value.trim();
        const email = document.querySelector('input[name="email"]').value.trim();
        const type = document.querySelector('select[name="type"]').value;
        const age = document.querySelector('input[name="age"]').value;
        if(!nom || !prenom || !email || !type || !age){
            alert('Veuillez remplir tous les champs.');
            return false;
        }
        if(!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)){
            alert('Email invalide.');
            return false;
        }
        return true;
    }
</script>

</body>
</html>
