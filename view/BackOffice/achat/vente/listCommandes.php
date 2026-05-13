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
require_once __DIR__ . '/../../../../controller/CommandeController.php';
$cp = new CommandeController();
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Commandes — Commerce</title>
    <link rel="stylesheet" href="../../commerce.css">
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
<div class="content commerce-page">
    <div class="container">
        <div class="topbar">
            <div>
                <div class="page-title">Commandes clients</div>
                <p class="hint" style="margin:6px 0 0">Montants TND · statuts · livraison</p>
            </div>
            <div class="actions">
                <input class="search-input" placeholder="Rechercher…" id="searchInput" aria-label="Filtrer le tableau">
                <a href="commerceHub.php" class="btn btn-secondary">← Hub</a>
            </div>
        </div>
        <div class="commerce-table-wrap">
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
            <?php foreach ($list as $c) {
                $st = $c['statut'] ?? '';
                $badgeClass = 'commerce-badge commerce-badge--' . preg_replace('/[^a-z0-9_]/', '', $st);
            ?>
                <tr>
                    <td><strong>#<?= (int) $c['idcommande'] ?></strong></td>
                    <td><?= htmlspecialchars($c['date_commande']) ?></td>
                    <td><?= htmlspecialchars(trim(($c['prenom'] ?? '') . ' ' . ($c['nom'] ?? ''))) ?><br><span class="hint"><?= htmlspecialchars($c['email'] ?? '') ?></span></td>
                    <td><strong><?= number_format((float) $c['montant_total'], 3, ',', ' ') ?> TND</strong></td>
                    <td><span class="<?= htmlspecialchars($badgeClass) ?>"><?= htmlspecialchars($labels[$st] ?? $st) ?></span></td>
                    <td><?= htmlspecialchars($c['ville'] ?? '') ?></td>
                    <td><a class="btn btn-secondary" href="detailCommande.php?id=<?= (int) $c['idcommande'] ?>">Détail</a></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<script>
document.getElementById('searchInput').addEventListener('input', function(e){
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('#dataTable tbody tr').forEach(function(r){
        r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
</body>
</html>
