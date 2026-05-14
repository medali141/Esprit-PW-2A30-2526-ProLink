<?php
<<<<<<< HEAD
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
=======
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../../controller/FormationP.php';
$fp = new FormationP();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$formation = $id ? $fp->get($id) : null;
$sent = false;
$err = null;
$quizMessage = '';
$quizSuccess = false;

// Récupérer la certification associée
$certification = null;
$quiz = null;
$questions = [];

if($formation && !empty($formation['id_certification'])) {
    $certification = $fp->getCertificationByFormation($formation['id_formation']);
    if($certification) {
        $quiz = $fp->getQuizByCertification($certification['id_certification']);
        if($quiz) {
            $questions = $fp->getQuestionsByQuiz($quiz['id_quiz']);
        }
    }
}

// Traitement du quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $score = 0;
    $totalPoints = 0;
    $userAnswers = $_POST['answer'] ?? [];
    
    foreach($questions as $q) {
        $totalPoints += $q['points'];
        $userAnswer = $userAnswers[$q['id_question']] ?? '';
        if($userAnswer) {
            $correct = false;
            foreach($q['reponses'] as $rep) {
                if($rep['id_reponse'] == $userAnswer && $rep['est_correcte'] == 1) {
                    $correct = true;
                    break;
                }
            }
            if($correct) $score += $q['points'];
        }
    }
    
    $pourcentage = ($totalPoints > 0) ? round(($score / $totalPoints) * 100) : 0;
    $reussi = ($pourcentage >= $quiz['seuil_reussite']);
    
    $userId = $_SESSION['user']['iduser'] ?? 1;
    $fp->saveQuizAttempt($userId, $quiz['id_quiz'], $pourcentage, $reussi ? 'reussi' : 'echoue');
    
    if($reussi && !$fp->hasObtainedCertification($userId, $certification['id_certification'])) {
        $numeroCert = 'CERT-' . strtoupper(uniqid()) . '-' . date('Ymd');
        $fp->saveAchievement($userId, $certification['id_certification'], $numeroCert, $pourcentage);
        $quizMessage = '<div class="success">✅ Félicitations ! Score: ' . $pourcentage . '% - Certificat obtenu !</div>';
        $quizSuccess = true;
    } else if($reussi) {
        $quizMessage = '<div class="success">✅ Félicitations ! Score: ' . $pourcentage . '% - Vous avez déjà obtenu cette certification !</div>';
        $quizSuccess = true;
    } else {
        $quizMessage = '<div class="error">❌ Échec : ' . $pourcentage . '% (Seuil requis: ' . $quiz['seuil_reussite'] . '%)</div>';
    }
}

// Traitement de l'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inscrire'])) {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    
    if (empty($nom) || empty($email)) {
        $err = 'Le nom et l\'email sont obligatoires.';
    } else {
        $data = [
            'id_formation' => $id,
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'telephone' => $telephone
        ];
        $ok = $fp->addInscription($data);
        if ($ok) {
            $sent = true;
        } else {
            $err = 'Impossible d\'enregistrer l\'inscription. Veuillez réessayer.';
        }
    }
}

if (!$formation) {
    header('Location: formation.php');
    exit;
}
?>

