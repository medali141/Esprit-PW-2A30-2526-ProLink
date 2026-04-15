<?php
// Ensure session is started before any output. This prevents "headers already sent" warnings
// and makes session data available to the navbar.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$__nav_user = $_SESSION['user'] ?? null;

// Try to compute project root (folder inside htdocs). Falls back to empty string.
$projectFolder = basename(dirname(__DIR__, 3));
$root = $projectFolder ? '/' . $projectFolder : '';
$viewRoot = $root . '/view';
// Build absolute base URL (http(s)://host{projectFolder}/view) to avoid wrong root-relative links
$host = $_SERVER['HTTP_HOST'] ?? '';
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl = $host ? $scheme . '://' . $host . $viewRoot : $viewRoot;
?>

<nav class="navbar">
    <div class="logo">ProLink</div>

    <ul class="nav-links">
    <li><a href="<?= $baseUrl ?>/FrontOffice/home.php">Accueil</a></li>
        <li><a href="#">Réseau</a></li>
        <li><a href="#">Projets</a></li>
        <li><a href="#">Événements</a></li>
    </ul>

<!-- global stylesheet -->
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/style.css">

     <div class="auth">
        <?php if ($__nav_user): ?>
            <a href="<?= $baseUrl ?>/FrontOffice/profile.php" class="btn login">Bonjour, <?= htmlspecialchars($__nav_user['prenom'] ?? $__nav_user['nom'] ?? 'Utilisateur') ?></a>
            <a href="<?= $baseUrl ?>/FrontOffice/profile.php?action=logout" class="btn register">Se déconnecter</a>
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
</style>