<?php
require_once __DIR__ . '/../controller/UserP.php';

$userP = new UserP();
$list = $userP->listUsers();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des utilisateurs - ProLink</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f7fb; }
        
        .content { margin-left: 280px; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .page-title { font-size: 24px; font-weight: bold; color: #333; }
        .actions { display: flex; gap: 10px; align-items: center; }
        .search-input { padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px; width: 250px; }
        
        .btn { padding: 8px 15px; border-radius: 5px; text-decoration: none; display: inline-block; margin: 0 2px; }
        .btn-primary { background: #28a745; color: white; border: none; }
        .btn-secondary { background: #0073b1; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        
        .table-modern { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .table-modern th, .table-modern td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        .table-modern th { background: #4a6fdc; color: white; font-weight: 600; }
        .table-modern tr:hover { background: #f8f9fa; }
        
        @media (max-width: 768px) { .content { margin-left: 0; } }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="content">
    <div class="container">

        <div class="topbar">
            <div class="page-title">📋 Liste des utilisateurs</div>
            <div class="actions">
                <input class="search-input" placeholder="🔍 Rechercher un utilisateur..." id="searchInput">
                <a href="addUser.php" class="btn btn-primary">➕ Ajouter</a>
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
                    <th>Âge</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($list as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['iduser']); ?></td>
                    <td><?= htmlspecialchars($user['nom']); ?></td>
                    <td><?= htmlspecialchars($user['prenom']); ?></td>
                    <td><?= htmlspecialchars($user['email']); ?></td>
                    <td><?= htmlspecialchars($user['type']); ?></td>
                    <td><?= htmlspecialchars($user['age']); ?></td>
                    <td>
                        <a class="btn btn-secondary" href="detailUser.php?id=<?= $user['iduser']; ?>">👁️ Voir</a>
                        <a class="btn btn-secondary" href="updateUser.php?id=<?= $user['iduser']; ?>">✏️ Modifier</a>
                        <a class="btn btn-danger js-delete" href="#" data-confirm="Voulez-vous vraiment supprimer cet utilisateur ?" data-href="deleteUser.php?id=<?= $user['iduser']; ?>">🗑️ Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</div>

<script>
    // Recherche client-side
    document.getElementById('searchInput').addEventListener('input', function(e){
        const q = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#usersTable tbody tr');
        rows.forEach(r => {
            const text = Array.from(r.cells).slice(0, 6).map(c => c.textContent.toLowerCase()).join(' ');
            r.style.display = text.includes(q) ? '' : 'none';
        });
    });
    
    // Confirmation suppression
    document.querySelectorAll('.js-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if(confirm(this.getAttribute('data-confirm'))) {
                window.location.href = this.getAttribute('data-href');
            }
        });
    });
</script>

</body>
</html>