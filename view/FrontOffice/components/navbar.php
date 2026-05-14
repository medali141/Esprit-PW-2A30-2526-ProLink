<?php
// Ensure the common init/bootstrap is loaded. init.php sets up session and $baseUrl.
if (!defined('APP_INIT')) {
    require_once dirname(__DIR__, 3) . '/init.php';
}

$__nav_user = $_SESSION['user'] ?? null;
$__nav_type = strtolower($__nav_user['type'] ?? '');
$__cart = (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) ? array_sum($_SESSION['cart']) : 0;
<<<<<<< HEAD

// --- Notifications utilisateur (cloche dans la navbar) ---------------------
$__nav_notifs = [];
$__nav_unread = 0;
$__nav_welcome = false;
if ($__nav_user) {
    require_once dirname(__DIR__, 3) . '/controller/NotificationP.php';
    $__np = new NotificationP();
    $__nav_unread = $__np->countUnread((int) ($__nav_user['iduser'] ?? 0));
    $__nav_notifs = $__np->listForUser((int) ($__nav_user['iduser'] ?? 0), false, 5);
    if (!empty($_SESSION['__just_logged_in'])) {
        $__nav_welcome = true;
        unset($_SESSION['__just_logged_in']);
    }
}
$__projectRoot = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
$__projectRoot = ''; // built below from $baseUrl which already includes /<project>/view
=======
>>>>>>> formation
?>
<script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>

<nav class="navbar">
    <div class="logo">ProLink</div>

    <ul class="nav-links">
    <li><a href="<?= $baseUrl ?>/FrontOffice/home.php">Accueil</a></li>
        <li><a href="<?= $baseUrl ?>/FrontOffice/catalogue.php">Boutique</a></li>
        <li><a href="<?= $baseUrl ?>/FrontOffice/panier.php">Panier<?= $__cart > 0 ? ' (' . (int) $__cart . ')' : '' ?></a></li>
        <?php if ($__nav_user): ?>
            <li><a href="<?= $baseUrl ?>/FrontOffice/mesCommandes.php">Mes commandes</a></li>
            <?php if ($__nav_type === 'entrepreneur'): ?>
                <li><a href="<?= $baseUrl ?>/FrontOffice/mesProduits.php">Mes produits</a></li>
                <li><a href="<?= $baseUrl ?>/FrontOffice/mesVentes.php">Mes ventes</a></li>
            <?php endif; ?>
        <?php endif; ?>
    <li><a href="#">Réseau</a></li>
    <li><a href="<?= $baseUrl ?>/FrontOffice/projects.php">Projets</a></li>
    <li><a href="<?= $baseUrl ?>/FrontOffice/formation.php">Formations</a></li>
        <li><a href="<?= $baseUrl ?>/FrontOffice/evenements.php">Événements</a></li>
        <li><a href="<?= $baseUrl ?>/FrontOffice/forum.php">Forum</a></li>
    </ul>

<!-- global stylesheet -->
<link rel="stylesheet" href="<?= $baseUrl ?>/assets/style.css">

     <div class="auth">
        <button type="button" class="theme-toggle js-theme-toggle" aria-label="Activer le mode sombre" aria-pressed="false">🌙</button>
