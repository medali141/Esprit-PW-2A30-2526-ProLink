<?php
require_once __DIR__ . '/../../../../init.php';
require_once __DIR__ . '/../../../../controller/AuthController.php';
require_once __DIR__ . '/../../../../controller/AdminMessengerController.php';

$auth = new AuthController();
$user = $auth->profile();
if (!$user) {
    header('Location: ../../../login.php');
    exit;
}
$meId = (int) ($user['iduser'] ?? 0);
$meType = strtolower((string) ($user['type'] ?? ''));
if ($meType !== 'admin') {
    header('Location: ../../../FrontOffice/home.php');
    exit;
}

$ctl = new AdminMessengerController();
$contacts = $ctl->listAdminInboxUsers($meId);
$senders = $ctl->listUsersWhoSentMessages(250);
$clientIndex = [];
foreach ($senders as $row) {
    $uid = (int) ($row['iduser'] ?? 0);
    if ($uid <= 0) continue;
    $clientIndex[$uid] = [
        'iduser' => $uid,
        'nom' => (string) ($row['nom'] ?? ''),
        'prenom' => (string) ($row['prenom'] ?? ''),
        'email' => (string) ($row['email'] ?? ''),
        'last_at' => (string) ($row['last_at'] ?? ''),
        'last_message' => (string) ($row['last_message'] ?? ''),
        'unread_count' => 0,
        'sent_count' => (int) ($row['sent_count'] ?? 0),
    ];
}
foreach ($contacts as $row) {
    $uid = (int) ($row['iduser'] ?? 0);
    if ($uid <= 0) continue;
    if (!isset($clientIndex[$uid])) {
        $clientIndex[$uid] = [
            'iduser' => $uid,
            'nom' => (string) ($row['nom'] ?? ''),
            'prenom' => (string) ($row['prenom'] ?? ''),
            'email' => (string) ($row['email'] ?? ''),
            'last_at' => (string) ($row['last_at'] ?? ''),
            'last_message' => (string) ($row['last_message'] ?? ''),
            'unread_count' => 0,
            'sent_count' => 0,
        ];
    }
    $clientIndex[$uid]['unread_count'] = max((int) ($clientIndex[$uid]['unread_count'] ?? 0), (int) ($row['unread_count'] ?? 0));
    if (((string) ($row['last_at'] ?? '')) > ((string) ($clientIndex[$uid]['last_at'] ?? ''))) {
        $clientIndex[$uid]['last_at'] = (string) ($row['last_at'] ?? '');
        $clientIndex[$uid]['last_message'] = (string) ($row['last_message'] ?? '');
    }
}
$clients = array_values($clientIndex);
usort($clients, static function (array $a, array $b): int {
    return strcmp((string) ($b['last_at'] ?? ''), (string) ($a['last_at'] ?? ''));
});
$selectedUserId = (int) ($_GET['uid'] ?? 0);
if ($selectedUserId <= 0 && !empty($clients)) {
    $selectedUserId = (int) ($clients[0]['iduser'] ?? 0);
}
$selectedProfile = $selectedUserId > 0 ? $ctl->getUserChatProfile($selectedUserId) : null;
$selectedName = trim((string) (($selectedProfile['prenom'] ?? '') . ' ' . ($selectedProfile['nom'] ?? '')));
$selectedName = $selectedName !== '' ? $selectedName : ($selectedUserId > 0 ? ('User #' . $selectedUserId) : '—');
$selectedEmail = trim((string) ($selectedProfile['email'] ?? ''));
$selectedPhone = trim((string) ($selectedProfile['telephone'] ?? ''));

