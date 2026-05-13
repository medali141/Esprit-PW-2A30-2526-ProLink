<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/ReclamationCommandeController.php';

$auth = new AuthController();
$u = $auth->profile();
if (!$u) {
    header('Location: ../login.php');
    exit;
}

$rc = new ReclamationCommandeController();
$userId = (int) ($u['iduser'] ?? 0);
$error = '';
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? 'create');
    try {
        if ($action === 'close') {
            $idReclamation = (int) ($_POST['idreclamation'] ?? 0);
            $rating = (int) ($_POST['rating'] ?? 0);
            $rc->closeByUserWithRating($idReclamation, $userId, $rating);
            $ok = 'Reclamation cloturee. Merci pour votre note.';
        } else {
            $idCommande = (int) ($_POST['idcommande'] ?? 0);
            $sujet = (string) ($_POST['sujet'] ?? '');
            $message = (string) ($_POST['message'] ?? '');
            $rc->addForUser($userId, $idCommande, $sujet, $message);
            $ok = 'Reclamation envoyee avec succes.';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$commandes = $rc->listUserCommandes($userId);
$reclamations = $rc->listByUser($userId);
$selectedCommande = (int) ($_POST['idcommande'] ?? ($_GET['commande'] ?? 0));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reclamations commandes — ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Reclamation commande</h1>
        <p class="fo-lead">Ce formulaire est reserve aux commandes passees depuis votre compte.</p>
    </header>

    <?php if ($ok !== ''): ?>
        <p class="fo-banner fo-banner--ok"><?= htmlspecialchars($ok) ?></p>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <p class="fo-banner fo-banner--err"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if (empty($commandes)): ?>
        <div class="fo-empty">
            <p class="hint" style="margin:0 0 12px">Aucune commande trouvée pour votre compte.</p>
            <a href="catalogue.php">Aller a la boutique</a>
        </div>
    <?php else: ?>
        <form method="post" class="fo-form-card">
            <input type="hidden" name="action" value="create">
            <label for="r-idcommande">Commande concernee *</label>
            <select name="idcommande" id="r-idcommande" required>
                <option value="">Selectionner une commande</option>
                <?php foreach ($commandes as $c): ?>
                    <?php $cid = (int) ($c['idcommande'] ?? 0); ?>
                    <option value="<?= $cid ?>" <?= $selectedCommande === $cid ? 'selected' : '' ?>>
                        #<?= $cid ?> — <?= htmlspecialchars((string) ($c['date_commande'] ?? '')) ?> — <?= number_format((float) ($c['montant_total'] ?? 0), 3, ',', ' ') ?> TND
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="r-sujet">Sujet *</label>
            <input type="text" name="sujet" id="r-sujet" maxlength="120" required value="<?= htmlspecialchars((string) ($_POST['sujet'] ?? '')) ?>" placeholder="Ex: Produit manquant, retard livraison...">

            <label for="r-message">Message detaille *</label>
            <textarea name="message" id="r-message" rows="5" minlength="10" maxlength="1500" required placeholder="Decrivez le probleme rencontre sur cette commande..."><?= htmlspecialchars((string) ($_POST['message'] ?? '')) ?></textarea>

            <div class="fo-actions" style="margin-top:16px">
                <button type="submit" class="fo-btn fo-btn--primary">Envoyer la reclamation</button>
                <a href="mesCommandes.php" class="fo-btn fo-btn--secondary" style="text-decoration:none">Retour commandes</a>
            </div>
        </form>
    <?php endif; ?>

    <?php if (!empty($reclamations)): ?>
        <div class="fo-table-wrap" style="margin-top:20px">
            <table class="table-modern">
                <thead><tr><th>Commande</th><th>Sujet</th><th>Statut</th><th>Date</th><th>Message</th><th>Reponse admin</th><th>Points excuses</th><th>Note</th><th>Action</th></tr></thead>
                <tbody>
                <?php foreach ($reclamations as $r): ?>
                    <?php
                    $st = (string) ($r['statut'] ?? 'ouverte');
                    $rating = (int) ($r['user_rating'] ?? 0);
                    ?>
                    <tr>
                        <td>#<?= (int) ($r['idcommande'] ?? 0) ?></td>
                        <td><?= htmlspecialchars((string) ($r['sujet'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($st) ?></td>
                        <td><?= htmlspecialchars((string) ($r['created_at'] ?? '')) ?></td>
                        <td><?= nl2br(htmlspecialchars((string) ($r['message'] ?? ''))) ?></td>
                        <td><?= nl2br(htmlspecialchars((string) ($r['admin_response'] ?? '—'))) ?></td>
                        <td><?= (int) ($r['compensation_points'] ?? 0) ?> pts</td>
                        <td>
                            <?php if ($rating >= 1 && $rating <= 5): ?>
                                <span style="color:#f59e0b;font-weight:800"><?= str_repeat('★', $rating) ?><span style="color:#cbd5e1"><?= str_repeat('★', 5 - $rating) ?></span></span>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($st !== 'resolue'): ?>
                                <form method="post" style="display:flex;flex-direction:column;gap:6px;min-width:155px">
                                    <input type="hidden" name="action" value="close">
                                    <input type="hidden" name="idreclamation" value="<?= (int) ($r['idreclamation'] ?? 0) ?>">
                                    <div class="fo-rating-stars" aria-label="Noter la prise en charge">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" id="rate-<?= (int) ($r['idreclamation'] ?? 0) ?>-<?= $i ?>" name="rating" value="<?= $i ?>" required>
                                            <label for="rate-<?= (int) ($r['idreclamation'] ?? 0) ?>-<?= $i ?>" title="<?= $i ?> étoiles">★</label>
                                        <?php endfor; ?>
                                    </div>
                                    <button type="submit" class="fo-btn fo-btn--secondary" style="padding:6px 10px;font-size:0.75rem">Cloturer</button>
                                </form>
                            <?php else: ?>
                                <span class="hint">Cloturee</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
<style>
.fo-rating-stars {
    display: inline-flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 2px;
}
.fo-rating-stars input {
    display: none;
}
.fo-rating-stars label {
    cursor: pointer;
    color: #cbd5e1;
    font-size: 1rem;
    line-height: 1;
}
.fo-rating-stars input:checked ~ label,
.fo-rating-stars label:hover,
.fo-rating-stars label:hover ~ label {
    color: #f59e0b;
}
</style>
</body>
</html>
