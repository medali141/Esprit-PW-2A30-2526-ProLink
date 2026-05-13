<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/AchatsStockOffresController.php';

$auth = new AuthController();
$u = $auth->profile();
if (!$u) {
    header('Location: ../login.php');
    exit;
}
$type = strtolower((string) ($u['type'] ?? ''));
if (!in_array($type, ['entrepreneur', 'candidat'], true)) {
    header('Location: home.php');
    exit;
}

$so = new AchatsStockOffresController();
$error = '';
$uid = (int) $u['iduser'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        $error = 'Session expirée. Rechargez la page puis réessayez.';
    } else {
        $idao = (int) ($_POST['idao'] ?? 0);
        $prixRaw = str_replace(',', '.', trim((string) ($_POST['prix_propose'] ?? '')));
        $prix = is_numeric($prixRaw) ? (float) $prixRaw : -1.0;
        $delai = (int) ($_POST['delai_jours'] ?? 0);
        $notes = trim((string) ($_POST['notes'] ?? ''));
        if ($idao <= 0) {
            $error = 'Appel d’offres invalide.';
        } elseif ($prix < 0) {
            $error = 'Montant invalide.';
        } elseif ($delai < 1 || $delai > 365) {
            $error = 'Délai de livraison : entre 1 et 365 jours.';
        } else {
            try {
                $so->addReponseOffre($idao, $uid, $prix, $delai, $notes);
                header('Location: mesAppelsOffres.php?ok=1');
                exit;
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }
    }
}

$aos = $so->listAppelsOffresPublies();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Appels d’offres — ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Appels d’offres ouverts</h1>
        <p class="fo-lead">
            Répondez aux consultations publiées par l’équipe achats. Votre proposition met à jour le comparatif prix / délais côté administration.
        </p>
    </header>

    <?php if ($error !== ''): ?>
        <p class="fo-banner fo-banner--err" role="alert"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if (isset($_GET['ok'])): ?>
        <p class="fo-banner fo-banner--ok fade-in" role="status">Réponse enregistrée.</p>
    <?php endif; ?>

    <?php if (empty($aos)): ?>
        <div class="fo-empty fo-form-card" style="max-width:560px">
            <p class="hint" style="margin:0">Aucun appel d’offres ouvert pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="fo-ao-list">
            <?php foreach ($aos as $ao):
                $idao = (int) ($ao['idao'] ?? 0);
                $lim = (string) ($ao['date_limite'] ?? '');
                $existing = $so->getReponseVendeurPourAo($idao, $uid);
                ?>
                <article class="fo-form-card fo-ao-card">
                    <div class="fo-ao-card__head">
                        <h2><?= htmlspecialchars((string) ($ao['titre'] ?? '')) ?></h2>
                        <span class="fo-ao-card__meta">
                            Clôture <?= htmlspecialchars($lim !== '' ? date('d/m/Y', strtotime($lim)) : '—') ?>
                            · <?= (int) ($ao['nb_reponses'] ?? 0) ?> proposition(s)
                        </span>
                    </div>
                    <?php if (!empty($ao['description'])): ?>
                        <p class="fo-ao-card__desc"><?= nl2br(htmlspecialchars((string) $ao['description'])) ?></p>
                    <?php endif; ?>

                    <?php if ($existing): ?>
                        <div class="fo-ao-existing">
                            <strong>Votre proposition</strong>
                            <span><?= number_format((float) ($existing['prix_propose'] ?? 0), 2, ',', ' ') ?> TND</span>
                            <span>· <?= (int) ($existing['delai_jours'] ?? 0) ?> j.</span>
                            <?php if (!empty($existing['notes'])): ?>
                                <p class="hint" style="margin:8px 0 0"><?= htmlspecialchars((string) $existing['notes']) ?></p>
                            <?php endif; ?>
                            <p class="hint" style="margin:10px 0 0;font-size:12px">Vous pouvez la modifier en renvoyant le formulaire ci-dessous.</p>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="fo-ao-form" action="mesAppelsOffres.php">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="idao" value="<?= $idao ?>">
                        <div class="fo-ao-form__grid">
                            <label class="fo-field">
                                <span>Prix proposé (TND)</span>
                                <input type="text" name="prix_propose" inputmode="decimal" required placeholder="ex. 1250,500"
                                       value="<?= $existing ? htmlspecialchars(number_format((float) ($existing['prix_propose'] ?? 0), 3, ',', '')) : '' ?>">
                            </label>
                            <label class="fo-field">
                                <span>Délai (jours)</span>
                                <input type="number" name="delai_jours" min="1" max="365" required
                                       value="<?= $existing ? (int) ($existing['delai_jours'] ?? 7) : 7 ?>">
                            </label>
                        </div>
                        <label class="fo-field">
                            <span>Notes (optionnel)</span>
                            <textarea name="notes" rows="2" maxlength="500" placeholder="Conditions, références…"><?= $existing ? htmlspecialchars((string) ($existing['notes'] ?? '')) : '' ?></textarea>
                        </label>
                        <button type="submit" class="fo-btn fo-btn--primary"><?= $existing ? 'Mettre à jour la proposition' : 'Envoyer la proposition' ?></button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
