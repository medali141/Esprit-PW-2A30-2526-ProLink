<?php
require_once __DIR__ . '/../../../../controller/AuthController.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$auth = new AuthController();
$user = $auth->profile();
if (!$user || strtolower($user['type'] ?? '') !== 'admin') {
    header('Location: ../../../login.php');
    exit;
}
require_once __DIR__ . '/../../../../controller/CommandeP.php';
$cp = new CommandeP();
$list = $cp->listAllAdmin();

$labels = [
    'brouillon' => 'Brouillon',
    'en_attente_paiement' => 'En attente paiement',
    'payee' => 'Payée',
    'en_preparation' => 'En préparation',
    'expediee' => 'Expédiée',
    'livree' => 'Livrée',
    'annulee' => 'Annulée',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commandes — BackOffice</title>
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
<div class="content">
    <div class="container">
        <div class="topbar">
            <div class="page-title">Commandes</div>
            <div class="actions">
                <input class="search-input" placeholder="Rechercher..." id="searchInput">
                <a href="commerceHub.php" class="btn btn-secondary">Hub commerce</a>
            </div>
        </div>
        <table class="table-modern" id="dataTable">
            <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Acheteur</th>
                <th>Montant</th>
                <th>Statut</th>
                <th>Ville</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($list as $c) { ?>
                <tr>
                    <td><?= (int) $c['idcommande'] ?></td>
                    <td><?= htmlspecialchars($c['date_commande']) ?></td>
                    <td><?= htmlspecialchars(trim(($c['prenom'] ?? '') . ' ' . ($c['nom'] ?? ''))) ?><br><span class="hint"><?= htmlspecialchars($c['email'] ?? '') ?></span></td>
                    <td><?= number_format((float) $c['montant_total'], 3, ',', ' ') ?> TND</td>
                    <td><?= htmlspecialchars($labels[$c['statut']] ?? $c['statut']) ?></td>
                    <td><?= htmlspecialchars($c['ville'] ?? '') ?></td>
                    <td><a class="btn btn-secondary" href="detailCommande.php?id=<?= (int) $c['idcommande'] ?>">Détail / suivi</a></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<script>
document.getElementById('searchInput').addEventListener('input', function(e){
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('#dataTable tbody tr').forEach(r => {
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
</body>
</html>
