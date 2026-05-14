<?php
<<<<<<< HEAD
require_once __DIR__ . '/../../init.php';
requireLogin('Connectez-vous pour consulter ce projet.');
require_once __DIR__ . '/../../controller/ProjectP.php';
require_once __DIR__ . '/../../controller/UserP.php';
require_once __DIR__ . '/../../controller/CandidatureP.php';

$pp = new ProjectP();
$up = new UserP();
$cp = new CandidatureP();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
=======
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../../controller/ProjectP.php';
require_once __DIR__ . '/../../controller/UserP.php';
$pp = new ProjectP();
$up = new UserP();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
>>>>>>> formation
$pr = $id ? $pp->get($id) : null;
if (!$pr) {
    header('Location: projects.php');
    exit;
}
<<<<<<< HEAD
$owner = $pr['owner_id'] ? $up->showUser((int) $pr['owner_id']) : null;
$ownerName = $owner ? trim(($owner['prenom'] ?? '') . ' ' . ($owner['nom'] ?? '')) : '—';

$u = currentUser();
$uid = (int) ($u['iduser'] ?? 0);
$isOwner = $owner && $uid > 0 && $uid === (int) $owner['iduser'];

$err = $info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $uid > 0 && !$isOwner) {
    $action = (string) ($_POST['action'] ?? 'apply');
    if ($action === 'apply') {
        $cvFile = $_FILES['cv'] ?? null;
        $newId = $cp->apply($id, $uid, $_POST, $cvFile);
        if ($newId !== false) {
            $info = 'Votre candidature a été envoyée. Le propriétaire du projet vous répondra prochainement.';
        } else {
            $err = $cp->getLastError() ?: 'Impossible d\'envoyer la candidature.';
        }
    } elseif ($action === 'withdraw') {
        $candId = (int) ($_POST['id_candidature'] ?? 0);
        if ($cp->withdraw($candId, $uid)) {
            $info = 'Votre candidature a été retirée.';
        } else {
            $err = 'Impossible de retirer cette candidature.';
        }
    }
}

$candidature = $uid > 0 ? $cp->findForUser($id, $uid) : null;
$candStatus  = $candidature ? (string) $candidature['statut'] : null;
$statusCounts = $isOwner ? $cp->countByStatus($id) : null;

