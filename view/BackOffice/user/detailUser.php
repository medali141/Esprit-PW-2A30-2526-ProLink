<?php
require_once __DIR__ . '/../../../controller/UserP.php';

$userP = new UserP();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$user = $id > 0 ? $userP->showUser($id) : null;
if (!$user || !is_array($user)) {
    header('Location: listUsers.php');
    exit;
}

$p0 = trim((string) ($user['prenom'] ?? ''));
$n0 = trim((string) ($user['nom'] ?? ''));
$ch1 = function (string $s): string {
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

$typeKey = strtolower((string) ($user['type'] ?? ''));
$typeLabels = [
    'admin' => 'Administrateur',
    'candidat' => 'Candidat',
    'entrepreneur' => 'Entrepreneur',
];
$typeLabel = $typeLabels[$typeKey] ?? htmlspecialchars($user['type'] ?? '');

$typeClass = 'user-detail-badge--' . preg_replace('/[^a-z]/', '', $typeKey);
if ($typeClass === 'user-detail-badge--') {
    $typeClass = 'user-detail-badge--default';
}

$displayName = trim(htmlspecialchars(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')));
if ($displayName === '') {
    $displayName = htmlspecialchars($user['email'] ?? 'Utilisateur');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Détail utilisateur — BackOffice</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Colonne centrée, plus large (fiche + barre) */
        .container.page-full.user-detail-page {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: auto;
            width: 100%;
            max-width: 100%;
            padding-top: 0;
        }
        .user-detail-column {
            width: 100%;
            max-width: min(960px, 96vw);
            margin: 0 auto;
            box-sizing: border-box;
        }
        .container.page-full.user-detail-page .topbar {
            justify-content: flex-start;
            flex-wrap: wrap;
            align-items: center;
            gap: 14px 18px;
            width: 100%;
            box-sizing: border-box;
            margin-bottom: 20px;
            border-radius: 14px;
            overflow: hidden;
        }
        .container.page-full.user-detail-page .topbar .btn {
            border-radius: 10px;
        }
        .container.page-full.user-detail-page .topbar .page-title {
            margin: 0;
            flex: 0 0 auto;
        }
        .container.page-full.user-detail-page .topbar .actions {
            flex: 0 0 auto;
        }

        .user-detail-wrap {
            font-family: Inter, system-ui, sans-serif;
            width: 100%;
        }
        .user-detail-hero {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #0e7490 0%, #0369a1 35%, #5b21b6 100%);
            box-shadow: 0 20px 50px rgba(8, 47, 73, 0.18);
            color: #f8fafc;
        }
        .user-detail-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 60% at 100% 0%, rgba(255,255,255,0.15), transparent 55%);
            pointer-events: none;
        }
        .user-detail-hero-inner {
            position: relative;
            z-index: 1;
            padding: 28px 24px 24px;
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .user-detail-avatar {
            width: 88px;
            height: 88px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.35);
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            flex-shrink: 0;
        }
        .user-detail-hero-text { min-width: 0; flex: 1; }
        .user-detail-hero-text h1 {
            margin: 0 0 6px;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.2;
        }
        .user-detail-hero-text .user-detail-email {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.88;
            word-break: break-all;
        }
        .user-detail-badge {
            display: inline-flex;
            align-items: center;
            margin-top: 12px;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            background: rgba(15, 23, 42, 0.35);
            border: 1px solid rgba(255,255,255,0.25);
        }
        .user-detail-badge--admin { background: rgba(220, 38, 38, 0.35); border-color: rgba(252, 165, 165, 0.4); }
        .user-detail-badge--candidat { background: rgba(14, 165, 233, 0.35); border-color: rgba(125, 211, 252, 0.45); }
        .user-detail-badge--entrepreneur { background: rgba(147, 51, 234, 0.35); border-color: rgba(216, 180, 254, 0.45); }
        .user-detail-badge--default { background: rgba(15, 23, 42, 0.4); }

        .user-detail-card {
            background: #fff;
            border-radius: 14px;
            padding: 4px 0 8px;
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
            border: 1px solid rgba(148, 163, 184, 0.18);
        }
        .user-detail-card h2 {
            margin: 0;
            padding: 18px 22px 14px;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #64748b;
            border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        }
        .user-detail-row {
            display: grid;
            grid-template-columns: minmax(140px, 22%) 1fr;
            gap: 12px 24px;
            padding: 14px 22px;
            align-items: baseline;
            border-bottom: 1px solid rgba(241, 245, 249, 0.9);
        }
        .user-detail-row:last-child { border-bottom: none; }
        .user-detail-row dt {
            margin: 0;
            font-size: 0.8rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .user-detail-row dd {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            color: #0f172a;
            word-break: break-word;
        }
        .user-detail-row dd.mono {
            font-variant-numeric: tabular-nums;
            font-weight: 700;
            color: #0369a1;
        }
        .user-detail-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 22px;
            justify-content: center;
        }
        .user-detail-actions .btn {
            border-radius: 10px;
        }

        /*
         * Mode sombre : view/assets/style.css impose `html.dark-mode body * { color:#fff !important }`
         * ce qui rend le texte illisible sur la carte claire. On redéfinit carte + textes avec !important.
         */
        html.dark-mode .user-detail-card {
            background: #151b26 !important;
            border-color: rgba(148, 163, 184, 0.22) !important;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.35) !important;
            color: #e2e8f0 !important;
        }
        html.dark-mode .user-detail-card h2 {
            color: #94a3b8 !important;
            border-bottom-color: rgba(148, 163, 184, 0.18) !important;
        }
        html.dark-mode .user-detail-row {
            border-bottom-color: rgba(51, 65, 85, 0.75) !important;
        }
        html.dark-mode .user-detail-row dt {
            color: #64748b !important;
        }
        html.dark-mode .user-detail-row dd {
            color: #f1f5f9 !important;
        }
        html.dark-mode .user-detail-row dd.mono {
            color: #38bdf8 !important;
        }

        @media (max-width: 520px) {
            .user-detail-row { grid-template-columns: 1fr; gap: 4px; }
            .user-detail-row dt { font-size: 0.72rem; }
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/../_layout/sidebar.php'; ?>

<div class="content">
    <div class="container page-full user-detail-page">
        <div class="user-detail-column">
        <div class="topbar">
            <div class="page-title">Fiche utilisateur</div>
            <div class="actions">
                <a href="listUsers.php" class="btn btn-secondary">← Liste</a>
                <a href="updateUser.php?id=<?= (int) $user['iduser'] ?>" class="btn btn-primary">Modifier</a>
            </div>
        </div>

        <div class="user-detail-wrap">
            <div class="user-detail-hero">
                <div class="user-detail-hero-inner">
                    <div class="user-detail-avatar" aria-hidden="true"><?= htmlspecialchars($initials) ?></div>
                    <div class="user-detail-hero-text">
                        <h1><?= $displayName ?></h1>
                        <p class="user-detail-email"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                        <span class="user-detail-badge <?= htmlspecialchars($typeClass) ?>"><?= htmlspecialchars($typeLabel) ?></span>
                    </div>
                </div>
            </div>

            <div class="user-detail-card">
                <h2>Informations du compte</h2>
                <dl>
                    <div class="user-detail-row">
                        <dt>Identifiant</dt>
                        <dd class="mono">#<?= (int) $user['iduser'] ?></dd>
                    </div>
                    <div class="user-detail-row">
                        <dt>Nom</dt>
                        <dd><?= htmlspecialchars($user['nom'] ?? '') ?></dd>
                    </div>
                    <div class="user-detail-row">
                        <dt>Prénom</dt>
                        <dd><?= htmlspecialchars($user['prenom'] ?? '') ?></dd>
                    </div>
                    <div class="user-detail-row">
                        <dt>Email</dt>
                        <dd><?= htmlspecialchars($user['email'] ?? '') ?></dd>
                    </div>
                    <div class="user-detail-row">
                        <dt>Âge</dt>
                        <dd><?= htmlspecialchars((string) ($user['age'] ?? '—')) ?></dd>
                    </div>
                </dl>
            </div>

            <div class="user-detail-actions">
                <a href="listUsers.php" class="btn btn-secondary">Retour à la liste</a>
                <a href="updateUser.php?id=<?= (int) $user['iduser'] ?>" class="btn btn-primary">Éditer le profil</a>
            </div>
        </div>
        </div>
    </div>
</div>

</body>
</html>