<<<<<<< HEAD

        <?php if ($__nav_user): ?>
            <div class="pl-notif" data-notif-root>
                <button type="button" class="pl-notif-btn" aria-haspopup="true" aria-expanded="false" aria-label="Notifications" data-notif-toggle>
                    <span aria-hidden="true">🔔</span>
                    <?php if ($__nav_unread > 0): ?>
                        <span class="pl-notif-badge" data-notif-badge><?= $__nav_unread > 99 ? '99+' : (int) $__nav_unread ?></span>
                    <?php endif; ?>
                </button>
                <div class="pl-notif-panel" hidden data-notif-panel>
                    <div class="pl-notif-head">
                        <strong>Notifications</strong>
                        <?php if ($__nav_unread > 0): ?>
                            <form method="post" action="<?= $baseUrl ?>/FrontOffice/notifications.php" style="margin:0">
                                <input type="hidden" name="action" value="mark_all_read">
                                <input type="hidden" name="redirect_back" value="1">
                                <button type="submit" class="pl-notif-link">Tout marquer lu</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <ul class="pl-notif-list">
                        <?php if (empty($__nav_notifs)): ?>
                            <li class="pl-notif-empty">Aucune notification pour le moment.</li>
                        <?php else: ?>
                            <?php foreach ($__nav_notifs as $__n):
                                $isUnread = (int) ($__n['is_read'] ?? 0) === 0;
                                $link = $baseUrl . '/FrontOffice/notifications.php?go=' . (int) $__n['id_notification'];
                                $ts = strtotime((string) $__n['created_at']);
                                $when = $ts ? date('d/m H:i', $ts) : '';
                            ?>
                                <li class="pl-notif-item<?= $isUnread ? ' is-unread' : '' ?>">
                                    <a href="<?= htmlspecialchars($link) ?>">
                                        <span class="pl-notif-title"><?= htmlspecialchars((string) $__n['title']) ?></span>
                                        <?php if (!empty($__n['body'])): ?>
                                            <span class="pl-notif-body"><?= nl2br(htmlspecialchars(mb_substr((string) $__n['body'], 0, 160))) ?><?= mb_strlen((string) $__n['body']) > 160 ? '…' : '' ?></span>
                                        <?php endif; ?>
                                        <span class="pl-notif-time"><?= htmlspecialchars($when) ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    <a href="<?= $baseUrl ?>/FrontOffice/notifications.php" class="pl-notif-all">Voir toutes les notifications →</a>
                </div>
            </div>
=======
        <?php if ($__nav_user): ?>
>>>>>>> formation
            <a href="<?= $baseUrl ?>/FrontOffice/profile/profile.php" class="btn login">Bonjour, <?= htmlspecialchars($__nav_user['prenom'] ?? $__nav_user['nom'] ?? 'Utilisateur') ?></a>
            <a href="<?= $baseUrl ?>/FrontOffice/profile/profile.php?action=logout" class="btn register">Se déconnecter</a>
        <?php else: ?>
            <a href="<?= $baseUrl ?>/login.php" class="btn login">Login</a>
            <a href="<?= $baseUrl ?>/register.php" class="btn register">Register</a>
        <?php endif; ?>
    </div>
</nav>

<<<<<<< HEAD
<?php if ($__nav_user && $__nav_welcome): ?>
    <div class="pl-toast" id="plToast" role="status" aria-live="polite">
        <div class="pl-toast-icon">👋</div>
        <div class="pl-toast-body">
            <strong>Bienvenue, <?= htmlspecialchars($__nav_user['prenom'] ?? $__nav_user['nom'] ?? 'utilisateur') ?> !</strong>
            <?php if ($__nav_unread > 0): ?>
                <span>Vous avez <strong><?= (int) $__nav_unread ?> notification<?= $__nav_unread > 1 ? 's' : '' ?> non lue<?= $__nav_unread > 1 ? 's' : '' ?></strong>.</span>
                <a href="<?= $baseUrl ?>/FrontOffice/notifications.php">Voir mes notifications →</a>
            <?php else: ?>
                <span>Aucune nouvelle notification.</span>
            <?php endif; ?>
        </div>
        <button type="button" class="pl-toast-close" aria-label="Fermer" onclick="this.parentElement.remove()">✕</button>
    </div>
<?php endif; ?>

=======
>>>>>>> formation
<style>
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 50px;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.logo {
    font-size: 22px;
    font-weight: bold;
    color: #0073b1;
}

.nav-links {
    list-style: none;
    display: flex;
    gap: 20px;
}

.nav-links a {
    text-decoration: none;
    color: #333;
    font-weight: 500;
}

