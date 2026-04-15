<?php /* Sidebar partial - styles are in sidebar.css (same directory) */ ?>
<link rel="stylesheet" href="sidebar.css">

<div class="sidebar">
    <div class="brand">ProLink</div>

    <nav>
        <ul>
            <li><a href="dashboard.php"><span class="icon">🏠</span> <span>Dashboard</span></a></li>
            <li><a href="listUsers.php"><span class="icon">👤</span> <span>Gestion Users</span></a></li>
            <li><a href="#"><span class="icon">📁</span> <span>Gestion Projets</span></a></li>
            <li><a href="#"><span class="icon">📅</span> <span>Gestion Events</span></a></li>
        </ul>
    </nav>
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
<script src="backoffice.js"></script>