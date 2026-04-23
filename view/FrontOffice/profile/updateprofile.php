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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier le profil — ProLink</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --pe-cyan: #06b6d4;
            --pe-blue: #2563eb;
            --pe-violet: #7c3aed;
            --pe-fuchsia: #d946ef;
            --pe-amber: #fbbf24;
            --pe-card: #ffffff;
            --pe-text: #0f172a;
            --pe-muted: #64748b;
            --pe-border: rgba(148, 163, 184, 0.35);
        }

        .profile-edit-main {
            font-family: Inter, system-ui, -apple-system, sans-serif;
            flex: 1;
            padding: 28px 18px 48px;
            background:
                radial-gradient(ellipse 100% 80% at 10% -20%, rgba(6, 182, 212, 0.22), transparent 55%),
                radial-gradient(ellipse 90% 70% at 95% 10%, rgba(124, 58, 237, 0.2), transparent 50%),
                radial-gradient(ellipse 70% 50% at 50% 100%, rgba(217, 70, 239, 0.12), transparent 55%),
                linear-gradient(165deg, #f0f9ff 0%, #eef2ff 45%, #faf5ff 100%);
        }

        .profile-edit-shell {
            max-width: 560px;
            margin: 0 auto;
        }

        .profile-edit-hero {
            border-radius: 18px;
            padding: 22px 24px 26px;
            margin-bottom: 18px;
            background: linear-gradient(125deg, #0891b2 0%, #2563eb 42%, #7c3aed 78%, #a21caf 100%);
            color: #f8fafc;
            box-shadow: 0 20px 50px rgba(37, 99, 235, 0.25);
            position: relative;
            overflow: hidden;
        }
        .profile-edit-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 100% 0%, rgba(255,255,255,0.2), transparent 45%);
            pointer-events: none;
        }
        .profile-edit-hero-inner { position: relative; z-index: 1; }
        .profile-edit-eyebrow {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            opacity: 0.88;
            margin: 0 0 8px;
        }
        .profile-edit-hero h1 {
            margin: 0;
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.2;
        }
        .profile-edit-hero p {
            margin: 10px 0 0;
            font-size: 0.92rem;
            opacity: 0.9;
            max-width: 36ch;
        }

        .profile-edit-card {
            background: var(--pe-card);
            border-radius: 18px;
            padding: 26px 24px 28px;
            box-shadow:
                0 4px 6px rgba(15, 23, 42, 0.04),
                0 18px 40px rgba(37, 99, 235, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .profile-form-error {
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 18px;
            font-weight: 600;
            font-size: 0.9rem;
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .profile-edit-form .field {
            margin-bottom: 18px;
        }
        .profile-edit-form label {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--pe-muted);
            margin-bottom: 6px;
        }
        .profile-edit-form input,
        .profile-edit-form select {
            width: 100%;
            box-sizing: border-box;
            padding: 12px 14px;
            font-size: 1rem;
            font-family: inherit;
            border: 1px solid var(--pe-border);
            border-radius: 12px;
            background: #fff;
            color: var(--pe-text);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .profile-edit-form input:focus,
        .profile-edit-form select:focus {
            outline: none;
            border-color: var(--pe-cyan);
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.25);
        }
        .profile-edit-form select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 40px;
        }

        .profile-edit-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            margin-top: 26px;
            padding-top: 8px;
        }
        .profile-edit-actions .btn-submit {
            flex: 1 1 auto;
            min-width: 160px;
            padding: 14px 22px;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            color: #fff;
            background: linear-gradient(105deg, var(--pe-cyan), var(--pe-blue) 45%, var(--pe-violet));
            box-shadow: 0 10px 28px rgba(37, 99, 235, 0.35);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .profile-edit-actions .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 36px rgba(124, 58, 237, 0.4);
        }
        .profile-edit-actions .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 18px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            border-radius: 12px;
            color: var(--pe-muted);
            border: 1px solid var(--pe-border);
            background: rgba(248, 250, 252, 0.9);
            transition: background 0.2s, color 0.2s, border-color 0.2s;
        }
        .profile-edit-actions .btn-back:hover {
            color: var(--pe-blue);
            border-color: rgba(37, 99, 235, 0.35);
            background: #fff;
        }

        /* ——— Mode sombre ——— */
        html.dark-mode body.fo-profile-page .profile-edit-main {
            background:
                radial-gradient(ellipse 100% 80% at 10% -20%, rgba(6, 182, 212, 0.12), transparent 55%),
                radial-gradient(ellipse 90% 70% at 95% 10%, rgba(124, 58, 237, 0.1), transparent 50%),
                #0c1017 !important;
        }
        html.dark-mode body.fo-profile-page .profile-edit-hero {
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
        }
        html.dark-mode body.fo-profile-page .profile-edit-card {
            background: #151b26 !important;
            border-color: rgba(148, 163, 184, 0.2) !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4) !important;
            color: #e2e8f0 !important;
        }
        html.dark-mode body.fo-profile-page .profile-edit-form label {
            color: #94a3b8 !important;
        }
        html.dark-mode body.fo-profile-page .profile-edit-form input,
        html.dark-mode body.fo-profile-page .profile-edit-form select {
            background: #1e293b !important;
            border-color: rgba(148, 163, 184, 0.25) !important;
            color: #f1f5f9 !important;
        }
        html.dark-mode body.fo-profile-page .profile-edit-form input:focus,
        html.dark-mode body.fo-profile-page .profile-edit-form select:focus {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2) !important;
        }
        html.dark-mode body.fo-profile-page .profile-form-error {
            background: rgba(127, 29, 29, 0.35) !important;
            color: #fecaca !important;
            border-color: rgba(248, 113, 113, 0.35) !important;
        }
        html.dark-mode body.fo-profile-page .profile-edit-actions .btn-back {
            background: #1e293b !important;
            color: #e2e8f0 !important;
            border-color: rgba(148, 163, 184, 0.25) !important;
        }
        html.dark-mode body.fo-profile-page .profile-edit-actions .btn-back:hover {
            color: #38bdf8 !important;
            border-color: rgba(56, 189, 248, 0.4) !important;
        }
    </style>
