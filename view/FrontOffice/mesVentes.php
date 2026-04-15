<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/CommandeP.php';

$auth = new AuthController();
$u = $auth->profile();
if (!$u) {
    header('Location: ../login.php');
    exit;
}
if (strtolower($u['type'] ?? '') !== 'entrepreneur') {
    header('Location: home.php');
    exit;
}

$cp = new CommandeP();
$list = $cp->listByVendeur((int) $u['iduser']);
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
    <title>Mes ventes — ProLink</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container">
    <h1>Commandes contenant mes produits</h1>
    <p class="hint">Vue vendeur : suivi global géré par l’administrateur (statut, numéro de suivi).</p>
    <?php if (empty($list)): ?>
        <p class="hint">Aucune commande pour vos références.</p>
    <?php else: ?>
        <table class="table-modern card" style="margin-top:16px;padding:0">
            <thead><tr><th>#</th><th>Date</th><th>Acheteur</th><th>Montant TTC</th><th>Statut</th><th>Suivi</th></tr></thead>
            <tbody>
            <?php foreach ($list as $c): ?>
                <tr>
                    <td><?= (int) $c['idcommande'] ?></td>
                    <td><?= htmlspecialchars($c['date_commande']) ?></td>
                    <td><?= htmlspecialchars(trim(($c['prenom'] ?? '') . ' ' . ($c['nom'] ?? ''))) ?></td>
                    <td><?= number_format((float) $c['montant_total'], 3, ',', ' ') ?> TND</td>
                    <td><?= htmlspecialchars($labels[$c['statut']] ?? $c['statut']) ?></td>
                    <td><?= htmlspecialchars((string) ($c['numero_suivi'] ?? '—')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
