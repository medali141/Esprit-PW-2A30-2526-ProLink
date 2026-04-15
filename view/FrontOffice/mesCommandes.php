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

$cp = new CommandeP();
$list = $cp->listByAcheteur((int) $u['iduser']);

$labels = [
    'brouillon' => 'Brouillon',
    'en_attente_paiement' => 'En attente paiement',
    'payee' => 'Payée',
    'en_preparation' => 'En préparation',
    'expediee' => 'Expédiée',
    'livree' => 'Livrée',
    'annulee' => 'Annulée',
];
$newId = isset($_GET['new']) ? (int) $_GET['new'] : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mes commandes — ProLink</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container">
    <h1>Mes commandes</h1>
    <?php if ($newId > 0): ?>
        <p style="color:var(--accent)">Commande #<?= $newId ?> enregistrée. Vous serez notifié du suivi par l’administrateur.</p>
    <?php endif; ?>
    <?php if (empty($list)): ?>
        <p class="hint">Aucune commande. <a href="catalogue.php">Parcourir le catalogue</a></p>
    <?php else: ?>
        <table class="table-modern card" style="margin-top:16px;padding:0;overflow:hidden">
            <thead><tr><th>#</th><th>Date</th><th>Montant</th><th>Statut</th><th>Ville</th><th>Suivi</th></tr></thead>
            <tbody>
            <?php foreach ($list as $c): ?>
                <tr>
                    <td><?= (int) $c['idcommande'] ?></td>
                    <td><?= htmlspecialchars($c['date_commande']) ?></td>
                    <td><?= number_format((float) $c['montant_total'], 3, ',', ' ') ?> TND</td>
                    <td><?= htmlspecialchars($labels[$c['statut']] ?? $c['statut']) ?></td>
                    <td><?= htmlspecialchars($c['ville'] ?? '') ?></td>
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
