<?php
<<<<<<< HEAD
// Ensure the common init/bootstrap is loaded. init.php sets up session and $baseUrl.
if (!defined('APP_INIT')) {
    require_once dirname(__DIR__, 3) . '/init.php';
}

$__nav_user = $_SESSION['user'] ?? null;
$__nav_type = strtolower($__nav_user['type'] ?? '');
$__cart = (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) ? array_sum($_SESSION['cart']) : 0;
=======
// Ensure session is started before any output. This prevents "headers already sent" warnings
// and makes session data available to the navbar.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$__nav_user = $_SESSION['user'] ?? null;
$__nav_type = strtolower($__nav_user['type'] ?? '');
$__cart = (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) ? array_sum($_SESSION['cart']) : 0;

// Try to compute project root (folder inside htdocs). Falls back to empty string.
$projectFolder = basename(dirname(__DIR__, 3));
$root = $projectFolder ? '/' . $projectFolder : '';
$viewRoot = $root . '/view';
// Build absolute base URL (http(s)://host{projectFolder}/view) to avoid wrong root-relative links
$host = $_SERVER['HTTP_HOST'] ?? '';
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl = $host ? $scheme . '://' . $host . $viewRoot : $viewRoot;
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
?>
<script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>

<nav class="navbar">
    <div class="logo">ProLink</div>

    <ul class="nav-links">
    <li><a href="<?= $baseUrl ?>/FrontOffice/home.php">Accueil</a></li>
        <li><a href="<?= $baseUrl ?>/FrontOffice/catalogue.php">Boutique</a></li>
        <li><a href="<?= $baseUrl ?>/FrontOffice/panier.php">Panier<?= $__cart > 0 ? ' (' . (int) $__cart . ')' : '' ?></a></li>
        <?php if ($__nav_user): ?>
            <li><a href="<?= $baseUrl ?>/FrontOffice/mesCommandes.php">Mes commandes</a></li>
            <?php if ($__nav_type === 'entrepreneur'): ?>
                <li><a href="<?= $baseUrl ?>/FrontOffice/mesProduits.php">Mes produits</a></li>
                <li><a href="<?= $baseUrl ?>/FrontOffice/mesVentes.php">Mes ventes</a></li>
            <?php endif; ?>
        <?php endif; ?>
<<<<<<< HEAD
    <li><a href="#">Réseau</a></li>
    <li><a href="<?= $baseUrl ?>/FrontOffice/projects.php">Projets</a></li>
    <li><a href="<?= $baseUrl ?>/FrontOffice/formation.php">Formations</a></li>
        <li><a href="<?= $baseUrl ?>/FrontOffice/evenements.php">Événements</a></li>
        <li><a href="<?= $baseUrl ?>/FrontOffice/forum.php">Forum</a></li>
=======
        <li><a href="#">Réseau</a></li>
        <li><a href="#">Projets</a></li>
        <li><a href="#">Événements</a></li>
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
    </ul>

<!-- global stylesheet -->
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/style.css">

     <div class="auth">
        <button type="button" class="theme-toggle js-theme-toggle" aria-label="Activer le mode sombre" aria-pressed="false">🌙</button>
        <?php if ($__nav_user): ?>
<<<<<<< HEAD
            <a href="<?= $baseUrl ?>/FrontOffice/profile/profile.php" class="btn login">Bonjour, <?= htmlspecialchars($__nav_user['prenom'] ?? $__nav_user['nom'] ?? 'Utilisateur') ?></a>
            <a href="<?= $baseUrl ?>/FrontOffice/profile/profile.php?action=logout" class="btn register">Se déconnecter</a>
=======
            <a href="<?= $baseUrl ?>/FrontOffice/profile.php" class="btn login">Bonjour, <?= htmlspecialchars($__nav_user['prenom'] ?? $__nav_user['nom'] ?? 'Utilisateur') ?></a>
            <a href="<?= $baseUrl ?>/FrontOffice/profile.php?action=logout" class="btn register">Se déconnecter</a>
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
        <?php else: ?>
            <a href="<?= $baseUrl ?>/login.php" class="btn login">Login</a>
            <a href="<?= $baseUrl ?>/register.php" class="btn register">Register</a>
        <?php endif; ?>
    </div>
</nav>

<style>
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 50px;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.logo {
    font-size: 22px;
    font-weight: bold;
    color: #0073b1;
}

.nav-links {
    list-style: none;
    display: flex;
    gap: 20px;
}

.nav-links a {
    text-decoration: none;
    color: #333;
    font-weight: 500;
}

.nav-links a:hover {
    color: #0073b1;
}

.auth .btn {
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    margin-left: 10px;
}

.login {
    border: 1px solid #0073b1;
    color: #0073b1;
}

.register {
    background: #0073b1;
    color: white;
}

.auth {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

html.dark-mode .navbar {
    background: #121826 !important;
    box-shadow: 0 4px 24px rgba(0,0,0,0.35) !important;
}
html.dark-mode .logo { color: #38bdf8 !important; }
html.dark-mode .nav-links a { color: #e2e8f0 !important; }
html.dark-mode .nav-links a:hover { color: #38bdf8 !important; }
html.dark-mode .login {
    border-color: rgba(56,189,248,0.45) !important;
    color: #38bdf8 !important;
}
html.dark-mode .register {
    background: #38bdf8 !important;
    color: #0f1724 !important;
}
</style>
<script src="<?= $baseUrl ?>/assets/theme.js" defer></script>