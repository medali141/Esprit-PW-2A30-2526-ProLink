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

$proc = new AchatsProcessusAvancesController();
$pp = new ProduitController();
$produits = $pp->listAllAdmin();

$focusId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$flashOk = isset($_GET['ok']) ? (string) $_GET['ok'] : '';
$flashErr = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    try {
        if ($action === 'create_da') {
            $id = $proc->createDemandeAchat(
                (string) ($_POST['libelle'] ?? ''),
                trim((string) ($_POST['notes'] ?? '')) ?: null,
                $idActor
            );
            header('Location: achatsDemandesAchat.php?id=' . $id . '&ok=created');
            exit;
        }
        $iddaPost = (int) ($_POST['idda'] ?? 0);
        if ($action === 'add_ligne') {
            $proc->addLigneDemande(
                $iddaPost,
                (int) ($_POST['idproduit'] ?? 0),
                (int) ($_POST['quantite'] ?? 0),
                (float) str_replace(',', '.', (string) ($_POST['prix_estime'] ?? '0')),
                $idActor
            );
            header('Location: achatsDemandesAchat.php?id=' . $iddaPost . '&ok=ligne');
            exit;
        }
        if ($action === 'del_ligne') {
            $proc->deleteLigneDemande((int) ($_POST['iddal'] ?? 0), $iddaPost, $idActor);
            header('Location: achatsDemandesAchat.php?id=' . $iddaPost . '&ok=del_ligne');
            exit;
        }
        if ($action === 'submit_da') {
            $proc->submitDemandeAchat($iddaPost, $idActor);
            header('Location: achatsDemandesAchat.php?id=' . $iddaPost . '&ok=submitted');
            exit;
        }
        if ($action === 'valider_da') {
            $proc->validerDemandeAchat($iddaPost, $idActor);
            header('Location: achatsDemandesAchat.php?id=' . $iddaPost . '&ok=validated');
            exit;
        }
        if ($action === 'rejeter_da') {
            $proc->rejeterDemandeAchat($iddaPost, (string) ($_POST['motif_rejet'] ?? ''), $idActor);
            header('Location: achatsDemandesAchat.php?id=' . $iddaPost . '&ok=rejected');
            exit;
        }
    } catch (Throwable $e) {
        $flashErr = $e->getMessage();
    }
}

$liste = $proc->listDemandesAchat();
$detail = $focusId > 0 ? $proc->getDemandeAchat($focusId) : null;
$lignes = $detail ? $proc->listLignesDemande($focusId) : [];
$statut = $detail ? (string) ($detail['statut'] ?? '') : '';

