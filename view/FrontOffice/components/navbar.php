<?php
// Ensure the common init/bootstrap is loaded. init.php sets up session and $baseUrl.
if (!defined('APP_INIT')) {
    require_once dirname(__DIR__, 3) . '/init.php';
}

$__nav_user = $_SESSION['user'] ?? null;
$__nav_type = strtolower((string) ($__nav_user['type'] ?? ''));
$__cart = (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) ? array_sum($_SESSION['cart']) : 0;
$__points = (int) ($__nav_user['points_fidelite'] ?? 0);
$__cartLabel = $__cart > 0 ? 'Panier, ' . (int) $__cart . ' article' . ($__cart > 1 ? 's' : '') : 'Panier';
$__assistantJsVer = (int) (@filemtime(dirname(__DIR__, 2) . '/assets/front-assistant.js') ?: time());
$__storefrontCssVer = (int) (@filemtime(dirname(__DIR__, 2) . '/assets/storefront.css') ?: time());
?>
<script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/storefront.css?v=<?= $__storefrontCssVer ?>">

<nav class="navbar fo-topnav" aria-label="Navigation principale">
    <div class="fo-topnav__bar">
        <a class="logo" href="<?= $baseUrl ?>/FrontOffice/home.php">Pro<span class="logo__accent">Link</span></a>

        <button type="button" class="fo-nav-toggle js-nav-toggle" aria-expanded="false" aria-controls="fo-nav-panel">
            <span class="fo-nav-toggle__bars" aria-hidden="true"><span></span><span></span><span></span></span>
            <span class="fo-nav-toggle__label">Menu</span>
        </button>

        <div class="fo-nav-panel" id="fo-nav-panel">
            <ul class="nav-links">
                <li><a href="<?= $baseUrl ?>/FrontOffice/home.php">Accueil</a></li>
                <li><a href="<?= $baseUrl ?>/FrontOffice/catalogue.php">Boutique</a></li>
                <li>
                    <a href="<?= $baseUrl ?>/FrontOffice/panier.php" class="nav-link-cart" aria-label="<?= htmlspecialchars($__cartLabel) ?>">
                        Panier<?php if ($__cart > 0): ?>
                            <span class="fo-cart-badge" aria-hidden="true"><?= (int) $__cart ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if ($__nav_user): ?>
                    <li><a href="<?= $baseUrl ?>/FrontOffice/mesCommandes.php">Mes commandes</a></li>
                    <li><a href="<?= $baseUrl ?>/FrontOffice/reclamationsCommandes.php">Réclamations</a></li>
                    <li><a href="<?= $baseUrl ?>/FrontOffice/messagesAdmin.php">Contact service clients</a></li>
                    <?php if ($__nav_type === 'entrepreneur'): ?>
                        <li><a href="<?= $baseUrl ?>/FrontOffice/mesProduits.php">Mes produits</a></li>
                        <li><a href="<?= $baseUrl ?>/FrontOffice/mesVentes.php">Mes ventes</a></li>
                    <?php endif; ?>
                    <?php if ($__nav_type === 'entrepreneur' || $__nav_type === 'candidat'): ?>
                        <li><a href="<?= $baseUrl ?>/FrontOffice/mesAppelsOffres.php">Appels d’offres</a></li>
                    <?php endif; ?>
                    <li><a href="<?= $baseUrl ?>/FrontOffice/mesCommandesStats.php">Stats achats</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="auth fo-topnav__auth">
            <button type="button" class="theme-toggle js-theme-toggle" aria-label="Basculer le thème clair ou sombre" aria-pressed="false">☀️</button>
            <?php if ($__nav_user): ?>
                <span class="nav-points" title="Points fidélité"><?= $__points ?> pts</span>
                <a href="<?= $baseUrl ?>/FrontOffice/profile.php" class="btn login"><?= htmlspecialchars(trim((string) ($__nav_user['prenom'] ?? '') ?: (string) ($__nav_user['nom'] ?? 'Compte'))) ?></a>
                <a href="<?= $baseUrl ?>/FrontOffice/profile.php?action=logout" class="btn register btn-register--muted">Déconnexion</a>
            <?php else: ?>
                <a href="<?= $baseUrl ?>/login.php" class="btn login">Connexion</a>
                <a href="<?= $baseUrl ?>/register.php" class="btn register">Inscription</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<script src="<?= $baseUrl ?>/assets/theme.js" defer></script>
<script>
window.PROLINK_BASE_URL = <?= json_encode((string) $baseUrl) ?>;
</script>
<script src="<?= $baseUrl ?>/assets/front-assistant.js?v=<?= $__assistantJsVer ?>" defer></script>
