<?php
require_once __DIR__ . '/../../controller/AuthController.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$auth = new AuthController();
$user = $auth->profile();
if (!$user || strtolower($user['type'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require_once __DIR__ . '/../../controller/CommandeController.php';
require_once __DIR__ . '/../../model/CommerceMetier.php';
$cp = new CommandeController();
$q = trim((string) ($_GET['q'] ?? ''));
$tri = (string) ($_GET['tri'] ?? 'date');
$triAllowed = ['date', 'id', 'montant', 'statut', 'ville'];
if (!in_array($tri, $triAllowed, true)) {
    $tri = 'date';
}
$ordre = strtolower((string) ($_GET['ordre'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
$statut = (string) ($_GET['statut'] ?? '');
$allowedStatuts = [
    'brouillon', 'en_attente_paiement', 'payee', 'en_preparation',
    'expediee', 'livree', 'annulee',
];
if ($statut !== '' && !in_array($statut, $allowedStatuts, true)) {
    $statut = '';
}
$list = $cp->listAllAdminFiltered($q, $tri, $ordre, $statut);
$page = CommerceMetier::sanitizePage((int) ($_GET['page'] ?? 1));
$perPage = 12;
$totalRows = count($list);
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page = CommerceMetier::sanitizePage($page, $totalPages);
$start = ($page - 1) * $perPage;
$rows = array_slice($list, $start, $perPage);

$labels = [
    'brouillon' => 'Brouillon',
    'en_attente_paiement' => 'En attente paiement',
    'payee' => 'Payée',
    'en_preparation' => 'En préparation',
    'expediee' => 'Expédiée',
    'livree' => 'Livrée',
    'annulee' => 'Annulée',
];
$paymentLabels = [
    'card' => 'Carte bancaire',
    'cash_on_delivery' => 'Cash livraison',
];
$triOpts = [
    'date' => 'Date',
    'id' => 'N° commande',
    'montant' => 'Montant',
    'statut' => 'Statut',
    'ville' => 'Ville',
];
$activeFilters = [];
if ($q !== '') {
    $activeFilters[] = ['k' => 'Recherche', 'v' => $q];
}
if ($statut !== '') {
    $activeFilters[] = ['k' => 'Statut', 'v' => $labels[$statut] ?? $statut];
}
if ($tri !== 'date') {
    $activeFilters[] = ['k' => 'Tri', 'v' => $triOpts[$tri] ?? $tri];
}
if ($ordre !== 'desc') {
    $activeFilters[] = ['k' => 'Ordre', 'v' => 'Croissant'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Commandes — Commerce</title>
</head>
<body>
<?php include 'sidebar.php'; ?>
<link rel="stylesheet" href="commerce.css">
<div class="content commerce-page">
    <div class="container">
        <div class="topbar">
            <div>
                <div class="page-title">Commandes clients</div>
                <p class="hint" style="margin:6px 0 0">Montants TND · statuts · livraison</p>
            </div>
            <div class="actions">
                <a href="commerceHub.php" class="btn btn-secondary">← Hub</a>
            </div>
        </div>
        <form method="get" class="commerce-filters" action="listCommandes.php" aria-label="Recherche et tri commandes" data-enhanced="1">
            <div class="commerce-filters__field">
                <label for="c-q">Recherche</label>
                <input class="search-input" type="text" name="q" id="c-q" value="<?= htmlspecialchars($q) ?>" placeholder="N°, acheteur, ville…">
            </div>
            <div class="commerce-filters__field">
                <label for="c-statut">Statut</label>
                <select name="statut" id="c-statut">
                    <option value="" <?= $statut === '' ? 'selected' : '' ?>>Tous les statuts</option>
                    <?php foreach ($labels as $k => $lab): ?>
                        <option value="<?= htmlspecialchars($k) ?>" <?= $statut === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="commerce-filters__field">
                <label for="c-tri">Trier par</label>
                <select name="tri" id="c-tri">
                    <?php foreach ($triOpts as $k => $lab): ?>
                        <option value="<?= htmlspecialchars($k) ?>" <?= $tri === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="commerce-filters__field">
                <label for="c-ordre">Ordre</label>
                <select name="ordre" id="c-ordre">
                    <option value="desc" <?= $ordre === 'desc' ? 'selected' : '' ?>>Décroissant</option>
                    <option value="asc" <?= $ordre === 'asc' ? 'selected' : '' ?>>Croissant</option>
                </select>
            </div>
            <div class="commerce-filters__actions">
                <button type="submit" class="btn btn-primary">Appliquer</button>
                <a class="btn btn-secondary" href="listCommandes.php">Réinitialiser</a>
            </div>
        </form>
        <?php if (!empty($activeFilters)): ?>
            <div class="commerce-active-filters" aria-label="Filtres actifs">
                <?php foreach ($activeFilters as $f): ?>
                    <span class="commerce-active-filter-chip"><strong><?= htmlspecialchars($f['k']) ?>:</strong> <?= htmlspecialchars($f['v']) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($q !== '' || $statut !== ''): ?>
            <p class="commerce-result-hint"><?= $totalRows ?> résultat(s)</p>
        <?php endif; ?>
        <div class="commerce-table-wrap">
        <table class="table-modern" id="dataTable">
            <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Acheteur</th>
                <th>Articles</th>
                <th>Lignes</th>
                <th>Montant</th>
                <th>Paiement</th>
                <th>Statut</th>
                <th>Ville</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $c) {
                $st = $c['statut'] ?? '';
                $payMode = (string) ($c['mode_paiement'] ?? 'cash_on_delivery');
                $badgeClass = 'commerce-badge commerce-badge--' . preg_replace('/[^a-z0-9_]/', '', $st);
            ?>
                <tr>
                    <td><strong>#<?= (int) $c['idcommande'] ?></strong></td>
                    <td><?= htmlspecialchars($c['date_commande']) ?></td>
                    <td><?= htmlspecialchars(trim(($c['prenom'] ?? '') . ' ' . ($c['nom'] ?? ''))) ?><br><span class="hint"><?= htmlspecialchars($c['email'] ?? '') ?></span></td>
                    <td><?= (int) ($c['nb_articles'] ?? 0) ?></td>
                    <td><?= (int) ($c['nb_lignes'] ?? 0) ?></td>
                    <td><strong><?= number_format((float) $c['montant_total'], 3, ',', ' ') ?> TND</strong></td>
                    <td><?= htmlspecialchars($paymentLabels[$payMode] ?? $payMode) ?></td>
                    <td><span class="<?= htmlspecialchars($badgeClass) ?>"><?= htmlspecialchars($labels[$st] ?? $st) ?></span></td>
                    <td><?= htmlspecialchars($c['ville'] ?? '') ?></td>
                    <td><a class="btn btn-secondary" href="detailCommande.php?id=<?= (int) $c['idcommande'] ?>">Détail</a></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        </div>
        <?php if ($totalPages > 1): ?>
            <div class="commerce-filters__actions" style="margin-top:12px">
                <?php if ($page > 1): ?>
                    <a class="btn btn-secondary" href="?q=<?= urlencode($q) ?>&statut=<?= urlencode($statut) ?>&tri=<?= urlencode($tri) ?>&ordre=<?= urlencode($ordre) ?>&page=<?= $page - 1 ?>">Précédent</a>
                <?php endif; ?>
                <span class="commerce-result-hint">Page <?= $page ?> / <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a class="btn btn-secondary" href="?q=<?= urlencode($q) ?>&statut=<?= urlencode($statut) ?>&tri=<?= urlencode($tri) ?>&ordre=<?= urlencode($ordre) ?>&page=<?= $page + 1 ?>">Suivant</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
(function () {
    var form = document.querySelector('.commerce-filters[data-enhanced="1"]');
    if (!form) return;
    var selects = form.querySelectorAll('select');
    for (var i = 0; i < selects.length; i++) {
        selects[i].addEventListener('change', function () { form.submit(); });
    }
})();
</script>
</body>
</html>
