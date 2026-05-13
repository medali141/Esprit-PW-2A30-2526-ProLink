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

require_once __DIR__ . '/../../../../controller/AchatsStockOffresController.php';
$mc = new AchatsStockOffresController();

$flashOk = isset($_GET['ok']) ? (string) $_GET['ok'] : '';
$flashErr = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    try {
        if ($action === 'create_ao') {
            $id = $mc->createAppelOffre(
                (string) ($_POST['titre'] ?? ''),
                (string) ($_POST['description'] ?? ''),
                (string) ($_POST['date_limite'] ?? '')
            );
            header('Location: achatsSourcing.php?id=' . $id . '&ok=created');
            exit;
        }
        if ($action === 'publish_ao') {
            $mc->setStatutAppelOffre((int) ($_POST['idao'] ?? 0), 'publie');
            header('Location: achatsSourcing.php?id=' . (int) ($_POST['idao'] ?? 0) . '&ok=published');
            exit;
        }
        if ($action === 'cancel_ao') {
            $mc->setStatutAppelOffre((int) ($_POST['idao'] ?? 0), 'annule');
            header('Location: achatsSourcing.php?id=' . (int) ($_POST['idao'] ?? 0) . '&ok=cancelled');
            exit;
        }
        if ($action === 'add_reponse') {
            $idao = (int) ($_POST['idao'] ?? 0);
            $mc->addReponseOffre(
                $idao,
                (int) ($_POST['id_vendeur'] ?? 0),
                (float) str_replace(',', '.', (string) ($_POST['prix_propose'] ?? '0')),
                (int) ($_POST['delai_jours'] ?? 7),
                (string) ($_POST['notes'] ?? '')
            );
            header('Location: achatsSourcing.php?id=' . $idao . '&ok=response');
            exit;
        }
        if ($action === 'attribuer') {
            $idao = (int) ($_POST['idao'] ?? 0);
            $mc->attribuerReponse($idao, (int) ($_POST['idr'] ?? 0), (int) ($user['iduser'] ?? 0));
            header('Location: achatsSourcing.php?id=' . $idao . '&ok=attrib');
            exit;
        }
    } catch (Throwable $e) {
        $flashErr = $e->getMessage();
    }
}

$idao = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$detail = $idao > 0 ? $mc->getAppelOffre($idao) : null;
$reponses = ($detail !== null) ? $mc->listReponses($idao) : [];
$vendeurs = $mc->listVendeursPourOffres();
$liste = $mc->listAppelsOffres();

$bestPriceId = null;
if ($detail !== null && count($reponses) > 0 && (($detail['statut'] ?? '') !== 'attribue')) {
    $minP = null;
    foreach ($reponses as $rr) {
        $p = (float) ($rr['prix_propose'] ?? 0);
        if ($minP === null || $p < $minP) {
            $minP = $p;
            $bestPriceId = (int) ($rr['idr'] ?? 0);
        }
    }
}

function ao_statut_badge(string $st): string {
    $map = [
        'brouillon' => ['Brouillon', 'commerce-badge--brouillon'],
        'publie' => ['Publié', 'commerce-badge--payee'],
        'attribue' => ['Attribué', 'commerce-badge--livree'],
        'annule' => ['Annulé', 'commerce-badge--annulee'],
    ];
    $x = $map[$st] ?? [$st, 'commerce-badge--brouillon'];
    return '<span class="commerce-badge ' . htmlspecialchars($x[1]) . '">' . htmlspecialchars($x[0]) . '</span>';
}

if ($detail === null) {
    $plReportBanner = [
        'title' => 'Appels d’offres',
        'lead' => 'Liste des consultations et création',
    ];
} else {
    $plReportBanner = [
        'title' => 'AO #' . (int) ($detail['idao'] ?? 0),
        'lead' => (string) ($detail['titre'] ?? ''),
    ];
}
$sourcingPdfFooter = $detail === null
    ? 'ProLink · Appels d’offres · ' . date('d/m/Y')
    : 'ProLink · AO #' . (int) ($detail['idao'] ?? 0) . ' · ' . date('d/m/Y');