$prefNom    = $err !== '' ? (string) ($_POST['nom'] ?? '')    : (string) ($u['nom'] ?? '');
$prefPrenom = $err !== '' ? (string) ($_POST['prenom'] ?? '') : (string) ($u['prenom'] ?? '');
$prefEmail  = $err !== '' ? (string) ($_POST['email'] ?? '')  : (string) ($u['email'] ?? '');
$prefMsg    = $err !== '' ? (string) ($_POST['message'] ?? '') : '';
$cvWebPath  = CandidatureP::cvWebPath();
=======
$owner = $pr['owner_id'] ? $up->showUser((int)$pr['owner_id']) : null;
>>>>>>> formation
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pr['title'] ?? 'Projet') ?> — ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
<<<<<<< HEAD
    <style>
        .cand-card {
            margin-top: 18px; background:#fff; border:1px solid #e2e8f0;
            border-radius:14px; padding:18px 20px; box-shadow: 0 12px 28px rgba(15,23,42,0.06);
        }
        .cand-card h3 { margin:0 0 10px; font-size:1.05rem; color:#0f172a; }
        .cand-card textarea,
        .cand-card input[type=text],
        .cand-card input[type=email],
        .cand-card input[type=file] {
            width:100%; box-sizing:border-box;
            padding:10px 12px; border:1px solid #cbd5e1; border-radius:10px;
            font: inherit; font-size:0.92rem;
        }
        .cand-card textarea { min-height:120px; resize:vertical; }
        .cand-card textarea:focus,
        .cand-card input[type=text]:focus,
        .cand-card input[type=email]:focus { outline:none; border-color:#0073b1; box-shadow: 0 0 0 3px rgba(0,115,177,0.18); }
        .cand-field { margin-bottom: 10px; }
        .cand-field label { display:block; font-weight:600; font-size:0.85rem; color:#334155; margin-bottom:4px; }
        .cand-grid { display:grid; gap:10px; grid-template-columns: 1fr 1fr; }
        @media (max-width: 600px) { .cand-grid { grid-template-columns: 1fr; } }
        .cand-help { color:#64748b; font-size:0.78rem; margin-top:4px; }
        .cand-status {
            display:inline-flex; align-items:center; gap:6px;
            padding:4px 12px; border-radius:999px; font-weight:700; font-size:0.82rem;
        }
        .cs-en_attente { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
        .cs-acceptee   { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
        .cs-refusee    { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
        .cs-retiree    { background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; }
        .cand-banner   { padding:10px 14px; border-radius:10px; font-weight:600; margin-bottom:10px; }
        .cand-banner.ok  { background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; }
        .cand-banner.err { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
        .cand-actions { display:flex; flex-wrap:wrap; gap:10px; margin-top: 10px; }
        .cand-summary {
            display:flex; flex-wrap:wrap; gap:14px; margin: 6px 0 0;
            font-size:0.85rem; color:#475569;
        }
        .cand-summary strong { color:#0f172a; }
    </style>
=======
>>>>>>> formation
</head>
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <article class="fo-form-card fo-content-card">
        <h1><?= htmlspecialchars($pr['title']) ?></h1>
<<<<<<< HEAD
        <div class="fo-meta">
            Par <strong><?= htmlspecialchars($ownerName) ?></strong> · <?= htmlspecialchars($pr['status']) ?>
        </div>
=======
        <div class="fo-meta">Par <strong><?= htmlspecialchars(trim(($owner['prenom'] ?? '') . ' ' . ($owner['nom'] ?? ''))) ?></strong> · <?= htmlspecialchars($pr['status']) ?></div>
>>>>>>> formation
        <?php if (!empty($pr['description'])): ?>
            <div class="fo-body"><?= nl2br(htmlspecialchars($pr['description'])) ?></div>
        <?php else: ?>
            <p class="hint">Aucune description fournie.</p>
        <?php endif; ?>
        <p><a href="projects.php">← Retour à la liste</a></p>
    </article>
<<<<<<< HEAD

    <?php if ($isOwner): ?>
        <section class="cand-card">
            <h3>🛠️ Vous êtes le porteur de ce projet</h3>
            <p class="cand-summary">
                <span>📨 <strong><?= (int) $statusCounts[CandidatureP::STATUS_PENDING] ?></strong> en attente</span>
                <span>✅ <strong><?= (int) $statusCounts[CandidatureP::STATUS_ACCEPTED] ?></strong> acceptées</span>
                <span>❌ <strong><?= (int) $statusCounts[CandidatureP::STATUS_REJECTED] ?></strong> refusées</span>
                <span>↩ <strong><?= (int) $statusCounts[CandidatureP::STATUS_WITHDRAWN] ?></strong> retirées</span>
            </p>
            <div class="cand-actions">
                <a class="fo-btn fo-btn--primary" href="project_candidatures.php?id=<?= (int) $id ?>">Gérer les candidatures</a>
            </div>
        </section>
    <?php else: ?>
        <section class="cand-card">
            <h3>📨 Postuler à ce projet</h3>

            <?php if ($info): ?><p class="cand-banner ok"><?= htmlspecialchars($info) ?></p><?php endif; ?>
            <?php if ($err):  ?><p class="cand-banner err"><?= htmlspecialchars($err) ?></p><?php endif; ?>

            <?php if ($candidature && $candStatus !== CandidatureP::STATUS_WITHDRAWN): ?>
                <p>Statut de votre candidature :
                    <span class="cand-status cs-<?= htmlspecialchars($candStatus) ?>">
                        <?= htmlspecialchars(CandidatureP::STATUS_LABELS[$candStatus] ?? $candStatus) ?>
                    </span>
                </p>
                <p style="color:#475569;font-size:0.9rem;margin:6px 0 0">
                    Envoyée par <strong><?= htmlspecialchars(trim(((string) ($candidature['prenom'] ?? '')) . ' ' . ((string) ($candidature['nom'] ?? '')))) ?></strong>
                    &middot; <?= htmlspecialchars((string) ($candidature['email'] ?? '')) ?>
                    <?php if (!empty($candidature['cv_fichier'])): ?>
                        &middot; <a href="<?= htmlspecialchars('../../' . $cvWebPath . basename((string) $candidature['cv_fichier'])) ?>" target="_blank" rel="noopener">📄 Mon CV</a>
                    <?php endif; ?>
                </p>
                <?php if (!empty($candidature['message'])): ?>
                    <details style="margin-top:8px">
                        <summary style="cursor:pointer;font-weight:600">Voir mon message</summary>
                        <p style="white-space:pre-wrap;color:#334155;margin-top:6px"><?= htmlspecialchars((string) $candidature['message']) ?></p>
                    </details>
                <?php endif; ?>
                <?php if ($candStatus === CandidatureP::STATUS_PENDING): ?>
                    <form method="post" class="cand-actions">
                        <input type="hidden" name="action" value="withdraw">
                        <input type="hidden" name="id_candidature" value="<?= (int) $candidature['id_candidature'] ?>">
                        <button type="submit" class="fo-btn"
                                onclick="return confirm('Retirer votre candidature ?');">Retirer ma candidature</button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <?php if ($candidature && $candStatus === CandidatureP::STATUS_WITHDRAWN): ?>
                    <p style="color:#64748b;font-size:0.9rem">Vous aviez déjà postulé puis retiré votre candidature. Vous pouvez postuler à nouveau ci-dessous.</p>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" name="action" value="apply">

                    <div class="cand-grid">
                        <div class="cand-field">
                            <label for="cand-nom">Nom *</label>
                            <input id="cand-nom" type="text" name="nom" required maxlength="100"
                                   value="<?= htmlspecialchars($prefNom) ?>">
                        </div>
                        <div class="cand-field">
                            <label for="cand-prenom">Prénom</label>
                            <input id="cand-prenom" type="text" name="prenom" maxlength="100"
                                   value="<?= htmlspecialchars($prefPrenom) ?>">
                        </div>
                    </div>

                    <div class="cand-field">
                        <label for="cand-email">Email *</label>
                        <input id="cand-email" type="email" name="email" required maxlength="150"
                               value="<?= htmlspecialchars($prefEmail) ?>">
                    </div>

                    <div class="cand-field">
                        <label for="cand-cv">CV (PDF, DOC ou DOCX — max 5 Mo)</label>
                        <input id="cand-cv" type="file" name="cv" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                        <p class="cand-help">Le porteur du projet pourra télécharger votre CV.</p>
                    </div>

                    <div class="cand-field">
                        <label for="cand-message">Message au porteur du projet (facultatif)</label>
                        <textarea id="cand-message" name="message" maxlength="4000"
                                  placeholder="Présentez-vous, vos compétences, votre motivation..."><?= htmlspecialchars($prefMsg) ?></textarea>
                    </div>

                    <div class="cand-actions">
                        <button type="submit" class="fo-btn fo-btn--primary">Envoyer ma candidature</button>
                    </div>
                </form>
            <?php endif; ?>
        </section>
    <?php endif; ?>
=======
>>>>>>> formation
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
