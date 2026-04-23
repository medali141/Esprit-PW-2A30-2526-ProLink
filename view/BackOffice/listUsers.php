<?php
include '../../Controller/UserP.php';

$userP = new UserP();
$list = $userP->listUsers();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des utilisateurs</title>
    <?php /* Styles are in sidebar.css included by sidebar.php */ ?>
    <style>
        .alert{padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600}
        .alert-danger{background:#f8d7da;color:#842029;border:1px solid #f5c2c7}
        .alert-success{background:#d1e7dd;color:#0f5132;border:1px solid #badbcc}
    </style>
</head>

<body>

<?php include 'sidebar.php'; ?>

<div class="content">
    <div class="container">
        <?php if (isset($_GET['error']) && $_GET['error'] === 'hasCommandes'): ?>
            <div class="alert alert-danger">Cet utilisateur possède des commandes et ne peut pas être supprimé.</div>
        <?php elseif (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Utilisateur supprimé avec succès.</div>
        <?php endif; ?>

        <div class="topbar">
            <div class="page-title">Liste des utilisateurs</div>
            <div class="actions">
                <input class="search-input" placeholder="Rechercher un utilisateur..." id="searchInput">
                <a href="addUser.php" class="btn btn-primary">+ Ajouter</a>
            </div>
        </div>

        <table class="table-modern" id="usersTable">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Type</th>
                <th>Age</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($list as $user) { ?>
                <tr>
                    <td><?= htmlspecialchars($user['iduser']); ?></td>
                    <td><?= htmlspecialchars($user['nom']); ?></td>
                    <td><?= htmlspecialchars($user['prenom']); ?></td>
                    <td><?= htmlspecialchars($user['email']); ?></td>
                    <td><?= htmlspecialchars($user['type']); ?></td>
                    <td><?= htmlspecialchars($user['age']); ?></td>
                    <td>
                        <a class="btn btn-secondary" href="detailUser.php?id=<?= $user['iduser']; ?>">Voir</a>
                        <a class="btn btn-secondary" href="updateUser.php?id=<?= $user['iduser']; ?>">Modifier</a>
                        <a class="btn btn-danger js-delete" href="#" data-confirm="Voulez-vous vraiment supprimer cet utilisateur ?" data-href="deleteUser.php?id=<?= $user['iduser']; ?>">Supprimer</a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

    </div>
</div>

<script>
    // small client-side search (non-blocking)
    document.getElementById('searchInput').addEventListener('input', function(e){
        const q = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#usersTable tbody tr');
        rows.forEach(r => {
            r.style.display = Array.from(r.cells).some(c => c.textContent.toLowerCase().includes(q)) ? '' : 'none';
        });
    });
</script>

</body>
</html>
