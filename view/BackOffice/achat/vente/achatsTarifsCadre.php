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
$idActor = (int) ($user['iduser'] ?? 0);

require_once __DIR__ . '/../../../../controller/AchatsProcessusAvancesController.php';
require_once __DIR__ . '/../../../../controller/ProduitController.php';
require_once __DIR__ . '/../../../../controller/AchatsStockOffresController.php';

$proc = new AchatsProcessusAvancesController();
$pp = new ProduitController();
$aoCtrl = new AchatsStockOffresController();

$flashOk = isset($_GET['ok']) ? (string) $_GET['ok'] : '';
$flashErr = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    try {
        if ($action === 'add_tarif') {
            $proc->addTarifCadre(
                (int) ($_POST['id_vendeur'] ?? 0),
                (int) ($_POST['idproduit'] ?? 0),
                (float) str_replace(',', '.', (string) ($_POST['prix_negocie'] ?? '0')),
                trim((string) ($_POST['date_debut'] ?? '')),
                trim((string) ($_POST['date_fin'] ?? '')),
                trim((string) ($_POST['reference_contrat'] ?? '')) ?: null,
                trim((string) ($_POST['commentaire'] ?? '')) ?: null,
                $idActor
            );
            header('Location: achatsTarifsCadre.php?ok=add');
            exit;
        }
        if ($action === 'delete_tarif') {
            $proc->deleteTarifCadre((int) ($_POST['idtarif'] ?? 0), $idActor);
            header('Location: achatsTarifsCadre.php?ok=del');
            exit;
        }
    } catch (Throwable $e) {
        $flashErr = $e->getMessage();
    }
}

$rows = $proc->listTarifsCadre();
$produits = $pp->listAllAdmin();
$today = date('Y-m-d');

$nomsVendeurs = [];
foreach ($aoCtrl->listVendeursPourOffres() as $v) {
    $uid = (int) ($v['iduser'] ?? 0);
    if ($uid > 0) {
        $nomsVendeurs[$uid] = trim(($v['prenom'] ?? '') . ' ' . ($v['nom'] ?? ''));
    }
}

