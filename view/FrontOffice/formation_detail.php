<?php
require_once __DIR__ . '/../../init.php';
requireLogin('Connectez-vous pour consulter cette formation.');
require_once __DIR__ . '/../../controller/FormationP.php';
require_once __DIR__ . '/../../lib/FormationQuiz.php';

$fp = new FormationP();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$f = $id ? $fp->get($id) : null;
if (!$f) {
    header('Location: formation.php');
    exit;
}

$u = currentUser();
$uid = (int) ($u['iduser'] ?? 0);
$uemail = (string) ($u['email'] ?? '');

$inscription = $uid > 0 ? $fp->findInscriptionForUser($uid, $id, $uemail) : null;
$sent = false;
$err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$inscription) {
    $data = array_merge(['id_formation' => $id, 'id_user' => $uid], $_POST);
    $newId = $fp->addInscription($data);
    if ($newId) {
        $sent = true;
        $inscription = $fp->findInscriptionForUser($uid, $id, $uemail);
    } else {
        $err = 'Impossible d\'enregistrer l\'inscription pour le moment.';
    }
}

$quizPassed = $inscription && (int) ($inscription['quiz_passed'] ?? 0) === 1;
$quizScore  = $inscription && $inscription['quiz_score'] !== null ? (int) $inscription['quiz_score'] : null;

