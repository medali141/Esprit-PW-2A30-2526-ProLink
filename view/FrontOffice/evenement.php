<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controller/eventC.php';
require_once __DIR__ . '/../../controller/participationC.php';
require_once __DIR__ . '/../../model/participation.php';
require_once __DIR__ . '/../../controller/AuthController.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$ec = new EventC();
$ev = $id > 0 ? $ec->getEventPublic($id) : false;

$auth = new AuthController();
$sessionUser = $auth->profile();

$error = '';
$ok = isset($_GET['ok']);

if ($ev && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $pc = new ParticipationC();
    $p = new Participation(
        (string) $id,
        (string) ($_POST['nom'] ?? ''),
        (string) ($_POST['prenom'] ?? ''),
        (string) ($_POST['email'] ?? ''),
        (string) ($_POST['telephone'] ?? ''),
        'confirmé'
    );
    $result = $pc->addParticipation($p);
    if ($result === true) {
        header('Location: evenement.php?id=' . $id . '&ok=1');
        exit;
    }
    $error = (string) $result;
}

function fo_format_date2(string $d): string {
    $t = strtotime($d);
    return $t ? date('d/m/Y', $t) : $d;
}

<<<<<<< HEAD
function fo_event_photo_src2(array $ev): string {
    $candidates = ['photo_event', 'image_event', 'image', 'photo'];
    $raw = '';
    foreach ($candidates as $k) {
        if (!empty($ev[$k]) && is_string($ev[$k])) {
            $raw = trim((string) $ev[$k]);
            if ($raw !== '') {
                break;
            }
        }
    }
    if ($raw === '') {
        return '../assets/event-placeholder.svg';
    }
    if (preg_match('#^https?://#i', $raw) || str_starts_with($raw, '../') || str_starts_with($raw, '/')) {
        return $raw;
    }
    return '../' . ltrim($raw, '/');
}

=======
>>>>>>> formation
$defNom = $sessionUser['nom'] ?? '';
$defPrenom = $sessionUser['prenom'] ?? '';
$defEmail = $sessionUser['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $ev ? htmlspecialchars($ev['titre_event']) : 'Événement' ?> — ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <?php if (!$ev): ?>
        <header class="fo-hero fo-hero--tight">
            <p class="fo-eyebrow">Agenda</p>
            <h1>Événement</h1>
            <p class="fo-lead">Cet événement n’existe pas ou n’est plus affiché (dates passées ou retiré).</p>
        </header>
        <p class="fo-event-actions"><a href="evenements.php" class="fo-btn fo-btn--secondary">← Tous les événements</a></p>
    <?php else:
        $statut = (string) ($ev['statut'] ?? '');
        $cap = (int) ($ev['capacite_max'] ?? 0);
        $insc = (int) ($ev['inscrits'] ?? 0);
        $complet = $statut === 'Complet' || ($cap > 0 && $insc >= $cap);
    ?>
        <header class="fo-hero fo-hero--tight">
            <p class="fo-eyebrow"><?= htmlspecialchars($ev['type_event']) ?></p>
            <h1><?= htmlspecialchars($ev['titre_event']) ?></h1>
            <p class="fo-lead">
                <span class="fo-hero__inline">📅 <?= fo_format_date2((string) $ev['date_debut']) ?> → <?= fo_format_date2((string) $ev['date_fin']) ?></span>
                <span class="fo-hero__sep">·</span>
                <span class="fo-hero__inline">📍 <?= htmlspecialchars($ev['lieu_event']) ?></span>
            </p>
        </header>

        <div class="fo-event-detail">
            <div class="fo-event-detail__info fo-form-card">
<<<<<<< HEAD
                <div class="fo-event-media fo-event-media--detail">
                    <img src="<?= htmlspecialchars(fo_event_photo_src2($ev)) ?>" alt="Photo de l'événement <?= htmlspecialchars((string) ($ev['titre_event'] ?? '')) ?>" loading="lazy">
                </div>
=======
>>>>>>> formation
                <p class="fo-event-badges">
                    <span class="fo-event-pill"><?= htmlspecialchars($ev['type_event']) ?></span>
                    <?php if ($complet): ?>
                        <span class="fo-event-pill fo-event-pill--full">Complet</span>
                    <?php else: ?>
                        <span class="fo-event-pill fo-event-pill--open">Places disponibles</span>
                    <?php endif; ?>
                </p>
                <p class="fo-event-capacity-line">Inscrits : <strong><?= $cap > 0 ? $insc . ' / ' . $cap : (string) $insc ?></strong></p>
                <div class="fo-prose">
                    <?= nl2br(htmlspecialchars((string) $ev['description_event'])) ?>
                </div>
            </div>

            <aside class="fo-event-aside">
                <h2 class="fo-event-aside__title">Inscription</h2>
                <?php if ($ok): ?>
                    <p class="fo-banner fo-banner--ok" role="status">Votre inscription a bien été enregistrée.</p>
                    <p class="hint" style="margin:12px 0 0">Vous recevrez les informations pratiques par e-mail.</p>
                    <p class="fo-event-actions"><a href="evenements.php" class="fo-btn fo-btn--secondary fo-btn--block">Autres événements</a></p>
                <?php elseif ($error): ?>
                    <p class="fo-banner fo-banner--err" role="alert"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <?php if ($complet): ?>
                    <p class="hint" style="margin:0">Les inscriptions sont closes (événement complet).</p>
                <?php elseif (!$ok): ?>
                    <form method="post" action="evenement.php?id=<?= (int) $id ?>" novalidate data-validate="event-inscription-form" class="fo-event-form">
                        <div>
                            <label for="nom">Nom</label>
                            <input type="text" name="nom" id="nom" value="<?= htmlspecialchars((string) ($_POST['nom'] ?? $defNom)) ?>" required autocomplete="family-name" maxlength="100">
                        </div>
                        <div>
                            <label for="prenom">Prénom</label>
                            <input type="text" name="prenom" id="prenom" value="<?= htmlspecialchars((string) ($_POST['prenom'] ?? $defPrenom)) ?>" required autocomplete="given-name" maxlength="100">
                        </div>
                        <div>
                            <label for="email">E-mail</label>
                            <input type="email" name="email" id="email" value="<?= htmlspecialchars((string) ($_POST['email'] ?? $defEmail)) ?>" required autocomplete="email" maxlength="150">
                        </div>
                        <div>
                            <label for="telephone">Téléphone (8 chiffres)</label>
                            <input type="text" name="telephone" id="telephone" value="<?= htmlspecialchars((string) ($_POST['telephone'] ?? '')) ?>" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" required autocomplete="tel">
                        </div>
                        <button type="submit" class="fo-btn fo-btn--primary fo-btn--block" style="margin-top:4px">M’inscrire</button>
                    </form>
                <?php endif; ?>
            </aside>
        </div>

        <p class="fo-event-footer">
            <a href="evenements.php" class="fo-link-back">← Retour aux événements</a>
        </p>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
<script src="../assets/forms-validation.js"></script>
</body>
</html>
