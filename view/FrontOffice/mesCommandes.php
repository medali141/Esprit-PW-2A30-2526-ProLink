<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/CommandeController.php';
require_once __DIR__ . '/../../model/CommerceRegles.php';

$auth = new AuthController();
$u = $auth->profile();
if (!$u) {
    header('Location: ../login.php');
    exit;
}

$cp = new CommandeController();
$q = trim((string) ($_GET['q'] ?? ''));
$tri = (string) ($_GET['tri'] ?? 'date');
$triAllowed = ['date', 'id', 'montant', 'statut', 'ville'];
if (!in_array($tri, $triAllowed, true)) {
    $tri = 'date';
}
$ordre = strtolower((string) ($_GET['ordre'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
$statut = (string) ($_GET['statut'] ?? '');
$labels = [
    'brouillon' => 'Brouillon',
    'en_attente_paiement' => 'En attente paiement',
    'payee' => 'Payée',
    'en_preparation' => 'En préparation',
    'expediee' => 'En cours de livraison',
    'livree' => 'Livrée',
    'annulee' => 'Annulée',
];
$paymentLabels = [
    'card' => 'Carte bancaire',
    'cash_on_delivery' => 'Cash livraison',
];
$allowedStatuts = array_keys($labels);
if ($statut !== '' && !in_array($statut, $allowedStatuts, true)) {
    $statut = '';
}
$list = $cp->listByAcheteurFiltered((int) $u['iduser'], $q, $tri, $ordre, $statut);
$page = CommerceRegles::sanitizePage((int) ($_GET['page'] ?? 1));
$perPage = 10;
$totalRows = count($list);
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page = CommerceRegles::sanitizePage($page, $totalPages);
$start = ($page - 1) * $perPage;
$rows = array_slice($list, $start, $perPage);
$newId = isset($_GET['new']) ? (int) $_GET['new'] : 0;
$factureNotice = (string) ($_GET['facture'] ?? '');
$pointsTotalVisible = 0;
$amountTotalVisible = 0.0;
foreach ($list as $cmdRow) {
    $stat = (string) ($cmdRow['statut'] ?? '');
    $amountTotalVisible += (float) ($cmdRow['montant_total'] ?? 0);
    if ($stat === 'annulee') {
        continue;
    }
    $pointsTotalVisible += CommerceRegles::pointsFromAmount((float) ($cmdRow['montant_total'] ?? 0));
}
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
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Mes commandes</h1>
        <p class="fo-lead">Historique, montants en TND, points fidélité et numéro de suivi communiqué par l’administrateur.</p>
    </header>
    <div class="fo-track-panel" role="status">
        <strong>Points fidélité estimés (hors commandes annulées): <?= (int) $pointsTotalVisible ?> pts</strong><br>
        <span class="hint">Montant cumulé visible: <?= number_format($amountTotalVisible, 3, ',', ' ') ?> TND · Commandes: <?= (int) $totalRows ?></span>
    </div>
    <div class="fo-actions fo-actions--toolbar fo-actions--dense">
        <a href="mesCommandesStats.php" target="_blank" rel="noopener" class="fo-btn fo-btn--secondary fo-btn--ghost">Voir les statistiques (graphiques)</a>
        <a href="reclamationsCommandes.php" class="fo-btn fo-btn--secondary fo-btn--ghost">Mes réclamations commandes</a>
    </div>
    <?php if ($newId > 0): ?>
        <p class="fo-banner fo-banner--ok" role="status">
            Commande #<?= $newId ?> enregistrée.
            <a href="suiviCommande.php?id=<?= $newId ?>" style="font-weight:800;margin-left:8px">Voir le suivi logistique →</a>
        </p>
    <?php endif; ?>
    <?php if ($factureNotice === 'unavailable'): ?>
        <p class="fo-banner fo-banner--err" role="status">Facture non disponible pour cette commande.</p>
    <?php endif; ?>
    <form method="get" class="fo-filters" action="mesCommandes.php" aria-label="Filtres mes commandes">
        <?php if ($newId > 0): ?>
            <input type="hidden" name="new" value="<?= $newId ?>">
        <?php endif; ?>
        <div class="fo-filters__field">
            <label for="mc-q">Recherche</label>
            <input type="search" name="q" id="mc-q" value="<?= htmlspecialchars($q) ?>" placeholder="N°, ville, suivi…">
        </div>
        <div class="fo-filters__field">
            <label for="mc-statut">Statut</label>
            <select name="statut" id="mc-statut">
                <option value="" <?= $statut === '' ? 'selected' : '' ?>>Tous</option>
                <?php foreach ($labels as $k => $lab): ?>
                    <option value="<?= htmlspecialchars($k) ?>" <?= $statut === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="fo-filters__field">
            <label for="mc-tri">Trier par</label>
            <select name="tri" id="mc-tri">
                <option value="date" <?= $tri === 'date' ? 'selected' : '' ?>>Date</option>
                <option value="id" <?= $tri === 'id' ? 'selected' : '' ?>>N° commande</option>
                <option value="montant" <?= $tri === 'montant' ? 'selected' : '' ?>>Montant</option>
                <option value="statut" <?= $tri === 'statut' ? 'selected' : '' ?>>Statut</option>
                <option value="ville" <?= $tri === 'ville' ? 'selected' : '' ?>>Ville</option>
            </select>
        </div>
        <div class="fo-filters__field">
            <label for="mc-ordre">Ordre</label>
            <select name="ordre" id="mc-ordre">
                <option value="desc" <?= $ordre === 'desc' ? 'selected' : '' ?>>Décroissant</option>
                <option value="asc" <?= $ordre === 'asc' ? 'selected' : '' ?>>Croissant</option>
            </select>
        </div>
        <div class="fo-filters__actions">
            <button type="submit" class="fo-btn fo-btn--primary">Appliquer</button>
            <a href="mesCommandes.php<?= $newId > 0 ? '?new=' . $newId : '' ?>" class="fo-btn fo-btn--secondary fo-btn--ghost">Réinitialiser</a>
        </div>
    </form>
    <?php if ($q !== '' || $statut !== ''): ?>
        <p class="fo-result-hint"><?= $totalRows ?> commande(s)</p>
    <?php endif; ?>
    <?php if (empty($rows)): ?>
        <div class="fo-empty">
            <p class="hint" style="margin:0 0 12px">Aucune commande pour l’instant.</p>
            <a href="catalogue.php">Parcourir le catalogue</a>
        </div>
    <?php else: ?>
        <div class="fo-table-wrap">
        <table class="table-modern">
            <thead><tr><th>#</th><th>Date</th><th>Articles</th><th>Lignes</th><th>Montant</th><th>Paiement</th><th>Points</th><th>Statut</th><th>Ville</th><th>N° suivi</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $c):
                $st = $c['statut'] ?? '';
                $payMode = (string) ($c['mode_paiement'] ?? 'cash_on_delivery');
                $badgeClass = 'fo-badge fo-badge--' . preg_replace('/[^a-z0-9_]/', '', $st);
                $pointsLigne = CommerceRegles::pointsFromAmount((float) ($c['montant_total'] ?? 0));
                $canInvoice = !in_array($st, ['brouillon', 'annulee'], true);
            ?>
                <tr>
                    <td><strong>#<?= (int) $c['idcommande'] ?></strong></td>
                    <td><?= htmlspecialchars($c['date_commande']) ?></td>
                    <td><?= (int) ($c['nb_articles'] ?? 0) ?></td>
                    <td><?= (int) ($c['nb_lignes'] ?? 0) ?></td>
                    <td><?= number_format((float) $c['montant_total'], 3, ',', ' ') ?> TND</td>
                    <td><?= htmlspecialchars($paymentLabels[$payMode] ?? $payMode) ?></td>
                    <td><?= $st === 'annulee' ? '—' : ((int) $pointsLigne . ' pts') ?></td>
                    <td><span class="<?= htmlspecialchars($badgeClass) ?>"><?= htmlspecialchars($labels[$st] ?? $st) ?></span></td>
                    <td><?= htmlspecialchars($c['ville'] ?? '') ?></td>
                    <td><?= htmlspecialchars((string) ($c['numero_suivi'] ?? '—')) ?></td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <a class="fo-btn fo-btn--secondary" style="text-decoration:none;padding:6px 12px;font-size:0.82rem" href="suiviCommande.php?id=<?= (int) $c['idcommande'] ?>">Suivi</a>
                            <a class="fo-btn fo-btn--secondary" style="text-decoration:none;padding:6px 12px;font-size:0.82rem" href="reclamationsCommandes.php?commande=<?= (int) $c['idcommande'] ?>">Réclamation</a>
                            <?php if ($canInvoice): ?>
                                <a class="fo-btn fo-btn--secondary" style="text-decoration:none;padding:6px 12px;font-size:0.82rem" href="factureCommande.php?id=<?= (int) $c['idcommande'] ?>">Facture</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php if ($totalPages > 1): ?>
            <div class="fo-actions fo-actions--pager">
                <?php if ($page > 1): ?>
                    <a class="fo-btn fo-btn--secondary fo-btn--ghost" href="?q=<?= urlencode($q) ?>&statut=<?= urlencode($statut) ?>&tri=<?= urlencode($tri) ?>&ordre=<?= urlencode($ordre) ?>&page=<?= $page - 1 ?>">Précédent</a>
                <?php endif; ?>
                <span class="fo-result-hint fo-result-hint--inline">Page <?= $page ?> / <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a class="fo-btn fo-btn--secondary fo-btn--ghost" href="?q=<?= urlencode($q) ?>&statut=<?= urlencode($statut) ?>&tri=<?= urlencode($tri) ?>&ordre=<?= urlencode($ordre) ?>&page=<?= $page + 1 ?>">Suivant</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
