<?php
/**
 * Front-office — gestion des candidatures par le porteur du projet.
 * Accessible uniquement à l'owner du projet (ou à un admin connecté).
 *
 * URL : project_candidatures.php?id=<idproject>
 *
 * POST actions:
 *  - id_candidature, new_status=acceptee|refusee|en_attente
 */
require_once __DIR__ . '/../../init.php';
requireLogin('Connectez-vous pour gérer les candidatures.');
require_once __DIR__ . '/../../controller/ProjectP.php';
require_once __DIR__ . '/../../controller/UserP.php';
require_once __DIR__ . '/../../controller/CandidatureP.php';

$pp = new ProjectP();
$up = new UserP();
$cp = new CandidatureP();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$pr = $id ? $pp->get($id) : null;
if (!$pr) {
    header('Location: projects.php');
    exit;
}

$u = currentUser();
$uid = (int) ($u['iduser'] ?? 0);
$isAdmin = strtolower((string) ($u['type'] ?? '')) === 'admin';
$isOwner = $uid > 0 && (int) ($pr['owner_id'] ?? 0) === $uid;
if (!$isOwner && !$isAdmin) {
    flashSet('auth', 'Accès réservé au porteur du projet.');
    header('Location: project.php?id=' . $id);
    exit;
}

$info = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        if (!in_array($newStatus, [CandidatureP::STATUS_ACCEPTED, CandidatureP::STATUS_REJECTED, CandidatureP::STATUS_PENDING], true)) {
            $err = 'Action non autorisée.';
        } elseif ($cp->updateStatus($candId, $newStatus)) {
            $info = 'Statut mis à jour.';
        } else {
            $err = 'Mise à jour échouée.';
        }
    }
}

