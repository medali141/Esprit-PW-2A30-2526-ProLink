<?php require_once __DIR__ . '/../../init.php'; ?>
<?php
// Require authentication for FrontOffice pages: redirect to login when user is not authenticated.
require_once __DIR__ . '/../../controller/AuthController.php';
$auth = new AuthController();
$u = $auth->profile();
if (!$u) {
    header('Location: ../login.php');
    exit;
}
$isAdmin = strtolower((string) ($u['type'] ?? '')) === 'admin';
$boDashboardUrl = ($baseUrl ?? '') . '/BackOffice/dashboard/dashboard.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ProLink - Accueil</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <!-- Project stylesheet (relative to this file) -->
    <link rel="stylesheet" href="../assets/style.css">

    <style>
        /* Thème aligné profil FO + storefront (cyan → violet) */
        :root {
            --home-cyan: #06b6d4;
            --home-blue: #2563eb;
            --home-violet: #7c3aed;
            --home-text: #0f172a;
            --home-muted: #64748b;
            --home-radius: 18px;
        }

        body.fo-home-page {
            font-family: Inter, system-ui, -apple-system, sans-serif;
            background: #eef2ff;
        }

        body.fo-home-page .home-shell {
            max-width: 1100px;
            margin: 0 auto;
            padding: 28px 18px 48px;
            box-sizing: border-box;
            background:
                radial-gradient(ellipse 100% 80% at 10% -20%, rgba(6, 182, 212, 0.22), transparent 55%),
                radial-gradient(ellipse 90% 70% at 95% 10%, rgba(124, 58, 237, 0.2), transparent 50%),
                radial-gradient(ellipse 70% 50% at 50% 100%, rgba(217, 70, 239, 0.12), transparent 55%),
                linear-gradient(165deg, #f0f9ff 0%, #eef2ff 45%, #faf5ff 100%);
        }

        body.fo-home-page .home-hero {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 24px;
            padding: 28px 26px 30px;
            margin-bottom: 22px;
            border-radius: var(--home-radius);
            background: linear-gradient(125deg, #0891b2 0%, #2563eb 42%, #7c3aed 78%, #a21caf 100%);
            color: #f8fafc;
            box-shadow: 0 20px 50px rgba(37, 99, 235, 0.25);
            position: relative;
            overflow: hidden;
        }

        body.fo-home-page .home-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 100% 0%, rgba(255, 255, 255, 0.22), transparent 45%);
            pointer-events: none;
        }

        body.fo-home-page .home-hero-inner {
            position: relative;
            z-index: 1;
            flex: 1;
            min-width: 260px;
        }

        body.fo-home-page .home-eyebrow {
            margin: 0 0 8px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            opacity: 0.88;
            color: rgba(248, 250, 252, 0.95);
        }

        body.fo-home-page .home-hero h1 {
            margin: 0 0 10px;
            font-size: clamp(1.65rem, 4vw, 2.35rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.15;
            color: #f8fafc;
        }

        body.fo-home-page .home-hero .home-lead {
            margin: 0 0 18px;
            font-size: 0.98rem;
            line-height: 1.55;
            opacity: 0.92;
            max-width: 48ch;
            color: rgba(248, 250, 252, 0.95);
        }

        body.fo-home-page .home-ctas {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }

        body.fo-home-page .home-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            font-size: 0.92rem;
            font-weight: 700;
            text-decoration: none;
            border-radius: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        body.fo-home-page .home-btn--primary {
            background: linear-gradient(105deg, #f0fdfa, #e0f2fe);
            color: #0369a1;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        body.fo-home-page .home-btn--primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18);
            color: #0c4a6e;
        }

        body.fo-home-page .home-btn--ghost {
            color: #f8fafc;
            background: rgba(15, 23, 42, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.35);
        }

        body.fo-home-page .home-btn--ghost:hover {
            background: rgba(15, 23, 42, 0.4);
            color: #fff;
        }

        body.fo-home-page .home-btn--admin {
            color: #fff;
            background: rgba(15, 23, 42, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        body.fo-home-page .home-btn--admin:hover {
            background: rgba(15, 23, 42, 0.55);
            border-color: rgba(255, 255, 255, 0.55);
        }

        body.fo-home-page .home-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 18px;
        }

        body.fo-home-page .home-feature-card {
            background: #fff;
            border-radius: var(--home-radius);
            padding: 22px 20px 20px;
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow:
                0 4px 6px rgba(15, 23, 42, 0.04),
                0 18px 40px rgba(37, 99, 235, 0.08);
            display: flex;
            flex-direction: column;
            gap: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            opacity: 0;
            transform: translateY(10px);
            animation: homeFadeUp 0.5s ease forwards;
        }

        body.fo-home-page .home-feature-card:nth-child(1) { animation-delay: 0.06s; }
        body.fo-home-page .home-feature-card:nth-child(2) { animation-delay: 0.12s; }
        body.fo-home-page .home-feature-card:nth-child(3) { animation-delay: 0.18s; }

        body.fo-home-page .home-feature-card:hover {
            transform: translateY(-4px);
            box-shadow:
                0 8px 16px rgba(15, 23, 42, 0.06),
                0 22px 48px rgba(124, 58, 237, 0.12);
        }

        body.fo-home-page .home-feature-top {
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        body.fo-home-page .home-feature-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--home-cyan), var(--home-blue) 50%, var(--home-violet));
            box-shadow: 0 8px 22px rgba(37, 99, 235, 0.25);
        }

        body.fo-home-page .home-feature-card h3 {
            margin: 0 0 6px;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--home-text);
            letter-spacing: -0.02em;
        }

        body.fo-home-page .home-feature-card .home-feature-desc {
            margin: 0;
            font-size: 0.9rem;
            line-height: 1.5;
            color: #475569;
        }

        body.fo-home-page .home-feature-card .home-btn-inline {
            align-self: flex-start;
            margin-top: 4px;
            padding: 10px 16px;
            font-size: 0.85rem;
            font-weight: 700;
            border-radius: 10px;
            text-decoration: none;
            color: #fff;
            background: linear-gradient(105deg, var(--home-cyan), var(--home-blue) 45%, var(--home-violet));
            box-shadow: 0 8px 22px rgba(37, 99, 235, 0.3);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        body.fo-home-page .home-feature-card .home-btn-inline:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(124, 58, 237, 0.35);
            color: #fff;
        }

        @keyframes homeFadeUp {
            to { opacity: 1; transform: none; }
        }

        @media (max-width: 640px) {
            body.fo-home-page .home-hero { padding: 22px 18px; }
        }

        /* ——— Mode sombre ——— */
        html.dark-mode body.fo-home-page {
            background: #0c1017 !important;
        }

        html.dark-mode body.fo-home-page .home-shell {
            background:
                radial-gradient(ellipse 100% 80% at 10% -20%, rgba(6, 182, 212, 0.12), transparent 55%),
                radial-gradient(ellipse 90% 70% at 95% 10%, rgba(124, 58, 237, 0.1), transparent 50%),
                #0c1017 !important;
        }

        html.dark-mode body.fo-home-page .home-hero {
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
        }

        html.dark-mode body.fo-home-page .home-eyebrow,
        html.dark-mode body.fo-home-page .home-hero h1,
        html.dark-mode body.fo-home-page .home-hero .home-lead {
            color: #f8fafc !important;
        }

        html.dark-mode body.fo-home-page .home-feature-card {
            background: #151b26 !important;
            border-color: rgba(148, 163, 184, 0.2) !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4) !important;
        }

        html.dark-mode body.fo-home-page .home-feature-card h3 {
            color: #f1f5f9 !important;
        }

        html.dark-mode body.fo-home-page .home-feature-card .home-feature-desc {
            color: #cbd5e1 !important;
        }

        /* Mode sombre : bouton clair sur héros = texte bien foncé (lisible sans règle globale body *) */
        html.dark-mode body.fo-home-page .home-hero .home-btn--primary {
            background: linear-gradient(105deg, #f0fdfa, #e0f2fe) !important;
            color: #0c4a6e !important;
            border: 1px solid rgba(15, 23, 42, 0.12) !important;
        }

        html.dark-mode body.fo-home-page .home-hero .home-btn--primary:hover {
            color: #082f49 !important;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.2) !important;
        }
    </style>
</head>

<body class="fo-home-page">

<!-- NAVBAR -->
<?php include 'components/navbar.php'; ?>

<!-- MAIN -->
<main class="main home-shell">

<section class="home-hero" aria-labelledby="home-title">
    <div class="home-hero-inner">
        <p class="home-eyebrow">Accueil</p>
        <h1 id="home-title">Bienvenue sur ProLink</h1>
        <p class="home-lead">Connectez-vous avec des professionnels, partagez vos projets et développez votre réseau.</p>
        <div class="home-ctas">
            <a href="catalogue.php" class="home-btn home-btn--primary">Explorer le catalogue</a>
            <a href="evenements.php" class="home-btn home-btn--ghost">Événements</a>
            <a href="forum.php" class="home-btn home-btn--ghost">Forum</a>
            <a href="profile/profile.php" class="home-btn home-btn--ghost">Mon profil</a>
            <?php if ($isAdmin): ?>
                <a href="<?= htmlspecialchars($boDashboardUrl) ?>" class="home-btn home-btn--admin">Administration</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="home-features" aria-label="Fonctionnalités">
    <article class="home-feature-card">
        <div class="home-feature-top">
            <div class="home-feature-icon" aria-hidden="true">🌐</div>
            <div>
                <h3>Réseau</h3>
                <p class="home-feature-desc">Ajoutez et interagissez avec des professionnels pour étendre votre réseau.</p>
            </div>
        </div>
    </article>

    <article class="home-feature-card">
        <div class="home-feature-top">
            <div class="home-feature-icon" aria-hidden="true">📁</div>
            <div>
                <h3>Projets</h3>
                <p class="home-feature-desc">Publiez et collaborez sur des projets, trouvez des partenaires.</p>
            </div>
        </div>
    </article>

    <article class="home-feature-card">
        <div class="home-feature-top">
            <div class="home-feature-icon" aria-hidden="true">🛒</div>
            <div>
                <h3>Achat / Vente</h3>
                <p class="home-feature-desc">Catalogue produits, panier, commandes et suivi livraison.</p>
            </div>
        </div>
        <a href="catalogue.php" class="home-btn-inline">Voir la boutique</a>
    </article>
</section>

</main>

<!-- FOOTER -->
<?php include 'components/footer.php'; ?>

</body>
</html>