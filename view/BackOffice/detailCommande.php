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
$cp = new CommandeController();
$id = (int) ($_GET['id'] ?? 0);
$cmd = $id ? $cp->getById($id) : null;
if (!$cmd) {
    header('Location: listCommandes.php');
    exit;
}
$lignes = $cp->getLignes($id);
$error = '';
$ok = isset($_GET['saved']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statut = trim($_POST['statut'] ?? '');
    $numero_suivi = trim($_POST['numero_suivi'] ?? '');
    $date_prevue = trim($_POST['date_livraison_prevue'] ?? '');
    $date_eff = trim($_POST['date_livraison_effective'] ?? '');
    if ($date_eff !== '') {
        $date_eff = str_replace('T', ' ', $date_eff);
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $date_eff)) {
            $date_eff .= ':00';
        }
    }
    $notes = trim($_POST['notes'] ?? '');
    try {
        $cp->updateMeta($id, $statut, $numero_suivi, $date_prevue, $date_eff, $notes);
        header('Location: detailCommande.php?id=' . $id . '&saved=1');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$statuts = [
    'brouillon' => 'Brouillon',
    'en_attente_paiement' => 'En attente paiement',
    'payee' => 'Payée',
    'en_preparation' => 'En préparation',
    'expediee' => 'Expédiée',
    'livree' => 'Livrée',
    'annulee' => 'Annulée',
];
$deVal = $cmd['date_livraison_effective'] ?? '';
if ($deVal && strpos($deVal, ' ') !== false) {
    $deVal = str_replace(' ', 'T', substr($deVal, 0, 16));
}
$st = $cmd['statut'] ?? '';
$badgeClass = 'commerce-badge commerce-badge--' . preg_replace('/[^a-z0-9_]/', '', $st);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Commande #<?= $id ?></title>
</head>
<body>
<?php include 'sidebar.php'; ?>
<link rel="stylesheet" href="commerce.css">
<div class="content commerce-page">
    <div class="topbar">
        <div>
            <div class="page-title">Commande #<?= $id ?></div>
            <p class="hint" style="margin:6px 0 0">Statut : <span class="<?= htmlspecialchars($badgeClass) ?>"><?= htmlspecialchars($statuts[$st] ?? $st) ?></span></p>
        </div>
        <a href="listCommandes.php" class="btn btn-secondary">Retour liste</a>
    </div>
    <div class="container" style="max-width:960px">
        <?php if ($ok): ?>
            <div class="commerce-alert commerce-alert--ok">Modifications enregistrées.</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="commerce-alert commerce-alert--err"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="commerce-form-card" style="margin-bottom:20px">
            <h3 class="commerce-card-title">Lignes de commande</h3>
            <div class="commerce-table-wrap">
            <table class="table-modern">
                <thead><tr><th>Produit</th><th>Vendeur</th><th>Qté</th><th>Prix u.</th><th>Total ligne</th></tr></thead>
                <tbody>
                <?php foreach ($lignes as $l): ?>
                    <tr>
                        <td><?= htmlspecialchars($l['designation']) ?> <span class="hint">(<?= htmlspecialchars($l['reference']) ?>)</span></td>
                        <td><?= htmlspecialchars(trim(($l['v_prenom'] ?? '') . ' ' . ($l['v_nom'] ?? ''))) ?></td>
                        <td><?= (int) $l['quantite'] ?></td>
                        <td><?= number_format((float) $l['prix_unitaire'], 3, ',', ' ') ?> TND</td>
                        <td><?= number_format((float) $l['prix_unitaire'] * (int) $l['quantite'], 3, ',', ' ') ?> TND</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <p style="margin-top:14px;font-weight:700">Total : <?= number_format((float) $cmd['montant_total'], 3, ',', ' ') ?> TND</p>
        </div>

        <div class="commerce-form-card">
            <h3 class="commerce-card-title">Livraison et suivi</h3>
            <div class="commerce-addr-block">
                <strong>Adresse de livraison</strong><br>
                <?= htmlspecialchars($cmd['adresse_livraison']) ?>,
                <?= htmlspecialchars($cmd['code_postal']) ?> <?= htmlspecialchars($cmd['ville']) ?>,
                <?= htmlspecialchars($cmd['pays'] ?? '') ?>
            </div>
            <form method="post" class="commerce-detail-grid" novalidate data-validate="commande-form">
                <div>
                    <label class="field-first">Statut</label>
                    <select name="statut">
                        <?php foreach ($statuts as $k => $lab): ?>
                            <option value="<?= htmlspecialchars($k) ?>" <?= ($cmd['statut'] === $k) ? 'selected' : '' ?>><?= htmlspecialchars($lab) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Numéro de suivi</label>
                    <input type="text" name="numero_suivi" value="<?= htmlspecialchars((string) ($cmd['numero_suivi'] ?? '')) ?>">
                </div>
                <div>
                    <label>Date livraison prévue</label>
                    <input type="date" name="date_livraison_prevue" value="<?= htmlspecialchars(substr((string) ($cmd['date_livraison_prevue'] ?? ''), 0, 10)) ?>">
                </div>
                <div>
                    <label>Date livraison effective</label>
                    <input type="datetime-local" name="date_livraison_effective" value="<?= htmlspecialchars($deVal) ?>">
                </div>
                <div style="grid-column:1/-1">
                    <label>Notes internes</label>
                    <textarea name="notes" rows="3"><?= htmlspecialchars((string) ($cmd['notes'] ?? '')) ?></textarea>
                </div>
                <div style="grid-column:1/-1" class="btn-submit-wrap">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
