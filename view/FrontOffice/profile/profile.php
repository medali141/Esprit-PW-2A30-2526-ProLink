<?php
require_once __DIR__ . '/../../../controller/AuthController.php';

$auth = new AuthController();
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_start();
    session_unset();
    session_destroy();
    header('Location: ../../login.php');
    exit;
}

$user = $auth->profile();
if (!$user) {
    header('Location: ../../login.php');
    exit;
}

$p0 = trim((string) ($user['prenom'] ?? ''));
$n0 = trim((string) ($user['nom'] ?? ''));
$ch1 = static function (string $s): string {
    if ($s === '') {
        return '';
    }
    return function_exists('mb_substr')
        ? mb_substr($s, 0, 1, 'UTF-8')
        : substr($s, 0, 1);
};
$initials = strtoupper($ch1($p0) . $ch1($n0));
if ($initials === '') {
    $initials = '?';
}
$showUpdated = isset($_GET['updated']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil — ProLink</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --pv-cyan: #06b6d4;
            --pv-blue: #2563eb;
            --pv-violet: #7c3aed;
        }

        .profile-view-main {
            font-family: Inter, system-ui, -apple-system, sans-serif;
            flex: 1;
            padding: 28px 18px 48px;
            background:
                radial-gradient(ellipse 100% 80% at 10% -20%, rgba(6, 182, 212, 0.22), transparent 55%),
                radial-gradient(ellipse 90% 70% at 95% 10%, rgba(124, 58, 237, 0.2), transparent 50%),
                radial-gradient(ellipse 70% 50% at 50% 100%, rgba(217, 70, 239, 0.12), transparent 55%),
                linear-gradient(165deg, #f0f9ff 0%, #eef2ff 45%, #faf5ff 100%);
        }

        .profile-view-shell {
            max-width: 640px;
            margin: 0 auto;
        }

        .profile-flash {
            padding: 12px 16px;
            border-radius: 14px;
            margin-bottom: 16px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .profile-flash--ok {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .profile-view-hero {
            border-radius: 18px;
            padding: 24px 22px;
            margin-bottom: 18px;
            background: linear-gradient(125deg, #0891b2 0%, #2563eb 42%, #7c3aed 78%, #a21caf 100%);
            color: #f8fafc;
            box-shadow: 0 20px 50px rgba(37, 99, 235, 0.25);
            position: relative;
            overflow: hidden;
        }
        .profile-view-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 100% 0%, rgba(255,255,255,0.22), transparent 45%);
            pointer-events: none;
        }
        .profile-view-hero-grid {
            position: relative;
            z-index: 1;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 20px;
        }
        .profile-view-avatar {
            width: 88px;
            height: 88px;
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.45);
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            flex-shrink: 0;
        }
        .profile-view-meta { flex: 1; min-width: 200px; }
        .profile-view-eyebrow {
            margin: 0 0 6px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            opacity: 0.88;
        }
        .profile-view-meta h1 {
            margin: 0 0 8px;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.15;
        }
        .profile-view-meta .sub {
            margin: 0;
            font-size: 0.92rem;
            opacity: 0.9;
            word-break: break-word;
        }
        .profile-view-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            width: 100%;
        }
        @media (min-width: 520px) {
            .profile-view-actions { width: auto; margin-left: auto; justify-content: flex-end; }
            .profile-view-hero-grid { flex-wrap: nowrap; }
        }
        .profile-view-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            font-size: 0.88rem;
            font-weight: 700;
            text-decoration: none;
            border-radius: 12px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .profile-view-actions .edit {
            background: linear-gradient(105deg, #f0fdfa, #e0f2fe);
            color: #0369a1;
            box-shadow: 0 4px 14px rgba(0,0,0,0.12);
        }
        .profile-view-actions .edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .profile-view-actions .logout {
            background: rgba(15, 23, 42, 0.25);
            color: #f8fafc;
            border: 1px solid rgba(255,255,255,0.35);
        }
        .profile-view-actions .logout:hover {
            background: rgba(15, 23, 42, 0.4);
        }

        .profile-view-card {
            background: #fff;
            border-radius: 18px;
            padding: 8px 0 12px;
            box-shadow:
                0 4px 6px rgba(15, 23, 42, 0.04),
                0 18px 40px rgba(37, 99, 235, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.9);
        }
        .profile-view-section-title {
            margin: 0;
            padding: 18px 22px 12px;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #64748b;
            border-bottom: 1px solid rgba(148, 163, 184, 0.22);
        }
        .profile-dl { margin: 0; padding: 0; }
        .profile-row {
            display: grid;
            grid-template-columns: minmax(120px, 28%) 1fr;
            gap: 8px 18px;
            padding: 14px 22px;
            border-bottom: 1px solid rgba(241, 245, 249, 0.95);
        }
        .profile-row:last-child { border-bottom: none; }
        .profile-row dt {
            margin: 0;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #94a3b8;
        }
        .profile-row dd {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            color: #0f172a;
            word-break: break-word;
        }
        @media (max-width: 480px) {
            .profile-row { grid-template-columns: 1fr; gap: 4px; }
        }

        /* Mode sombre */
        html.dark-mode body.fo-profile-page .profile-view-main {
            background:
                radial-gradient(ellipse 100% 80% at 10% -20%, rgba(6, 182, 212, 0.12), transparent 55%),
                radial-gradient(ellipse 90% 70% at 95% 10%, rgba(124, 58, 237, 0.1), transparent 50%),
                #0c1017 !important;
        }
        html.dark-mode body.fo-profile-page .profile-flash--ok {
            background: rgba(6, 78, 59, 0.45) !important;
            color: #6ee7b7 !important;
            border-color: rgba(52, 211, 153, 0.35) !important;
        }
        html.dark-mode body.fo-profile-page .profile-view-hero {
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
        }
        html.dark-mode body.fo-profile-page .profile-view-card {
            background: #151b26 !important;
            border-color: rgba(148, 163, 184, 0.2) !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4) !important;
        }
        html.dark-mode body.fo-profile-page .profile-view-section-title {
            color: #94a3b8 !important;
            border-bottom-color: rgba(148, 163, 184, 0.15) !important;
        }
        html.dark-mode body.fo-profile-page .profile-row {
            border-bottom-color: rgba(51, 65, 85, 0.65) !important;
        }
        html.dark-mode body.fo-profile-page .profile-row dt {
            color: #64748b !important;
        }
        html.dark-mode body.fo-profile-page .profile-row dd {
            color: #f1f5f9 !important;
        }
        html.dark-mode body.fo-profile-page .profile-view-actions .edit {
            background: linear-gradient(105deg, #e0f2fe, #cffafe) !important;
            color: #0c4a6e !important;
        }
    </style>
</head>
<body class="fo-profile-page">

<?php include __DIR__ . '/../components/navbar.php'; ?>

<main class="main profile-view-main">
    <div class="profile-view-shell">
        <?php if ($showUpdated): ?>
            <div class="profile-flash profile-flash--ok" role="status">Profil mis à jour avec succès.</div>
        <?php endif; ?>

        <header class="profile-view-hero">
            <div class="profile-view-hero-grid">
                <div class="profile-view-avatar" aria-hidden="true"><?= htmlspecialchars($initials) ?></div>
                <div class="profile-view-meta">
                    <p class="profile-view-eyebrow">Mon profil</p>
                    <h1><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h1>
                    <p class="sub"><?= htmlspecialchars($user['email']) ?> · <?= htmlspecialchars($user['type']) ?></p>
                </div>
                <div class="profile-view-actions">
                    <a class="edit" href="updateprofile.php">Modifier le profil</a>
                    <a class="logout" href="profile.php?action=logout">Se déconnecter</a>
                </div>
            </div>
        </header>

        <section class="profile-view-card" aria-labelledby="profile-info-title">
            <h2 id="profile-info-title" class="profile-view-section-title">Informations du compte</h2>
            <dl class="profile-dl">
                <div class="profile-row">
                    <dt>Nom</dt>
                    <dd><?= htmlspecialchars($user['nom']) ?></dd>
                </div>
                <div class="profile-row">
                    <dt>Prénom</dt>
                    <dd><?= htmlspecialchars($user['prenom']) ?></dd>
                </div>
                <div class="profile-row">
                    <dt>Email</dt>
                    <dd><?= htmlspecialchars($user['email']) ?></dd>
                </div>
                <div class="profile-row">
                    <dt>Type</dt>
                    <dd><?= htmlspecialchars($user['type']) ?></dd>
                </div>
                <div class="profile-row">
                    <dt>Âge</dt>
                    <dd><?= htmlspecialchars((string) $user['age']) ?></dd>
                </div>
            </dl>
        </section>
    </div>
</main>

<?php include __DIR__ . '/../components/footer.php'; ?>

</body>
</html>
