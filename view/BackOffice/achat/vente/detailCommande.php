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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commande #<?= $id ?></title>
    <style>.grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px} @media(max-width:800px){.grid2{grid-template-columns:1fr}} label{display:block;margin-top:10px;font-weight:600} input,select,textarea{width:100%;padding:8px;margin-top:4px;box-sizing:border-box}</style>
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
<div class="content">
    <div class="topbar">
        <div class="page-title">Commande #<?= $id ?></div>
        <a href="listCommandes.php" class="btn btn-secondary">Retour liste</a>
    </div>
    <div class="container" style="max-width:960px">
        <?php if ($ok): ?><p style="color:#27ae60">Enregistré.</p><?php endif; ?>
        <?php if ($error): ?><p style="color:#c0392b"><?= htmlspecialchars($error) ?></p><?php endif; ?>

        <div class="card" style="margin-bottom:16px">
            <h3 style="margin-top:0">Lignes</h3>
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
            <p style="margin-top:12px"><strong>Total :</strong> <?= number_format((float) $cmd['montant_total'], 3, ',', ' ') ?> TND</p>
        </div>

        <div class="card">
            <h3 style="margin-top:0">Livraison &amp; suivi</h3>
            <p><strong>Adresse :</strong> <?= htmlspecialchars($cmd['adresse_livraison']) ?>,
                <?= htmlspecialchars($cmd['code_postal']) ?> <?= htmlspecialchars($cmd['ville']) ?>,
                <?= htmlspecialchars($cmd['pays'] ?? '') ?></p>
            <form method="post" class="grid2" novalidate data-validate="commande-form">
                <div>
                    <label>Statut</label>
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
                <div style="grid-column:1/-1">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
