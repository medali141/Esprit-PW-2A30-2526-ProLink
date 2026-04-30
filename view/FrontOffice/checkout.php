<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/ProduitController.php';
require_once __DIR__ . '/../../controller/CommandeController.php';
require_once __DIR__ . '/../../model/CommerceMetier.php';

$auth = new AuthController();
$u = $auth->profile();
if (!$u) {
    header('Location: ../login.php');
    exit;
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || !array_sum($_SESSION['cart'])) {
    header('Location: panier.php');
    exit;
}

$error = '';
$usePointsInput = isset($_POST['use_points']) ? (string) $_POST['use_points'] === '1' : false;
$paymentMethodInput = (string) ($_POST['payment_method'] ?? 'cash_on_delivery');
if (!in_array($paymentMethodInput, ['cash_on_delivery', 'card'], true)) {
    $paymentMethodInput = 'cash_on_delivery';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Session expirée. Rechargez la page puis recommencez.';
    }
    $adr = trim($_POST['adresse_livraison'] ?? '');
    $cp = trim($_POST['code_postal'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $pays = trim($_POST['pays'] ?? 'Tunisie');
    $tel = trim($_POST['telephone_livraison'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $cardName = trim((string) ($_POST['card_name'] ?? ''));
    $cardNumberRaw = preg_replace('/\D+/', '', (string) ($_POST['card_number'] ?? '')) ?? '';
    $cardExp = trim((string) ($_POST['card_exp'] ?? ''));
    $cardCvc = trim((string) ($_POST['card_cvc'] ?? ''));
    if ($error === '' && (strlen($adr) < 5 || strlen($adr) > 300)) {
        $error = 'Adresse : entre 5 et 300 caractères.';
    } elseif ($error === '' && (strlen($cp) < 2 || strlen($cp) > 20 || !preg_match('/^[\w\s-]+$/u', $cp))) {
        $error = 'Code postal invalide.';
    } elseif ($error === '' && (strlen($ville) < 2 || strlen($ville) > 100)) {
        $error = 'Ville : entre 2 et 100 caractères.';
    } elseif ($error === '' && $pays !== '' && strlen($pays) > 100) {
        $error = 'Pays : maximum 100 caractères.';
    } elseif ($error === '' && !preg_match('/^\d{8}$/', $tel)) {
        $error = 'Numéro de téléphone invalide (8 chiffres).';
    } elseif ($error === '' && strlen($notes) > 500) {
        $error = 'Notes : maximum 500 caractères.';
    } elseif ($error === '' && $paymentMethodInput === 'card') {
        if (strlen($cardName) < 3 || strlen($cardName) > 120) {
            $error = 'Titulaire carte invalide.';
        } elseif (!preg_match('/^\d{13,19}$/', $cardNumberRaw)) {
            $error = 'Numéro de carte invalide.';
        } elseif (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $cardExp)) {
            $error = 'Expiration invalide (MM/AA).';
        } elseif (!preg_match('/^\d{3,4}$/', $cardCvc)) {
            $error = 'CVC invalide.';
        }
    } elseif ($error === '') {
        try {
            $cmdP = new CommandeController();
            $idCmd = $cmdP->createFromCart((int) $u['iduser'], $_SESSION['cart'], [
                'adresse_livraison' => $adr,
                'code_postal' => $cp,
                'ville' => $ville,
                'pays' => $pays !== '' ? $pays : 'Tunisie',
                'telephone_livraison' => $tel,
                'notes' => $notes !== '' ? $notes : null,
                'use_points' => $usePointsInput,
                'payment_method' => $paymentMethodInput,
            ]);
            $_SESSION['cart'] = [];
            header('Location: mesCommandes.php?new=' . $idCmd);
            exit;
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$pp = new ProduitController();
$total = 0.0;
foreach ($_SESSION['cart'] as $pid => $qte) {
    $p = $pp->getById((int) $pid);
    if ($p && (int) $p['actif']) {
        $total += (float) $p['prix_unitaire'] * (int) $qte;
    }
}
$currentPoints = (int) ($u['points_fidelite'] ?? 0);
$currentPointsTnd = CommerceMetier::dinarFromPoints($currentPoints);
$discountPreview = $usePointsInput ? min($currentPointsTnd, $total) : 0.0;
$payablePreview = max(0.0, $total - $discountPreview);
$earnedAfterDiscount = CommerceMetier::pointsFromAmount($payablePreview);
$payLabel = $paymentMethodInput === 'card' ? 'Carte bancaire' : 'Paiement à la livraison (cash)';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Commande — ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Valider la commande</h1>
        <p class="fo-lead">Montant articles <strong><?= number_format($total, 3, ',', ' ') ?> TND</strong> — livraison <strong>gratuite</strong> sur toutes les commandes.</p>
        <p class="fo-lead" style="margin-top:8px">
            Fidélité: solde <strong><?= $currentPoints ?> pts</strong> (<?= number_format($currentPointsTnd, 3, ',', ' ') ?> TND).
        </p>
    </header>
    <?php if ($error): ?>
        <p class="fo-banner fo-banner--err"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" class="fo-checkout-card" novalidate data-validate="checkout-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $_SESSION['csrf_token']) ?>">
        <div class="fo-track-estimate" role="status" style="margin-bottom:14px">
            <strong>Sous-total articles:</strong> <span id="sumSubtotal"><?= number_format($total, 3, ',', ' ') ?></span> TND
            <br><strong>Livraison:</strong> 0,000 TND (offerte)
            <br><strong>Remise fidélité:</strong> -<span id="sumDiscount"><?= number_format($discountPreview, 3, ',', ' ') ?></span> TND
            <br><strong>Total à payer:</strong> <span id="sumPayable"><?= number_format($payablePreview, 3, ',', ' ') ?></span> TND
            <br><strong>Mode de paiement:</strong> <span id="sumPayment"><?= htmlspecialchars($payLabel) ?></span>
            <br><span class="hint" id="sumPointsHint">Points estimés après commande: +<?= $earnedAfterDiscount ?> pts<?= $usePointsInput && $discountPreview > 0 ? ' (après utilisation de vos points)' : '' ?>.</span>
        </div>
        <label class="fo-form-check" style="margin-top:0">
            <input type="checkbox" name="use_points" value="1" <?= $usePointsInput ? 'checked' : '' ?>>
            Utiliser mes points fidélité disponibles pour réduire le total (optionnel)
        </label>
        <div class="fo-filters" style="margin-top:12px">
            <div class="fo-filters__field" style="min-width:220px">
                <label>Mode de paiement</label>
                <label class="fo-form-check" style="margin-top:8px">
                    <input type="radio" name="payment_method" value="cash_on_delivery" <?= $paymentMethodInput === 'cash_on_delivery' ? 'checked' : '' ?>>
                    Cash à la livraison
                </label>
                <label class="fo-form-check" style="margin-top:8px">
                    <input type="radio" name="payment_method" value="card" <?= $paymentMethodInput === 'card' ? 'checked' : '' ?>>
                    Carte bancaire
                </label>
            </div>
            <div id="cardFieldsWrap" class="fo-filters__field" style="min-width:320px;flex:1">
                <label for="card_name">Paiement carte (sécurisé)</label>
                <input type="text" id="card_name" name="card_name" placeholder="Titulaire de la carte" value="<?= htmlspecialchars((string) ($_POST['card_name'] ?? '')) ?>">
                <div style="display:grid;grid-template-columns:1.6fr 1fr 1fr;gap:8px;margin-top:8px">
                    <input type="text" name="card_number" placeholder="Numéro carte" inputmode="numeric" autocomplete="cc-number" value="<?= htmlspecialchars((string) ($_POST['card_number'] ?? '')) ?>">
                    <input type="text" name="card_exp" placeholder="MM/AA" inputmode="numeric" autocomplete="cc-exp" value="<?= htmlspecialchars((string) ($_POST['card_exp'] ?? '')) ?>">
                    <input type="text" name="card_cvc" placeholder="CVC" inputmode="numeric" autocomplete="cc-csc" value="<?= htmlspecialchars((string) ($_POST['card_cvc'] ?? '')) ?>">
                </div>
                <p class="hint" style="margin:8px 0 0">Les données carte ne sont pas stockées en base. Seul le mode de paiement est conservé.</p>
            </div>
        </div>
        <label>Adresse de livraison *</label>
        <input type="text" name="adresse_livraison" value="<?= htmlspecialchars($_POST['adresse_livraison'] ?? '') ?>">
        <label>Code postal *</label>
        <input type="text" name="code_postal" value="<?= htmlspecialchars($_POST['code_postal'] ?? '') ?>">
        <label>Ville *</label>
        <input type="text" name="ville" value="<?= htmlspecialchars($_POST['ville'] ?? '') ?>">
        <label>Pays</label>
        <input type="text" name="pays" value="<?= htmlspecialchars($_POST['pays'] ?? 'Tunisie') ?>">
        <label>Téléphone livraison *</label>
        <input type="text" name="telephone_livraison" inputmode="numeric" placeholder="8 chiffres" value="<?= htmlspecialchars($_POST['telephone_livraison'] ?? '') ?>">
        <label>Notes (optionnel)</label>
        <textarea name="notes" rows="2"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
        <div class="fo-actions" style="margin-top:20px">
            <button type="submit" class="fo-btn fo-btn--primary">Confirmer la commande</button>
            <a href="panier.php" class="fo-btn fo-btn--secondary" style="text-decoration:none">Retour panier</a>
        </div>
    </form>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
<script src="../assets/forms-validation.js"></script>
<script>
(function () {
    var radios = document.querySelectorAll('input[name="payment_method"]');
    var usePoints = document.querySelector('input[name="use_points"]');
    var cardWrap = document.getElementById('cardFieldsWrap');
    var elDiscount = document.getElementById('sumDiscount');
    var elPayable = document.getElementById('sumPayable');
    var elPayment = document.getElementById('sumPayment');
    var elPointsHint = document.getElementById('sumPointsHint');
    if (!radios.length || !cardWrap || !elDiscount || !elPayable || !elPayment || !elPointsHint) return;

    var subtotal = <?= json_encode((float) $total) ?>;
    var pointsValueTnd = <?= json_encode((float) $currentPointsTnd) ?>;
    var dinarPerPoint = <?= json_encode((float) CommerceMetier::DINAR_PER_POINT) ?>;

    function formatTnd(v) {
        return Number(v).toLocaleString('fr-FR', {minimumFractionDigits: 3, maximumFractionDigits: 3});
    }

    function pointsFromAmount(amountTnd) {
        if (amountTnd <= 0) return 0;
        return Math.floor(amountTnd / 10);
    }

    function refreshEstimate() {
        var checked = !!(usePoints && usePoints.checked);
        var discount = checked ? Math.min(pointsValueTnd, subtotal) : 0;
        var payable = Math.max(0, subtotal - discount);
        var earned = pointsFromAmount(payable);

        elDiscount.textContent = formatTnd(discount);
        elPayable.textContent = formatTnd(payable);
        elPointsHint.textContent = 'Points estimés après commande: +' + earned + ' pts' +
            (checked && discount > 0 ? ' (après utilisation de vos points).' : '.');
    }

    function refreshCardFields() {
        var active = document.querySelector('input[name="payment_method"]:checked');
        var isCard = active && active.value === 'card';
        cardWrap.style.display = isCard ? '' : 'none';
        elPayment.textContent = isCard ? 'Carte bancaire' : 'Paiement à la livraison (cash)';
    }
    for (var i = 0; i < radios.length; i++) {
        radios[i].addEventListener('change', function () {
            refreshCardFields();
            refreshEstimate();
        });
    }
    if (usePoints) {
        usePoints.addEventListener('change', refreshEstimate);
    }
    refreshCardFields();
    refreshEstimate();
})();
</script>
</body>
</html>
