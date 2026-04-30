<?php
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../model/CommerceMetier.php';

$auth = new AuthController();
// logout handler
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_start();
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit;
}

$user = $auth->profile();
if (!$user) {
    header('Location: ../login.php');
    exit;
}
$points = (int) ($user['points_fidelite'] ?? 0);
$pointsTnd = CommerceMetier::dinarFromPoints($points);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil - ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
    <style>
        .fo-profile-wrap { max-width: 980px; margin: 28px auto 10px; padding: 0 18px; }
        .fo-profile-card {
            background: var(--card);
            border-radius: 16px;
            border: 1px solid rgba(15, 23, 42, 0.1);
            box-shadow: var(--shadow);
            padding: 22px;
        }
        .fo-profile-head {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .fo-profile-avatar {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
            font-weight: 800;
            color: #0c4a6e;
            background: linear-gradient(135deg, #bae6fd, #a7f3d0);
            border: 1px solid rgba(14, 116, 144, 0.24);
        }
        .fo-profile-meta h1 {
            margin: 0 0 4px;
            font-size: clamp(1.2rem, 2.6vw, 1.55rem);
        }
        .fo-profile-meta p {
            margin: 0;
            color: var(--muted);
            font-size: 0.92rem;
        }
        .fo-profile-actions {
            margin-left: auto;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .fo-profile-stats {
            margin-top: 16px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 10px;
        }
        .fo-profile-stat {
            border-radius: 12px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: linear-gradient(135deg, rgba(11, 102, 195, 0.07), rgba(6, 182, 212, 0.06));
            padding: 11px 12px;
        }
        .fo-profile-stat .k {
            margin: 0 0 4px;
            font-size: 0.72rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-weight: 800;
            color: var(--muted);
        }
        .fo-profile-stat .v {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
        }
        .fo-profile-details {
            margin-top: 18px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
        }
        .fo-profile-detail {
            padding: 12px;
            border-radius: 12px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: #f8fafc;
        }
        .fo-profile-detail .k {
            display: block;
            font-size: 0.74rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
            font-weight: 800;
            margin-bottom: 4px;
        }
        .fo-profile-detail .v {
            font-size: 0.98rem;
            font-weight: 700;
            color: #0f172a;
            word-break: break-word;
        }
        html.dark-mode .fo-profile-card,
        html.dark-mode .fo-profile-detail {
            background: #151b26;
            border-color: rgba(148, 163, 184, 0.2);
        }
        html.dark-mode .fo-profile-stat {
            border-color: rgba(148, 163, 184, 0.2);
            background: rgba(30, 41, 59, 0.7);
        }
        html.dark-mode .fo-profile-stat .v,
        html.dark-mode .fo-profile-detail .v {
            color: #f1f5f9;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/components/navbar.php'; ?>

<main class="fo-page">
<div class="fo-profile-wrap">
    <section class="fo-profile-card">
        <div class="fo-profile-head">
            <div class="fo-profile-avatar"><?= strtoupper(substr((string) $user['nom'], 0, 1) . substr((string) $user['prenom'], 0, 1)) ?></div>
            <div class="fo-profile-meta">
                <h1><?= htmlspecialchars((string) $user['prenom'] . ' ' . (string) $user['nom']) ?></h1>
                <p><?= htmlspecialchars((string) $user['email']) ?> • Compte <?= htmlspecialchars((string) $user['type']) ?></p>
            </div>
            <div class="fo-profile-actions">
                <a class="fo-btn fo-btn--primary" href="updateprofile.php" style="text-decoration:none">Modifier le profil</a>
                <a class="fo-btn fo-btn--secondary" href="profile.php?action=logout" style="text-decoration:none">Se déconnecter</a>
            </div>
        </div>

        <div class="fo-profile-stats" aria-label="Indicateurs du compte">
            <article class="fo-profile-stat">
                <p class="k">Points fidélité</p>
                <p class="v"><?= $points ?> pts</p>
            </article>
            <article class="fo-profile-stat">
                <p class="k">Valeur des points</p>
                <p class="v"><?= number_format($pointsTnd, 3, ',', ' ') ?> TND</p>
            </article>
            <article class="fo-profile-stat">
                <p class="k">Niveau compte</p>
                <p class="v"><?= htmlspecialchars((string) ucfirst((string) $user['type'])) ?></p>
            </article>
            <article class="fo-profile-stat">
                <p class="k">Âge</p>
                <p class="v"><?= htmlspecialchars((string) ($user['age'] ?? '—')) ?> ans</p>
            </article>
        </div>

        <div class="fo-profile-details">
            <div class="fo-profile-detail"><span class="k">Nom</span><span class="v"><?= htmlspecialchars((string) $user['nom']) ?></span></div>
            <div class="fo-profile-detail"><span class="k">Prénom</span><span class="v"><?= htmlspecialchars((string) $user['prenom']) ?></span></div>
            <div class="fo-profile-detail"><span class="k">Email</span><span class="v"><?= htmlspecialchars((string) $user['email']) ?></span></div>
            <div class="fo-profile-detail"><span class="k">Type</span><span class="v"><?= htmlspecialchars((string) $user['type']) ?></span></div>
        </div>
    </section>
</div>
</main>

<?php include __DIR__ . '/components/footer.php'; ?>

</body>
</html>
