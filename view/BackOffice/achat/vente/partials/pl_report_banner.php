<?php
/**
 * Bandeau d’en-tête pour rapports PDF / écran (variables : $plReportBanner).
 *
 * @var array{title:string, kicker?:string, lead?:string} $plReportBanner
 */
if (!isset($plReportBanner) || !is_array($plReportBanner)) {
    return;
}
$bKicker = trim((string) ($plReportBanner['kicker'] ?? ''));
$bTitle = (string) ($plReportBanner['title'] ?? 'Rapport');
$bLead = isset($plReportBanner['lead']) ? trim((string) $plReportBanner['lead']) : '';
$dtIso = date('c');
$dtHuman = date('d/m/Y à H:i');
?>
<div class="pl-pdf-banner">
    <div class="pl-pdf-banner__grid">
        <div class="pl-pdf-banner__brand">
            <?php if ($bKicker !== ''): ?>
                <p class="pl-pdf-banner__kicker"><?= htmlspecialchars($bKicker) ?></p>
            <?php endif; ?>
            <h1 class="pl-pdf-banner__title"><?= htmlspecialchars($bTitle) ?></h1>
            <?php if ($bLead !== ''): ?>
                <p class="pl-pdf-banner__lead"><?= htmlspecialchars($bLead) ?></p>
            <?php endif; ?>
        </div>
        <time class="pl-pdf-banner__time" datetime="<?= htmlspecialchars($dtIso) ?>"><?= htmlspecialchars($dtHuman) ?></time>
    </div>
</div>