if (isset($_GET['ajax']) && $_GET['ajax'] === 'thread') {
    header('Content-Type: application/json; charset=utf-8');
    if ($selectedUserId <= 0) {
        echo json_encode(['ok' => true, 'messages' => []]);
        exit;
    }
    $ctl->markAsRead($meId, $selectedUserId);
    echo json_encode(['ok' => true, 'messages' => $ctl->listConversation($meId, $selectedUserId)]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $to = (int) ($_POST['to_user'] ?? 0);
    $msg = trim((string) ($_POST['message'] ?? ''));
    if ($to > 0 && $msg !== '') {
        $ctl->sendMessage($meId, $to, $msg);
    }
    if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true]);
        exit;
    }
    header('Location: messagesClients.php?uid=' . $to);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Messages clients — BackOffice</title>
    <link rel="stylesheet" href="../../../assets/style.css">
    <style>
        .bo-chat-layout { display: grid; grid-template-columns: 320px 1fr; gap: 14px; margin-top: 14px; }
        .bo-chat-card { background: var(--card); border: 1px solid rgba(148,163,184,.2); border-radius: 14px; overflow: hidden; }
        .bo-chat-head { padding: 12px 14px; border-bottom: 1px solid rgba(148,163,184,.2); font-weight: 800; }
        .bo-chat-head--thread { display:flex; align-items:center; justify-content:space-between; gap:10px; }
        .bo-chat-peer { display:flex; flex-direction:column; gap:2px; }
        .bo-chat-peer__name { font-size:1rem; font-weight:800; }
        .bo-chat-peer__meta { font-size:.76rem; color:var(--muted); font-weight:700; }
        .bo-chat-tools { display:flex; flex-wrap:wrap; gap:6px; align-items:center; }
        .bo-chat-call {
            border: 1px solid rgba(14,165,233,.35);
            background: linear-gradient(135deg, rgba(14,165,233,.18), rgba(37,99,235,.14));
            color: #075985;
            border-radius: 999px;
            padding: 6px 11px;
            font-size: .78rem;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 6px 16px rgba(14,165,233,.18);
        }
        .bo-chat-call__icon {
            width: 20px;
            height: 20px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,.7);
            font-size: .75rem;
            line-height: 1;
        }
        .bo-chat-call[aria-disabled="true"] { opacity:.5; pointer-events:none; }
        .bo-chat-filter { padding: 10px 12px; border-bottom: 1px solid rgba(148,163,184,.15); background: rgba(11,102,195,.04); }
        .bo-chat-filter input {
            width: 100%;
            min-width: 0;
            border: 1px solid rgba(148,163,184,.28);
            border-radius: 10px;
            padding: 8px 10px;
            font-size: .86rem;
            background: #fff;
        }
        .bo-chat-users { max-height: 72vh; overflow: auto; }
        .bo-chat-user { display: block; padding: 10px 12px; border-bottom: 1px solid rgba(148,163,184,.14); text-decoration: none; color: inherit; }
        .bo-chat-user:hover { background: rgba(11,102,195,.05); }
        .bo-chat-user.is-active { background: rgba(34,197,94,.1); border-left: 3px solid #22c55e; padding-left: 9px; }
        .bo-chat-user__line { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
        .bo-chat-user__main { display: flex; align-items: center; gap: 8px; min-width: 0; }
        .bo-chat-user__name { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .bo-chat-user__avatar {
            width: 30px;
            height: 30px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .72rem;
            font-weight: 900;
            color: #fff;
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            flex: 0 0 auto;
        }
        .bo-chat-user__meta { display: flex; align-items: center; gap: 8px; margin-top: 3px; font-size: .74rem; color: #64748b; font-weight: 700; }
        .bo-chat-badge { background: #ef4444; color: #fff; border-radius: 999px; font-size: .72rem; font-weight: 800; padding: 2px 8px; }
        .bo-chat-badge--count { background: #0ea5e9; }
        .bo-chat-thread { display: grid; grid-template-rows: auto 1fr auto; min-height: 72vh; }
        .bo-msg-list { padding: 14px; overflow: auto; display: flex; flex-direction: column; gap: 9px; background: linear-gradient(180deg, rgba(11,102,195,.05), rgba(15,23,42,.02)); }
        .bo-msg-row { max-width: 76%; padding: 9px 11px; border-radius: 12px; font-size: .9rem; line-height: 1.4; }
        .bo-msg-row--me { align-self: flex-end; background: #0ea5e9; color: #fff; border-bottom-right-radius: 5px; }
        .bo-msg-row--other { align-self: flex-start; background: #e2e8f0; color: #0f172a; border-bottom-left-radius: 5px; }
        .bo-msg-meta { margin-top: 4px; font-size: .7rem; opacity: .78; font-weight: 700; }
        .bo-msg-form { display: flex; gap: 8px; padding: 10px; border-top: 1px solid rgba(148,163,184,.2); }
        .bo-msg-form input { flex: 1; min-width: 0; }
        .bo-msg-quick {
            display:flex;
            gap:6px;
            flex-wrap:wrap;
            padding:8px 10px 0;
            border-top: 1px dashed rgba(148,163,184,.25);
            background: rgba(11,102,195,.03);
        }
        .bo-msg-quick button {
            border:1px solid rgba(14,165,233,.25);
            background: rgba(14,165,233,.08);
            color:#075985;
            border-radius:999px;
            padding:5px 10px;
            font-size:.75rem;
            font-weight:700;
            cursor:pointer;
        }
        .bo-msg-footnote {
            padding: 0 10px 10px;
            color: #64748b;
            font-size: .74rem;
            font-weight: 700;
        }
        .bo-chat-live {
            font-size:.72rem;
            font-weight:800;
            color:#0f766e;
            background:rgba(20,184,166,.12);
            border:1px solid rgba(45,212,191,.35);
            border-radius:999px;
            padding:3px 8px;
        }
        html.dark-mode .bo-msg-quick { background: rgba(30,41,59,.55); border-color: rgba(148,163,184,.18); }
        html.dark-mode .bo-msg-quick button { color:#bae6fd; background:rgba(14,116,144,.25); border-color: rgba(56,189,248,.35); }
        html.dark-mode .bo-msg-footnote { color:#94a3b8; }
        html.dark-mode .bo-chat-live { color:#99f6e4; background:rgba(13,148,136,.2); border-color:rgba(45,212,191,.35); }
        .bo-msg-empty { color: var(--muted); font-weight: 700; }
        html.dark-mode .bo-chat-call { color:#bae6fd; border-color:rgba(56,189,248,.45); background:rgba(14,116,144,.3); }
        html.dark-mode .bo-chat-filter { background: rgba(30,41,59,.65); border-color: rgba(148,163,184,.18); }
        html.dark-mode .bo-chat-filter input { background: #0f172a; color: #f1f5f9; border-color: rgba(148,163,184,.28); }
        html.dark-mode .bo-chat-user__meta { color: #94a3b8; }
        @media (max-width: 980px) { .bo-chat-layout { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../../_layout/sidebar.php'; ?>
<main class="main-content">
    <h1>Messages clients</h1>
    <p class="hint">Messagerie service clients en temps réel avec les utilisateurs.</p>
    <section class="bo-chat-layout">
        <aside class="bo-chat-card">
            <div class="bo-chat-head">Liste clients</div>
            <div class="bo-chat-filter">
                <input type="search" id="bo-client-search" placeholder="Rechercher client (nom, email, message)...">
            </div>
            <div class="bo-chat-users" id="bo-client-list">
                <?php if (empty($clients)): ?>
                    <p class="bo-msg-empty" style="padding:10px 12px">Aucun client n’a encore envoyé de message.</p>
                <?php else: foreach ($clients as $c):
                    $uid = (int) ($c['iduser'] ?? 0);
                    $active = $uid === $selectedUserId;
                    $name = trim(((string) ($c['prenom'] ?? '')) . ' ' . ((string) ($c['nom'] ?? '')));
                    $name = $name !== '' ? $name : ('User #' . $uid);
                    $unread = (int) ($c['unread_count'] ?? 0);
                    $countSent = (int) ($c['sent_count'] ?? 0);
                    $lastAt = (string) ($c['last_at'] ?? '');
                    $initials = strtoupper(substr(trim((string) ($c['prenom'] ?? '')), 0, 1) . substr(trim((string) ($c['nom'] ?? '')), 0, 1));
                    if ($initials === '') $initials = 'CL';
                ?>
                    <a
                        class="bo-chat-user<?= $active ? ' is-active' : '' ?>"
                        href="messagesClients.php?uid=<?= $uid ?>"
                        data-search="<?= htmlspecialchars(strtolower($name . ' ' . (string) ($c['email'] ?? '') . ' ' . (string) ($c['last_message'] ?? ''))) ?>"
                    >
                        <div class="bo-chat-user__line">
                            <div class="bo-chat-user__main">
                                <span class="bo-chat-user__avatar" aria-hidden="true"><?= htmlspecialchars($initials) ?></span>
                                <strong class="bo-chat-user__name"><?= htmlspecialchars($name) ?></strong>
                            </div>
                            <div>
                                <?php if ($unread > 0): ?><span class="bo-chat-badge"><?= $unread ?></span><?php endif; ?>
                            </div>
                        </div>
                        <div class="bo-chat-user__meta">
                            <?php if ($countSent > 0): ?><span class="bo-chat-badge bo-chat-badge--count"><?= $countSent ?> msg</span><?php endif; ?>
                            <?php if ($lastAt !== ''): ?><span><?= htmlspecialchars(date('d/m H:i', strtotime($lastAt))) ?></span><?php endif; ?>
                        </div>
                        <div class="hint" style="font-size:.8rem"><?= htmlspecialchars((string) ($c['last_message'] ?? '')) ?></div>
                    </a>
                <?php endforeach; endif; ?>
            </div>
        </aside>

        <section class="bo-chat-card bo-chat-thread">
            <div class="bo-chat-head bo-chat-head--thread">
                <div class="bo-chat-peer">
                    <span class="bo-chat-peer__name" id="bo-peer-name"><?= htmlspecialchars($selectedName) ?></span>
                    <span class="bo-chat-peer__meta" id="bo-peer-meta">
                        <?= htmlspecialchars($selectedEmail !== '' ? $selectedEmail : 'Email non disponible') ?>
                        <?php if ($selectedPhone !== ''): ?> · <?= htmlspecialchars($selectedPhone) ?><?php endif; ?>
                    </span>
                </div>
                <div class="bo-chat-tools">
                    <span class="bo-chat-live" id="bo-live-state">Sync...</span>
                    <a id="bo-call-audio" class="bo-chat-call" href="<?= $selectedPhone !== '' ? 'tel:' . htmlspecialchars($selectedPhone) : '#' ?>" <?= $selectedPhone === '' ? 'aria-disabled="true"' : '' ?>><span class="bo-chat-call__icon" aria-hidden="true">☎</span>Appel audio</a>
                </div>
            </div>
            <div class="bo-msg-list" id="bo-msg-list">
                <p class="bo-msg-empty">Sélectionnez une conversation.</p>
            </div>
            <form class="bo-msg-form" id="bo-msg-form" method="post">
                <input type="hidden" name="send_message" value="1">
                <input type="hidden" name="ajax" value="1">
                <input type="hidden" name="to_user" id="bo-to-user" value="<?= (int) $selectedUserId ?>">
                <input type="text" name="message" id="bo-msg-input" placeholder="Réponse service clients..." maxlength="2000" <?= $selectedUserId > 0 ? '' : 'disabled' ?> required>
                <button type="submit" class="btn btn-primary" <?= $selectedUserId > 0 ? '' : 'disabled' ?>>Envoyer</button>
            </form>
            <div class="bo-msg-quick" id="bo-msg-quick">
                <button type="button" data-msg="Bonjour, nous avons bien recu votre message. Nous traitons votre demande rapidement.">Accuse reception</button>
                <button type="button" data-msg="Pouvez-vous partager le numero de commande concerne pour que nous puissions verifier ?">Demander commande</button>
                <button type="button" data-msg="Merci. Votre dossier est en cours de verification avec notre equipe logistique.">Verification logistique</button>
                <button type="button" data-msg="Nous sommes desoles pour ce desagrement. Une solution vous sera proposee sous peu.">Excuses + solution</button>
            </div>
            <div class="bo-msg-footnote">Astuce: utilisez les reponses rapides puis personnalisez avant envoi.</div>
        </section>
    </section>
</main>

<?php if ($selectedUserId > 0): ?>
<script>
(function () {
    var listEl = document.getElementById('bo-msg-list');
    var formEl = document.getElementById('bo-msg-form');
    var inputEl = document.getElementById('bo-msg-input');
    var liveStateEl = document.getElementById('bo-live-state');
    var quickWrapEl = document.getElementById('bo-msg-quick');
    var isLoading = false;
    var lastLoadAt = 0;
    function fmt(dt) {
        var d = new Date(String(dt).replace(' ', 'T'));
        if (isNaN(d.getTime())) return '';
        return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    function esc(v) {
        return String(v || '').replace(/[&<>"']/g, function (c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
        });
    }
    function render(messages) {
        if (!messages || !messages.length) {
            listEl.innerHTML = '<p class="bo-msg-empty">Pas encore de message.</p>';
            return;
        }
        var html = '';
        for (var i = 0; i < messages.length; i++) {
            var m = messages[i] || {};
            var mine = Number(m.id_sender || 0) === <?= (int) $meId ?>;
            var senderLabel = mine ? 'Admin' : (((m.sender_prenom || '') + ' ' + (m.sender_nom || '')).trim() || 'Client');
            html += '<div class="bo-msg-row ' + (mine ? 'bo-msg-row--me' : 'bo-msg-row--other') + '">';
            html += '<div>' + esc(m.message || '') + '</div>';
            html += '<div class="bo-msg-meta">' + esc((mine ? 'Service clients' : senderLabel)) + ' • ' + esc(fmt(m.created_at)) + '</div>';
            html += '</div>';
        }
        listEl.innerHTML = html;
        listEl.scrollTop = listEl.scrollHeight;
    }
    function load() {
        if (isLoading) return;
        isLoading = true;
        fetch('messagesClients.php?uid=<?= (int) $selectedUserId ?>&ajax=thread', { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                render((data && data.messages) || []);
                lastLoadAt = Date.now();
                if (liveStateEl) liveStateEl.textContent = 'En ligne';
            })
            .catch(function () {
                if (liveStateEl) liveStateEl.textContent = 'Reseau instable';
            })
            .finally(function () { isLoading = false; });
    }
    formEl.addEventListener('submit', function (e) {
        e.preventDefault();
        var msg = inputEl.value.trim();
        if (!msg) return;
        var fd = new FormData(formEl);
        fetch('messagesClients.php?uid=<?= (int) $selectedUserId ?>', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function () {
                inputEl.value = '';
                load();
            });
    });
    if (quickWrapEl) {
        quickWrapEl.addEventListener('click', function (e) {
            var t = e.target;
            if (!t || t.tagName !== 'BUTTON') return;
            var msg = String(t.getAttribute('data-msg') || '');
            if (!msg) return;
            inputEl.value = msg;
            inputEl.focus();
        });
    }
    var callAudioBtn = document.getElementById('bo-call-audio');
    function wireCall(btn) {
        if (!btn) return;
        btn.addEventListener('click', function (e) {
            if (btn.getAttribute('aria-disabled') === 'true') {
                e.preventDefault();
                return;
            }
            var label = 'Appel audio';
            var old = btn.textContent;
            btn.textContent = label + '...';
            setTimeout(function () { btn.textContent = old; }, 1800);
        });
    }
    wireCall(callAudioBtn);
    var searchInput = document.getElementById('bo-client-search');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var q = String(searchInput.value || '').toLowerCase().trim();
            var rows = document.querySelectorAll('#bo-client-list .bo-chat-user');
            for (var i = 0; i < rows.length; i++) {
                var el = rows[i];
                var hay = String(el.getAttribute('data-search') || '').toLowerCase();
                el.style.display = (!q || hay.indexOf(q) >= 0) ? '' : 'none';
            }
        });
    }
    load();
    setInterval(function () {
        load();
        if (liveStateEl && lastLoadAt > 0) {
            var sec = Math.max(0, Math.round((Date.now() - lastLoadAt) / 1000));
            if (sec > 12) liveStateEl.textContent = 'Sync ' + sec + 's';
        }
    }, 3000);
})();
</script>
<?php endif; ?>
</body>
</html>

