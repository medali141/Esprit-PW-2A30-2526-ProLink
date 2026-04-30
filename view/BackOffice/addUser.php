<?php
require_once __DIR__ . '/../../controller/UserP.php';
require_once __DIR__ . '/../../model/User.php';

$error = '';
$userP = new UserP();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mdp = $_POST['mdp'] ?? '';
    $type = $_POST['type'] ?? '';
    $age = (int) ($_POST['age'] ?? 0);

    try {
        $user = new User($nom, $prenom, $email, $mdp, $type, $age);
        $userP->addUser($user);
        header('Location: listUsers.php');
        exit;
    } catch (RuntimeException $e) {
        if ($e->getMessage() === 'duplicate_email') {
            $error = 'Cet email est déjà utilisé.';
        } else {
            $error = 'Impossible d\'ajouter l\'utilisateur.';
        }
    } catch (Throwable $e) {
        $error = 'Impossible d\'ajouter l\'utilisateur.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter utilisateur</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; }
        form { width: 320px; margin: 20px auto; }
        input, select { width: 100%; padding: 8px; margin: 8px 0; box-sizing: border-box; }
        button { background: green; color: white; padding: 10px; border: none; border-radius: 4px; }
    </style>
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="content">
    <div class="topbar">
        <div class="page-title">Ajouter utilisateur</div>
        <div class="actions">
            <a href="listUsers.php" class="btn btn-secondary">← Retour</a>
        </div>
    </div>

    <div class="card" style="max-width:760px; margin:0 auto;">
        <form method="POST" novalidate data-validate="user-form" autocomplete="off">
            <div class="form-grid">
                <div>
                    <input type="text" name="nom" placeholder="Nom" required minlength="2" maxlength="100"
                           pattern="[A-Za-zÀ-ÖØ-öø-ÿ' \-]+"
                           title="Lettres, espaces et tirets"
                           value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                </div>
                <div>
                    <input type="text" name="prenom" placeholder="Prénom" required minlength="2" maxlength="100"
                           pattern="[A-Za-zÀ-ÖØ-öø-ÿ' \-]+"
                           title="Lettres, espaces et tirets"
                           value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                </div>
                <div>
                    <input type="email" name="email" placeholder="Email" required maxlength="150"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div>
                    <input type="password" name="mdp" placeholder="Mot de passe" required minlength="6" maxlength="128"
                           autocomplete="new-password">
                </div>
                <div>
                    <select name="type" required>
                        <option value="admin">Admin</option>
                        <option value="candidat">Candidat</option>
                        <option value="entrepreneur">Entrepreneur</option>
                    </select>
                </div>
                <div>
                    <input type="number" name="age" placeholder="Âge" required min="13" max="120" inputmode="numeric"
                           value="<?= htmlspecialchars($_POST['age'] ?? '') ?>">
                </div>
            </div>

            <div style="text-align:right; margin-top:12px;">
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>

        <?php if ($error !== ''): ?>
            <p style="color:red; text-align:center; margin-top:10px;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/forms-validation.js"></script>
</body>
</html>