$prefillNom    = $err !== null ? (string) ($_POST['nom'] ?? '')    : (string) ($u['nom'] ?? '');
$prefillPrenom = $err !== null ? (string) ($_POST['prenom'] ?? '') : (string) ($u['prenom'] ?? '');
$prefillEmail  = $err !== null ? (string) ($_POST['email'] ?? '')  : $uemail;
$prefillTel    = $err !== null ? (string) ($_POST['telephone'] ?? '') : (string) ($u['telephone'] ?? $u['tel'] ?? '');
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($f['titre']) ?> — Formation</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
    <style>
        .fo-quiz-state {
            display: flex; flex-direction: column; gap: 10px;
            padding: 16px; border-radius: 14px; margin-top: 8px;
            background: #f0f9ff; border: 1px solid #bae6fd;
        }
        .fo-quiz-state.is-passed { background: #ecfdf5; border-color: #a7f3d0; }
        .fo-quiz-state.is-failed { background: #fef3c7; border-color: #fcd34d; }
        .fo-quiz-state h4 { margin: 0; font-size: 1rem; color: #0c4a6e; }
        .fo-quiz-state.is-passed h4 { color: #047857; }
        .fo-quiz-state.is-failed h4 { color: #92400e; }
        .fo-quiz-state p { margin: 0; font-size: 0.9rem; color: #334155; }
        .fo-quiz-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px; }
        .fo-badge-cat {
            display: inline-flex; padding: 3px 10px; border-radius: 999px;
            background: rgba(6,182,212,0.12); color: #0e7490;
            font-size: 0.78rem; font-weight: 700;
            border: 1px solid rgba(6,182,212,0.22);
        }
    </style>
</head>
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <article class="fo-event-detail">
        <div class="fo-event-card">
            <h1 class="fo-event-title"><?= htmlspecialchars($f['titre']) ?></h1>
            <?php if (!empty($f['categorie'])): ?>
                <p style="margin:6px 0 12px"><span class="fo-badge-cat"><?= htmlspecialchars((string) $f['categorie']) ?></span></p>
            <?php endif; ?>
            <div class="fo-event-kv">
                <div class="fo-event-kv__row"><strong>Date début :</strong> <?= htmlspecialchars($f['date_debut'] ?? '—') ?></div>
                <div class="fo-event-kv__row"><strong>Date fin :</strong> <?= htmlspecialchars($f['date_fin'] ?? '—') ?></div>
            </div>
            <div class="fo-event-excerpt"><?= nl2br(htmlspecialchars($f['description'] ?? '')) ?></div>
        </div>

        <aside class="fo-form-card">
            <?php if ($inscription): ?>
                <?php $idIns = (int) $inscription['id_inscription']; ?>
                <h3>Votre inscription</h3>
                <p style="color:#475569;font-size:0.9rem;margin-top:0">
                    Inscription #<?= $idIns ?> &middot; <?= htmlspecialchars((string) ($inscription['email'] ?? '')) ?>
                </p>

                <?php if ($quizPassed): ?>
                    <div class="fo-quiz-state is-passed">
                        <h4>✅ Quiz réussi (<?= $quizScore !== null ? $quizScore : '?' ?>/<?= FormationQuiz::TOTAL_QUESTIONS ?>)</h4>
                        <p>Félicitations ! Votre certificat est prêt.</p>
                        <div class="fo-quiz-actions">
                            <a class="fo-btn fo-btn--primary" href="formation_certificat.php?id_inscription=<?= $idIns ?>" target="_blank" rel="noopener">📄 Télécharger mon certificat</a>
                        </div>
                    </div>
                <?php elseif ($quizScore !== null): ?>
                    <div class="fo-quiz-state is-failed">
                        <h4>⚠️ Quiz non validé (<?= $quizScore ?>/<?= FormationQuiz::TOTAL_QUESTIONS ?>)</h4>
                        <p>Il faut au moins <?= FormationQuiz::PASS_THRESHOLD ?> bonnes réponses sur <?= FormationQuiz::TOTAL_QUESTIONS ?> pour obtenir le certificat. Vous pouvez retenter le quiz autant de fois que nécessaire.</p>
                        <div class="fo-quiz-actions">
                            <a class="fo-btn fo-btn--primary" href="formation_quiz.php?id_inscription=<?= $idIns ?>">🔁 Reprendre le quiz</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="fo-quiz-state">
                        <h4>🎯 Passez le quiz pour obtenir votre certificat</h4>
                        <p><?= FormationQuiz::TOTAL_QUESTIONS ?> questions à choix multiple. Vous devez en réussir au moins <?= FormationQuiz::PASS_THRESHOLD ?> pour valider la formation.</p>
                        <div class="fo-quiz-actions">
                            <a class="fo-btn fo-btn--primary" href="formation_quiz.php?id_inscription=<?= $idIns ?>">▶ Commencer le quiz</a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php elseif ($sent): ?>
                <p class="fo-banner fo-banner--ok">Votre inscription a été enregistrée. Vous pouvez maintenant passer le quiz pour obtenir votre certificat.</p>
                <p><a href="formation_detail.php?id=<?= (int) $id ?>">Actualiser cette page</a></p>
            <?php else: ?>
                <?php if ($err): ?><p class="fo-banner fo-banner--err"><?= htmlspecialchars($err) ?></p><?php endif; ?>
                <h3>Inscription</h3>
                <form method="post">
                    <label>Nom *</label>
                    <input name="nom" required value="<?= htmlspecialchars($prefillNom) ?>">
                    <label>Prénom</label>
                    <input name="prenom" value="<?= htmlspecialchars($prefillPrenom) ?>">
                    <label>Email *</label>
                    <input type="email" name="email" required value="<?= htmlspecialchars($prefillEmail) ?>">
                    <label>Téléphone</label>
                    <input name="telephone" value="<?= htmlspecialchars($prefillTel) ?>">
                    <div style="margin-top:12px">
                        <button class="fo-btn fo-btn--primary">S'inscrire</button>
                    </div>
                </form>
                <p style="color:#64748b;font-size:0.8rem;margin-top:10px">Un quiz vous sera proposé après inscription. Vous obtiendrez le certificat en obtenant au moins <?= FormationQuiz::PASS_THRESHOLD ?>/<?= FormationQuiz::TOTAL_QUESTIONS ?>.</p>
            <?php endif; ?>
        </aside>
    </article>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
