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
require_once __DIR__ . '/../../../../controller/ProduitController.php';
require_once __DIR__ . '/../../../../controller/CommandeController.php';
require_once __DIR__ . '/../../../../controller/AchatsCommerceController.php';
require_once __DIR__ . '/../../../../controller/AchatsStockOffresController.php';
require_once __DIR__ . '/../../../../controller/ReclamationCommandeController.php';

$pp = new ProduitController();
$cp = new CommandeController();
$commerce = new AchatsCommerceController();
$stockAo = new AchatsStockOffresController();
$reclamCtl = new ReclamationCommandeController();

$nProd = count($pp->listAllAdmin());
$nCmd = $cp->countAll();
$annee = (int) date('Y');
$nbBudgetLines = count($commerce->listBudgets($annee));
$nbAoPub = $stockAo->countAppelsOffresPublies();
$reapproRows = $stockAo->getReapproDashboard(90);
$nbStockCrit = count(array_filter($reapproRows, static fn (array $r): bool => ($r['niveau_alerte'] ?? '') === 'critique'));
$nbFournisseursSuivis = count($commerce->getFournisseurIndicateurs());
$reclamStats = $reclamCtl->getAdminStats();
$nbReclamOuvertes = (int) ($reclamStats['ouvertes'] ?? 0) + (int) ($reclamStats['en_cours'] ?? 0);

