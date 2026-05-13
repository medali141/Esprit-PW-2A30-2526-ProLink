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

require_once __DIR__ . '/../../../../controller/AchatsCommerceController.php';
$ac = new AchatsCommerceController();

$annee = isset($_GET['y']) ? (int) $_GET['y'] : (int) date('Y');
if ($annee < 2000 || $annee > 2100) {
    $annee = (int) date('Y');
}

$flashOk = isset($_GET['ok']) ? (string) $_GET['ok'] : '';
$flashErr = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    try {
        if ($action === 'add_budget') {
            $libelle = (string) ($_POST['libelle'] ?? '');
            $y = (int) ($_POST['annee'] ?? $annee);
            $catRaw = trim((string) ($_POST['idcategorie'] ?? ''));
            $idcategorie = $catRaw === '' ? null : (int) $catRaw;
            $montant = (float) str_replace(',', '.', (string) ($_POST['montant_alloue'] ?? '0'));
            $ac->addBudget($libelle, $y, $idcategorie, $montant);
            header('Location: achatsBudget.php?y=' . $y . '&ok=add');
            exit;
        }
        if ($action === 'delete_budget') {
            $idb = (int) ($_POST['idbudget'] ?? 0);
            $ac->deleteBudget($idb);
            header('Location: achatsBudget.php?y=' . $annee . '&ok=del');
            exit;
        }
    } catch (Throwable $e) {
        $flashErr = $e->getMessage();
    }
}

$dash = $ac->getBudgetDashboard($annee);
$categories = $ac->listCategories();
$yearsOpts = range((int) date('Y') + 1, (int) date('Y') - 5);

