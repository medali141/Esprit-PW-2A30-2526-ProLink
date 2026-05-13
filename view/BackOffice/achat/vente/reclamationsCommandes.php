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

require_once __DIR__ . '/../../../../controller/ReclamationCommandeController.php';
$rc = new ReclamationCommandeController();

$error = '';
$ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idRec = (int) ($_POST['idreclamation'] ?? 0);
    $statut = (string) ($_POST['statut'] ?? 'en_cours');
    $reponse = (string) ($_POST['admin_response'] ?? '');
    $points = (int) ($_POST['compensation_points'] ?? 0);
    try {
        $rc->respondAsAdmin($idRec, (int) ($user['iduser'] ?? 0), $statut, $reponse, $points);
        $ok = 'Reclamation mise a jour avec succes.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$q = trim((string) ($_GET['q'] ?? ''));
$statutFilter = trim((string) ($_GET['statut'] ?? ''));
$allowed = ['', 'ouverte', 'en_cours', 'resolue'];
if (!in_array($statutFilter, $allowed, true)) {
    $statutFilter = '';
}

$stats = $rc->getAdminStats();
$rows = $rc->listAllAdmin($statutFilter, $q);
$labels = [
    'ouverte' => 'Ouverte',
    'en_cours' => 'En cours',
    'resolue' => 'Resolue',
];

/**
 * @param array<string,mixed> $row
 */
function detectReclamationType(array $row): string {
    $text = strtolower(trim((string) ($row['sujet'] ?? '') . ' ' . (string) ($row['message'] ?? '')));
    if ($text === '') {
        return 'autre';
    }
    $map = [
        'retard_livraison' => ['retard', 'livraison', 'delai', 'late', 'expedie', 'suivi'],
        'produit_manquant' => ['manquant', 'missing', 'absent', 'non recu', 'incomplet'],
        'produit_endommage' => ['casse', 'endommage', 'defaut', 'defect', 'abime'],
        'erreur_produit' => ['mauvais produit', 'erreur', 'wrong product', 'non conforme'],
        'remboursement' => ['remboursement', 'refund', 'annulation', 'annulee'],
        'paiement' => ['paiement', 'payment', 'carte', 'transaction', 'facture'],
    ];
    foreach ($map as $type => $keywords) {
        foreach ($keywords as $kw) {
            if (strpos($text, $kw) !== false) {
                return $type;
            }
        }
    }
    return 'autre';
}

$responseTemplates = [
    'retard_livraison' => [
        'label' => 'Retard de livraison',
        'status' => 'en_cours',
        'points' => 40,
        'response' => "Bonjour,\n\nNous vous presentons nos excuses pour le retard de livraison. Votre dossier est priorise avec notre transporteur.\n\nDedommagement: 40 points fidelite credites sur votre compte.\n\nCordialement,\nService client ProLink",
    ],
    'produit_manquant' => [
        'label' => 'Produit manquant',
        'status' => 'en_cours',
        'points' => 60,
        'response' => "Bonjour,\n\nNous sommes desoles pour l article manquant. Nous lancons une verification immediate puis un renvoi prioritaire si l anomalie est confirmee.\n\nDedommagement: 60 points fidelite accordes.\n\nCordialement,\nService client ProLink",
    ],
    'produit_endommage' => [
        'label' => 'Produit endommage / defectueux',
        'status' => 'en_cours',
        'points' => 80,
        'response' => "Bonjour,\n\nNous vous presentons nos excuses pour cet incident qualite. Nous proposons echange ou remboursement selon votre preference.\n\nDedommagement: 80 points fidelite credites.\n\nCordialement,\nService client ProLink",
    ],
    'erreur_produit' => [
        'label' => 'Produit non conforme / erreur',
        'status' => 'en_cours',
        'points' => 50,
        'response' => "Bonjour,\n\nNous avons bien recu votre signalement. Nous organisons la reprise de l article incorrect et l expedition du bon produit.\n\nDedommagement: 50 points fidelite accordes.\n\nCordialement,\nService client ProLink",
    ],
    'remboursement' => [
        'label' => 'Remboursement / annulation',
        'status' => 'en_cours',
        'points' => 20,
        'response' => "Bonjour,\n\nVotre demande de remboursement est prise en charge. Le delai de traitement depend du mode de paiement utilise.\n\nA titre commercial, 20 points fidelite ont ete ajoutes.\n\nCordialement,\nService client ProLink",
    ],
    'paiement' => [
        'label' => 'Probleme de paiement / facture',
        'status' => 'en_cours',
        'points' => 15,
        'response' => "Bonjour,\n\nMerci pour votre retour. Nous analysons la transaction et la facture associee afin de corriger l incident rapidement.\n\nA titre d excuse, 15 points fidelite sont accordes.\n\nCordialement,\nService client ProLink",
    ],
    'autre' => [
        'label' => 'Autre demande client',
        'status' => 'en_cours',
        'points' => 10,
        'response' => "Bonjour,\n\nNous avons bien recu votre reclamation. Notre equipe la traite avec priorite et vous tiendra informe de chaque etape.\n\nCordialement,\nService client ProLink",
    ],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reclamations commandes — Admin</title>
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
<link rel="stylesheet" href="../../commerce.css">
<div class="content commerce-page">
    <div class="container">
        <div class="topbar">
            <div>
                <div class="page-title">Reclamations commandes</div>
                <p class="hint" style="margin:6px 0 0">L admin repond et propose une compensation; seul le client cloture avec une note /5.</p>
            </div>
            <div class="actions">
                <a href="gestionAchats.php" class="btn btn-secondary">← Achats</a>
            </div>
        </div>

        <?php if ($ok !== ''): ?>
            <p class="banner success"><?= htmlspecialchars($ok) ?></p>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <p class="banner error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <div class="kpi-grid">
            <article class="kpi-card"><p>Total</p><strong><?= (int) ($stats['total'] ?? 0) ?></strong></article>
            <article class="kpi-card"><p>Ouvertes</p><strong><?= (int) ($stats['ouvertes'] ?? 0) ?></strong></article>
            <article class="kpi-card"><p>En cours</p><strong><?= (int) ($stats['en_cours'] ?? 0) ?></strong></article>
            <article class="kpi-card"><p>Resolues</p><strong><?= (int) ($stats['resolues'] ?? 0) ?></strong></article>
            <article class="kpi-card"><p>Points offerts</p><strong><?= (int) ($stats['points_offerts'] ?? 0) ?> pts</strong></article>
            <article class="kpi-card"><p>Note moyenne</p><strong><?= (int) ($stats['note_moyenne'] ?? 0) ?>/5</strong></article>
        </div>

        <form method="get" class="commerce-filters" action="reclamationsCommandes.php">
            <div class="commerce-filters__field">
                <label for="r-q">Recherche</label>
                <input type="text" name="q" id="r-q" value="<?= htmlspecialchars($q) ?>" placeholder="ID, commande, client, sujet...">
            </div>
            <div class="commerce-filters__field">
                <label for="r-st">Statut</label>
                <select name="statut" id="r-st">
                    <option value="" <?= $statutFilter === '' ? 'selected' : '' ?>>Tous</option>
                    <?php foreach ($labels as $k => $lab): ?>
                        <option value="<?= htmlspecialchars($k) ?>" <?= $statutFilter === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="commerce-filters__actions">
                <button class="btn btn-primary" type="submit">Filtrer</button>
                <a class="btn btn-secondary" href="reclamationsCommandes.php">Reinitialiser</a>
            </div>
        </form>

        <?php if (empty($rows)): ?>
            <div class="empty-state">Aucune reclamation a afficher.</div>
        <?php else: ?>
            <div class="reclamation-list">
                <?php foreach ($rows as $r): ?>
                    <?php
                    $st = (string) ($r['statut'] ?? 'ouverte');
                    $badgeClass = 'commerce-badge commerce-badge--' . preg_replace('/[^a-z0-9_]/', '', $st);
                    $recType = detectReclamationType($r);
                    $isClosed = $st === 'resolue';
                    $rating = (int) ($r['user_rating'] ?? 0);
                    ?>
                    <article class="reclamation-card">
                        <header class="reclamation-card__head">
                            <div>
                                <h3>#<?= (int) ($r['idreclamation'] ?? 0) ?> · Cmd #<?= (int) ($r['idcommande'] ?? 0) ?></h3>
                                <p class="hint"><?= htmlspecialchars(trim((string) ($r['client_prenom'] ?? '') . ' ' . (string) ($r['client_nom'] ?? ''))) ?> · <?= htmlspecialchars((string) ($r['client_email'] ?? '')) ?></p>
                                <p class="hint">Type detecte: <strong><?= htmlspecialchars((string) ($responseTemplates[$recType]['label'] ?? 'Autre')) ?></strong></p>
                            </div>
                            <span class="<?= htmlspecialchars($badgeClass) ?>"><?= htmlspecialchars($labels[$st] ?? $st) ?></span>
                        </header>

                        <div class="reclamation-card__body">
                            <p><strong>Sujet:</strong> <?= htmlspecialchars((string) ($r['sujet'] ?? '')) ?></p>
                            <p><strong>Message client:</strong><br><?= nl2br(htmlspecialchars((string) ($r['message'] ?? ''))) ?></p>
                            <?php if (!empty($r['admin_response'])): ?>
                                <p><strong>Derniere reponse admin:</strong><br><?= nl2br(htmlspecialchars((string) $r['admin_response'])) ?></p>
                            <?php endif; ?>
                            <p class="hint">Cree le <?= htmlspecialchars((string) ($r['created_at'] ?? '')) ?> · Points excuses actuels: <strong><?= (int) ($r['compensation_points'] ?? 0) ?> pts</strong></p>
                            <?php if ($rating >= 1 && $rating <= 5): ?>
                                <p class="hint">Note client: <strong style="color:#f59e0b"><?= str_repeat('★', $rating) ?><span style="color:#cbd5e1"><?= str_repeat('★', 5 - $rating) ?></span></strong></p>
                            <?php endif; ?>
                        </div>

                        <form method="post" class="reclamation-card__form" data-reclam-form="1">
                            <input type="hidden" name="idreclamation" value="<?= (int) ($r['idreclamation'] ?? 0) ?>">
                            <label>Reponses pretes</label>
                            <div class="reclamation-card__quick">
                                <select class="js-template-select" data-default-type="<?= htmlspecialchars($recType) ?>">
                                    <?php foreach ($responseTemplates as $tplKey => $tpl): ?>
                                        <option value="<?= htmlspecialchars($tplKey) ?>" <?= $recType === $tplKey ? 'selected' : '' ?>><?= htmlspecialchars((string) $tpl['label']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" class="btn btn-secondary js-apply-template">Appliquer modele</button>
                            </div>
                            <label>Statut</label>
                            <select name="statut" required <?= $isClosed ? 'disabled' : '' ?>>
                                <?php foreach ($labels as $k => $lab): ?>
                                    <option value="<?= htmlspecialchars($k) ?>" <?= $st === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($isClosed): ?>
                                <input type="hidden" name="statut" value="resolue">
                            <?php endif; ?>

                            <label>Points d excuse (0-2000)</label>
                            <input type="number" min="0" max="2000" step="1" name="compensation_points" value="<?= (int) ($r['compensation_points'] ?? 0) ?>" <?= $isClosed ? 'readonly' : '' ?>>

                            <label>Reponse admin</label>
                            <textarea name="admin_response" rows="4" required <?= $isClosed ? 'readonly' : '' ?>><?= htmlspecialchars((string) ($r['admin_response'] ?? '')) ?></textarea>

                            <?php if (!$isClosed): ?>
                                <button type="submit" class="btn btn-primary">Enregistrer la reponse</button>
                            <?php else: ?>
                                <p class="hint" style="margin:2px 0 0">Reclamation cloturee par le client.</p>
                            <?php endif; ?>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.banner { padding: 10px 12px; border-radius: 10px; margin: 10px 0; font-weight: 600; }
.banner.success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.banner.error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.kpi-grid { display:grid; grid-template-columns: repeat(auto-fit,minmax(130px,1fr)); gap:10px; margin: 10px 0 14px; }
.kpi-card { background:#fff; border:1px solid #dbe4ef; border-radius:12px; padding:10px 12px; }
.kpi-card p { margin:0; color:#64748b; font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
.kpi-card strong { font-size:1.2rem; color:#0f172a; }
.empty-state { background:#fff; border:1px dashed #cbd5e1; border-radius:12px; padding:24px; color:#64748b; text-align:center; }
.reclamation-list { display:flex; flex-direction:column; gap:14px; margin-top:12px; }
.reclamation-card { background:#fff; border:1px solid #dbe4ef; border-radius:14px; box-shadow:0 8px 20px rgba(15,23,42,.05); padding:14px; }
.reclamation-card__head { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; }
.reclamation-card__head h3 { margin:0; font-size:1rem; color:#0f172a; }
.reclamation-card__body { margin-top:10px; }
.reclamation-card__body p { margin:8px 0; line-height:1.45; }
.reclamation-card__form { margin-top:12px; display:grid; gap:8px; grid-template-columns:1fr; max-width:620px; }
.reclamation-card__form label { font-size:.82rem; font-weight:700; color:#334155; }
.reclamation-card__quick { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
.reclamation-card__form input, .reclamation-card__form select, .reclamation-card__form textarea {
    border:1px solid #cbd5e1; border-radius:8px; padding:8px 10px; font:inherit;
}
html.dark-mode .banner.success { background: rgba(34,197,94,.15); color:#86efac; border-color: rgba(74,222,128,.35); }
html.dark-mode .banner.error { background: rgba(239,68,68,.14); color:#fecaca; border-color: rgba(248,113,113,.35); }
html.dark-mode .kpi-card,
html.dark-mode .reclamation-card,
html.dark-mode .empty-state {
    background:#151b26;
    border-color: rgba(148,163,184,.2);
    color:#e2e8f0;
}
html.dark-mode .kpi-card p,
html.dark-mode .empty-state,
html.dark-mode .reclamation-card .hint {
    color:#94a3b8;
}
html.dark-mode .kpi-card strong,
html.dark-mode .reclamation-card__head h3,
html.dark-mode .reclamation-card__body p strong {
    color:#f8fafc;
}
html.dark-mode .reclamation-card__form label {
    color:#cbd5e1;
}
html.dark-mode .reclamation-card__form input,
html.dark-mode .reclamation-card__form select,
html.dark-mode .reclamation-card__form textarea {
    background:#0f172a;
    border-color: rgba(148,163,184,.28);
    color:#f1f5f9;
}
html.dark-mode .reclamation-card__form input::placeholder,
html.dark-mode .reclamation-card__form textarea::placeholder {
    color:#94a3b8;
}
html.dark-mode .reclamation-card__quick .btn.btn-secondary {
    background:#334155;
    color:#f1f5f9;
    border-color: rgba(148,163,184,.25);
}
html.dark-mode .reclamation-card__form input:focus,
html.dark-mode .reclamation-card__form select:focus,
html.dark-mode .reclamation-card__form textarea:focus {
    outline:none;
    border-color: rgba(56,189,248,.52);
    box-shadow: 0 0 0 3px rgba(56,189,248,.16);
}
</style>
<script>
(function () {
    var templates = <?= json_encode($responseTemplates, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    var forms = document.querySelectorAll('form[data-reclam-form="1"]');
    forms.forEach(function (form) {
        var select = form.querySelector('.js-template-select');
        var applyBtn = form.querySelector('.js-apply-template');
        var statusSel = form.querySelector('select[name="statut"]');
        var pointsInp = form.querySelector('input[name="compensation_points"]');
        var textArea = form.querySelector('textarea[name="admin_response"]');
        if (!select || !applyBtn || !statusSel || !pointsInp || !textArea) return;

        function applyTemplate(key) {
            if (!templates[key]) return;
            var t = templates[key];
            if (!statusSel.disabled) {
                statusSel.value = t.status || 'en_cours';
            }
            pointsInp.value = String(t.points || 0);
            textArea.value = t.response || '';
            textArea.focus();
        }

        applyBtn.addEventListener('click', function () {
            applyTemplate(select.value);
        });
    });
})();
</script>
</body>
</html>