$plReportBanner = [
    'title' => 'Vue synthèse',
    'lead' => 'Liens et indicateurs de la rubrique gestion achats',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion d'achats — ProLink</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
<link rel="stylesheet" href="../../commerce.css">
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Gestion d'achats</div>
        <div class="actions">
            <button type="button" id="exportGestionAchatsPdf" class="btn btn-secondary">Exporter PDF</button>
        </div>
    </div>

    <div class="container commerce-dashboard pl-gestion-wrap pl-pdf-export-root" id="gestionAchatsPdfRoot" style="max-width:1180px">
        <?php include __DIR__ . '/partials/pl_report_banner.php'; ?>

        <header class="commerce-hub-intro pl-gestion-intro">
            <div class="pl-gestion-intro__text">
                <p>
                    Budgets, fournisseurs, stocks, consultations : accès depuis les cartes ci-dessous.
                    Les pastilles catalogue et les réponses AO côté vendeurs concernent uniquement réapprovisionnement et appels d’offres publiés.
                </p>
            </div>
            <div class="commerce-kpi-grid pl-gestion-kpis">
                <div class="commerce-kpi-card pl-gestion-kpi pl-gestion-kpi--accent">
                    <p class="commerce-kpi-label">Catalogue</p>
                    <p class="commerce-kpi-value"><?= (int) $nProd ?></p>
                    <p class="pl-gestion-kpi-hint">Références</p>
                </div>
                <div class="commerce-kpi-card pl-gestion-kpi">
                    <p class="commerce-kpi-label">Commandes</p>
                    <p class="commerce-kpi-value"><?= (int) $nCmd ?></p>
                    <p class="pl-gestion-kpi-hint">Total plateforme</p>
                </div>
                <div class="commerce-kpi-card pl-gestion-kpi<?= $nbStockCrit > 0 ? ' pl-gestion-kpi--warn' : '' ?>">
                    <p class="commerce-kpi-label">Priorité stock</p>
                    <p class="commerce-kpi-value"><?= (int) $nbStockCrit ?></p>
                    <p class="pl-gestion-kpi-hint">SKU en critique réappro</p>
                </div>
                <div class="commerce-kpi-card pl-gestion-kpi">
                    <p class="commerce-kpi-label">AO publiés</p>
                    <p class="commerce-kpi-value"><?= (int) $nbAoPub ?></p>
                    <p class="pl-gestion-kpi-hint">Ouverts fournisseurs</p>
                </div>
                <div class="commerce-kpi-card pl-gestion-kpi">
                    <p class="commerce-kpi-label">Budgets <?= $annee ?></p>
                    <p class="commerce-kpi-value"><?= (int) $nbBudgetLines ?></p>
                    <p class="pl-gestion-kpi-hint">Lignes enveloppes</p>
                </div>
                <div class="commerce-kpi-card pl-gestion-kpi">
                    <p class="commerce-kpi-label">Fournisseurs suivis</p>
                    <p class="commerce-kpi-value"><?= (int) $nbFournisseursSuivis ?></p>
                    <p class="pl-gestion-kpi-hint">Avec commandes actives</p>
                </div>
                <div class="commerce-kpi-card pl-gestion-kpi<?= $nbReclamOuvertes > 0 ? ' pl-gestion-kpi--warn' : '' ?>">
                    <p class="commerce-kpi-label">Réclamations ouvertes</p>
                    <p class="commerce-kpi-value"><?= (int) $nbReclamOuvertes ?></p>
                    <p class="pl-gestion-kpi-hint">Ouvertes + en cours</p>
                </div>
            </div>
        </header>

        <section class="pl-gestion-section pl-gestion-section--fade" aria-labelledby="pl-ge-title">
            <div class="pl-gestion-section__bar">
                <h2 id="pl-ge-title" class="pl-gestion-section__title">Opérations courantes</h2>
                <p class="pl-gestion-section__lead">Vue d’ensemble, catalogue et traitement des commandes.</p>
            </div>
            <div class="commerce-hub-grid pl-gestion-grid">
                <article class="commerce-hub-card commerce-hub-card--orders pl-gestion-card pl-pdf-hide">
                    <div class="hub-icon" aria-hidden="true">S</div>
                    <h2>Statistiques</h2>
                    <div class="commerce-hub-stat" aria-hidden="true">—</div>
                    <p class="hub-hint">CA, répartition des statuts, top ventes</p>
                    <div class="commerce-hub-actions">
                        <a class="btn btn-primary" href="commerceStats.php">Statistiques</a>
                    </div>
                </article>
                <article class="commerce-hub-card commerce-hub-card--products pl-gestion-card">
                    <div class="hub-icon" aria-hidden="true">P</div>
                    <h2>Produits</h2>
                    <div class="commerce-hub-stat"><?= (int) $nProd ?></div>
                    <p class="hub-hint">Références catalogue (actives / inactives)</p>
                    <div class="commerce-hub-actions">
                        <a class="btn btn-primary" href="listProduits.php">Liste</a>
                        <a class="btn btn-secondary" href="addProduit.php">+ Ajouter</a>
                    </div>
                </article>
                <article class="commerce-hub-card commerce-hub-card--orders pl-gestion-card">
                    <div class="hub-icon" aria-hidden="true">C</div>
                    <h2>Commandes</h2>
                    <div class="commerce-hub-stat"><?= (int) $nCmd ?></div>
                    <p class="hub-hint">Statuts, livraisons et détails</p>
                    <div class="commerce-hub-actions">
                        <a class="btn btn-primary" href="listCommandes.php">Liste des commandes</a>
                    </div>
                </article>
                <article class="commerce-hub-card commerce-hub-card--orders pl-gestion-card">
                    <div class="hub-icon" aria-hidden="true">R</div>
                    <h2>Réclamations clients</h2>
                    <div class="commerce-hub-stat"><?= (int) $nbReclamOuvertes ?></div>
                    <p class="hub-hint">Réponses SAV, clôture des dossiers et points d’excuse.</p>
                    <div class="commerce-hub-actions">
                        <a class="btn btn-primary" href="reclamationsCommandes.php">Traiter les réclamations</a>
                    </div>
                </article>
            </div>
        </section>

        <section class="pl-gestion-section pl-gestion-section--fade pl-gestion-section--delay" aria-labelledby="pl-ge-title2">
            <div class="pl-gestion-section__bar">
                <h2 id="pl-ge-title2" class="pl-gestion-section__title">Engagement &amp; performance</h2>
                <p class="pl-gestion-section__lead">Enveloppes, performance fournisseurs, tarifs conventionnels et dossiers internes.</p>
            </div>
            <div class="commerce-hub-grid pl-gestion-grid">
                <article class="commerce-hub-card commerce-hub-card--budget pl-gestion-card">
                    <div class="hub-icon" aria-hidden="true">€</div>
                    <h2>Budget &amp; engagement</h2>
                    <div class="commerce-hub-stat"><?= (int) $nbBudgetLines ?></div>
                    <p class="pl-card-stat-caption">Lignes d’enveloppe · exercice <?= (int) $annee ?></p>
                    <p class="hub-hint">Montants alloués vs commandes engagées et livrées (alerte si dépassement).</p>
                    <p class="pl-metier-fo"><span class="pl-metier-fo__tag pl-metier-fo__tag--int">FO</span> Non affiché aux clients — pilotage interne uniquement.</p>
                    <div class="commerce-hub-actions">
                        <a class="btn btn-primary" href="achatsBudget.php">Budgets</a>
                    </div>
                </article>
                <article class="commerce-hub-card commerce-hub-card--supplier pl-gestion-card">
                    <div class="hub-icon" aria-hidden="true">F</div>
                    <h2>Fournisseurs</h2>
                    <div class="commerce-hub-stat"><?= (int) $nbFournisseursSuivis ?></div>
                    <p class="pl-card-stat-caption">Fournisseurs avec commandes prises en compte</p>
                    <p class="hub-hint">CA lignes, ponctualité des livraisons livrées, synthèse par niveau.</p>
                    <p class="pl-metier-fo"><span class="pl-metier-fo__tag pl-metier-fo__tag--int">FO</span> Pas d’indicateur public sur la boutique (tableau admin).</p>
                    <div class="commerce-hub-actions">
                        <a class="btn btn-primary" href="achatsFournisseurs.php">Tableau fournisseurs</a>
                    </div>
                </article>
                <article class="commerce-hub-card pl-gestion-card">
                    <div class="hub-icon" aria-hidden="true">T</div>
                    <h2>Tarifs, demandes, journal</h2>
                    <div class="commerce-hub-stat" aria-hidden="true">—</div>
                    <p class="hub-hint">
                        Contrats prix par vendeur/produit et période, dossiers multi-lignes avec statuts, enregistrement des opérations (AO, dossiers).
                    </p>
                    <div class="commerce-hub-actions pl-hub-actions-rows">
                        <a class="btn btn-secondary" href="achatsTarifsCadre.php">Tarifs négociés</a>
                        <a class="btn btn-secondary" href="achatsDemandesAchat.php">Demandes d’achat</a>
                        <a class="btn btn-secondary" href="achatsJournalAudit.php">Journal</a>
                    </div>
                </article>
            </div>
        </section>

        <section class="pl-gestion-section pl-gestion-section--fade pl-gestion-section--delay2" aria-labelledby="pl-ge-title3">
            <div class="pl-gestion-section__bar">
                <h2 id="pl-ge-title3" class="pl-gestion-section__title">Supply &amp; consultations</h2>
                <p class="pl-gestion-section__lead">Seuils de stock et appels d’offres&nbsp;: pastilles catalogue et réponses lorsque les AO sont publiés.</p>
            </div>
            <div class="commerce-hub-grid pl-gestion-grid">
                <article class="commerce-hub-card commerce-hub-card--reappro pl-gestion-card">
                    <div class="hub-icon" aria-hidden="true">R</div>
                    <h2>Réapprovisionnement</h2>
                    <div class="commerce-hub-stat"><?= (int) $nbStockCrit ?></div>
                    <p class="pl-card-stat-caption">Références en alerte <strong>critique</strong> (pas le total des lignes du tableau)</p>
                    <p class="hub-hint">Seuils, couverture de stock, suggestions de réassort sur 90 jours.</p>
                    <p class="pl-metier-fo"><span class="pl-metier-fo__tag pl-metier-fo__tag--pub">FO</span> Pastilles sur le catalogue + colonne « Pilotage » dans <em>Mes produits</em> vendeur.</p>
                    <div class="commerce-hub-actions">
                        <a class="btn btn-primary" href="achatsReappro.php">Tableau réappro</a>
                    </div>
                </article>
                <article class="commerce-hub-card commerce-hub-card--sourcing pl-gestion-card">
                    <div class="hub-icon" aria-hidden="true">A</div>
                    <h2>Appels d’offres</h2>
                    <div class="commerce-hub-stat"><?= (int) $nbAoPub ?></div>
                    <p class="pl-card-stat-caption">Consultations au statut <strong>publié</strong> (ouvertes aux réponses)</p>
                    <p class="hub-hint">Rédaction, publication, comparatif des offres, attribution.</p>
                    <p class="pl-metier-fo"><span class="pl-metier-fo__tag pl-metier-fo__tag--pub">FO</span> Page <em>Appels d’offres</em> (entrepreneurs / candidats) pour proposer prix et délais.</p>
                    <div class="commerce-hub-actions">
                        <a class="btn btn-primary" href="achatsSourcing.php">Admin AO</a>
                    </div>
                </article>
            </div>
        </section>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="../../../assets/backoffice-report-pdf.js"></script>
<script>
prolinkBindReportPdf(<?= json_encode([
    'buttonId' => 'exportGestionAchatsPdf',
    'rootId' => 'gestionAchatsPdfRoot',
    'footerLine' => 'ProLink · Synthèse achats · ' . date('d/m/Y'),
    'fileName' => 'prolink-synthese-achats.pdf',
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
</script>
</body>
</html>
