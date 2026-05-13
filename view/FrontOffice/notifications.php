<?php
/**
 * Front-office — page de notifications de l'utilisateur connecté.
 *
 * Actions :
 *  - POST action=mark_all_read    : marque tout comme lu
 *  - POST action=mark_one         : marque une notification précise (id)
 *  - POST action=delete           : supprime une notification (id)
 *  - GET  ?go=<id>                : marque l'id comme lu puis redirige vers son `link`
 */
require_once __DIR__ . '/../../init.php';
requireLogin('Connectez-vous pour voir vos notifications.');
require_once __DIR__ . '/../../controller/NotificationP.php';

$u = currentUser();
$uid = (int) ($u['iduser'] ?? 0);
$np  = new NotificationP();

$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    if ($action === 'mark_all_read') {
        $n = $np->markAllRead($uid);
        if (!empty($_POST['redirect_back']) && !empty($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
        $info = $n > 0 ? ($n . ' notification' . ($n > 1 ? 's' : '') . ' marquée' . ($n > 1 ? 's' : '') . ' comme lue' . ($n > 1 ? 's' : '') . '.') : 'Aucune notification non lue.';
    } elseif ($action === 'mark_one') {
        $np->markRead((int) ($_POST['id'] ?? 0), $uid);
        $info = 'Notification marquée comme lue.';
    } elseif ($action === 'delete') {
        $np->delete((int) ($_POST['id'] ?? 0), $uid);
        $info = 'Notification supprimée.';
    }
}

if (isset($_GET['go'])) {
    $goId = (int) $_GET['go'];
    $np->markRead($goId, $uid);
    $list = $np->listForUser($uid, false, 100);
    foreach ($list as $n) {
        if ((int) $n['id_notification'] === $goId && !empty($n['link'])) {
            $linkRaw = ltrim((string) $n['link'], '/');
            // Liens stockés sous la forme "view/FrontOffice/x.php" : on bâtit
            // l'URL absolue à partir de $baseUrl (qui contient déjà /view).
            if (strpos($linkRaw, '://') !== false || strncmp($linkRaw, '/', 1) === 0) {
                $target = $linkRaw;
            } elseif (strncmp($linkRaw, 'view/', 5) === 0) {
                $target = $baseUrl . '/' . substr($linkRaw, 5);
            } else {
                $target = $baseUrl . '/' . $linkRaw;
            }
            header('Location: ' . $target);
            exit;
        }
    }
    header('Location: notifications.php');
    exit;
}

$notifications = $np->listForUser($uid, false, 50);
$unread = $np->countUnread($uid);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notifications — ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
    <style>
        .nf-page { max-width: 780px; margin: 0 auto; }
        .nf-toolbar {
            display: flex; align-items: center; justify-content: space-between;
            gap: 10px; margin-bottom: 14px; flex-wrap: wrap;
        }
        .nf-stats { color: #475569; font-size: 0.9rem; }
        .nf-stats strong { color: #0f172a; }
        .nf-list { display: grid; gap: 10px; }
        .nf-card {
            display: flex; gap: 12px; align-items: flex-start;
            padding: 14px 16px; background: #fff; border: 1px solid #e2e8f0;
            border-radius: 14px; box-shadow: 0 6px 18px rgba(15,23,42,0.04);
        }
        .nf-card.is-unread { border-color: #93c5fd; background: #eff6ff; }
        .nf-icon {
            flex-shrink: 0; width: 40px; height: 40px; border-radius: 999px;
            background: #e0f2fe; color: #0369a1; display: flex; align-items: center;
            justify-content: center; font-size: 1.15rem;
        }
        .nf-card.is-unread .nf-icon { background: #dbeafe; color: #1d4ed8; }
        .nf-body { flex: 1; min-width: 0; }
        .nf-title { font-weight: 700; color: #0f172a; }
        .nf-text  { color: #334155; font-size: 0.92rem; margin-top: 3px; white-space: pre-wrap; }
        .nf-time  { display: block; color: #94a3b8; font-size: 0.78rem; margin-top: 4px; }
        .nf-actions { display: flex; flex-direction: column; gap: 4px; align-items: flex-end; }
        .nf-link {
            background: transparent; border: none; cursor: pointer; padding: 0;
            color: #0073b1; font-weight: 600; font-size: 0.8rem;
        }
        .nf-link:hover { text-decoration: underline; }
        .nf-link.danger { color: #b91c1c; }
        .nf-banner { padding: 10px 14px; border-radius: 10px; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; margin-bottom: 14px; font-weight: 600; }
        .nf-empty {
            text-align: center; padding: 40px 20px; color: #64748b;
            background: #fff; border: 1px dashed #cbd5e1; border-radius: 14px;
        }
    </style>
</head>
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page nf-page">
    <header class="fo-hero fo-hero--tight">
        <h1>🔔 Notifications</h1>
        <p class="fo-lead">Tous vos messages — nouvelles candidatures, acceptations, évaluations…</p>
    </header>

    <?php if ($info): ?>
        <p class="nf-banner"><?= htmlspecialchars($info) ?></p>
    <?php endif; ?>

    <div class="nf-toolbar">
        <div class="nf-stats">
            <strong><?= count($notifications) ?></strong> notification<?= count($notifications) > 1 ? 's' : '' ?> ·
            <strong><?= (int) $unread ?></strong> non lue<?= $unread > 1 ? 's' : '' ?>
        </div>
        <?php if ($unread > 0): ?>
            <form method="post" style="margin:0">
                <input type="hidden" name="action" value="mark_all_read">
                <button type="submit" class="fo-btn fo-btn--primary">Tout marquer comme lu</button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="nf-empty">
            <p>Vous n'avez aucune notification pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="nf-list">
            <?php foreach ($notifications as $n):
                $isUnread = (int) ($n['is_read'] ?? 0) === 0;
                $type = (string) ($n['type'] ?? '');
                $icon = '🔔';
                if (strpos($type, 'accepted') !== false)        $icon = '🎉';
                elseif (strpos($type, 'rejected') !== false)    $icon = '✖';
                elseif (strpos($type, 'received') !== false)    $icon = '📨';
                elseif (strpos($type, 'evaluated') !== false)   $icon = '⭐';
                elseif (strpos($type, 'pending') !== false)     $icon = '⏳';
                $ts = strtotime((string) ($n['created_at'] ?? ''));
                $when = $ts ? date('d/m/Y H:i', $ts) : '';
            ?>
                <article class="nf-card<?= $isUnread ? ' is-unread' : '' ?>">
                    <div class="nf-icon" aria-hidden="true"><?= $icon ?></div>
                    <div class="nf-body">
                        <div class="nf-title"><?= htmlspecialchars((string) $n['title']) ?></div>
                        <?php if (!empty($n['body'])): ?>
                            <div class="nf-text"><?= htmlspecialchars((string) $n['body']) ?></div>
                        <?php endif; ?>
                        <span class="nf-time"><?= htmlspecialchars($when) ?></span>
                        <?php if (!empty($n['link'])): ?>
                            <a href="notifications.php?go=<?= (int) $n['id_notification'] ?>" class="fo-btn" style="margin-top:8px">Ouvrir →</a>
                        <?php endif; ?>
                    </div>
                    <div class="nf-actions">
                        <?php if ($isUnread): ?>
                            <form method="post" style="margin:0">
                                <input type="hidden" name="action" value="mark_one">
                                <input type="hidden" name="id" value="<?= (int) $n['id_notification'] ?>">
                                <button type="submit" class="nf-link">Marquer lu</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" style="margin:0" onsubmit="return confirm('Supprimer cette notification ?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $n['id_notification'] ?>">
                            <button type="submit" class="nf-link danger">Supprimer</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
