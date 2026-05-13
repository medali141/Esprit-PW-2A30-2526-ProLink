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
            <li><a href="listUsers.php"><span class="icon">👤</span> <span>Gestion Users</span></a></li>
             <li><a href="#"><span class="icon">📁</span> <span>Gestion Projets</span></a></li>
            <li><a href="#"><span class="icon">📅</span> <span>Gestion Events</span></a></li>
            <li><a href="commerceHub.php"><span class="icon">🛒</span> <span>Gestion vente / achat</span></a></li>
            <li><a href="#"><span class="icon">🎓</span> <span>Gestion des formation</span></a></li>
            <li><a href="profile_admin.php"><span class="icon">👤</span> <span>Mon profil (admin)</span></a></li>
            <li><a href="../logout.php"><span class="icon">🔓</span> <span>Se déconnecter</span></a></li>
           
        </ul>
    </nav>
    <div class="sidebar-footer">
        <button type="button" class="theme-toggle-sidebar js-theme-toggle" aria-label="Activer le mode sombre" aria-pressed="false">🌙</button>
    </div>
</div>
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