<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/CommandeController.php';
require_once __DIR__ . '/../../model/CommerceRegles.php';

$auth = new AuthController();
$u = $auth->profile();
if (!$u) {
    header('Location: ../login.php');
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: mesCommandes.php');
    exit;
}

$cp = new CommandeController();
$cmd = $cp->getById($id);
if (!$cmd || (int) ($cmd['id_acheteur'] ?? 0) !== (int) ($u['iduser'] ?? 0)) {
    header('Location: mesCommandes.php');
    exit;
}

$status = (string) ($cmd['statut'] ?? '');
if (in_array($status, ['brouillon', 'annulee'], true)) {
    header('Location: mesCommandes.php?facture=unavailable');
    exit;
}

$lignes = $cp->getLignes($id);
$statusLabels = [
    'brouillon' => 'Brouillon',
    'en_attente_paiement' => 'En attente de paiement',
    'payee' => 'Payée',
    'en_preparation' => 'En préparation',
    'expediee' => 'En cours de livraison',
    'livree' => 'Livrée',
    'annulee' => 'Annulée',
];
$paymentLabels = [
    'card' => 'Carte bancaire',
    'cash_on_delivery' => 'Paiement à la livraison (cash)',
];
$paymentMode = (string) ($cmd['mode_paiement'] ?? 'cash_on_delivery');
$pointsOrder = CommerceRegles::pointsFromAmount((float) ($cmd['montant_total'] ?? 0));
$invoiceNo = 'FAC-' . date('Ym') . '-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
$commandeDateRaw = (string) ($cmd['date_commande'] ?? '');
$commandeDateDisplay = $commandeDateRaw !== '' ? substr($commandeDateRaw, 0, 16) : date('Y-m-d H:i');
$cashierName = 'Nadia Ben Amor';
$cashierMatricule = 'PLK-CASH-024';
$cashierService = 'Service Caisse & Facturation';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Facture #<?= (int) $id ?> — ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
    <link rel="stylesheet" href="../assets/pdf-snapshot-export.css">
    <style>
        #invoiceDocument {
            --pl-ink: #0f172a;
            --pl-muted: #64748b;
            --pl-line: rgba(15, 23, 42, 0.08);
            --pl-accent: #0b66c3;
            --pl-accent2: #0891b2;
            font-family: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            color: var(--pl-ink);
            background: #fff;
            border: 1px solid var(--pl-line);
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(15, 23, 42, 0.04), 0 24px 48px rgba(15, 23, 42, 0.08);
            padding: 0;
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
        }
        .fo-invoice-wrap { max-width: 900px; margin: 24px auto; padding: 0 16px; }
        .fo-invoice-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
        .fo-inv-accent {
            height: 5px;
            background: linear-gradient(90deg, var(--pl-accent) 0%, var(--pl-accent2) 48%, #6366f1 100%);
        }
        .fo-inv-inner { padding: 28px 32px 32px; }
        .fo-inv-top {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 20px;
            align-items: start;
            margin-bottom: 28px;
        }
        @media (max-width: 640px) {
            .fo-inv-top { grid-template-columns: 1fr; }
            .fo-inv-doc-label { justify-self: start; }
        }
        .fo-inv-brand-row {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .fo-inv-mark {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(145deg, #0f172a 0%, #1e3a5f 100%);
            color: #fff;
            font-weight: 800;
            font-size: 1rem;
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(11, 102, 195, 0.22);
        }
        .fo-inv-company { font-size: 1.35rem; font-weight: 800; letter-spacing: -0.03em; margin: 0; line-height: 1.15; }
        .fo-inv-tagline { margin: 4px 0 0; font-size: 0.8rem; color: var(--pl-muted); font-weight: 500; }
        .fo-inv-doc-label {
            text-align: right;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.22em;
            color: var(--pl-accent);
            padding: 10px 14px;
            border: 1px solid rgba(11, 102, 195, 0.25);
            border-radius: 12px;
            background: linear-gradient(180deg, rgba(11, 102, 195, 0.06), rgba(255, 255, 255, 0));
        }
        .fo-inv-facts {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
            margin: 0;
            padding: 16px 18px;
            background: #f8fafc;
            border-radius: 14px;
            border: 1px solid var(--pl-line);
        }
        .fo-inv-facts > div { margin: 0; }
        .fo-inv-facts dt {
            margin: 0 0 4px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--pl-muted);
        }
        .fo-inv-facts dd { margin: 0; font-size: 0.92rem; font-weight: 700; color: var(--pl-ink); }
        .fo-inv-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            background: rgba(11, 102, 195, 0.1);
            color: var(--pl-accent);
        }
        .fo-inv-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .fo-inv-card {
            border: 1px solid var(--pl-line);
            border-radius: 14px;
            padding: 16px 18px;
            background: #fff;
        }
        .fo-inv-card h3 {
            margin: 0 0 12px;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--pl-muted);
        }
        .fo-inv-card p { margin: 0 0 6px; font-size: 0.88rem; line-height: 1.5; color: #334155; }
        .fo-inv-card p:last-child { margin-bottom: 0; }
        .fo-inv-card strong { color: var(--pl-ink); font-weight: 700; }
        .fo-inv-section-title {
            margin: 0 0 12px;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--pl-muted);
        }
        .fo-inv-table-wrap { overflow: hidden; border-radius: 12px; border: 1px solid var(--pl-line); margin-bottom: 20px; }
        .fo-inv-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }
        .fo-inv-table thead th {
            text-align: left;
            padding: 12px 14px;
            background: linear-gradient(180deg, #f8fafc, #f1f5f9);
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--pl-muted);
            border-bottom: 2px solid var(--pl-ink);
        }
        .fo-inv-table tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--pl-line);
            vertical-align: top;
            color: #334155;
        }
        .fo-inv-table tbody tr:last-child td { border-bottom: none; }
        .fo-inv-table td:last-child,
        .fo-inv-table th:last-child,
        .fo-inv-table td:nth-child(4),
        .fo-inv-table th:nth-child(4) { text-align: right; white-space: nowrap; }
        .fo-inv-table td:nth-child(3),
        .fo-inv-table th:nth-child(3) { text-align: center; }
        .fo-inv-total-bar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 28px;
        }
        .fo-inv-total-card {
            min-width: min(320px, 100%);
            padding: 18px 22px;
            border-radius: 14px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #0b66c3 160%);
            color: #fff;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.18);
        }
        .fo-inv-total-card span:first-child {
            display: block;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            opacity: 0.85;
            margin-bottom: 6px;
        }
        .fo-inv-total-card span:last-child {
            font-size: 1.65rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .fo-inv-foot {
            padding-top: 20px;
            border-top: 1px solid var(--pl-line);
            font-size: 0.72rem;
            color: var(--pl-muted);
            line-height: 1.55;
            margin-bottom: 24px;
        }
        .fo-inv-sign-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-end;
            gap: 24px;
        }
        .fo-inv-badge {
            padding: 14px 18px;
            border-radius: 12px;
            border: 1px dashed rgba(11, 102, 195, 0.35);
            background: rgba(11, 102, 195, 0.04);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--pl-accent);
            max-width: 200px;
            line-height: 1.45;
        }
        .fo-inv-sign-card {
            flex: 1;
            min-width: 240px;
            max-width: 320px;
            text-align: right;
            padding-top: 12px;
            border-top: 1px solid var(--pl-line);
        }
        .fo-sign-script {
            margin: 0 0 4px auto;
            display: block;
            width: 200px;
            height: 52px;
        }
        .fo-sign-name {
            margin: 0;
            font-size: 0.92rem;
            font-weight: 800;
            color: var(--pl-ink);
        }
        .fo-sign-role {
            margin: 6px 0 0;
            font-size: 0.78rem;
            color: var(--pl-muted);
            font-weight: 600;
        }
        .fo-sign-meta {
            margin: 10px 0 0;
            font-size: 0.74rem;
            color: var(--pl-muted);
            line-height: 1.45;
        }
        .fo-sign-date {
            margin: 8px 0 0;
            font-size: 0.72rem;
            color: #94a3b8;
            font-weight: 600;
        }
        html.dark-mode #invoiceDocument {
            --pl-ink: #f1f5f9;
            --pl-muted: #94a3b8;
            --pl-line: rgba(148, 163, 184, 0.15);
            background: #151b26;
            border-color: rgba(148, 163, 184, 0.18);
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.35);
        }
        html.dark-mode .fo-inv-facts { background: rgba(30, 41, 59, 0.55); }
        html.dark-mode .fo-inv-card { background: rgba(30, 41, 59, 0.35); }
        html.dark-mode .fo-inv-card p { color: #cbd5e1; }
        html.dark-mode .fo-inv-table thead th {
            background: linear-gradient(180deg, #1e293b, #0f172a);
            color: #94a3b8;
            border-bottom-color: #38bdf8;
        }
        html.dark-mode .fo-inv-table tbody td { color: #e2e8f0; border-bottom-color: rgba(148, 163, 184, 0.12); }
        html.dark-mode .fo-inv-mark {
            background: linear-gradient(145deg, #334155 0%, #0f172a 100%);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
        }
        html.dark-mode .fo-inv-doc-label {
            border-color: rgba(125, 211, 252, 0.35);
            background: linear-gradient(180deg, rgba(56, 189, 248, 0.12), transparent);
            color: #7dd3fc;
        }
        html.dark-mode .fo-inv-foot { border-top-color: rgba(148, 163, 184, 0.15); }
        html.dark-mode .fo-inv-sign-card { border-top-color: rgba(148, 163, 184, 0.15); }
        html.dark-mode .fo-inv-badge {
            border-color: rgba(125, 211, 252, 0.28);
            background: rgba(56, 189, 248, 0.08);
            color: #7dd3fc;
        }
        @media print {
            .navbar, .footer, .fo-invoice-actions { display: none !important; }
            .fo-invoice-wrap { margin: 0; max-width: none; padding: 0; }
            #invoiceDocument { box-shadow: none; border-radius: 0; border: none; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="fo-invoice-wrap">
    <div class="fo-invoice-actions">
        <a href="mesCommandes.php" class="fo-btn fo-btn--secondary" style="text-decoration:none">← Mes commandes</a>
        <button type="button" class="fo-btn fo-btn--primary" id="downloadInvoicePdf">Télécharger la facture (PDF)</button>
    </div>
    <article id="invoiceDocument">
        <div class="fo-inv-accent" aria-hidden="true"></div>
        <div class="fo-inv-inner">
            <header class="fo-inv-top">
                <div class="fo-inv-brand-row">
                    <div class="fo-inv-mark">PL</div>
                    <div>
                        <p class="fo-inv-company">ProLink</p>
                        <p class="fo-inv-tagline">Plateforme d’achats professionnels · Tunisie</p>
                    </div>
                </div>
                <div class="fo-inv-doc-label">FACTURE</div>
                <dl class="fo-inv-facts">
                    <div>
                        <dt>N° document</dt>
                        <dd><?= htmlspecialchars($invoiceNo) ?></dd>
                    </div>
                    <div>
                        <dt>Réf. commande</dt>
                        <dd>#<?= (int) $id ?></dd>
                    </div>
                    <div>
                        <dt>Date d’émission</dt>
                        <dd><?= htmlspecialchars($commandeDateDisplay) ?></dd>
                    </div>
                    <div>
                        <dt>Statut</dt>
                        <dd><span class="fo-inv-pill"><?= htmlspecialchars($statusLabels[$status] ?? $status) ?></span></dd>
                    </div>
                </dl>
            </header>

            <section class="fo-inv-grid" aria-label="Parties">
                <div class="fo-inv-card">
                    <h3>Facturer à</h3>
                    <p><strong><?= htmlspecialchars(trim((string) ($cmd['prenom'] ?? '') . ' ' . (string) ($cmd['nom'] ?? ''))) ?></strong></p>
                    <p><?= htmlspecialchars((string) ($cmd['email'] ?? '')) ?></p>
                    <p><?= htmlspecialchars((string) ($cmd['adresse_livraison'] ?? '')) ?></p>
                    <p><?= htmlspecialchars((string) ($cmd['code_postal'] ?? '')) ?> <?= htmlspecialchars((string) ($cmd['ville'] ?? '')) ?> · <?= htmlspecialchars((string) ($cmd['pays'] ?? 'Tunisie')) ?></p>
                </div>
                <div class="fo-inv-card">
                    <h3>Paiement & livraison</h3>
                    <p><strong>Mode :</strong> <?= htmlspecialchars($paymentLabels[$paymentMode] ?? $paymentMode) ?></p>
                    <p><strong>Livraison :</strong> incluse</p>
                    <p><strong>Fidélité :</strong> +<?= (int) $pointsOrder ?> pts sur cette commande</p>
                    <?php if (!empty($cmd['numero_suivi'])): ?>
                        <p><strong>Suivi :</strong> <?= htmlspecialchars((string) $cmd['numero_suivi']) ?></p>
                    <?php endif; ?>
                </div>
            </section>

            <h2 class="fo-inv-section-title">Détail des articles</h2>
            <div class="fo-inv-table-wrap">
                <table class="fo-inv-table">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Réf.</th>
                            <th>Qté</th>
                            <th>Prix unit.</th>
                            <th>Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($lignes as $l): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($l['designation'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string) ($l['reference'] ?? '')) ?></td>
                            <td><?= (int) ($l['quantite'] ?? 0) ?></td>
                            <td><?= number_format((float) ($l['prix_unitaire'] ?? 0), 3, ',', ' ') ?> TND</td>
                            <td><?= number_format((float) ($l['prix_unitaire'] ?? 0) * (int) ($l['quantite'] ?? 0), 3, ',', ' ') ?> TND</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="fo-inv-total-bar">
                <div class="fo-inv-total-card">
                    <span>Total à payer</span>
                    <span><?= number_format((float) ($cmd['montant_total'] ?? 0), 3, ',', ' ') ?> TND</span>
                </div>
            </div>

            <footer class="fo-inv-foot">
                Document électronique conservé pour le dossier client — ProLink.
            </footer>

            <div class="fo-inv-sign-row">
                <div class="fo-inv-badge">Référence<br>interne</div>
                <div class="fo-inv-sign-card">
                    <svg class="fo-sign-script" viewBox="0 0 200 52" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Signature">
                        <path d="M6 38 Q18 12 34 28 T62 22 Q74 18 82 34 Q92 14 104 28 Q118 12 132 26 Q146 40 164 22 Q178 10 192 28" fill="none" stroke="#0b66c3" stroke-width="2.4" stroke-linecap="round"/>
                        <path d="M118 42 Q148 38 178 36" fill="none" stroke="#0b66c3" stroke-width="1.8" stroke-linecap="round" opacity="0.85"/>
                    </svg>
                    <p class="fo-sign-name"><?= htmlspecialchars($cashierName) ?></p>
                    <p class="fo-sign-role">Service facturation — ProLink</p>
                    <p class="fo-sign-meta">
                        ID <?= htmlspecialchars($cashierMatricule) ?> · <?= htmlspecialchars($cashierService) ?>
                    </p>
                    <p class="fo-sign-date">Horodaté le <?= htmlspecialchars($commandeDateDisplay) ?></p>
                </div>
            </div>
        </div>
    </article>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script>
(function () {
    var btn = document.getElementById('downloadInvoicePdf');
    var target = document.getElementById('invoiceDocument');
    if (!btn || !target) return;

    function buildPdf(canvas) {
        var jsPDF = window.jspdf.jsPDF;
        var pdf = new jsPDF('p', 'mm', 'a4');
        var pageW = 210;
        var pageH = 297;
        var margin = 12;
        var imgW = pageW - margin * 2;
        var imgH = canvas.height * imgW / canvas.width;
        var imgData = canvas.toDataURL('image/png', 0.92);
        var headerH = 7;
        var y0 = margin + headerH;
        var availH = pageH - y0 - margin;

        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(9);
        pdf.setTextColor(100, 116, 139);
        pdf.text('ProLink · Facture commande #<?= (int) $id ?>', margin, margin + 5);

        if (imgH <= availH) {
            pdf.addImage(imgData, 'PNG', margin, y0, imgW, imgH);
        } else {
            var slicePx = Math.floor(availH * canvas.width / imgW);
            var offsetPx = 0;
            var remaining = imgH;
            var page = 0;
            while (remaining > 0) {
                if (page > 0) {
                    pdf.addPage();
                    y0 = margin;
                    availH = pageH - margin * 2;
                    slicePx = Math.floor(availH * canvas.width / imgW);
                }
                var hPx = Math.min(slicePx, canvas.height - offsetPx);
                var slice = document.createElement('canvas');
                slice.width = canvas.width;
                slice.height = hPx;
                var ctx = slice.getContext('2d');
                if (!ctx) break;
                ctx.drawImage(canvas, 0, offsetPx, canvas.width, hPx, 0, 0, canvas.width, hPx);
                var part = slice.toDataURL('image/png', 0.92);
                var partH = hPx * imgW / canvas.width;
                pdf.addImage(part, 'PNG', margin, y0, imgW, partH);
                remaining -= partH;
                offsetPx += hPx;
                page++;
                if (page > 40) break;
            }
        }
        pdf.save('facture-prolink-commande-<?= (int) $id ?>.pdf');
    }

    btn.addEventListener('click', function () {
        if (typeof html2canvas === 'undefined' || !window.jspdf) {
            window.print();
            return;
        }
        var oldTxt = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Préparation PDF...';

        function runCapture() {
            return html2canvas(target, {
                scale: 2,
                backgroundColor: '#ffffff',
                logging: false,
                onclone: function (doc) {
                    var inv = doc.getElementById('invoiceDocument');
                    doc.documentElement.classList.remove('dark-mode');
                    doc.body.classList.remove('dark-mode');
                    doc.documentElement.classList.add('pl-pdf-snapshot-html');
                    doc.documentElement.style.colorScheme = 'light';
                    doc.body.style.backgroundColor = '#ffffff';
                    doc.body.style.color = '#0f172a';
                    if (inv) {
                        inv.classList.add('pl-pdf-snapshot-root');
                        inv.style.colorScheme = 'light';
                    }
                }
            });
        }

        var p = (document.fonts && document.fonts.ready) ? document.fonts.ready.then(runCapture) : runCapture();
        p.then(buildPdf).finally(function () {
            btn.disabled = false;
            btn.textContent = oldTxt;
        });
    });
})();
</script>
</body>
</html>
