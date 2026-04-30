<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/CommandeController.php';
require_once __DIR__ . '/../../model/CommerceMetier.php';

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
$pointsOrder = CommerceMetier::pointsFromAmount((float) ($cmd['montant_total'] ?? 0));
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
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
    <style>
        .fo-invoice-wrap { max-width: 980px; margin: 24px auto; padding: 0 16px; }
        .fo-invoice-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; }
        .fo-invoice-doc {
            background: #fff;
            border: 1px solid rgba(15, 23, 42, 0.12);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            padding: 22px;
        }
        .fo-invoice-head {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }
        .fo-invoice-brand h1 { margin: 0; font-size: 1.5rem; }
        .fo-invoice-brand p { margin: 6px 0 0; color: #475569; }
        .fo-invoice-meta { text-align: right; }
        .fo-invoice-meta p { margin: 2px 0; }
        .fo-invoice-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 12px;
            margin: 14px 0 16px;
        }
        .fo-invoice-box {
            border: 1px solid rgba(15, 23, 42, 0.1);
            border-radius: 12px;
            padding: 12px;
            background: #f8fafc;
        }
        .fo-invoice-box h3 {
            margin: 0 0 6px;
            font-size: 0.78rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #64748b;
        }
        .fo-invoice-box p { margin: 3px 0; }
        .fo-invoice-total {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px dashed rgba(15, 23, 42, 0.2);
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            font-weight: 800;
        }
        .fo-invoice-sign {
            margin-top: 24px;
            display: flex;
            justify-content: flex-end;
            align-items: flex-end;
            gap: 22px;
            flex-wrap: wrap;
        }
        .fo-invoice-stamp {
            width: 132px;
            height: 132px;
            border-radius: 50%;
            border: 3px double rgba(11, 102, 195, 0.8);
            color: rgba(11, 102, 195, 0.92);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-weight: 900;
            font-size: 0.72rem;
            letter-spacing: 0.08em;
            line-height: 1.35;
            text-transform: uppercase;
            transform: rotate(-13deg);
            background: rgba(11, 102, 195, 0.03);
        }
        .fo-invoice-sign-card {
            min-width: 260px;
            text-align: center;
            border-top: 1px solid rgba(15, 23, 42, 0.25);
            padding-top: 10px;
        }
        .fo-sign-script {
            margin: 0 0 2px;
            width: 220px;
            height: 64px;
        }
        .fo-sign-name {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 800;
            letter-spacing: 0.03em;
            color: #0f172a;
        }
        .fo-sign-meta {
            margin: 6px 0 0;
            padding-top: 6px;
            border-top: 1px dashed rgba(15, 23, 42, 0.2);
            font-size: 0.8rem;
            color: #475569;
            line-height: 1.45;
            font-weight: 600;
        }
        .fo-sign-role {
            margin: 4px 0 0;
            font-size: 0.84rem;
            color: #475569;
            font-weight: 700;
        }
        .fo-sign-date {
            margin: 4px 0 0;
            font-size: 0.78rem;
            color: #64748b;
            font-weight: 600;
        }
        html.dark-mode .fo-invoice-doc { background: #151b26; border-color: rgba(148, 163, 184, 0.2); }
        html.dark-mode .fo-invoice-brand p,
        html.dark-mode .fo-sign-role,
        html.dark-mode .fo-sign-date,
        html.dark-mode .fo-sign-meta { color: #94a3b8; }
        html.dark-mode .fo-invoice-box { background: rgba(30, 41, 59, 0.62); border-color: rgba(148, 163, 184, 0.18); }
        html.dark-mode .fo-sign-name { color: #f1f5f9; }
        html.dark-mode .fo-invoice-stamp {
            color: rgba(125, 211, 252, 0.92);
            border-color: rgba(125, 211, 252, 0.8);
            background: rgba(56, 189, 248, 0.08);
        }
        @media print {
            .navbar, .footer, .fo-invoice-actions { display: none !important; }
            .fo-invoice-wrap { margin: 0; max-width: none; padding: 0; }
            .fo-invoice-doc { box-shadow: none; border: none; border-radius: 0; }
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
    <article class="fo-invoice-doc" id="invoiceDocument">
        <header class="fo-invoice-head">
            <div class="fo-invoice-brand">
                <h1>Facture ProLink</h1>
                <p>Plateforme d’achats professionnels — document commercial officiel</p>
            </div>
            <div class="fo-invoice-meta">
                <p><strong>N° facture:</strong> <?= htmlspecialchars($invoiceNo) ?></p>
                <p><strong>Commande:</strong> #<?= (int) $id ?></p>
                <p><strong>Date:</strong> <?= htmlspecialchars($commandeDateDisplay) ?></p>
                <p><strong>Statut:</strong> <?= htmlspecialchars($statusLabels[$status] ?? $status) ?></p>
            </div>
        </header>

        <section class="fo-invoice-grid">
            <div class="fo-invoice-box">
                <h3>Client</h3>
                <p><strong><?= htmlspecialchars(trim((string) ($cmd['prenom'] ?? '') . ' ' . (string) ($cmd['nom'] ?? ''))) ?></strong></p>
                <p><?= htmlspecialchars((string) ($cmd['email'] ?? '')) ?></p>
                <p><?= htmlspecialchars((string) ($cmd['adresse_livraison'] ?? '')) ?></p>
                <p><?= htmlspecialchars((string) ($cmd['code_postal'] ?? '')) ?> <?= htmlspecialchars((string) ($cmd['ville'] ?? '')) ?>, <?= htmlspecialchars((string) ($cmd['pays'] ?? 'Tunisie')) ?></p>
            </div>
            <div class="fo-invoice-box">
                <h3>Paiement & livraison</h3>
                <p><strong>Mode paiement:</strong> <?= htmlspecialchars($paymentLabels[$paymentMode] ?? $paymentMode) ?></p>
                <p><strong>Livraison:</strong> gratuite</p>
                <p><strong>Points générés:</strong> +<?= (int) $pointsOrder ?> pts</p>
                <?php if (!empty($cmd['numero_suivi'])): ?>
                    <p><strong>Suivi:</strong> <?= htmlspecialchars((string) $cmd['numero_suivi']) ?></p>
                <?php endif; ?>
            </div>
        </section>

        <div class="fo-table-wrap" style="margin-top:8px">
            <table class="table-modern">
                <thead>
                <tr><th>Produit</th><th>Référence</th><th>Qté</th><th>Prix unitaire</th><th>Total ligne</th></tr>
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

        <div class="fo-invoice-total">
            <span>Total TTC</span>
            <span><?= number_format((float) ($cmd['montant_total'] ?? 0), 3, ',', ' ') ?> TND</span>
        </div>

        <div class="fo-invoice-sign">
            <div class="fo-invoice-stamp" aria-label="Cachet ProLink">
                ProLink<br>Cachet<br>Officiel
            </div>
            <div class="fo-invoice-sign-card">
                <svg class="fo-sign-script" viewBox="0 0 240 72" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Signature ProLink">
                    <path d="M8 48 C22 20, 30 58, 45 36 C57 19, 62 64, 79 30 C90 8, 101 56, 116 32 C129 12, 137 45, 149 35 C161 25, 170 52, 183 34 C194 20, 206 40, 226 24" fill="none" stroke="#0b66c3" stroke-width="3.4" stroke-linecap="round"/>
                    <path d="M154 56 C170 54, 196 52, 226 50" fill="none" stroke="#0b66c3" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
                <p class="fo-sign-name"><?= htmlspecialchars($cashierName) ?></p>
                <p class="fo-sign-role">Caissier professionnel — ProLink</p>
                <p class="fo-sign-meta">
                    Matricule: <?= htmlspecialchars($cashierMatricule) ?><br>
                    <?= htmlspecialchars($cashierService) ?>
                </p>
                <p class="fo-sign-date">Validé le <?= htmlspecialchars($commandeDateDisplay) ?></p>
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
    btn.addEventListener('click', function () {
        if (typeof html2canvas === 'undefined' || !window.jspdf) {
            window.print();
            return;
        }
        var oldTxt = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Préparation PDF...';
        html2canvas(target, {
            scale: 2,
            backgroundColor: '#ffffff',
            onclone: function (doc) {
                // Force un rendu PDF en thème clair, quel que soit le thème actif côté utilisateur.
                doc.documentElement.classList.remove('dark-mode');
            }
        }).then(function (canvas) {
            var jsPDF = window.jspdf.jsPDF;
            var pdf = new jsPDF('p', 'mm', 'a4');
            var pageW = 210;
            var margin = 10;
            var imgW = pageW - margin * 2;
            var imgH = canvas.height * imgW / canvas.width;
            var imgData = canvas.toDataURL('image/png');
            pdf.addImage(imgData, 'PNG', margin, margin, imgW, imgH);
            pdf.save('facture-prolink-commande-<?= (int) $id ?>.pdf');
        }).finally(function () {
            btn.disabled = false;
            btn.textContent = oldTxt;
        });
    });
})();
</script>
</body>
</html>