</head>
<body class="fo-profile-page">

<?php include __DIR__ . '/../components/navbar.php'; ?>

<main class="main profile-edit-main">
    <div class="profile-edit-shell">
        <header class="profile-edit-hero">
            <div class="profile-edit-hero-inner">
                <p class="profile-edit-eyebrow">Compte</p>
                <h1>Modifier mon profil</h1>
                <p>Mettez à jour vos informations. Les changements sont enregistrés sur votre compte ProLink.</p>
            </div>
        </header>

        <section class="profile-edit-card">
            <?php if ($error): ?>
                <div class="profile-form-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form class="profile-edit-form" method="POST" novalidate data-validate="user-form">
                <div class="field">
                    <label for="f-nom">Nom</label>
                    <input id="f-nom" type="text" name="nom" placeholder="Votre nom" value="<?= htmlspecialchars($_POST['nom'] ?? $user['nom']) ?>" required>
                </div>
                <div class="field">
                    <label for="f-prenom">Prénom</label>
                    <input id="f-prenom" type="text" name="prenom" placeholder="Votre prénom" value="<?= htmlspecialchars($_POST['prenom'] ?? $user['prenom']) ?>" required>
                </div>
                <div class="field">
                    <label for="f-email">Email</label>
                    <input id="f-email" type="email" name="email" placeholder="vous@exemple.com" value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>" required>
                </div>
                <div class="field">
                    <label for="f-type">Type de profil</label>
                    <select id="f-type" name="type" required>
                        <option value="">— Choisir —</option>
                        <option value="candidat" <?= ((($_POST['type'] ?? $user['type']) === 'candidat') ? 'selected' : '') ?>>Candidat</option>
                        <option value="entrepreneur" <?= ((($_POST['type'] ?? $user['type']) === 'entrepreneur') ? 'selected' : '') ?>>Entrepreneur</option>
                    </select>
                </div>
                <div class="field">
                    <label for="f-age">Âge</label>
                    <input id="f-age" type="number" name="age" min="1" max="120" placeholder="Âge" value="<?= htmlspecialchars($_POST['age'] ?? $user['age']) ?>" required>
                </div>

                <div class="profile-edit-actions">
                    <button type="submit" class="btn-submit">Enregistrer les modifications</button>
                    <a class="btn-back" href="profile.php">← Retour au profil</a>
                </div>
            </form>
        </section>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

<script src="../../assets/forms-validation.js"></script>

</body>
</html>