<!DOCTYPE html>
>>>>>>> formation
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<<<<<<< HEAD
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
=======
    <title><?= htmlspecialchars($formation['titre']) ?> — Formation</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fb; }
        .container { max-width: 1000px; margin: 0 auto; padding: 40px 20px; }
        .detail-card, .form-card, .quiz-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        h1 { font-size: 28px; color: #1a2a3a; margin-bottom: 20px; }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        .certif-badge {
            background: linear-gradient(135deg, #f5af19, #f12711);
            color: white;
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 15px;
        }
        .quiz-question {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .quiz-option {
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quiz-option input {
            width: 18px;
            height: 18px;
        }
        label { display: block; margin: 15px 0 5px; font-weight: 600; }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
        }
        button {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            margin-top: 15px;
            width: 100%;
        }
        button:hover { background: #218838; }
        .btn-quiz {
            background: #f5af19;
            width: 100%;
        }
        .btn-quiz:hover { background: #e5a00f; }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #0073b1;
            text-decoration: none;
        }
        h3 { margin-bottom: 20px; color: #1a2a3a; }
        .quiz-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .certificat-card {
            background: linear-gradient(135deg, #fef9e6, #fff);
            text-align: center;
        }
        .certificat-card h4 {
            color: #f5af19;
            font-size: 24px;
            margin-bottom: 15px;
        }
        .certificat-number {
            font-family: monospace;
            font-size: 14px;
            background: #f0f2f5;
            padding: 8px;
            border-radius: 8px;
            display: inline-block;
        }
        .btn-certificat {
            background: #28a745;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>

<div class="container">
    <?php if ($sent): ?>
        <div class="success">
            ✅ Votre inscription a été enregistrée avec succès !<br>
            Un email de confirmation vous sera envoyé.
        </div>
        <a href="formation.php" class="back-link">← Retour aux formations</a>
    <?php elseif ($quizSuccess): ?>
        <!-- Affichage du certificat directement -->
        <div class="quiz-card certificat-card">
            <div class="success">
                🎉 Félicitations ! Vous avez obtenu votre certification !
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <h4>🏆 CERTIFICAT DE RÉUSSITE</h4>
                <p>Cette certification est décernée à</p>
                <h2><?= strtoupper($_SESSION['user']['prenom'] ?? '') . ' ' . strtoupper($_SESSION['user']['nom'] ?? 'Utilisateur') ?></h2>
                <p>pour avoir réussi la certification</p>
                <h3><?= htmlspecialchars($certification['titre']) ?></h3>
                <p>Avec un score de <strong><?= $pourcentage ?>%</strong></p>
                <p>Le <?= date('d/m/Y') ?></p>
                <p class="certificat-number">Numéro: <?= $numeroCert ?? 'CERT-' . uniqid() ?></p>
                <div style="margin-top: 20px;">
                    <button onclick="window.print()" class="btn-quiz" style="width: auto; padding: 10px 30px;">
                        🖨️ Imprimer / PDF
                    </button>
                    <a href="formation.php" class="back-link" style="margin-left: 15px;">← Retour aux formations</a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Détails de la formation -->
        <div class="detail-card">
            <?php if(!empty($formation['certification_titre'])): ?>
                <div class="certif-badge">
                    <i class="fas fa-certificate"></i> Certification incluse
                </div>
            <?php endif; ?>
            
            <h1><?= htmlspecialchars($formation['titre']) ?></h1>
            <div class="info-row">
                <strong>📅 Date début</strong>
                <span><?= date('d/m/Y', strtotime($formation['date_debut'])) ?></span>
            </div>
            <div class="info-row">
                <strong>📅 Date fin</strong>
                <span><?= date('d/m/Y', strtotime($formation['date_fin'])) ?></span>
            </div>
            <div class="info-row">
                <strong>🏷️ Type</strong>
                <span><?= $formation['type'] == 'presentiel' ? 'Présentiel' : 'En ligne' ?></span>
            </div>
            <div class="info-row">
                <strong>👥 Places disponibles</strong>
                <span><?= $formation['places_max'] ?></span>
            </div>
            <div class="info-row">
                <strong>📝 Description</strong>
                <span><?= nl2br(htmlspecialchars($formation['description'] ?? '')) ?></span>
            </div>
        </div>

        <!-- Formulaire d'inscription -->
        <div class="form-card">
            <h3>📝 Inscription à la formation</h3>
            <?php if ($err): ?>
                <div class="error">❌ <?= htmlspecialchars($err) ?></div>
            <?php endif; ?>
            <form method="POST">
                <label>Nom complet *</label>
                <input type="text" name="nom" required>
                <label>Prénom</label>
                <input type="text" name="prenom">
                <label>Email *</label>
                <input type="email" name="email" required>
                <label>Téléphone</label>
                <input type="tel" name="telephone">
                <button type="submit" name="inscrire">✅ S'inscrire</button>
            </form>
        </div>

        <!-- QUIZ DE CERTIFICATION (intégré) -->
        <?php if($certification && $quiz && count($questions) > 0): ?>
            <div class="quiz-card">
                <div class="quiz-header">
                    <h3>🎓 Certification : <?= htmlspecialchars($certification['titre']) ?></h3>
                    <span style="background: #f0f2f5; padding: 5px 12px; border-radius: 20px;">
                        Seuil: <?= $quiz['seuil_reussite'] ?>%
                    </span>
                </div>
                <p><?= htmlspecialchars($certification['description']) ?></p>
                <hr style="margin: 15px 0;">
                
                <?= $quizMessage ?>
                
                <form method="POST">
                    <?php $numQuestion = 1; ?>
                    <?php foreach($questions as $q): ?>
                        <div class="quiz-question">
                            <strong>Question <?= $numQuestion ?>: <?= htmlspecialchars($q['question']) ?> (<?= $q['points'] ?> pt)</strong>
                            <?php foreach($q['reponses'] as $rep): ?>
                                <div class="quiz-option">
                                    <input type="radio" 
                                           name="answer[<?= $q['id_question'] ?>]" 
                                           value="<?= $rep['id_reponse'] ?>" 
                                           id="q<?= $q['id_question'] ?>_<?= $rep['id_reponse'] ?>"
                                           required>
                                    <label for="q<?= $q['id_question'] ?>_<?= $rep['id_reponse'] ?>">
                                        <?= htmlspecialchars($rep['reponse']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php $numQuestion++; ?>
                    <?php endforeach; ?>
                    <button type="submit" name="submit_quiz" class="btn-quiz">🏆 Valider la certification</button>
                </form>
            </div>
        <?php endif; ?>
        
        <a href="formation.php" class="back-link">← Retour aux formations</a>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
>>>>>>> formation