$plReportBanner = [
    'title' => 'Tarifs négociés',
    'lead' => 'Grille vendeur, produit, période',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tarifs cadre — ProLink</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
<link rel="stylesheet" href="../../commerce.css">
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Tarifs négociés</div>
        <div class="actions">
            <button type="button" id="exportAchatsTarifsCadrePdf" class="btn btn-secondary">Exporter PDF</button>
            <a href="gestionAchats.php" class="btn btn-secondary">← Achats</a>
            <a href="achatsJournalAudit.php" class="btn btn-secondary">Journal</a>
        </div>
    </div>

    <div class="container commerce-dashboard pl-pdf-export-root" id="achatsTarifsCadrePdfRoot" style="max-width:1120px">
        <?php include __DIR__ . '/partials/pl_report_banner.php'; ?>

        <header class="commerce-hub-intro pl-intro-supplier">
            <p>
                Prix conventionné par vendeur catalogue, référence produit et période [début – fin].
                Contrôles&nbsp;: pas de chevauchement sur la même paire&nbsp;; vendeur conforme au vendeur assigné au produit.
                Colonne « catalogue » pour comparaison avec le prix liste actuel du SKU.
            </p>
            <p class="hint" style="margin-top:10px;font-weight:600;color:#64748b">
                Le prix affiché sur la boutique utilise le prix catalogue produit&nbsp;; cette grille reste une référence interne sauf évolution fonctionnelle.
            </p>
        </header>

        <?php if ($flashOk === 'add'): ?>
            <p class="alert alert-success">Contrat cadre ajouté (traçabilité dans le journal d’audit).</p>
        <?php elseif ($flashOk === 'del'): ?>
            <p class="alert alert-success">Contrat cadre retiré.</p>
        <?php endif; ?>
        <?php if ($flashErr !== ''): ?>
            <p class="alert alert-danger"><?= htmlspecialchars($flashErr) ?></p>
        <?php endif; ?>

        <div class="commerce-form-card pl-pdf-hide" style="margin-top:22px">
            <h3 class="commerce-card-title">Nouvelle période tarifaire</h3>
            <form method="post" class="commerce-form commerce-form-stack">
                <input type="hidden" name="action" value="add_tarif">
                <div class="commerce-form-grid">
                    <label class="field">
                        <span>Vendeur (fournisseur catalogue)</span>
                        <select name="id_vendeur" required id="tc-vendeur">
                            <?php if ($nomsVendeurs === []): ?>
                                <option value="">— Aucun profil vendeur —</option>
                            <?php else: ?>
                                <?php foreach ($nomsVendeurs as $vid => $label): ?>
                                    <option value="<?= (int) $vid ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </label>
                    <label class="field commerce-form-span2">
                        <span>Produit</span>
                        <select name="idproduit" required id="tc-produit">
                            <?php foreach ($produits as $pr): ?>
                                <option value="<?= (int) $pr['idproduit'] ?>"
                                        data-vendeur="<?= (int) ($pr['id_vendeur'] ?? 0) ?>">
                                    <?= htmlspecialchars($pr['reference'] ?? '') ?>
                                    · <?= htmlspecialchars($pr['designation'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="field">
                        <span>Prix négocié (TND)</span>
                        <input type="text" inputmode="decimal" name="prix_negocie" required placeholder="0.00">
                    </label>
                    <label class="field">
                        <span>Début</span>
                        <input type="date" name="date_debut" required value="<?= htmlspecialchars($today) ?>">
                    </label>
                    <label class="field">
                        <span>Fin</span>
                        <input type="date" name="date_fin" required>
                    </label>
                    <label class="field">
                        <span>Réf. contrat (optionnel)</span>
                        <input type="text" name="reference_contrat" maxlength="120" placeholder="CC-2026-…">
                    </label>
                    <label class="field commerce-form-span2">
                        <span>Commentaire</span>
                        <input type="text" name="commentaire" maxlength="400">
                    </label>
                </div>
                <button type="submit" class="btn btn-primary">Enregistrer le tarif cadre</button>
            </form>
        </div>

        <div class="commerce-form-card" style="margin-top:22px">
            <h3 class="commerce-card-title">Contrats enregistrés</h3>
            <div class="commerce-table-wrap">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Période</th>
                            <th>Fournisseur</th>
                            <th>Produit</th>
                            <th>Catalogue</th>
                            <th>Négocié</th>
                            <th>Écart</th>
                            <th>État</th>
                            <th class="pl-pdf-hide"></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $r): ?>
                        <?php
                        $cat = (float) ($r['prix_catalogue'] ?? 0);
                        $neg = (float) ($r['prix_negocie'] ?? 0);
                        $ecart = $cat > 1e-6 ? round(100 * ($cat - $neg) / $cat, 1) : null;
                        $d1 = (string) ($r['date_debut'] ?? '');
                        $d2 = (string) ($r['date_fin'] ?? '');
                        $actif = $today >= $d1 && $today <= $d2;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($d1) ?> → <?= htmlspecialchars($d2) ?></td>
                            <td><?= htmlspecialchars(trim(($r['vendeur_prenom'] ?? '') . ' ' . ($r['vendeur_nom'] ?? ''))) ?></td>
                            <td>
                                <strong><?= htmlspecialchars((string) ($r['reference'] ?? '')) ?></strong><br>
                                <span class="hint"><?= htmlspecialchars((string) ($r['designation'] ?? '')) ?></span>
                            </td>
                            <td><?= number_format($cat, 2, ',', ' ') ?></td>
                            <td><?= number_format($neg, 2, ',', ' ') ?></td>
                            <td>
                                <?php if ($ecart !== null): ?>
                                    <?= $ecart >= 0 ? '−' . htmlspecialchars((string) $ecart) : '+' . htmlspecialchars((string) abs($ecart)) ?> %
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($actif): ?>
                                    <span class="pl-score-badge pl-score-badge--excellent">Actif</span>
                                <?php elseif ($today < $d1): ?>
                                    <span class="pl-score-badge pl-score-badge--pending">À venir</span>
                                <?php else: ?>
                                    <span class="pl-score-badge pl-score-badge--watch">Expiré</span>
                                <?php endif; ?>
                            </td>
                            <td class="pl-pdf-hide">
                                <form method="post" onsubmit="return confirm('Supprimer ce contrat cadre ?');">
                                    <input type="hidden" name="action" value="delete_tarif">
                                    <input type="hidden" name="idtarif" value="<?= (int) ($r['idtarif'] ?? 0) ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm">Retirer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($rows === []): ?>
                        <tr><td colspan="8" class="hint">Aucun contrat — créez une première période ci-dessus.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="../../../assets/backoffice-report-pdf.js"></script>
<script>
prolinkBindReportPdf(<?= json_encode([
    'buttonId' => 'exportAchatsTarifsCadrePdf',
    'rootId' => 'achatsTarifsCadrePdfRoot',
    'footerLine' => 'ProLink · Tarifs négociés · ' . date('d/m/Y'),
    'fileName' => 'prolink-tarifs-negocies.pdf',
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
</script>
<script>
(function () {
    var vSel = document.getElementById('tc-vendeur');
    var pSel = document.getElementById('tc-produit');
    if (!vSel || !pSel) return;
    function filterProduits() {
        var vid = String(vSel.value);
        var opts = pSel.querySelectorAll('option');
        var firstVisible = null;
        opts.forEach(function (o) {
            var pv = String(o.getAttribute('data-vendeur') || '');
            var ok = pv === vid;
            o.hidden = !ok;
            if (ok && !firstVisible) firstVisible = o;
        });
        if (firstVisible) {
            firstVisible.selected = true;
        }
    }
    vSel.addEventListener('change', filterProduits);
    filterProduits();
})();
</script>
</body>
</html>