$sourcingPdfName = $detail === null
    ? 'prolink-appels-offres.pdf'
    : 'prolink-ao-' . (int) ($detail['idao'] ?? 0) . '.pdf';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Appels d’offres — ProLink</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
<link rel="stylesheet" href="../../commerce.css">
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Appels d’offres</div>
        <div class="actions">
            <button type="button" id="exportAchatsSourcingPdf" class="btn btn-secondary">Exporter PDF</button>
            <a href="gestionAchats.php" class="btn btn-secondary">← Achats</a>
            <a href="achatsReappro.php" class="btn btn-secondary">Réapprovisionnement</a>
        </div>
    </div>

    <div class="container commerce-dashboard pl-pdf-export-root" id="achatsSourcingPdfRoot" style="max-width:1120px">
        <?php include __DIR__ . '/partials/pl_report_banner.php'; ?>

        <?php if ($flashOk === 'created'): ?>
            <div class="commerce-alert commerce-alert--ok">Appel d’offres créé — complétez la publication et les réponses.</div>
            <?php elseif ($flashOk === 'published'): ?>
            <div class="commerce-alert commerce-alert--ok">AO publié — vous pouvez enregistrer les réponses fournisseurs.</div>
        <?php elseif ($flashOk === 'cancelled'): ?>
            <div class="commerce-alert commerce-alert--ok">AO annulé.</div>
        <?php elseif ($flashOk === 'response'): ?>
            <div class="commerce-alert commerce-alert--ok">Réponse enregistrée ou mise à jour.</div>
        <?php elseif ($flashOk === 'attrib'): ?>
            <div class="commerce-alert commerce-alert--ok">Réponse retenue — AO clôturé côté attribution.</div>
        <?php endif; ?>
        <?php if ($flashErr !== ''): ?>
            <div class="commerce-alert commerce-alert--err"><?= htmlspecialchars($flashErr) ?></div>
        <?php endif; ?>

        <?php if ($detail === null): ?>
            <div class="commerce-hub-intro pl-intro-sourcing">
                <p>
                    Création d’un besoin sous forme d’AO, collecte des propositions prix et délais auprès des vendeurs,
                    comparaison et choix de l’offre retenue dans l’outil.
                </p>
                <p class="hint" style="margin-top:10px;font-weight:600;color:#64748b">
                    Une fois l’AO passé en <strong>Publié</strong>, les entrepreneurs et candidats le voient dans leur menu
                    <strong>Appels d’offres</strong> (front office) et peuvent soumettre leur offre.
                </p>
            </div>

            <div class="commerce-form-card pl-ao-create pl-pdf-hide">
                <h3 class="commerce-card-title">Nouvel appel d’offres</h3>
                <form method="post">
                    <input type="hidden" name="action" value="create_ao">
                    <label class="field-first">Titre
                        <input type="text" name="titre" required maxlength="200" placeholder="Ex. Fourniture mobilier salle réunion">
                    </label>
                    <label>Description (critères, volumes…)
                        <textarea name="description" rows="4" maxlength="8000" placeholder="Contraintes techniques, lieu de livraison, etc."></textarea>
                    </label>
                    <label>Date limite de réponse
                        <input type="date" name="date_limite" required value="<?= htmlspecialchars(date('Y-m-d', strtotime('+21 days'))) ?>">
                    </label>
                    <div class="btn-submit-wrap">
                        <button type="submit" class="btn btn-primary">Créer en brouillon</button>
                    </div>
                </form>
            </div>

            <div class="commerce-form-card" style="margin-top:22px">
                <h3 class="commerce-card-title">Tous les appels d’offres</h3>
                <div class="commerce-table-wrap">
                    <table class="table-modern">
                        <thead>
                            <tr><th>Réf.</th><th>Titre</th><th>Limite</th><th>Statut</th><th>Offres</th><th class="pl-pdf-hide"></th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($liste as $ao): ?>
                            <tr>
                                <td><strong>#<?= (int) ($ao['idao'] ?? 0) ?></strong></td>
                                <td><?= htmlspecialchars((string) ($ao['titre'] ?? '')) ?></td>
                                <td><?= htmlspecialchars(substr((string) ($ao['date_limite'] ?? ''), 0, 10)) ?></td>
                                <td><?= ao_statut_badge((string) ($ao['statut'] ?? 'brouillon')) ?></td>
                                <td><?= (int) ($ao['nb_reponses'] ?? 0) ?></td>
                                <td class="pl-pdf-hide"><a class="btn btn-secondary" style="font-size:0.78rem;padding:6px 10px" href="achatsSourcing.php?id=<?= (int) ($ao['idao'] ?? 0) ?>">Ouvrir</a></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($liste) === 0): ?>
                            <tr><td colspan="6" style="text-align:center;padding:24px;color:#64748b">Aucun AO — créez-en un ci-dessus.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <?php
            $st = (string) ($detail['statut'] ?? 'brouillon');
            $winId = isset($detail['id_reponse_retenue']) ? (int) $detail['id_reponse_retenue'] : 0;
            ?>
            <div class="commerce-hub-intro pl-intro-sourcing">
                <p class="hint" style="margin-top:0">
                    <?= ao_statut_badge($st) ?>
                    · Limite : <strong><?= htmlspecialchars(substr((string) ($detail['date_limite'] ?? ''), 0, 10)) ?></strong>
                    · <?= count($reponses) ?> offre(s)
                </p>
                <div class="pl-pdf-hide" style="margin-top:14px;display:flex;flex-wrap:wrap;gap:10px">
                    <a href="achatsSourcing.php" class="btn btn-secondary">← Liste AO</a>
                    <?php if ($st === 'brouillon'): ?>
                        <form method="post">
                            <input type="hidden" name="action" value="publish_ao">
                            <input type="hidden" name="idao" value="<?= (int) ($detail['idao'] ?? 0) ?>">
                            <button type="submit" class="btn btn-primary">Publier</button>
                        </form>
                    <?php endif; ?>
                    <?php if ($st !== 'attribue' && $st !== 'annule'): ?>
                        <form method="post" onsubmit="return confirm('Annuler cet appel d’offres ?');">
                            <input type="hidden" name="action" value="cancel_ao">
                            <input type="hidden" name="idao" value="<?= (int) ($detail['idao'] ?? 0) ?>">
                            <button type="submit" class="btn btn-secondary">Annuler AO</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (trim((string) ($detail['description'] ?? '')) !== ''): ?>
                <div class="commerce-addr-block" style="margin-bottom:18px">
                    <?= nl2br(htmlspecialchars((string) ($detail['description'] ?? ''))) ?>
                </div>
            <?php endif; ?>

            <?php if ($winId > 0): ?>
                <?php
                $winner = null;
                foreach ($reponses as $rr) {
                    if ((int) ($rr['idr'] ?? 0) === $winId) {
                        $winner = $rr;
                        break;
                    }
                }
                ?>
                <div class="commerce-alert commerce-alert--ok">
                    Offre retenue :
                    <?php if ($winner): ?>
                        <strong><?= htmlspecialchars(trim((string) ($winner['prenom'] ?? '') . ' ' . (string) ($winner['nom'] ?? ''))) ?></strong>
                        — <?= number_format((float) ($winner['prix_propose'] ?? 0), 2, ',', ' ') ?> TND,
                        <?= (int) ($winner['delai_jours'] ?? 0) ?> j.
                    <?php else: ?>
                        #<?= $winId ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="commerce-form-card">
                <h3 class="commerce-card-title">Comparatif des offres</h3>
                <div class="commerce-table-wrap">
                    <table class="table-modern">
                        <thead>
                            <tr>
                                <th>Vendeur</th><th>Prix proposé</th><th>Délai</th><th>Notes</th>
                                <?php if ($st !== 'attribue' && $st !== 'annule'): ?><th class="pl-pdf-hide">Attribuer</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($reponses as $rr): ?>
                            <?php
                            $idr = (int) ($rr['idr'] ?? 0);
                            $isBest = $bestPriceId !== null && $idr === $bestPriceId;
                            $isWin = $winId > 0 && $idr === $winId;
                            ?>
                            <tr class="<?= $isWin ? 'pl-ao-row-win' : ($isBest ? 'pl-ao-row-best' : '') ?>">
                                <td>
                                    <strong><?= htmlspecialchars(trim((string) ($rr['prenom'] ?? '') . ' ' . (string) ($rr['nom'] ?? ''))) ?></strong><br>
                                    <span class="pl-ref-sub"><?= htmlspecialchars((string) ($rr['email'] ?? '')) ?></span>
                                </td>
                                <td><?= number_format((float) ($rr['prix_propose'] ?? 0), 2, ',', ' ') ?> TND <?php if ($isBest && !$isWin): ?><span class="pl-chip-best">Meilleur prix</span><?php endif; ?></td>
                                <td><?= (int) ($rr['delai_jours'] ?? 0) ?> j</td>
                                <td><?= htmlspecialchars((string) ($rr['notes'] ?? '')) ?></td>
                                <?php if ($st !== 'attribue' && $st !== 'annule'): ?>
                                    <td class="pl-pdf-hide">
                                        <form method="post" class="commerce-actions" onsubmit="return confirm('Retenir cette offre et clôturer l’AO ?');">
                                            <input type="hidden" name="action" value="attribuer">
                                            <input type="hidden" name="idao" value="<?= (int) ($detail['idao'] ?? 0) ?>">
                                            <input type="hidden" name="idr" value="<?= $idr ?>">
                                            <button type="submit" class="btn btn-primary" style="font-size:0.76rem;padding:6px 10px">Retenir</button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (count($reponses) === 0): ?>
                            <tr><td colspan="<?= $st !== 'attribue' && $st !== 'annule' ? 5 : 4 ?>" style="text-align:center;padding:22px;color:#64748b">Aucune offre — saisissez-la ci-dessous.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($st !== 'attribue' && $st !== 'annule'): ?>
                <?php if (count($vendeurs) === 0): ?>
                    <div class="commerce-alert commerce-alert--err">Aucun vendeur (profil candidat / entrepreneur) — créez un utilisateur pour saisir des offres.</div>
                <?php else: ?>
                <div class="commerce-form-card pl-pdf-hide" style="margin-top:22px">
                    <h3 class="commerce-card-title">Enregistrer / mettre à jour une offre fournisseur</h3>
                    <p class="hint" style="margin:-6px 0 14px">Une ligne par vendeur ; nouvelle saisie écrase le prix et le délai précédents pour cet AO.</p>
                    <form method="post">
                        <input type="hidden" name="action" value="add_reponse">
                        <input type="hidden" name="idao" value="<?= (int) ($detail['idao'] ?? 0) ?>">
                        <label class="field-first">Vendeur
                            <select name="id_vendeur" required>
                                <?php foreach ($vendeurs as $v): ?>
                                    <option value="<?= (int) $v['iduser'] ?>">
                                        <?= htmlspecialchars(trim(($v['prenom'] ?? '') . ' ' . ($v['nom'] ?? '')) . ' — ' . ($v['email'] ?? '')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Prix proposé (TND)
                            <input type="number" name="prix_propose" min="0" step="0.01" required placeholder="0.00">
                        </label>
                        <label>Délai de livraison (jours)
                            <input type="number" name="delai_jours" min="1" max="365" value="14" required>
                        </label>
                        <label>Notes
                            <input type="text" name="notes" maxlength="500" placeholder="Optionnel">
                        </label>
                        <div class="btn-submit-wrap">
                            <button type="submit" class="btn btn-primary">Enregistrer l’offre</button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="../../../assets/backoffice-report-pdf.js"></script>
<script>
prolinkBindReportPdf(<?= json_encode([
    'buttonId' => 'exportAchatsSourcingPdf',
    'rootId' => 'achatsSourcingPdfRoot',
    'footerLine' => $sourcingPdfFooter,
    'fileName' => $sourcingPdfName,
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
</script>
</body>
</html>
