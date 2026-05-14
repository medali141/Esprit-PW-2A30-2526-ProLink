<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/CommandeController.php';
<<<<<<< HEAD
require_once __DIR__ . '/../../model/CommerceRegles.php';
=======
>>>>>>> formation

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

$cp = new CommandeController();
<<<<<<< HEAD
$q = trim((string) ($_GET['q'] ?? ''));
$tri = (string) ($_GET['tri'] ?? 'date');
$triAllowed = ['date', 'id', 'montant', 'statut', 'ville'];
if (!in_array($tri, $triAllowed, true)) {
    $tri = 'date';
}
$ordre = strtolower((string) ($_GET['ordre'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
$statut = (string) ($_GET['statut'] ?? '');
=======
$list = $cp->listByVendeur((int) $u['iduser']);
>>>>>>> formation
$labels = [
    'brouillon' => 'Brouillon',
    'en_attente_paiement' => 'En attente paiement',
    'payee' => 'Payée',
    'en_preparation' => 'En préparation',
    'expediee' => 'Expédiée',
    'livree' => 'Livrée',
    'annulee' => 'Annulée',
];
<<<<<<< HEAD
$allowedStatuts = array_keys($labels);
if ($statut !== '' && !in_array($statut, $allowedStatuts, true)) {
    $statut = '';
}
$list = $cp->listByVendeurFiltered((int) $u['iduser'], $q, $tri, $ordre, $statut);
$page = CommerceRegles::sanitizePage((int) ($_GET['page'] ?? 1));
$perPage = 10;
$totalRows = count($list);
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page = CommerceRegles::sanitizePage($page, $totalPages);
$start = ($page - 1) * $perPage;
$rows = array_slice($list, $start, $perPage);
=======
>>>>>>> formation
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mes ventes — ProLink</title>
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
<<<<<<< HEAD
    <form method="get" class="fo-filters" action="mesVentes.php" aria-label="Filtres mes ventes">
        <div class="fo-filters__field">
            <label for="mv-q">Recherche</label>
            <input type="search" name="q" id="mv-q" value="<?= htmlspecialchars($q) ?>" placeholder="N°, acheteur, suivi…">
        </div>
        <div class="fo-filters__field">
            <label for="mv-statut">Statut</label>
            <select name="statut" id="mv-statut">
                <option value="" <?= $statut === '' ? 'selected' : '' ?>>Tous</option>
                <?php foreach ($labels as $k => $lab): ?>
                    <option value="<?= htmlspecialchars($k) ?>" <?= $statut === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="fo-filters__field">
            <label for="mv-tri">Trier par</label>
            <select name="tri" id="mv-tri">
                <option value="date" <?= $tri === 'date' ? 'selected' : '' ?>>Date</option>
                <option value="id" <?= $tri === 'id' ? 'selected' : '' ?>>N° commande</option>
                <option value="montant" <?= $tri === 'montant' ? 'selected' : '' ?>>Montant</option>
                <option value="statut" <?= $tri === 'statut' ? 'selected' : '' ?>>Statut</option>
                <option value="ville" <?= $tri === 'ville' ? 'selected' : '' ?>>Ville</option>
            </select>
        </div>
        <div class="fo-filters__field">
            <label for="mv-ordre">Ordre</label>
            <select name="ordre" id="mv-ordre">
                <option value="desc" <?= $ordre === 'desc' ? 'selected' : '' ?>>Décroissant</option>
                <option value="asc" <?= $ordre === 'asc' ? 'selected' : '' ?>>Croissant</option>
            </select>
        </div>
        <div class="fo-filters__actions">
            <button type="submit" class="fo-btn fo-btn--primary">Appliquer</button>
            <a href="mesVentes.php" class="fo-btn fo-btn--secondary" style="text-decoration:none">Réinitialiser</a>
        </div>
    </form>
    <?php if ($q !== '' || $statut !== ''): ?>
        <p class="fo-result-hint"><?= $totalRows ?> commande(s)</p>
    <?php endif; ?>
    <?php if (empty($rows)): ?>
=======
    <?php if (empty($list)): ?>
>>>>>>> formation
        <div class="fo-empty">
            <p class="hint" style="margin:0 0 12px">Aucune commande pour vos références.</p>
            <a href="mesProduits.php">Gérer mes produits</a>
        </div>
    <?php else: ?>
        <div class="fo-table-wrap">
        <table class="table-modern">
<<<<<<< HEAD
            <thead><tr><th>#</th><th>Date</th><th>Acheteur</th><th>Mes articles</th><th>Mes lignes</th><th>Mon CA</th><th>Montant cmd</th><th>Statut</th><th>Suivi</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $c):
=======
            <thead><tr><th>#</th><th>Date</th><th>Acheteur</th><th>Montant</th><th>Statut</th><th>Suivi</th></tr></thead>
            <tbody>
            <?php foreach ($list as $c):
>>>>>>> formation
                $st = $c['statut'] ?? '';
                $badgeClass = 'fo-badge fo-badge--' . preg_replace('/[^a-z0-9_]/', '', $st);
            ?>
                <tr>
                    <td><strong>#<?= (int) $c['idcommande'] ?></strong></td>
                    <td><?= htmlspecialchars($c['date_commande']) ?></td>
                    <td><?= htmlspecialchars(trim(($c['prenom'] ?? '') . ' ' . ($c['nom'] ?? ''))) ?></td>
<<<<<<< HEAD
                    <td><?= (int) ($c['nb_articles_vendeur'] ?? 0) ?></td>
                    <td><?= (int) ($c['nb_lignes_vendeur'] ?? 0) ?></td>
                    <td><strong><?= number_format((float) ($c['montant_vendeur'] ?? 0), 3, ',', ' ') ?> TND</strong></td>
=======
>>>>>>> formation
                    <td><?= number_format((float) $c['montant_total'], 3, ',', ' ') ?> TND</td>
                    <td><span class="<?= htmlspecialchars($badgeClass) ?>"><?= htmlspecialchars($labels[$st] ?? $st) ?></span></td>
                    <td><?= htmlspecialchars((string) ($c['numero_suivi'] ?? '—')) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
<<<<<<< HEAD
        <?php if ($totalPages > 1): ?>
            <div class="fo-actions" style="margin-top:12px">
                <?php if ($page > 1): ?>
                    <a class="fo-btn fo-btn--secondary" style="text-decoration:none" href="?q=<?= urlencode($q) ?>&statut=<?= urlencode($statut) ?>&tri=<?= urlencode($tri) ?>&ordre=<?= urlencode($ordre) ?>&page=<?= $page - 1 ?>">Précédent</a>
                <?php endif; ?>
                <span class="fo-result-hint" style="margin:0">Page <?= $page ?> / <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <a class="fo-btn fo-btn--secondary" style="text-decoration:none" href="?q=<?= urlencode($q) ?>&statut=<?= urlencode($statut) ?>&tri=<?= urlencode($tri) ?>&ordre=<?= urlencode($ordre) ?>&page=<?= $page + 1 ?>">Suivant</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
=======
>>>>>>> formation
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
