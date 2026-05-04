<?php
require_once __DIR__ . '/forum_bootstrap.php';
require_once __DIR__ . '/../../../controller/ForumController.php';
require_once __DIR__ . '/../_layout/paths.php';

if (empty($_SESSION['forum_csrf'])) {
    $_SESSION['forum_csrf'] = bin2hex(random_bytes(16));
}

$fc = new ForumController();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$sujet = $id > 0 ? $fc->getSujet($id) : false;
if (!$sujet) {
    header('Location: liste_sujets.php');
    exit;
}

if (isset($_GET['delmsg'], $_GET['token']) && (string) $_GET['token'] === (string) $_SESSION['forum_csrf']) {
    if ($fc->deleteMessage((int) $_GET['delmsg'])) {
        header('Location: ' . bo_url('forum/sujet_messages.php?id=' . $id . '&ok=1'));
        exit;
    }
    header('Location: ' . bo_url('forum/sujet_messages.php?id=' . $id . '&err=1'));
    exit;
}

$msgs = $fc->listMessagesBySujet($id);
$t = urlencode((string) $_SESSION['forum_csrf']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messages — <?= htmlspecialchars((string) $sujet['titre']) ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars(bo_url('commerce.css')) ?>">
    <style>
        .msg-row { border-bottom: 1px solid #e2e8f0; padding: 14px 0; }
        .msg-row:last-child { border-bottom: 0; }
        .msg-meta { font-size: 0.82rem; color: #64748b; margin-bottom: 6px; }
        .msg-body { white-space: pre-wrap; word-break: break-word; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content commerce-page">
    <div class="topbar">
        <div class="page-title">Messages du sujet</div>
        <div class="actions">
            <a href="<?= htmlspecialchars(bo_url('forum/liste_sujets.php')) ?>" class="btn btn-secondary">← Sujets</a>
        </div>
    </div>
    <div class="card" style="max-width:800px;margin:0 auto 16px">
        <h1 style="font-size:1.15rem;margin:0 0 6px"><?= htmlspecialchars((string) $sujet['titre']) ?></h1>
        <p style="color:#64748b;margin:0;font-size:0.9rem">
            Catégorie : <?= htmlspecialchars((string) $sujet['cat_titre']) ?>
            · <?= (int) $sujet['epingle'] ? 'Épinglé' : 'Non épinglé' ?>
            · <?= (int) $sujet['verrouille'] ? 'Verrouillé' : 'Ouvert' ?>
        </p>
    </div>
    <?php if (isset($_GET['ok'])): ?>
        <p class="alert" style="max-width:800px;margin:0 auto 12px;padding:10px 14px;border-radius:8px;background:#ecfdf5;color:#047857;font-weight:600">Mis à jour.</p>
    <?php endif; ?>
    <?php if (isset($_GET['err'])): ?>
        <p class="alert" style="max-width:800px;margin:0 auto 12px;padding:10px 14px;border-radius:8px;background:#fef2f2;color:#b91c1c;font-weight:600">Impossible de supprimer le seul message du sujet. Supprimez le sujet entier.</p>
    <?php endif; ?>
    <div class="card" style="max-width:800px;margin:0 auto">
        <h2 style="margin-top:0;font-size:1rem">Réponses (<?= count($msgs) ?>)</h2>
        <?php foreach ($msgs as $m): ?>
            <div class="msg-row">
                <div class="msg-meta">
                    <strong><?= htmlspecialchars(trim(($m['prenom'] ?? '') . ' ' . ($m['nom'] ?? ''))) ?></strong>
                    &lt;<?= htmlspecialchars((string) ($m['email'] ?? '')) ?>&gt;
                    · <?= htmlspecialchars((string) $m['created_at']) ?>
                    · #<?= (int) $m['id_message'] ?>
                </div>
                <div class="msg-body"><?= strlen(trim((string) $m['contenu'])) ? htmlspecialchars((string) $m['contenu']) : '<span style="color:#94a3b8">(texte vide)</span>' ?></div>
                <?php if (!empty($m['image_fichier'])): ?>
                    <p style="margin:10px 0 0">
                        <img src="<?= htmlspecialchars('../../' . ltrim((string) $m['image_fichier'], '/')) ?>"
                             alt="" style="max-width:100%;max-height:400px;border-radius:8px;border:1px solid #e2e8f0">
                    </p>
                <?php endif; ?>
                <?php if (count($msgs) > 1): ?>
                    <p style="margin:10px 0 0">
                        <a class="btn btn-sm btn-danger" href="sujet_messages.php?id=<?= $id ?>&amp;delmsg=<?= (int) $m['id_message'] ?>&amp;token=<?= $t ?>"
                           onclick="return confirm('Supprimer ce message ?');">Supprimer ce message</a>
                    </p>
                <?php else: ?>
                    <p style="margin:10px 0 0;font-size:0.85rem;color:#94a3b8">Dernier message : supprimez le <a href="<?= htmlspecialchars(bo_url('forum/liste_sujets.php?delete=' . $id . '&token=' . urlencode((string) $_SESSION['forum_csrf']))) ?>">sujet entier</a> depuis la liste.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