.nav-links a:hover {
    color: #0073b1;
}

.auth .btn {
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    margin-left: 10px;
}

.login {
    border: 1px solid #0073b1;
    color: #0073b1;
}

.register {
    background: #0073b1;
    color: white;
}

.auth {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

html.dark-mode .navbar {
    background: #121826 !important;
    box-shadow: 0 4px 24px rgba(0,0,0,0.35) !important;
}
html.dark-mode .logo { color: #38bdf8 !important; }
html.dark-mode .nav-links a { color: #e2e8f0 !important; }
html.dark-mode .nav-links a:hover { color: #38bdf8 !important; }
html.dark-mode .login {
    border-color: rgba(56,189,248,0.45) !important;
    color: #38bdf8 !important;
}
html.dark-mode .register {
    background: #38bdf8 !important;
    color: #0f1724 !important;
}
<<<<<<< HEAD

/* ---- Notifications ---- */
.pl-notif { position: relative; }
.pl-notif-btn {
    position: relative; appearance: none; border: 1px solid rgba(0,115,177,0.25);
    background: #fff; color: #0073b1; width: 38px; height: 38px;
    border-radius: 999px; cursor: pointer; font-size: 1rem;
    display: inline-flex; align-items: center; justify-content: center;
}
.pl-notif-btn:hover { background: #f0f9ff; }
.pl-notif-btn:focus { outline: 3px solid rgba(0,115,177,0.25); outline-offset: 1px; }
.pl-notif-badge {
    position: absolute; top: -4px; right: -4px;
    background: #ef4444; color: #fff; font-size: 0.7rem; font-weight: 700;
    line-height: 1; padding: 3px 6px; border-radius: 999px;
    border: 2px solid #fff;
}
.pl-notif-panel {
    position: absolute; right: 0; top: calc(100% + 8px); width: 340px;
    max-width: 90vw; background: #fff; border-radius: 14px;
    box-shadow: 0 18px 40px rgba(15,23,42,0.18); border: 1px solid #e2e8f0;
    z-index: 999; overflow: hidden; animation: pl-pop 0.15s ease-out;
}
@keyframes pl-pop { from { transform: translateY(-4px); opacity: 0 } to { transform: translateY(0); opacity: 1 } }
.pl-notif-panel[hidden] { display: none; }
.pl-notif-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 14px; border-bottom: 1px solid #e2e8f0;
    background: linear-gradient(135deg, #0073b1, #00a0dc); color: #fff;
}
.pl-notif-head strong { font-size: 0.95rem; }
.pl-notif-link {
    background: rgba(255,255,255,0.16); color: #fff; border: none; cursor: pointer;
    padding: 4px 10px; border-radius: 999px; font-size: 0.78rem; font-weight: 600;
}
.pl-notif-link:hover { background: rgba(255,255,255,0.28); }
.pl-notif-list { list-style: none; margin: 0; padding: 0; max-height: 360px; overflow-y: auto; }
.pl-notif-empty { padding: 22px 16px; color: #64748b; text-align: center; font-size: 0.9rem; }
.pl-notif-item { border-bottom: 1px solid #f1f5f9; }
.pl-notif-item:last-child { border-bottom: none; }
.pl-notif-item a {
    display: block; padding: 10px 14px; text-decoration: none; color: #0f172a;
    transition: background 0.12s;
}
.pl-notif-item a:hover { background: #f8fafc; }
.pl-notif-item.is-unread a { background: #eff6ff; }
.pl-notif-item.is-unread a:hover { background: #dbeafe; }
.pl-notif-title { display: block; font-weight: 700; font-size: 0.88rem; }
.pl-notif-body { display: block; color: #475569; font-size: 0.82rem; margin-top: 2px; line-height: 1.35; }
.pl-notif-time { display: block; color: #94a3b8; font-size: 0.72rem; margin-top: 4px; }
.pl-notif-all { display: block; text-align: center; padding: 10px;
    color: #0073b1; text-decoration: none; font-weight: 600; font-size: 0.85rem;
    border-top: 1px solid #e2e8f0; background: #f8fafc;
}
.pl-notif-all:hover { background: #e0f2fe; }

/* ---- Welcome toast ---- */
.pl-toast {
    position: fixed; right: 22px; top: 80px; z-index: 1200;
    display: flex; gap: 12px; align-items: flex-start;
    padding: 14px 16px 14px 14px; max-width: 380px; width: calc(100% - 44px);
    background: #fff; color: #0f172a; border-radius: 14px;
    box-shadow: 0 18px 40px rgba(15,23,42,0.18);
    border-left: 6px solid #0073b1;
    animation: pl-slide-in 0.35s cubic-bezier(.2,.9,.3,1);
}
@keyframes pl-slide-in { from { transform: translateX(20px); opacity: 0 } to { transform: translateX(0); opacity: 1 } }
.pl-toast-icon { font-size: 1.5rem; line-height: 1; }
.pl-toast-body { flex: 1; font-size: 0.9rem; line-height: 1.45; }
.pl-toast-body strong { color: #0073b1; }
.pl-toast-body span { display: block; color: #475569; margin-top: 2px; }
.pl-toast-body a { display: inline-block; margin-top: 6px; color: #0073b1; font-weight: 600; text-decoration: none; }
.pl-toast-body a:hover { text-decoration: underline; }
.pl-toast-close {
    appearance: none; border: none; background: transparent; color: #94a3b8;
    cursor: pointer; font-size: 0.95rem; padding: 4px;
}
.pl-toast-close:hover { color: #0f172a; }

html.dark-mode .pl-notif-btn { background: #1e293b; border-color: rgba(56,189,248,0.4); color: #7dd3fc; }
html.dark-mode .pl-notif-btn:hover { background: #0f172a; }
html.dark-mode .pl-notif-panel { background: #0f172a; border-color: rgba(148,163,184,0.25); }
html.dark-mode .pl-notif-item a { color: #e2e8f0; }
html.dark-mode .pl-notif-item a:hover { background: #1e293b; }
html.dark-mode .pl-notif-item.is-unread a { background: #1e3a8a; }
html.dark-mode .pl-notif-body { color: #cbd5e1; }
html.dark-mode .pl-notif-all { background: #0b1220; border-top-color: rgba(148,163,184,0.18); color: #7dd3fc; }
html.dark-mode .pl-toast { background: #0f172a; color: #e2e8f0; border-left-color: #38bdf8; }
html.dark-mode .pl-toast-body span { color: #cbd5e1; }
html.dark-mode .pl-toast-body strong { color: #38bdf8; }
html.dark-mode .pl-toast-body a { color: #7dd3fc; }
</style>
<script src="<?= $baseUrl ?>/assets/theme.js" defer></script>
<script>
(function () {
    var root = document.querySelector('[data-notif-root]');
    if (!root) return;
    var btn = root.querySelector('[data-notif-toggle]');
    var panel = root.querySelector('[data-notif-panel]');
    if (!btn || !panel) return;

    function close() { panel.hidden = true; btn.setAttribute('aria-expanded', 'false'); }
    function open() { panel.hidden = false; btn.setAttribute('aria-expanded', 'true'); }

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        if (panel.hidden) open(); else close();
    });
    document.addEventListener('click', function (e) {
        if (!root.contains(e.target)) close();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') close();
    });

    var toast = document.getElementById('plToast');
    if (toast) {
        setTimeout(function () {
            toast.style.transition = 'opacity 0.5s, transform 0.5s';
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(20px)';
            setTimeout(function () { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 600);
        }, 8000);
    }
})();
</script>
=======
</style>
<script src="<?= $baseUrl ?>/assets/theme.js" defer></script>
>>>>>>> formation
