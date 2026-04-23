<?php
require_once __DIR__ . '/../../../controller/AuthController.php';
require_once __DIR__ . '/../../../controller/UserP.php';
require_once __DIR__ . '/../../../model/User.php';
require_once __DIR__ . '/../../../helpers/ProfilePhotoHelper.php';

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
        $uid = (int) $user['iduser'];
        $removePhoto = !empty($_POST['remove_photo']);

        if ($removePhoto) {
            ProfilePhotoHelper::deleteAllForUser($uid);
            $userP->setUserPhoto(null, $uid);
        } else {
            $file = $_FILES['photo'] ?? ['error' => UPLOAD_ERR_NO_FILE];
            $res = ProfilePhotoHelper::saveFromUpload($file, $uid);
            if (!$res['ok']) {
                $error = $res['error'] ?? 'Photo invalide.';
            } elseif ($res['path'] !== null) {
                $userP->setUserPhoto($res['path'], $uid);
            }
        }

        if ($error === '') {
            $updatedUser = new User($nom, $prenom, $email, $user['mdp'] ?? '', 'admin', $age);
            $userP->updateUser($updatedUser, $uid);

            $fresh = $userP->showUser($uid);
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
            $_SESSION['user'] = $fresh;

            header('Location: profile_admin.php?updated=1');
            exit;
        }
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

        <form method="POST" enctype="multipart/form-data" novalidate data-validate="user-form">
            <label>Photo de profil</label>
            <?php if (!empty($user['photo'])): ?>
                <p style="margin:4px 0 8px;font-size:13px;color:#555">Photo actuelle :</p>
                <p style="margin:0 0 12px"><img src="../../<?= htmlspecialchars(str_replace('\\', '/', $user['photo'])) ?>" alt="" style="max-width:120px;max-height:120px;border-radius:8px;border:1px solid #ddd"></p>
            <?php endif; ?>
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp">
            <p style="margin:4px 0 12px;font-size:13px;color:#666">JPEG, PNG ou WebP — 2 Mo max.</p>
            <?php if (!empty($user['photo'])): ?>
                <label style="display:flex;align-items:center;gap:8px;font-weight:normal;margin-bottom:12px">
                    <input type="checkbox" name="remove_photo" value="1"> Supprimer la photo
                </label>
            <?php endif; ?>

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
