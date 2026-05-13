<?php
require_once __DIR__ . '/../../../controller/AuthController.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$auth = new AuthController();
$user = $auth->profile();
if (!$user || strtolower($user['type'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require_once __DIR__ . '/../../../controller/ProjectP.php';
require_once __DIR__ . '/../../../controller/UserP.php';
require_once __DIR__ . '/../../../controller/CandidatureP.php';

$pp = new ProjectP();
$up = new UserP();
$cp = new CandidatureP();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$p = $id ? $pp->get($id) : null;
$owner = $p && !empty($p['owner_id']) ? $up->showUser((int) $p['owner_id']) : null;
$ownerName = $owner ? trim(($owner['prenom'] ?? '') . ' ' . ($owner['nom'] ?? '')) : '—';

$info = $err = '';
if ($p && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $candId = (int) ($_POST['id_candidature'] ?? 0);
    $action = (string) ($_POST['action'] ?? 'status');
    $candRow = $cp->get($candId);
    if (!$candRow || (int) $candRow['id_project'] !== $id) {
        $err = 'Candidature introuvable.';
    } elseif ($action === 'evaluate') {
        $note = (int) ($_POST['note'] ?? 0);
        $comment = (string) ($_POST['comment'] ?? '');
        if ($cp->evaluate($candId, $note, $comment)) {
            $info = 'Évaluation enregistrée.';
        } else {
            $err = $cp->getLastError() ?: 'Évaluation impossible.';
        }
    } else {
        $newStatus = (string) ($_POST['new_status'] ?? '');
        if ($cp->updateStatus($candId, $newStatus)) {
            $info = 'Statut mis à jour.';
        } else {
            $err = 'Mise à jour échouée.';
        }
    }
}
$candidatures = $p ? $cp->listForProject($id) : [];
$counts = $p ? $cp->countByStatus($id) : [];
$cvWebPath = CandidatureP::cvWebPath();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Détail projet — BackOffice</title>
    <style>
        .cand-table { width:100%; border-collapse: separate; border-spacing: 0; margin-top: 12px; }
        .cand-table th, .cand-table td { padding:10px 12px; border-bottom:1px solid #e2e8f0; vertical-align: top; text-align:left; font-size: 0.9rem; }
        .cand-table thead th { background:#f8fafc; color:#475569; font-size:0.78rem; text-transform:uppercase; letter-spacing:0.04em; }
        .cs { display:inline-flex; padding:2px 10px; border-radius:999px; font-weight:700; font-size:0.78rem; }
        .cs-en_attente { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
        .cs-acceptee   { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
        .cs-refusee    { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
        .cs-retiree    { background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; }
        .stats-pill { display:inline-flex; gap:14px; flex-wrap:wrap; }
        .stats-pill span { background:#f1f5f9; padding:4px 12px; border-radius:999px; font-size:0.85rem; }
        .cand-msg { color:#334155; white-space:pre-wrap; font-size:0.85rem; }
        .alert-success { background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; padding:8px 12px; border-radius:8px; margin-bottom: 10px; font-weight:600; }
        .alert-danger  { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; padding:8px 12px; border-radius:8px; margin-bottom: 10px; font-weight:600; }
        .btn-mini { font-size:0.8rem; padding:4px 10px; }
        .eval-row td { background:#fffbeb; border-top: none; padding-top: 4px; }
        .eval-row.saved td { background:#ecfdf5; }
        .eval-box-bo {
            display:flex; flex-wrap:wrap; gap:14px; align-items:flex-start;
            padding: 10px 12px; border:1px solid #fde68a; border-radius:10px;
            background:#fff;
        }
        .eval-box-bo.saved { border-color:#a7f3d0; }
        .eval-box-bo h5 { margin:0 0 6px; font-size:0.9rem; color:#92400e; }
        .eval-box-bo.saved h5 { color:#047857; }
        .eval-stars { display: inline-flex; flex-direction: row-reverse; gap: 3px; margin-top: 2px; }
        .eval-stars input { display: none; }
        .eval-stars label { font-size: 1.3rem; color: #cbd5e1; cursor: pointer; }
        .eval-stars input:checked ~ label,
        .eval-stars label:hover,
        .eval-stars label:hover ~ label { color: #f59e0b; }
        .eval-stars-static { color: #f59e0b; letter-spacing: 2px; }
        .eval-bo-form { flex: 1; min-width: 240px; }
        .eval-bo-form textarea {
            width:100%; box-sizing:border-box; min-height:50px;
            padding:6px 9px; border:1px solid #cbd5e1; border-radius:8px;
            font: inherit; font-size: 0.85rem; resize:vertical;
        }
        .eval-saved-comment { color:#334155; white-space:pre-wrap; font-size:0.85rem; margin: 4px 0 0; }
        .eval-toggle-bo { background:transparent;border:none;color:#0369a1;cursor:pointer;font-weight:600;font-size:0.8rem; padding:0; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content">
    <div class="container">
        <div class="topbar">
            <div class="page-title">Détail projet</div>
            <div class="actions">
                <a href="listProjects.php" class="btn btn-secondary">← Liste</a>
            </div>
        </div>

        <div class="card">
            <?php if (!$p): ?>
                <div class="alert-danger">Projet introuvable.</div>
            <?php else: ?>
                <h3 style="margin:0 0 6px"><?= htmlspecialchars((string) $p['title']) ?></h3>
                <p style="margin:0 0 6px;color:#475569">
                    Porteur : <strong><?= htmlspecialchars($ownerName) ?></strong>
                    <?php if ($owner && !empty($owner['email'])): ?>
                        &middot; <a href="mailto:<?= htmlspecialchars((string) $owner['email']) ?>"><?= htmlspecialchars((string) $owner['email']) ?></a>
                    <?php endif; ?>
                    &middot; Statut : <strong><?= htmlspecialchars((string) $p['status']) ?></strong>
                </p>
                <?php if (!empty($p['description'])): ?>
                    <p style="margin-top:14px;color:#334155;white-space:pre-wrap"><?= htmlspecialchars((string) $p['description']) ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if ($p): ?>
            <div class="card" style="margin-top:18px">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
                    <h3 style="margin:0">Candidatures</h3>
                    <p class="stats-pill">
                        <span>📨 <?= (int) ($counts[CandidatureP::STATUS_PENDING] ?? 0) ?> en attente</span>
                        <span>✅ <?= (int) ($counts[CandidatureP::STATUS_ACCEPTED] ?? 0) ?> acceptées</span>
                        <span>❌ <?= (int) ($counts[CandidatureP::STATUS_REJECTED] ?? 0) ?> refusées</span>
                        <span>↩ <?= (int) ($counts[CandidatureP::STATUS_WITHDRAWN] ?? 0) ?> retirées</span>
                    </p>
                </div>

                <?php if ($info): ?><div class="alert-success"><?= htmlspecialchars($info) ?></div><?php endif; ?>
                <?php if ($err):  ?><div class="alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>

                <?php if (empty($candidatures)): ?>
                    <p style="color:#64748b;margin-top:10px">Aucune candidature pour ce projet.</p>
                <?php else: ?>
                    <table class="cand-table">
                        <thead>
                            <tr>
                                <th>Candidat</th>
                                <th>Type</th>
                                <th>CV</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th style="text-align:right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($candidatures as $c):
                            $formNom    = (string) ($c['nom'] ?? '');
                            $formPrenom = (string) ($c['prenom'] ?? '');
                            $formEmail  = (string) ($c['email'] ?? '');
                            $accNom     = (string) ($c['account_nom'] ?? '');
                            $accPrenom  = (string) ($c['account_prenom'] ?? '');
                            $accEmail   = (string) ($c['account_email'] ?? '');
                            $fullName = trim(($formPrenom !== '' ? $formPrenom : $accPrenom) . ' ' . ($formNom !== '' ? $formNom : $accNom));
                            if ($fullName === '') $fullName = '—';
                            $showEmail = $formEmail !== '' ? $formEmail : $accEmail;
                            $status = (string) $c['statut'];
                            $createdFr = ($t = strtotime((string) $c['created_at'])) ? date('d/m/Y H:i', $t) : (string) $c['created_at'];
                            $cvFile = (string) ($c['cv_fichier'] ?? '');
                        ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($fullName) ?></strong><br>
                                    <a href="mailto:<?= htmlspecialchars($showEmail) ?>" style="color:#64748b;font-size:0.82rem"><?= htmlspecialchars($showEmail) ?></a>
                                </td>
                                <td><?= htmlspecialchars((string) ($c['account_type'] ?? '')) ?></td>
                                <td>
                                    <?php if ($cvFile !== ''): ?>
                                        <a class="btn btn-secondary btn-mini" href="../../../<?= htmlspecialchars($cvWebPath . basename($cvFile)) ?>" target="_blank" rel="noopener">📄 Voir</a>
                                    <?php else: ?>
                                        <span style="color:#94a3b8">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="cand-msg" style="max-width:300px">
                                    <?= !empty($c['message']) ? htmlspecialchars((string) $c['message']) : '<span style="color:#94a3b8">—</span>' ?>
                                </td>
                                <td><?= htmlspecialchars($createdFr) ?></td>
                                <td><span class="cs cs-<?= htmlspecialchars($status) ?>"><?= htmlspecialchars(CandidatureP::STATUS_LABELS[$status] ?? $status) ?></span></td>
                                <td style="text-align:right;white-space:nowrap">
                                    <?php if ($status !== CandidatureP::STATUS_WITHDRAWN): ?>
                                        <?php if ($status !== CandidatureP::STATUS_ACCEPTED): ?>
                                            <form method="post" style="display:inline">
                                                <input type="hidden" name="id_candidature" value="<?= (int) $c['id_candidature'] ?>">
                                                <input type="hidden" name="new_status" value="<?= CandidatureP::STATUS_ACCEPTED ?>">
                                                <button class="btn btn-primary btn-mini">Accepter</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($status !== CandidatureP::STATUS_REJECTED): ?>
                                            <form method="post" style="display:inline">
                                                <input type="hidden" name="id_candidature" value="<?= (int) $c['id_candidature'] ?>">
                                                <input type="hidden" name="new_status" value="<?= CandidatureP::STATUS_REJECTED ?>">
                                                <button class="btn btn-danger btn-mini">Refuser</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color:#94a3b8;font-size:0.85rem">retirée par le candidat</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if ($status === CandidatureP::STATUS_ACCEPTED): ?>
                                <?php
                                $note = $c['evaluation_note'] !== null ? (int) $c['evaluation_note'] : 0;
                                $evalComment = (string) ($c['evaluation_comment'] ?? '');
                                $hasEval = $note > 0;
                                ?>
                                <tr class="eval-row<?= $hasEval ? ' saved' : '' ?>">
                                    <td colspan="7">
                                        <div class="eval-box-bo<?= $hasEval ? ' saved' : '' ?>">
                                            <div style="min-width:170px">
                                                <h5><?= $hasEval ? '✅ Évaluation enregistrée' : '📝 Évaluer ce candidat' ?></h5>
                                                <?php if ($hasEval): ?>
                                                    <div>
                                                        <span class="eval-stars-static"><?= str_repeat('★', $note) . str_repeat('☆', 5 - $note) ?></span>
                                                        <strong style="margin-left:6px"><?= $note ?>/5</strong>
                                                    </div>
                                                    <?php if ($evalComment !== ''): ?>
                                                        <p class="eval-saved-comment"><?= htmlspecialchars($evalComment) ?></p>
                                                    <?php endif; ?>
                                                    <button type="button" class="eval-toggle-bo" data-toggle="#bo-eval-<?= (int) $c['id_candidature'] ?>">Modifier l'évaluation</button>
                                                <?php endif; ?>
                                            </div>
                                            <form method="post" class="eval-bo-form" id="bo-eval-<?= (int) $c['id_candidature'] ?>"<?= $hasEval ? ' style="display:none"' : '' ?>>
                                                <input type="hidden" name="action" value="evaluate">
                                                <input type="hidden" name="id_candidature" value="<?= (int) $c['id_candidature'] ?>">
                                                <div class="eval-stars" role="radiogroup">
                                                    <?php for ($n = 5; $n >= 1; $n--): ?>
                                                        <input type="radio" name="note" id="bo-note-<?= (int) $c['id_candidature'] ?>-<?= $n ?>" value="<?= $n ?>"<?= $note === $n ? ' checked' : '' ?> required>
                                                        <label for="bo-note-<?= (int) $c['id_candidature'] ?>-<?= $n ?>" title="<?= $n ?>/5">★</label>
                                                    <?php endfor; ?>
                                                </div>
                                                <textarea name="comment" maxlength="2000" placeholder="Commentaire (facultatif)..."><?= htmlspecialchars($evalComment) ?></textarea>
                                                <div style="margin-top:6px">
                                                    <button type="submit" class="btn btn-primary btn-mini"><?= $hasEval ? 'Mettre à jour' : 'Enregistrer' ?></button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
(function () {
    document.querySelectorAll('.eval-toggle-bo[data-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var sel = btn.getAttribute('data-toggle');
            var f = sel ? document.querySelector(sel) : null;
            if (!f) return;
            f.style.display = f.style.display === 'none' ? 'block' : 'none';
        });
    });
})();
</script>
</body>
</html>
