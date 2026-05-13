<?php
require_once __DIR__ . '/../../init.php';
requireLogin('Connectez-vous pour passer le quiz.');
require_once __DIR__ . '/../../controller/FormationP.php';
require_once __DIR__ . '/../../lib/FormationQuiz.php';

$fp = new FormationP();
$u = currentUser();
$uid = (int) ($u['iduser'] ?? 0);
$uemail = (string) ($u['email'] ?? '');

$idInscription = isset($_GET['id_inscription']) ? (int) $_GET['id_inscription'] : 0;
$inscription = $idInscription > 0 ? $fp->getInscription($idInscription) : null;

if (!$inscription || !$fp->inscriptionBelongsToUser($inscription, $uid, $uemail)) {
    header('Location: formation.php');
    exit;
}
$idFormation = (int) ($inscription['id_formation'] ?? 0);
$titreForm   = (string) ($inscription['formation_titre'] ?? '');
$categorie   = (string) ($inscription['formation_categorie'] ?? '');
$quiz        = FormationQuiz::getQuiz($categorie);

$score = null;
$passed = false;
$missing = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = is_array($_POST['answers'] ?? null) ? $_POST['answers'] : [];
    foreach ($quiz as $i => $_q) {
        if (!isset($answers[(string) $i])) {
            $missing = true;
            break;
        }
    }
    if (!$missing) {
        $score = FormationQuiz::score($quiz, $answers);
        $passed = FormationQuiz::isPassing($score);
        $fp->recordQuizAttempt($idInscription, $score, $passed);
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quiz — <?= htmlspecialchars($titreForm) ?></title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
    <style>
        .quiz-wrap { max-width: 760px; margin: 0 auto; }
        .quiz-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; box-shadow: 0 12px 30px rgba(15,23,42,0.06); }
        .quiz-card + .quiz-card { margin-top: 14px; }
        .quiz-q { font-size:1rem; font-weight:700; color:#0f172a; margin: 0 0 10px; display:flex; gap:8px; align-items:flex-start; }
        .quiz-q .quiz-num { display:inline-flex; align-items:center; justify-content:center; min-width:28px; height:28px; padding:0 8px; border-radius:999px; background:#e0f2fe; color:#0369a1; font-size:0.82rem; }
        .quiz-options { display:grid; gap:8px; }
        .quiz-options label {
            display:flex; align-items:center; gap:10px;
            padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px;
            cursor:pointer; transition: background 0.12s, border-color 0.12s;
            font-size:0.92rem;
        }
        .quiz-options label:hover { background:#f8fafc; border-color:#94a3b8; }
        .quiz-options input[type=radio] { accent-color:#0073b1; }
        .quiz-toolbar {
            display:flex; gap:10px; justify-content:flex-end; align-items:center;
            margin-top: 18px; flex-wrap: wrap;
        }
        .quiz-result {
            padding:18px 20px; border-radius:14px; margin-bottom: 16px;
            background:#ecfdf5; border:1px solid #a7f3d0;
        }
        .quiz-result.is-failed { background:#fef3c7; border-color:#fcd34d; }
        .quiz-result h2 { margin:0 0 6px; font-size:1.1rem; color:#047857; }
        .quiz-result.is-failed h2 { color:#92400e; }
        .quiz-result p { margin:0; color:#334155; font-size:0.92rem; }
        .quiz-result .quiz-actions { display:flex; gap:10px; flex-wrap:wrap; margin-top: 10px; }
        .quiz-banner-err {
            padding:10px 14px; border-radius:10px; background:#fef2f2;
            border:1px solid #fecaca; color:#991b1b; font-weight:600;
            margin-bottom:12px;
        }
    </style>
</head>
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <div class="quiz-wrap">
        <header class="fo-hero fo-hero--tight">
            <p class="fo-eyebrow">Quiz · <?= htmlspecialchars($categorie !== '' ? $categorie : 'Général') ?></p>
            <h1><?= htmlspecialchars($titreForm) ?></h1>
            <p class="fo-lead">Répondez aux <?= count($quiz) ?> questions. Il faut au moins <?= FormationQuiz::PASS_THRESHOLD ?> bonnes réponses pour valider la formation et débloquer votre certificat.</p>
        </header>
        <p class="fo-forum-nav"><a class="fo-link-back" href="formation_detail.php?id=<?= $idFormation ?>">← Retour à la formation</a></p>

        <?php if ($score !== null && $passed): ?>
            <div class="quiz-result">
                <h2>🎉 Bravo, quiz validé !</h2>
                <p>Score : <strong><?= $score ?>/<?= count($quiz) ?></strong>. Votre certificat est prêt à être téléchargé.</p>
                <div class="quiz-actions">
                    <a class="fo-btn fo-btn--primary" href="formation_certificat.php?id_inscription=<?= $idInscription ?>" target="_blank" rel="noopener">📄 Télécharger mon certificat</a>
                    <a class="fo-btn" href="formation_detail.php?id=<?= $idFormation ?>">← Retour à la formation</a>
                </div>
            </div>
        <?php elseif ($score !== null && !$passed): ?>
            <div class="quiz-result is-failed">
                <h2>⚠️ Quiz non validé</h2>
                <p>Score : <strong><?= $score ?>/<?= count($quiz) ?></strong>. Il faut <?= FormationQuiz::PASS_THRESHOLD ?> bonnes réponses minimum. Relancez le quiz pour réessayer.</p>
                <div class="quiz-actions">
                    <a class="fo-btn fo-btn--primary" href="formation_quiz.php?id_inscription=<?= $idInscription ?>">🔁 Réessayer</a>
                </div>
            </div>
        <?php elseif ($missing): ?>
            <p class="quiz-banner-err">Veuillez répondre à toutes les questions avant de soumettre.</p>
        <?php endif; ?>

        <?php if ($score === null || !$passed): ?>
            <form method="post" action="formation_quiz.php?id_inscription=<?= $idInscription ?>">
                <?php foreach ($quiz as $i => $q): ?>
                    <?php $selected = $missing ? (int) ($_POST['answers'][(string) $i] ?? -1) : -1; ?>
                    <div class="quiz-card">
                        <p class="quiz-q"><span class="quiz-num">Q<?= ($i + 1) ?></span><span><?= htmlspecialchars($q['q']) ?></span></p>
                        <div class="quiz-options">
                            <?php foreach ($q['options'] as $oi => $opt): ?>
                                <label>
                                    <input type="radio" name="answers[<?= $i ?>]" value="<?= $oi ?>"<?= $selected === $oi ? ' checked' : '' ?> required>
                                    <span><?= htmlspecialchars($opt) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="quiz-toolbar">
                    <a class="fo-btn" href="formation_detail.php?id=<?= $idFormation ?>">Annuler</a>
                    <button type="submit" class="fo-btn fo-btn--primary">Soumettre le quiz</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