$plReportBanner = [
    'title' => 'Demandes d’achat',
    'lead' => $focusId > 0 ? 'Dossier #' . $focusId : 'Liste et détail',
];
$daPdfName = $focusId > 0 ? 'prolink-demande-achat-' . $focusId . '.pdf' : 'prolink-demandes-achat.pdf';
$daPdfFooter = $focusId > 0
    ? ('ProLink · Demande #' . $focusId . ' · ' . date('d/m/Y'))
    : ('ProLink · Demandes achat · ' . date('d/m/Y'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demandes d’achat — ProLink</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
<link rel="stylesheet" href="../../commerce.css">
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Demandes d’achat internes</div>
        <div class="actions">
            <button type="button" id="exportAchatsDemandesPdf" class="btn btn-secondary">Exporter PDF</button>
            <a href="gestionAchats.php" class="btn btn-secondary">← Achats</a>
            <a href="achatsJournalAudit.php" class="btn btn-secondary">Journal</a>
        </div>
    </div>

    <div class="container commerce-dashboard pl-pdf-export-root" id="achatsDemandesPdfRoot" style="max-width:1120px">
        <?php include __DIR__ . '/partials/pl_report_banner.php'; ?>

        <header class="commerce-hub-intro pl-intro-supplier">
            <p>
                Dossiers multi-lignes (référence produit, quantité, prix unitaire estimé). Statuts&nbsp;:
                brouillon (édition autorisée), soumise, puis validée ou rejetée.
            </p>
            <div class="pl-da-workflow-strip pl-pdf-hide" aria-label="États">
                <span class="pl-da-step"><strong>Brouillon</strong> lignes éditables</span>
                <span class="pl-da-arrow">→</span>
                <span class="pl-da-step"><strong>Soumise</strong></span>
                <span class="pl-da-arrow">→</span>
                <span class="pl-da-step"><strong>Validée</strong> ou <strong>rejetée</strong></span>
            </div>
        </header>

        <?php
        $msgs = [
            'created' => 'Demande créée.',
            'ligne' => 'Ligne ajoutée.',
            'del_ligne' => 'Ligne retirée.',
            'submitted' => 'Demande soumise pour décision.',
            'validated' => 'Demande validée.',
            'rejected' => 'Demande rejetée avec motif conservé.',
        ];
        ?>
        <?php if ($flashOk !== '' && isset($msgs[$flashOk])): ?>
            <p class="alert alert-success"><?= htmlspecialchars($msgs[$flashOk]) ?></p>
        <?php endif; ?>
        <?php if ($flashErr !== ''): ?>
            <p class="alert alert-danger"><?= htmlspecialchars($flashErr) ?></p>
        <?php endif; ?>

        <div class="commerce-form-card pl-pdf-hide" style="margin-top:22px">
            <h3 class="commerce-card-title">Nouvelle demande</h3>
            <form method="post" class="commerce-form commerce-form-stack">
                <input type="hidden" name="action" value="create_da">
                <div class="commerce-form-grid">
                    <label class="field commerce-form-span2">
                        <span>Libellé</span>
                        <input type="text" name="libelle" maxlength="200" required placeholder="Réassort équipe projets…">
                    </label>
                    <label class="field commerce-form-span2">
                        <span>Notes internes</span>
                        <textarea name="notes" rows="3" maxlength="4000"></textarea>
                    </label>
                </div>
                <button type="submit" class="btn btn-primary">Créer en brouillon</button>
            </form>
        </div>

        <div class="pl-da-two-col">
            <div class="commerce-form-card pl-da-panel">
                <h3 class="commerce-card-title">Toutes les demandes</h3>
                <div class="commerce-table-wrap">
                    <table class="table-modern table-modern--dense">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Libellé</th>
                                <th>État</th>
                                <th>Lignes</th>
                                <th>Σ estimé</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($liste as $d): ?>
                            <?php
                            $sid = (int) ($d['idda'] ?? 0);
                            $stDa = htmlspecialchars((string) ($d['statut'] ?? ''));
                            ?>
                            <tr class="<?= $focusId === $sid ? 'pl-row-active' : '' ?>">
                                <td><a href="achatsDemandesAchat.php?id=<?= $sid ?>">#<?= $sid ?></a></td>
                                <td><?= htmlspecialchars((string) ($d['libelle'] ?? '')) ?></td>
                                <td><span class="pl-da-tag pl-da-tag--<?= htmlspecialchars(str_replace('_', '-', $d['statut'] ?? '')) ?>"><?= $stDa ?></span></td>
                                <td><?= (int) ($d['nb_lignes'] ?? 0) ?></td>
                                <td><?= number_format((float) ($d['montant_estime'] ?? 0), 2, ',', ' ') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($liste === []): ?>
                            <tr><td colspan="5" class="hint">Aucune demande.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="commerce-form-card pl-da-panel">
                <?php if ($detail): ?>
                    <h3 class="commerce-card-title">Détail #<?= (int) $focusId ?></h3>
                    <p class="hint">Créée par <?= htmlspecialchars(trim(($detail['prenom'] ?? '') . ' ' . ($detail['nom'] ?? ''))) ?></p>
                    <p><?= htmlspecialchars((string) ($detail['libelle'] ?? '')) ?></p>
                    <?php if (($detail['notes'] ?? '') !== ''): ?>
                        <p class="hint"><?= nl2br(htmlspecialchars((string) $detail['notes'])) ?></p>
                    <?php endif; ?>
                    <p><strong>Statut&nbsp;:</strong> <span class="pl-da-tag pl-da-tag--<?= htmlspecialchars(str_replace('_', '-', $statut)) ?>"><?= htmlspecialchars($statut) ?></span></p>

                    <?php if ($statut === 'brouillon'): ?>
                        <div class="pl-pdf-hide">
                        <form method="post" class="commerce-form commerce-form-stack" style="margin-top:14px">
                            <input type="hidden" name="action" value="add_ligne">
                            <input type="hidden" name="idda" value="<?= (int) $focusId ?>">
                            <div class="commerce-form-grid">
                                <label class="field commerce-form-span2">
                                    <span>Produit</span>
                                    <select name="idproduit" required>
                                        <?php foreach ($produits as $pr): ?>
                                            <option value="<?= (int) $pr['idproduit'] ?>">
                                                <?= htmlspecialchars(($pr['reference'] ?? '') . ' · ' . ($pr['designation'] ?? '') . ' (cat. ' . number_format((float) ($pr['prix_unitaire'] ?? 0), 2, ',', ' ') . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label class="field"><span>Quantité</span><input type="number" name="quantite" min="1" value="1" required></label>
                                <label class="field"><span>Prix estimé unitaire</span><input type="text" inputmode="decimal" name="prix_estime" required></label>
                            </div>
                            <button type="submit" class="btn btn-secondary">Ajouter ligne</button>
                        </form>
                        <form method="post" style="margin-top:14px">
                            <input type="hidden" name="action" value="submit_da">
                            <input type="hidden" name="idda" value="<?= (int) $focusId ?>">
                            <button type="submit" class="btn btn-primary">Soumettre pour validation</button>
                        </form>
                        </div>
                    <?php endif; ?>

                    <?php if ($statut === 'soumise'): ?>
                        <div class="pl-da-actions pl-pdf-hide" style="margin-top:14px">
                            <form method="post">
                                <input type="hidden" name="action" value="valider_da">
                                <input type="hidden" name="idda" value="<?= (int) $focusId ?>">
                                <button type="submit" class="btn btn-primary">Valider la demande</button>
                            </form>
                            <form method="post" class="commerce-form-inline">
                                <input type="hidden" name="action" value="rejeter_da">
                                <input type="hidden" name="idda" value="<?= (int) $focusId ?>">
                                <input type="text" name="motif_rejet" maxlength="500" required placeholder="Motif de rejet">
                                <button type="submit" class="btn btn-secondary">Rejeter</button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if (($detail['motif_rejet'] ?? '') !== '' && $statut === 'rejetee'): ?>
                        <p class="alert alert-danger" style="margin-top:14px">
                            <?= htmlspecialchars((string) $detail['motif_rejet']) ?>
                        </p>
                    <?php endif; ?>

                    <div class="commerce-table-wrap" style="margin-top:18px">
                        <table class="table-modern">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Qté</th>
                                    <th>Prix estimé</th>
                                    <th>Ligne Σ</th>
                                    <th>Prix catalogue</th>
                                    <?php if ($statut === 'brouillon'): ?><th class="pl-pdf-hide"></th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $colsLignes = $statut === 'brouillon' ? 6 : 5;
                            $sum = 0.0;
                            foreach ($lignes as $ln):
                                $q = (int) ($ln['quantite'] ?? 0);
                                $pu = (float) ($ln['prix_estime'] ?? 0);
                                $line = $q * $pu;
                                $sum += $line;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars((string) ($ln['reference'] ?? '')) ?></td>
                                    <td><?= $q ?></td>
                                    <td><?= number_format($pu, 2, ',', ' ') ?></td>
                                    <td><?= number_format($line, 2, ',', ' ') ?></td>
                                    <td><?= number_format((float) ($ln['prix_catalogue'] ?? 0), 2, ',', ' ') ?></td>
                                    <?php if ($statut === 'brouillon'): ?>
                                        <td class="pl-pdf-hide">
                                            <form method="post" onsubmit="return confirm('Retirer cette ligne ?');">
                                                <input type="hidden" name="action" value="del_ligne">
                                                <input type="hidden" name="idda" value="<?= (int) $focusId ?>">
                                                <input type="hidden" name="iddal" value="<?= (int) ($ln['iddal'] ?? 0) ?>">
                                                <button type="submit" class="btn btn-secondary btn-sm">×</button>
                                            </form>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ($lignes === []): ?>
                                <tr><td colspan="<?= (int) $colsLignes ?>" class="hint">Aucune ligne.</td></tr>
                            <?php endif; ?>
                            </tbody>
                            <?php if ($lignes !== []): ?>
                                <tfoot>
                                    <tr>
                                        <th colspan="3">Total estimé</th>
                                        <th><?= number_format($sum, 2, ',', ' ') ?></th>
                                        <?php if ($statut === 'brouillon'): ?>
                                            <th></th><th class="pl-pdf-hide"></th>
                                        <?php else: ?>
                                            <th></th>
                                        <?php endif; ?>
                                    </tr>
                                </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>

                <?php else: ?>
                    <h3 class="commerce-card-title">Détail</h3>
                    <p class="hint">Choisissez une demande dans la liste à gauche.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="../../../assets/backoffice-report-pdf.js"></script>
<script>
prolinkBindReportPdf(<?= json_encode([
    'buttonId' => 'exportAchatsDemandesPdf',
    'rootId' => 'achatsDemandesPdfRoot',
    'footerLine' => $daPdfFooter,
    'fileName' => $daPdfName,
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
</script>
</body>
</html>
