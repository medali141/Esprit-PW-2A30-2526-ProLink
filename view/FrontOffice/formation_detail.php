<?php
require_once __DIR__ . '/../../init.php';
requireLogin('Connectez-vous pour consulter cette formation.');
require_once __DIR__ . '/../../controller/FormationP.php';
$fp = new FormationP();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$f = $id ? $fp->get($id) : null;
$sent = false; $err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ok = $fp->addInscription(array_merge(['id_formation'=>$id], $_POST));
    if ($ok) { $sent = true; } else { $err = 'Impossible d\'enregistrer l\'inscription pour le moment.'; }
}
if (!$f) { header('Location: formation.php'); exit; }
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
</head>
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <article class="fo-event-detail">
        <div class="fo-event-card">
            <h1 class="fo-event-title"><?= htmlspecialchars($f['titre']) ?></h1>
            <div class="fo-event-kv">
                <div class="fo-event-kv__row"><strong>Date début :</strong> <?= htmlspecialchars($f['date_debut'] ?? '—') ?></div>
                <div class="fo-event-kv__row"><strong>Date fin :</strong> <?= htmlspecialchars($f['date_fin'] ?? '—') ?></div>
            </div>
            <div class="fo-event-excerpt"><?= nl2br(htmlspecialchars($f['description'] ?? '')) ?></div>
        </div>
        <aside class="fo-form-card">
            <?php if ($sent): ?>
                <p class="fo-banner fo-banner--ok">Votre inscription a été enregistrée. Un e-mail de confirmation sera envoyé.</p>
                <p><a href="formation.php">← Retour aux formations</a></p>
            <?php else: ?>
                <?php if ($err): ?><p class="fo-banner fo-banner--err"><?= htmlspecialchars($err) ?></p><?php endif; ?>
                <h3>Inscription</h3>
                <form method="post">
                    <label>Nom *</label>
                    <input name="nom" required>
                    <label>Prénom</label>
                    <input name="prenom">
                    <label>Email *</label>
                    <input type="email" name="email" required>
                    <label>Téléphone</label>
                    <input name="telephone">
                    <div style="margin-top:12px">
                        <button class="fo-btn fo-btn--primary">S'inscrire</button>
                    </div>
                </form>
            <?php endif; ?>
        </aside>
    </article>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
