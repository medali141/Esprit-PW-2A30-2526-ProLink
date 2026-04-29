<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controller/eventC.php';

$ec = new EventC();
$events = $ec->listeEvenementsPublic();

function fo_format_date(string $d): string {
    $t = strtotime($d);
    return $t ? date('d/m/Y', $t) : $d;
}

function fo_event_photo_src(array $ev): string {
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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Événements — ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Événements</h1>
        <p class="fo-lead">Prochains rendez-vous ProLink : conférences, ateliers et rencontres. Inscription en ligne — places limitées.</p>
    </header>

    <div class="fo-product-grid">
        <?php foreach ($events as $ev):
            $id = (int) $ev['id_event'];
            $statut = (string) ($ev['statut'] ?? '');
            $cap = (int) ($ev['capacite_max'] ?? 0);
            $insc = (int) ($ev['inscrits'] ?? 0);
            $complet = $statut === 'Complet' || ($cap > 0 && $insc >= $cap);
            $desc = (string) ($ev['description_event'] ?? '');
            $short = strlen($desc) > 120 ? substr($desc, 0, 117) . '…' : $desc;
            $low = !$complet && $cap > 0 && ($cap - $insc) > 0 && ($cap - $insc) <= 5;
            $ddeb = fo_format_date((string) ($ev['date_debut'] ?? ''));
            $dfin = fo_format_date((string) ($ev['date_fin'] ?? ''));
        ?>
            <article class="fo-product-card<?= $complet ? ' fo-product-card--out' : '' ?>">
                <div class="fo-event-media">
                    <img src="<?= htmlspecialchars(fo_event_photo_src($ev)) ?>" alt="Photo de l'événement <?= htmlspecialchars((string) ($ev['titre_event'] ?? '')) ?>" loading="lazy">
                </div>
                <h2><?= htmlspecialchars($ev['titre_event'] ?? '') ?></h2>
                <span class="fo-ref"><?= htmlspecialchars($ev['type_event'] ?? '') ?> · <?= htmlspecialchars($ddeb) ?> → <?= htmlspecialchars($dfin) ?></span>
                <?php if ($short !== ''): ?>
                    <p class="fo-desc"><?= nl2br(htmlspecialchars($short)) ?></p>
                <?php endif; ?>
                <?php if ($cap > 0): ?>
                    <div class="fo-event-places-block" aria-label="Places occupées">
                        <div class="fo-price"><?= (int) $insc ?> <span class="fo-event-places__sep">/</span> <?= (int) $cap ?></div>
                        <p class="fo-event-places__hint">places</p>
                    </div>
                <?php endif; ?>
                <div class="fo-meta">
                    <?php if ($complet): ?>
                        <span class="fo-stock-pill">Complet</span>
                    <?php else: ?>
                        <span class="fo-stock-pill<?= $low ? ' fo-stock-pill--low' : '' ?>">Inscriptions ouvertes</span>
                    <?php endif; ?>
                    · <?= htmlspecialchars($ev['lieu_event'] ?? '') ?>
                </div>
                <?php if (!$complet): ?>
                    <a class="fo-btn fo-btn--primary" href="evenement.php?id=<?= $id ?>">Détails &amp; inscription</a>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (empty($events)): ?>
        <div class="fo-empty fo-empty--events">
            <div class="fo-empty__icon" aria-hidden="true">📅</div>
            <p class="hint" style="margin:0 0 8px;font-weight:600;font-size:1.05rem;color:var(--sf-text)">Aucun événement à venir</p>
            <p class="hint" style="margin:0 0 16px">Revenez bientôt ou parcourez la boutique en attendant.</p>
            <a href="catalogue.php">Aller à la boutique</a>
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