$list = $cp->listForProject($id);
$counts = $cp->countByStatus($id);
$cvWebPath = CandidatureP::cvWebPath();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Candidatures — <?= htmlspecialchars((string) $pr['title']) ?></title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
    <style>
        .cand-list { display: grid; gap: 14px; margin-top: 18px; }
        .cand-item {
            background:#fff; border:1px solid #e2e8f0; border-radius:14px;
            padding:16px 18px; box-shadow:0 8px 20px rgba(15,23,42,0.05);
        }
        .cand-head { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; flex-wrap:wrap; }
        .cand-author { font-weight:700; color:#0f172a; }
        .cand-author small { display:block; font-weight:400; color:#64748b; font-size:0.82rem; }
        .cand-status {
            display:inline-flex; align-items:center; padding:3px 12px; border-radius:999px;
            font-weight:700; font-size:0.78rem;
        }
        .cs-en_attente { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
        .cs-acceptee   { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
        .cs-refusee    { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
        .cs-retiree    { background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; }
        .cand-msg {
            margin-top: 10px; padding:10px 12px; background:#f8fafc; border-radius:10px;
            border:1px dashed #e2e8f0; color:#334155; white-space:pre-wrap; font-size:0.9rem;
        }
        .cand-actions { margin-top:10px; display:flex; flex-wrap:wrap; gap:8px; }
        .cand-actions button, .cand-actions a.fo-btn { font-size:0.85rem; }
        .stats-row {
            display:flex; flex-wrap:wrap; gap:14px; padding:12px 16px;
            background:#fff; border:1px solid #e2e8f0; border-radius:12px;
            margin-bottom: 14px; font-size:0.9rem;
        }
        .stats-row span strong { color:#0f172a; }
        .cand-banner   { padding:10px 14px; border-radius:10px; font-weight:600; margin-bottom:10px; }
        .cand-banner.ok  { background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; }
        .cand-banner.err { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }

        .eval-box {
            margin-top: 12px; padding: 12px 14px;
            background: #fef9c3; border: 1px solid #fde68a; border-radius: 10px;
        }
        .eval-box.saved { background: #ecfdf5; border-color: #a7f3d0; }
        .eval-box h4 { margin: 0 0 8px; font-size: 0.95rem; color: #92400e; }
        .eval-box.saved h4 { color: #047857; }
        .eval-stars { display: inline-flex; flex-direction: row-reverse; gap: 4px; margin: 4px 0 8px; }
        .eval-stars input { display: none; }
        .eval-stars label {
            font-size: 1.6rem; color: #cbd5e1; cursor: pointer; transition: color 0.15s;
        }
        .eval-stars input:checked ~ label,
        .eval-stars label:hover,
        .eval-stars label:hover ~ label { color: #f59e0b; }
        .eval-stars-static { font-size: 1.1rem; color: #f59e0b; letter-spacing: 2px; }
        .eval-box textarea {
            width:100%; box-sizing:border-box; min-height:70px;
            padding:8px 10px; border:1px solid #cbd5e1; border-radius:8px;
            font: inherit; font-size:0.9rem; resize:vertical;
        }
        .eval-box textarea:focus { outline:none; border-color:#0073b1; box-shadow: 0 0 0 3px rgba(0,115,177,0.18); }
        .eval-saved-comment { color:#334155; white-space:pre-wrap; font-size:0.9rem; margin: 6px 0 0; }
        .eval-toggle {
            margin-top: 6px; background: transparent; border: none; color: #0369a1; cursor: pointer;
            font-weight: 600; font-size: 0.85rem; padding: 0;
        }
    </style>
</head>
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero fo-hero--tight">
        <p class="fo-eyebrow">Projet</p>
        <h1>Candidatures pour « <?= htmlspecialchars((string) $pr['title']) ?> »</h1>
        <p class="fo-lead">Acceptez ou refusez les candidats qui souhaitent rejoindre votre projet.</p>
    </header>
    <p class="fo-forum-nav"><a class="fo-link-back" href="project.php?id=<?= (int) $id ?>">← Retour au projet</a></p>

    <div class="stats-row">
        <span>📨 <strong><?= (int) $counts[CandidatureP::STATUS_PENDING] ?></strong> en attente</span>
        <span>✅ <strong><?= (int) $counts[CandidatureP::STATUS_ACCEPTED] ?></strong> acceptées</span>
        <span>❌ <strong><?= (int) $counts[CandidatureP::STATUS_REJECTED] ?></strong> refusées</span>
        <span>↩ <strong><?= (int) $counts[CandidatureP::STATUS_WITHDRAWN] ?></strong> retirées</span>
    </div>

    <?php if ($info): ?><p class="cand-banner ok"><?= htmlspecialchars($info) ?></p><?php endif; ?>
    <?php if ($err):  ?><p class="cand-banner err"><?= htmlspecialchars($err) ?></p><?php endif; ?>

    <?php if (empty($list)): ?>
        <div class="cand-item" style="text-align:center;color:#64748b">
            Aucune candidature pour ce projet pour le moment.
        </div>
    <?php else: ?>
        <div class="cand-list">
            <?php foreach ($list as $c):
                $formNom    = (string) ($c['nom'] ?? '');
                $formPrenom = (string) ($c['prenom'] ?? '');
                $formEmail  = (string) ($c['email'] ?? '');
                $accNom     = (string) ($c['account_nom'] ?? '');
                $accPrenom  = (string) ($c['account_prenom'] ?? '');
                $accEmail   = (string) ($c['account_email'] ?? '');
                $fullName = trim(($formPrenom !== '' ? $formPrenom : $accPrenom) . ' ' . ($formNom !== '' ? $formNom : $accNom));
                if ($fullName === '') $fullName = '—';
                $showEmail = $formEmail !== '' ? $formEmail : $accEmail;
                $created = (string) ($c['created_at'] ?? '');
                $ts = $created ? strtotime($created) : false;
                $createdFr = $ts ? date('d/m/Y H:i', $ts) : $created;
                $status = (string) $c['statut'];
                $cvFile = (string) ($c['cv_fichier'] ?? '');
            ?>
                <article class="cand-item">
                    <div class="cand-head">
                        <div>
                            <div class="cand-author">
                                <?= htmlspecialchars($fullName) ?>
                                <small>
                                    <?= htmlspecialchars($showEmail) ?>
                                    &middot; <?= htmlspecialchars((string) ($c['account_type'] ?? '')) ?>
                                    &middot; postulé le <?= htmlspecialchars($createdFr) ?>
                                </small>
                            </div>
                        </div>
                        <span class="cand-status cs-<?= htmlspecialchars($status) ?>">
                            <?= htmlspecialchars(CandidatureP::STATUS_LABELS[$status] ?? $status) ?>
                        </span>
                    </div>

                    <?php if ($cvFile !== ''): ?>
                        <p style="margin:10px 0 0">
                            <a class="fo-btn" href="<?= htmlspecialchars('../../' . $cvWebPath . basename($cvFile)) ?>" target="_blank" rel="noopener">📄 Télécharger le CV</a>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($c['message'])): ?>
                        <div class="cand-msg"><?= htmlspecialchars((string) $c['message']) ?></div>
                    <?php endif; ?>

                    <?php if ($status !== CandidatureP::STATUS_WITHDRAWN): ?>
                        <div class="cand-actions">
                            <?php if ($status !== CandidatureP::STATUS_ACCEPTED): ?>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="id_candidature" value="<?= (int) $c['id_candidature'] ?>">
                                    <input type="hidden" name="new_status" value="<?= CandidatureP::STATUS_ACCEPTED ?>">
                                    <button type="submit" class="fo-btn fo-btn--primary">✅ Accepter</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($status !== CandidatureP::STATUS_REJECTED): ?>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="id_candidature" value="<?= (int) $c['id_candidature'] ?>">
                                    <input type="hidden" name="new_status" value="<?= CandidatureP::STATUS_REJECTED ?>">
                                    <button type="submit" class="fo-btn">❌ Refuser</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($status !== CandidatureP::STATUS_PENDING): ?>
                                <form method="post" style="display:inline">
                                    <input type="hidden" name="id_candidature" value="<?= (int) $c['id_candidature'] ?>">
                                    <input type="hidden" name="new_status" value="<?= CandidatureP::STATUS_PENDING ?>">
                                    <button type="submit" class="fo-btn">⏳ Remettre en attente</button>
                                </form>
                            <?php endif; ?>
                            <?php if ($showEmail !== ''): ?>
                                <a class="fo-btn" href="mailto:<?= htmlspecialchars($showEmail) ?>">✉️ Contacter</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($status === CandidatureP::STATUS_ACCEPTED): ?>
                        <?php
                        $note = $c['evaluation_note'] !== null ? (int) $c['evaluation_note'] : 0;
                        $evalComment = (string) ($c['evaluation_comment'] ?? '');
                        $evalAt = (string) ($c['evaluated_at'] ?? '');
                        $hasEval = $note > 0;
                        ?>
                        <div class="eval-box<?= $hasEval ? ' saved' : '' ?>">
                            <h4>
                                <?php if ($hasEval): ?>
                                    ✅ Évaluation enregistrée
                                <?php else: ?>
                                    📝 Évaluer ce candidat
                                <?php endif; ?>
                            </h4>

                            <?php if ($hasEval): ?>
                                <div>
                                    <span class="eval-stars-static" aria-label="Note: <?= $note ?>/5"><?= str_repeat('★', $note) . str_repeat('☆', 5 - $note) ?></span>
                                    <strong style="margin-left:6px"><?= $note ?>/5</strong>
                                </div>
                                <?php if ($evalComment !== ''): ?>
                                    <p class="eval-saved-comment"><?= htmlspecialchars($evalComment) ?></p>
                                <?php endif; ?>
                                <button type="button" class="eval-toggle" data-toggle="#eval-form-<?= (int) $c['id_candidature'] ?>">Modifier l'évaluation</button>
                            <?php endif; ?>

                            <form method="post" id="eval-form-<?= (int) $c['id_candidature'] ?>"<?= $hasEval ? ' style="display:none;margin-top:8px"' : '' ?>>
                                <input type="hidden" name="action" value="evaluate">
                                <input type="hidden" name="id_candidature" value="<?= (int) $c['id_candidature'] ?>">
                                <div class="eval-stars" role="radiogroup" aria-label="Note de 1 à 5">
                                    <?php for ($n = 5; $n >= 1; $n--): ?>
                                        <input type="radio" name="note" id="note-<?= (int) $c['id_candidature'] ?>-<?= $n ?>" value="<?= $n ?>"<?= $note === $n ? ' checked' : '' ?> required>
                                        <label for="note-<?= (int) $c['id_candidature'] ?>-<?= $n ?>" title="<?= $n ?> / 5">★</label>
                                    <?php endfor; ?>
                                </div>
                                <textarea name="comment" maxlength="2000" placeholder="Commentaire (facultatif) sur la collaboration, les compétences, les progrès..."><?= htmlspecialchars($evalComment) ?></textarea>
                                <div style="margin-top:8px">
                                    <button type="submit" class="fo-btn fo-btn--primary"><?= $hasEval ? 'Mettre à jour' : 'Enregistrer l\'évaluation' ?></button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
<script>
(function () {
    document.querySelectorAll('.eval-toggle[data-toggle]').forEach(function (btn) {
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
