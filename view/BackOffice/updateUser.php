<?php
require_once __DIR__ . '/../../controller/UserP.php';
require_once __DIR__ . '/../../model/User.php';

$userP = new UserP();
$user = null;
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: listUsers.php');
    exit;
}

$user = $userP->showUser($id);
if (!$user) {
    header('Location: listUsers.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $type = $_POST['type'] ?? '';
    $age = (int) ($_POST['age'] ?? 0);

    try {
        $newUser = new User($nom, $prenom, $email, '', $type, $age);
        $userP->updateUser($newUser, $id);
        header('Location: listUsers.php');
        exit;
    } catch (RuntimeException $e) {
        if ($e->getMessage() === 'duplicate_email') {
            $error = 'Cet email est déjà utilisé.';
        } else {
            $error = 'Impossible de mettre à jour.';
        }
        $user['nom'] = $nom;
        $user['prenom'] = $prenom;
        $user['email'] = $email;
        $user['type'] = $type;
        $user['age'] = $age;
    } catch (Throwable $e) {
        $error = 'Impossible de mettre à jour.';
        $user['nom'] = $nom;
        $user['prenom'] = $prenom;
        $user['email'] = $email;
        $user['type'] = $type;
        $user['age'] = $age;
    }
} else {
    $error = '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier utilisateur</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; }
    </style>
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="content">
    <div class="topbar">
        <div class="page-title">Modifier utilisateur</div>
        <div class="actions">
            <a href="listUsers.php" class="btn btn-secondary">← Retour</a>
        </div>
    </div>

    <div class="card" style="max-width:760px; margin:0 auto;">
        <?php if (!empty($error)): ?>
            <p style="color:red; text-align:center;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST" novalidate data-validate="user-form">
            <div class="form-grid">
                <div>
                    <input type="text" name="nom" required minlength="2" maxlength="100"
                           pattern="[A-Za-zÀ-ÖØ-öø-ÿ' \-]+"
                           value="<?= htmlspecialchars($user['nom']) ?>">
                </div>
                <div>
                    <input type="text" name="prenom" required minlength="2" maxlength="100"
                           pattern="[A-Za-zÀ-ÖØ-öø-ÿ' \-]+"
                           value="<?= htmlspecialchars($user['prenom']) ?>">
                </div>
                <div>
                    <input type="email" name="email" required maxlength="150"
                           value="<?= htmlspecialchars($user['email']) ?>">
                </div>
                <div>
                    <select name="type" required>
                        <option value="admin" <?= $user['type'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="candidat" <?= $user['type'] === 'candidat' ? 'selected' : '' ?>>Candidat</option>
                        <option value="entrepreneur" <?= $user['type'] === 'entrepreneur' ? 'selected' : '' ?>>Entrepreneur</option>
                    </select>
                </div>
                <div>
                    <input type="number" name="age" required min="13" max="120" inputmode="numeric"
                           value="<?= htmlspecialchars((string) $user['age']) ?>">
                </div>
            </div>

            <div style="text-align:right; margin-top:12px;">
                <button type="submit" class="btn btn-primary">Modifier</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/forms-validation.js"></script>
</body>
</html>
