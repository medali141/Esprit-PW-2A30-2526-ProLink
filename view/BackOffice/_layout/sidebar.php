<?php
require_once __DIR__ . '/paths.php';
$__vb = view_web_base();
?>
<?php /* Sidebar — chemins via bo_url() pour compatibilité sous-dossiers */ ?>
<script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
<link rel="stylesheet" href="<?= htmlspecialchars($__vb) ?>assets/style.css">
<link rel="stylesheet" href="<?= htmlspecialchars(bo_url('_layout/sidebar.css')) ?>">

<div class="sidebar" aria-label="Navigation administration">
    <header class="sidebar-header">
        <div class="brand">ProLink</div>
        <span class="brand-sub">Administration</span>
    </header>

    <nav class="sidebar-nav" aria-label="Menu principal">
        <ul>
            <li><a href="<?= htmlspecialchars(bo_url('dashboard/dashboard.php')) ?>"><span class="icon">🏠</span> <span>Dashboard</span></a></li>
            <li><a href="<?= htmlspecialchars(bo_url('user/listUsers.php')) ?>"><span class="icon">👤</span> <span>Gestion Users</span></a></li>
            <li><a href="<?= htmlspecialchars(bo_url('_TODO/a-venir.php?module=projets')) ?>"><span class="icon">📁</span> <span>Gestion Projets</span></a></li>
            <li><a href="<?= htmlspecialchars(bo_url('_TODO/a-venir.php?module=evenements')) ?>"><span class="icon">📅</span> <span>Gestion Events</span></a></li>
            <li><a href="<?= htmlspecialchars(bo_url('achat/vente/commerceHub.php')) ?>"><span class="icon">🛒</span> <span>Gestion vente / achat</span></a></li>
            <li><a href="<?= htmlspecialchars(bo_url('_TODO/a-venir.php?module=formations')) ?>"><span class="icon">🎓</span> <span>Gestion des formation</span></a></li>
            <li><a href="<?= htmlspecialchars(bo_url('user/profile_admin.php')) ?>"><span class="icon">👤</span> <span>Mon profil (admin)</span></a></li>
            <li><a href="<?= htmlspecialchars($__vb) ?>logout.php"><span class="icon">🔓</span> <span>Se déconnecter</span></a></li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <button type="button" class="theme-toggle-sidebar js-theme-toggle" aria-label="Activer le mode sombre" aria-pressed="false">🌙</button>
    </div>
</div>
<div id="confirmModal" class="confirm-modal" aria-hidden="true">
    <div class="confirm-modal-backdrop"></div>
    <div class="confirm-modal-card" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
        <h3 id="confirmTitle">Confirmer la suppression</h3>
        <p id="confirmMessage">Voulez-vous vraiment supprimer cet utilisateur ? Cette action est irréversible.</p>
        <div class="confirm-actions">
            <button class="btn btn-secondary" id="confirmCancel">Annuler</button>
            <button class="btn btn-danger" id="confirmOk">Supprimer</button>
        </div>
    </div>
</div>
<script src="<?= htmlspecialchars($__vb) ?>assets/forms-validation.js"></script>
<script src="<?= htmlspecialchars($__vb) ?>assets/theme.js"></script>
<script src="<?= htmlspecialchars(bo_url('_layout/backoffice.js')) ?>"></script>
