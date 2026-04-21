<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$user = $_SESSION['user'] ?? null;
?>

<nav class="navbar">
    <div class="logo">ProLink</div>
    <ul class="nav-links">
        <li><a href="/FrontOffice/home.php">Accueil</a></li>
        <li><a href="/FrontOffice/formations/index.php">Formations</a></li>
        <li><a href="/FrontOffice/formations/search.php">Recherche</a></li>
        <li><a href="#">Boutique</a></li>
        <li><a href="#">Réseau</a></li>
        <li><a href="#">Projets</a></li>
    </ul>
    <div class="auth">
        <?php if($user): ?>
            <span>Bonjour, <?= htmlspecialchars($user['prenom'] ?? $user['nom']) ?></span>
            <a href="/logout.php">Déconnexion</a>
        <?php else: ?>
            <a href="/login.php">Connexion</a>
            <a href="/register.php">Inscription</a>
        <?php endif; ?>
    </div>
</nav>

<style>
    .navbar { display: flex; justify-content: space-between; align-items: center; background: white; padding: 15px 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .logo { font-size: 24px; font-weight: bold; color: #0073b1; }
    .nav-links { list-style: none; display: flex; gap: 20px; margin: 0; padding: 0; }
    .nav-links li { display: inline; }
    .nav-links a { text-decoration: none; color: #333; font-weight: 500; }
    .nav-links a:hover { color: #0073b1; }
    .auth a { margin-left: 15px; text-decoration: none; color: #0073b1; }
    .auth span { margin-right: 10px; color: #333; }
</style>