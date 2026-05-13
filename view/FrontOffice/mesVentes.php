<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../controller/AuthController.php';
<<<<<<< HEAD
require_once __DIR__ . '/../../controller/CommandeController.php';
=======
require_once __DIR__ . '/../../controller/CommandeP.php';
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5

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

<<<<<<< HEAD
$cp = new CommandeController();
=======
$cp = new CommandeP();
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
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
<<<<<<< HEAD
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Commandes contenant mes produits</h1>
        <p class="fo-lead">Vue vendeur : le suivi détaillé (statut, numéro de suivi) est géré par l’administrateur.</p>
    </header>
    <?php if (empty($list)): ?>
        <div class="fo-empty">
            <p class="hint" style="margin:0 0 12px">Aucune commande pour vos références.</p>
            <a href="mesProduits.php">Gérer mes produits</a>
        </div>
    <?php else: ?>
        <div class="fo-table-wrap">
        <table class="table-modern">
            <thead><tr><th>#</th><th>Date</th><th>Acheteur</th><th>Montant</th><th>Statut</th><th>Suivi</th></tr></thead>
            <tbody>
            <?php foreach ($list as $c):
                $st = $c['statut'] ?? '';
                $badgeClass = 'fo-badge fo-badge--' . preg_replace('/[^a-z0-9_]/', '', $st);
            ?>
                <tr>
                    <td><strong>#<?= (int) $c['idcommande'] ?></strong></td>
                    <td><?= htmlspecialchars($c['date_commande']) ?></td>
                    <td><?= htmlspecialchars(trim(($c['prenom'] ?? '') . ' ' . ($c['nom'] ?? ''))) ?></td>
                    <td><?= number_format((float) $c['montant_total'], 3, ',', ' ') ?> TND</td>
                    <td><span class="<?= htmlspecialchars($badgeClass) ?>"><?= htmlspecialchars($labels[$st] ?? $st) ?></span></td>
=======
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
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
                    <td><?= htmlspecialchars((string) ($c['numero_suivi'] ?? '—')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
<<<<<<< HEAD
        </div>
=======
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
