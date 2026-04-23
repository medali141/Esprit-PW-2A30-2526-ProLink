<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/CommandeController.php';

$auth = new AuthController();
$u = $auth->profile();
if (!$u) {
    header('Location: ../login.php');
    exit;
}

$cp = new CommandeController();
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
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Mes commandes</h1>
        <p class="fo-lead">Historique, montants en TND et numéro de suivi communiqué par l’administrateur.</p>
    </header>
    <?php if ($newId > 0): ?>
        <p class="fo-banner fo-banner--ok" role="status">Commande #<?= $newId ?> enregistrée. Suivi et statut sont gérés côté administration.</p>
    <?php endif; ?>
    <?php if (empty($list)): ?>
        <div class="fo-empty">
            <p class="hint" style="margin:0 0 12px">Aucune commande pour l’instant.</p>
            <a href="catalogue.php">Parcourir le catalogue</a>
        </div>
    <?php else: ?>
        <div class="fo-table-wrap">
        <table class="table-modern">
            <thead><tr><th>#</th><th>Date</th><th>Montant</th><th>Statut</th><th>Ville</th><th>Suivi</th></tr></thead>
            <tbody>
            <?php foreach ($list as $c):
                $st = $c['statut'] ?? '';
                $badgeClass = 'fo-badge fo-badge--' . preg_replace('/[^a-z0-9_]/', '', $st);
            ?>
                <tr>
                    <td><strong>#<?= (int) $c['idcommande'] ?></strong></td>
                    <td><?= htmlspecialchars($c['date_commande']) ?></td>
                    <td><?= number_format((float) $c['montant_total'], 3, ',', ' ') ?> TND</td>
                    <td><span class="<?= htmlspecialchars($badgeClass) ?>"><?= htmlspecialchars($labels[$st] ?? $st) ?></span></td>
                    <td><?= htmlspecialchars($c['ville'] ?? '') ?></td>
                    <td><?= htmlspecialchars((string) ($c['numero_suivi'] ?? '—')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