$plReportBanner = [
    'title' => 'Budgets et engagement',
    'lead' => 'Exercice ' . $annee,
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Budget & engagement achats — ProLink</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
<link rel="stylesheet" href="../../commerce.css">
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Budget & engagement achats</div>
        <div class="actions">
            <button type="button" id="exportAchatsBudgetPdf" class="btn btn-secondary">Exporter PDF</button>
            <a href="gestionAchats.php" class="btn btn-secondary">← Achats</a>
            <a href="achatsFournisseurs.php" class="btn btn-secondary">Fournisseurs</a>
        </div>
    </div>

    <div class="container commerce-dashboard pl-pdf-export-root" id="achatsBudgetPdfRoot" style="max-width:1120px">
        <?php include __DIR__ . '/partials/pl_report_banner.php'; ?>

        <div class="commerce-hub-intro pl-intro-tight">
            <p>
                Comparez les <strong>enveloppes</strong> aux montants <strong>engagés</strong> (commandes payées et suivantes)
                et <strong>réalisés</strong> (commandes livrées). Les enveloppes globales utilisent le total commande ;
                les enveloppes par catégorie ne comptent que les lignes concernées.
            </p>
            <p class="hint" style="margin-top:10px;font-weight:600;color:#64748b">
                Aucune donnée budget n’est affichée sur la boutique — usage interne uniquement.
            </p>
        </div>

        <?php if ($flashOk === 'add'): ?>
            <div class="commerce-alert commerce-alert--ok">Budget enregistré.</div>
        <?php elseif ($flashOk === 'del'): ?>
            <div class="commerce-alert commerce-alert--ok">Budget supprimé.</div>
        <?php endif; ?>
        <?php if ($flashErr !== ''): ?>
            <div class="commerce-alert commerce-alert--err"><?= htmlspecialchars($flashErr) ?></div>
        <?php endif; ?>

        <form method="get" class="commerce-filters pl-pdf-hide" action="achatsBudget.php" style="margin-bottom:20px">
            <div class="commerce-filters__field">
                <label for="fy">Exercice</label>
                <select id="fy" name="y" onchange="this.form.submit()">
                    <?php foreach ($yearsOpts as $y): ?>
                        <option value="<?= (int) $y ?>" <?= $y === $annee ? 'selected' : '' ?>><?= (int) $y ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="commerce-filters__actions">
                <button type="submit" class="btn btn-secondary">Actualiser</button>
            </div>
        </form>

        <section class="commerce-kpi-grid" aria-label="Totaux agrégés">
            <article class="commerce-kpi-card">
                <p class="commerce-kpi-label">Σ budgets enregistrés</p>
                <p class="commerce-kpi-value"><?= number_format((float) $dash['totaux']['alloue_enveloppes'], 2, ',', ' ') ?> TND</p>
            </article>
            <article class="commerce-kpi-card">
                <p class="commerce-kpi-label">Engagé (plateforme)</p>
                <p class="commerce-kpi-value"><?= number_format((float) $dash['totaux']['engage_plateforme'], 2, ',', ' ') ?> TND</p>
            </article>
            <article class="commerce-kpi-card">
                <p class="commerce-kpi-label">Réalisé — livré</p>
                <p class="commerce-kpi-value"><?= number_format((float) $dash['totaux']['realise_plateforme'], 2, ',', ' ') ?> TND</p>
            </article>
        </section>

        <div class="commerce-form-card pl-budget-form-card pl-pdf-hide">
            <h3 class="commerce-card-title">Nouvelle enveloppe</h3>
            <p class="form-lead" style="margin-top:-8px">Ajoutez un plafond pour l’exercice sélectionné ou une autre année.</p>
            <form method="post" class="pl-budget-inline-form">
                <input type="hidden" name="action" value="add_budget">
                <div class="pl-grid-4">
                    <label class="field-first">Libellé
                        <input type="text" name="libelle" required maxlength="150" placeholder="Ex. Budget téléphonie">
                    </label>
                    <label>Année
                        <select name="annee">
                            <?php foreach ($yearsOpts as $y): ?>
                                <option value="<?= (int) $y ?>" <?= $y === $annee ? 'selected' : '' ?>><?= (int) $y ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Catégorie
                        <select name="idcategorie">
                            <option value="">— Globale (tout le panier) —</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int) $cat['idcategorie'] ?>">
                                    <?= htmlspecialchars((string) $cat['libelle']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Montant alloué (TND)
                        <input type="number" name="montant_alloue" min="0" step="0.01" required placeholder="0.00">
                    </label>
                </div>
                <div class="btn-submit-wrap">
                    <button type="submit" class="btn btn-primary">Enregistrer l’enveloppe</button>
                </div>
            </form>
        </div>

        <div class="commerce-form-card" style="margin-top:22px">
            <h3 class="commerce-card-title">Suivi par enveloppe — <?= (int) $annee ?></h3>
            <div class="commerce-table-wrap">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>Libellé</th>
                            <th>Périmètre</th>
                            <th>Alloué</th>
                            <th>Engagé</th>
                            <th>Réalisé</th>
                            <th>Consommation</th>
                            <th class="pl-pdf-hide"></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($dash['rows'] as $row): ?>
                        <?php
                        $pct = (float) ($row['pct_engage'] ?? 0);
                        $warn = !empty($row['alerte_depassement']);
                        $meterClass = $warn ? 'pl-meter__fill pl-meter__fill--alert' : 'pl-meter__fill';
                        ?>
                        <tr class="<?= $warn ? 'pl-row-alert' : '' ?>">
                            <td><strong><?= htmlspecialchars((string) ($row['libelle'] ?? '')) ?></strong></td>
                            <td>
                                <?php if (!empty($row['idcategorie'])): ?>
                                    <span class="commerce-cat-pill" title="<?= htmlspecialchars((string) ($row['categorie_code'] ?? '')) ?>">
                                        <?= htmlspecialchars((string) ($row['categorie_libelle'] ?? '')) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="pl-scope-global">Plateforme entière</span>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format((float) ($row['montant_alloue'] ?? 0), 2, ',', ' ') ?></td>
                            <td><?= number_format((float) ($row['montant_engage'] ?? 0), 2, ',', ' ') ?></td>
                            <td><?= number_format((float) ($row['montant_realise'] ?? 0), 2, ',', ' ') ?></td>
                            <td style="min-width:180px">
                                <div class="pl-meter" aria-hidden="true"><span class="<?= $meterClass ?>" style="width:<?= min(100, $pct) ?>%"></span></div>
                                <span class="pl-meter-label"><?= number_format($pct, 1, ',', ' ') ?> % engagé<?= $warn ? ' · dépassement' : '' ?></span>
                            </td>
                            <td class="pl-pdf-hide">
                                <form method="post" class="commerce-actions" onsubmit="return confirm('Supprimer cette enveloppe ?');">
                                    <input type="hidden" name="action" value="delete_budget">
                                    <input type="hidden" name="idbudget" value="<?= (int) ($row['idbudget'] ?? 0) ?>">
                                    <button type="submit" class="btn btn-secondary" style="font-size:0.78rem">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (count($dash['rows']) === 0): ?>
                        <tr><td colspan="7" style="text-align:center;padding:28px;color:#64748b">Aucun budget pour cette année. Créez une enveloppe ci-dessus.</td></tr>
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
    'buttonId' => 'exportAchatsBudgetPdf',
    'rootId' => 'achatsBudgetPdfRoot',
    'footerLine' => 'ProLink · Budget achats ' . $annee . ' · ' . date('d/m/Y'),
    'fileName' => 'prolink-budget-' . $annee . '.pdf',
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
</script>
</body>
</html>
