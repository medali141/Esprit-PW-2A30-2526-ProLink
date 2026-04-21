<?php /* Sidebar partial - styles are in sidebar.css (same directory) */ ?>
<script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
<!-- Global modern stylesheet (shared with FrontOffice) -->
<link rel="stylesheet" href="../assets/style.css">
<link rel="stylesheet" href="sidebar.css">

<div class="sidebar" aria-label="Navigation administration">
    <header class="sidebar-header">
        <div class="brand">ProLink</div>
        <span class="brand-sub">Administration</span>
    </header>

    <nav class="sidebar-nav" aria-label="Menu principal">
        <ul>
            <li><a href="dashboard.php"><span class="icon">🏠</span> <span>Dashboard</span></a></li>
            <li><a href="listeUser.php"><span class="icon">👤</span> <span>Gestion Users</span></a></li>
            <li><a href="#"><span class="icon">📁</span> <span>Gestion Projets</span></a></li>
            <li><a href="#"><span class="icon">📅</span> <span>Gestion Events</span></a></li>
            <li><a href="commerceHub.php"><span class="icon">🛒</span> <span>Gestion vente / achat</span></a></li>
            
            <!-- Gestion Formations avec sous-menu -->
            <li class="has-submenu">
                <a href="javascript:void(0)" class="submenu-toggle">
                    <span class="icon">🎓</span> <span>Gestion formations</span> <span class="arrow">▼</span>
                </a>
                <ul class="submenu" style="display:none; padding-left: 30px; list-style: none;">
                    <li><a href="formation/liste.php">📋 Liste des formations</a></li>
                    <li><a href="formation/ajouter.php">➕ Ajouter une formation</a></li>
                    <li><a href="categories/gestionCategorie.php">📁 Gestion des catégories</a></li>
                    <li><a href="formation/inscriptions.php">📝 Voir les inscriptions</a></li>
                </ul>
            </li>
            
            <li><a href="profile_admin.php"><span class="icon">👤</span> <span>Mon profil (admin)</span></a></li>
            <li><a href="../logout.php"><span class="icon">🔓</span> <span>Se déconnecter</span></a></li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <button type="button" class="theme-toggle-sidebar js-theme-toggle" aria-label="Activer le mode sombre" aria-pressed="false">🌙</button>
    </div>
</div>

<script>
// Script pour le toggle du sous-menu
document.querySelectorAll('.submenu-toggle').forEach(function(toggle) {
    toggle.addEventListener('click', function(e) {
        e.preventDefault();
        let submenu = this.nextElementSibling;
        if(submenu && submenu.classList.contains('submenu')) {
            if(submenu.style.display === 'none' || submenu.style.display === '') {
                submenu.style.display = 'block';
                this.querySelector('.arrow').innerHTML = '▲';
            } else {
                submenu.style.display = 'none';
                this.querySelector('.arrow').innerHTML = '▼';
            }
        }
    });
});
</script>

<!-- Delete confirmation modal (global) -->
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
<script src="../assets/forms-validation.js"></script>
<script src="../assets/theme.js"></script>
<script src="backoffice.js"></script>