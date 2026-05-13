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
    <link rel="stylesheet" href="../assets/storefront.css">

    <style>
        :root{
            --bg: #0b1220;
            --muted: #9aa6b2;
            --card: #0f1724;
            --accent-1: #00a7ff;
            --accent-2: #00d4ff;
            --glass: rgba(255,255,255,0.04);
            --shadow: 0 12px 40px rgba(2,12,27,0.55);
            --radius-lg: 16px;
        }

        /* Page layout */
        body{
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
            background: radial-gradient(1200px 400px at 10% 10%, rgba(0,167,255,0.06), transparent 10%), linear-gradient(180deg,#061022 0%, #07162a 100%);
            color:#e6f0f6; margin:0; -webkit-font-smoothing:antialiased; display:flex; flex-direction:column; min-height:100vh;
        }

        .container{ max-width:1100px; margin:0 auto; padding:28px 20px; }
        .main{ flex:1; display:block }

        /* Hero */
        .hero{
            display:flex; gap:28px; align-items:center; justify-content:space-between; padding:56px; background: linear-gradient(90deg,var(--accent-1), var(--accent-2)); color:#042031; border-radius:var(--radius-lg); margin:28px 0; box-shadow: var(--shadow);
            position:relative; overflow:hidden;
        }

        /* subtle animated accent shape */
        .hero::after{
            content:''; position:absolute; right:-10%; top:-20%; width:380px; height:380px; background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.06), transparent 30%);
            transform: rotate(25deg); opacity:0.9; pointer-events:none;
        }

        .hero-left{ max-width:62%; }
        .hero h1{ font-size:44px; margin:0 0 12px; line-height:1.02; color:#00151b; letter-spacing:-0.5px }
        .hero p{ margin:0 0 18px; color:rgba(0,21,26,0.85); font-weight:600 }

        .cta{ display:inline-block; background:linear-gradient(90deg,#00151b, #012a35); color:var(--accent-2); padding:14px 22px; border-radius:12px; font-weight:800; text-decoration:none; box-shadow: 0 10px 30px rgba(0,167,255,0.12); transition: transform .18s ease, box-shadow .18s ease; }
        .cta:hover{ transform: translateY(-4px); box-shadow: 0 20px 40px rgba(0,212,255,0.14); }
        .cta:focus{ outline: 3px solid rgba(0,167,255,0.14); outline-offset:4px }

        .hero-visual{ flex:1; display:flex; justify-content:flex-end }
        .glass-card{ background:linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01)); border-radius:12px; padding:14px; backdrop-filter: blur(6px); box-shadow: 0 10px 30px rgba(2,12,27,0.5); color:#dff6ff }

        /* Features */
        .features{ display:grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap:18px; margin:28px 0 }
        .feature-card{ background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); border-radius:12px; padding:22px; text-align:left; box-shadow: 0 8px 26px rgba(2,12,27,0.55); display:flex; gap:14px; align-items:flex-start; transition: transform .18s ease, box-shadow .18s ease }
        .feature-card:hover{ transform: translateY(-6px); box-shadow: 0 18px 40px rgba(2,12,27,0.6) }

        .feature-icon{ width:64px; height:64px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:26px; background: linear-gradient(135deg,var(--accent-1),var(--accent-2)); color:#00151b; box-shadow: 0 8px 20px rgba(2,12,27,0.5) }
        .feature-card h3{ color:#e6f0f6; margin:0 0 6px; font-size:18px }
        .feature-card p{ margin:0; color:#9aa6b2 }

        /* subtle entrance animation */
        .feature-card, .stat, .glass-card{ opacity:0; transform: translateY(8px); animation: fadeUp .48s ease forwards; }
        .feature-card:nth-child(1){ animation-delay: 0.06s }
        .feature-card:nth-child(2){ animation-delay: 0.12s }
        .feature-card:nth-child(3){ animation-delay: 0.18s }

        @keyframes fadeUp{ to { opacity:1; transform:none } }

        @media (max-width:900px){ .hero{ flex-direction:column; text-align:left } .hero-left{ max-width:100% } .hero-visual{ justify-content:flex-start; width:100% } }
        @media (max-width:520px){ .hero{ padding:26px } .hero h1{ font-size:28px } .feature-icon{ width:52px; height:52px } }
    </style>
</head>

<body>

<!-- NAVBAR -->
<?php include 'components/navbar.php'; ?>

<!-- MAIN (fills available vertical space so footer stays at bottom) -->
<main class="main container fo-page">

<!-- HERO -->
<?php $homeUt = strtolower((string) ($u['type'] ?? '')); ?>
<section class="hero">
    <div class="hero-left">
        <h1>Bonjour, <?= htmlspecialchars(trim((string) ($u['prenom'] ?? '') . ' ' . (string) ($u['nom'] ?? ''))) ?></h1>
        <p>
            Boutique ProLink : le catalogue affiche les priorités stock définies par la gestion d’achats.
            <?php if ($homeUt === 'entrepreneur' || $homeUt === 'candidat'): ?>
                Répondez aux consultations ouvertes depuis « Appels d’offres » dans le menu.
            <?php endif; ?>
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:4px">
            <a href="catalogue.php" class="cta">Ouvrir le catalogue</a>
            <a href="mesCommandes.php" class="cta" style="background:linear-gradient(90deg,#0c4a6e,#0369a1);color:#ecfeff">Mes commandes</a>
            <?php if ($homeUt === 'entrepreneur' || $homeUt === 'candidat'): ?>
                <a href="mesAppelsOffres.php" class="cta" style="background:linear-gradient(90deg,#1e1b4b,#4c1d95);color:#e9d5ff">Appels d’offres</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="hero-visual">
        <div class="glass-card">
            <strong style="font-size:15px;display:block">Effets du pilotage achats</strong>
            <p style="margin:10px 0 0;font-size:13px;line-height:1.45;opacity:.92">
                Pastilles « Priorité réappro » et « Stock surveillé » sur les produits concernés — sans bloquer la commande.
            </p>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section>
    <div class="features">
        <div class="feature-card">
            <div style="font-size:28px">🌐</div>
            <h3>Réseau</h3>
            <p class="hint">Ajoutez et interagissez avec des professionnels pour étendre votre réseau.</p>
        </div>

        <div class="feature-card">
            <div style="font-size:28px">📁</div>
            <h3>Projets</h3>
            <p class="hint">Publiez et collaborez sur des projets, trouvez des partenaires.</p>
        </div>

        <div class="feature-card">
            <div style="font-size:28px">🛒</div>
            <h3>Achat / Vente</h3>
            <p class="hint">Catalogue, panier, commandes, suivi livraison — avec indicateurs stock alignés sur le réapprovisionnement.</p>
            <a href="catalogue.php" class="cta" style="margin-top:12px;display:inline-block;font-size:14px;padding:10px 16px">Voir la boutique</a>
        </div>
    </div>

</section>

</main>

<!-- FOOTER -->
<?php include 'components/footer.php'; ?>

</body>
</html>