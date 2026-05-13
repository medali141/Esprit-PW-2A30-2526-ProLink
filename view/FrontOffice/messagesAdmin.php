<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/AdminMessengerController.php';

$auth = new AuthController();
$user = $auth->profile();
if (!$user) {
    header('Location: ../login.php');
    exit;
}

$meId = (int) ($user['iduser'] ?? 0);
$meType = strtolower((string) ($user['type'] ?? ''));
if ($meType === 'admin') {
    header('Location: ../BackOffice/messagesClients.php');
    exit;
}

$chatCtl = new AdminMessengerController();
$adminIds = $chatCtl->getAdminIds();
$hasAdmin = !empty($adminIds);

if (isset($_GET['ajax']) && $_GET['ajax'] === 'thread') {
    header('Content-Type: application/json; charset=utf-8');
    if (!$hasAdmin) {
        echo json_encode(['ok' => false, 'error' => 'Service clients indisponible pour le moment.']);
        exit;
    }
    $chatCtl->markUserThreadAsRead($meId);
    echo json_encode(['ok' => true, 'messages' => $chatCtl->listUserConversationWithAdmins($meId)]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $content = trim((string) ($_POST['message'] ?? ''));
    if ($hasAdmin && $content !== '') {
        $chatCtl->sendMessageToAllAdmins($meId, $content);
    }
    if (isset($_POST['ajax']) && $_POST['ajax'] === '1') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true]);
        exit;
    }
    header('Location: messagesAdmin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Service contact clients — ProLink</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
    <style>
        .fo-msg-wrap {
            margin-top: 14px;
            display: grid;
            grid-template-rows: auto 1fr auto;
            height: min(76vh, 760px);
            background: var(--card);
            border: 1px solid rgba(148,163,184,.24);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 14px 38px rgba(15, 23, 42, 0.08);
        }
        .fo-msg-head {
            padding: 12px 14px;
            border-bottom: 1px solid rgba(148,163,184,.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            background: linear-gradient(120deg, rgba(22,163,74,.12), rgba(14,165,233,.08));
        }
        .fo-msg-head-title {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .fo-msg-avatar {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .95rem;
            font-weight: 900;
            color: #fff;
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            box-shadow: 0 8px 18px rgba(14, 165, 233, .34);
            flex: 0 0 auto;
        }
        .fo-msg-head-name {
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }
        .fo-msg-head-sub {
            font-size: .73rem;
            color: #475569;
            font-weight: 700;
        }
        .fo-msg-status {
            font-size: .76rem;
            color: #166534;
            font-weight: 800;
            background: rgba(34, 197, 94, .15);
            border: 1px solid rgba(74, 222, 128, .35);
            border-radius: 999px;
            padding: 4px 8px;
            white-space: nowrap;
        }
        .fo-msg-list {
            padding: 14px 14px 18px;
            overflow: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
            background:
                radial-gradient(circle at 20% 0, rgba(11,102,195,.08), transparent 35%),
                radial-gradient(circle at 100% 30%, rgba(16,185,129,.06), transparent 42%),
                #f8fafc;
        }
        .fo-msg-wrap-row {
            display: flex;
            align-items: flex-end;
            gap: 7px;
            max-width: 86%;
        }
        .fo-msg-wrap-row--me {
            align-self: flex-end;
            justify-content: flex-end;
        }
        .fo-msg-wrap-row--other {
            align-self: flex-start;
            justify-content: flex-start;
        }
        .fo-msg-mini-avatar {
            width: 24px;
            height: 24px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .66rem;
            font-weight: 900;
            flex: 0 0 auto;
            color: #fff;
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
        }
        .fo-msg-wrap-row--me .fo-msg-mini-avatar {
            background: linear-gradient(135deg, #16a34a, #22c55e);
        }
        .fo-msg-bubble {
            max-width: 100%;
            padding: 9px 11px;
            border-radius: 14px;
            font-size: .9rem;
            line-height: 1.42;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .fo-msg-bubble--me {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: #fff;
            border-bottom-right-radius: 6px;
            box-shadow: 0 8px 20px rgba(34, 197, 94, .28);
        }
        .fo-msg-bubble--other {
            background: #e2e8f0;
            color: #0f172a;
            border-bottom-left-radius: 6px;
        }
        .fo-msg-meta {
            margin-top: 4px;
            font-size: .68rem;
            opacity: .82;
            font-weight: 700;
        }
        .fo-msg-form {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 8px;
            padding: 10px;
            border-top: 1px solid rgba(148,163,184,.2);
            background: var(--card);
        }
        .fo-msg-quick {
            display:flex;
            flex-wrap:wrap;
            gap:6px;
            padding: 8px 10px 10px;
            border-top: 1px dashed rgba(148,163,184,.25);
            background: rgba(11,102,195,.04);
        }
        .fo-msg-quick button {
            border:1px solid rgba(14,165,233,.25);
            background: rgba(14,165,233,.08);
            color:#075985;
            border-radius:999px;
            padding:5px 10px;
            font-size:.75rem;
            font-weight:700;
            cursor:pointer;
        }
        .fo-msg-form input {
            min-width: 0;
            border-radius: 12px;
            border: 1px solid rgba(15, 23, 42, .16);
            padding: 9px 11px;
            font-size: .92rem;
            background: #fff;
        }
        .fo-msg-empty {
            margin: auto;
            color: #64748b;
            font-size: .9rem;
            font-weight: 700;
            background: rgba(148,163,184,.12);
            border: 1px dashed rgba(148,163,184,.45);
            border-radius: 12px;
            padding: 10px 12px;
        }
        html.dark-mode .fo-msg-wrap { border-color: rgba(148,163,184,.22); box-shadow: 0 16px 42px rgba(0,0,0,.35); }
        html.dark-mode .fo-msg-head { border-color: rgba(148,163,184,.2); background: linear-gradient(120deg, rgba(22,163,74,.2), rgba(14,165,233,.18)); }
        html.dark-mode .fo-msg-head-name { color: #f1f5f9; }
        html.dark-mode .fo-msg-head-sub { color: #94a3b8; }
        html.dark-mode .fo-msg-status { color: #bbf7d0; background: rgba(22,163,74,.22); border-color: rgba(74,222,128,.35); }
        html.dark-mode .fo-msg-list {
            background:
                radial-gradient(circle at 20% 0, rgba(14,165,233,.15), transparent 34%),
                radial-gradient(circle at 100% 30%, rgba(34,197,94,.12), transparent 42%),
                #0f172a;
        }
        html.dark-mode .fo-msg-bubble--other { background: #1e293b; color: #e2e8f0; }
        html.dark-mode .fo-msg-form input { background: #0b1220; color: #f1f5f9; border-color: rgba(148,163,184,.3); }
        html.dark-mode .fo-msg-empty { color: #94a3b8; background: rgba(30,41,59,.7); border-color: rgba(148,163,184,.35); }
        html.dark-mode .fo-msg-quick { background: rgba(30,41,59,.65); border-color: rgba(148,163,184,.2); }
        html.dark-mode .fo-msg-quick button { color:#bae6fd; background:rgba(14,116,144,.25); border-color: rgba(56,189,248,.35); }
    </style>
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Service contact clients</h1>
        <p class="fo-lead">Discutez directement avec notre service contact clients en temps réel (rafraîchissement automatique).</p>
    </header>

    <?php if (!$hasAdmin): ?>
        <p class="fo-banner fo-banner--err">Service clients indisponible pour le moment.</p>
    <?php else: ?>
        <section class="fo-msg-wrap">
            <div class="fo-msg-head">
                <div class="fo-msg-head-title">
                    <span class="fo-msg-avatar" aria-hidden="true">AD</span>
                    <div>
                        <div class="fo-msg-head-name">Conversation service clients</div>
                        <div class="fo-msg-head-sub">Support humain ProLink</div>
                    </div>
                </div>
                <span class="fo-msg-status" id="msg-status">Connexion...</span>
            </div>
            <div class="fo-msg-list" id="msg-list">
                <p class="fo-msg-empty">Chargement des messages...</p>
            </div>
            <form class="fo-msg-form" id="msg-form" method="post">
                <input type="hidden" name="send_message" value="1">
                <input type="hidden" name="ajax" value="1">
                <input type="text" name="message" id="msg-input" placeholder="Écrire au service clients..." maxlength="2000" required>
                <button type="submit" class="fo-btn fo-btn--primary">Envoyer</button>
            </form>
            <div class="fo-msg-quick" id="fo-msg-quick">
                <button type="button" data-msg="Bonjour, j ai besoin d aide concernant ma commande.">Aide commande</button>
                <button type="button" data-msg="Je souhaite signaler un retard de livraison.">Retard livraison</button>
                <button type="button" data-msg="Pouvez-vous verifier le statut de ma commande s il vous plait ?">Verifier statut</button>
                <button type="button" data-msg="Merci pour votre retour rapide.">Remerciement</button>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>

<?php if ($hasAdmin): ?>
<script>
(function () {
    var listEl = document.getElementById('msg-list');
    var formEl = document.getElementById('msg-form');
    var inputEl = document.getElementById('msg-input');
    var statusEl = document.getElementById('msg-status');
    var quickEl = document.getElementById('fo-msg-quick');
    if (!listEl || !formEl || !inputEl || !statusEl) return;

    var isLoading = false;
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
            listEl.innerHTML = '<p class="fo-msg-empty">Aucun message pour le moment.</p>';
            return;
        }
        var html = '';
        for (var i = 0; i < messages.length; i++) {
            var m = messages[i] || {};
            var mine = Number(m.id_sender || 0) === <?= (int) $meId ?>;
            html += '<div class="fo-msg-wrap-row ' + (mine ? 'fo-msg-wrap-row--me' : 'fo-msg-wrap-row--other') + '">';
            if (!mine) {
                html += '<span class="fo-msg-mini-avatar" aria-hidden="true">AD</span>';
            }
            html += '<div class="fo-msg-bubble ' + (mine ? 'fo-msg-bubble--me' : 'fo-msg-bubble--other') + '">';
            html += '<div>' + esc(m.message || '') + '</div>';
            html += '<div class="fo-msg-meta">' + (mine ? 'Vous' : 'Service clients') + ' • ' + esc(fmt(m.created_at)) + '</div>';
            html += '</div>';
            if (mine) {
                html += '<span class="fo-msg-mini-avatar" aria-hidden="true">VOUS</span>';
            }
            html += '</div>';
        }
        listEl.innerHTML = html;
        listEl.scrollTop = listEl.scrollHeight;
    }
    function load() {
        if (isLoading) return;
        isLoading = true;
        fetch('messagesAdmin.php?ajax=thread', { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data || !data.ok) throw new Error((data && data.error) || 'Erreur chargement');
                render(data.messages || []);
                statusEl.textContent = 'En ligne';
            })
            .catch(function () {
                statusEl.textContent = 'Hors ligne';
            })
            .finally(function () { isLoading = false; });
    }
    formEl.addEventListener('submit', function (e) {
        e.preventDefault();
        var msg = inputEl.value.trim();
        if (!msg) return;
        var fd = new FormData(formEl);
        fetch('messagesAdmin.php', { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function () {
                inputEl.value = '';
                load();
            })
            .catch(function () {
                statusEl.textContent = 'Erreur envoi';
            });
    });
    if (quickEl) {
        quickEl.addEventListener('click', function (e) {
            var t = e.target;
            if (!t || t.tagName !== 'BUTTON') return;
            var msg = String(t.getAttribute('data-msg') || '');
            if (!msg) return;
            inputEl.value = msg;
            inputEl.focus();
        });
    }
    load();
    setInterval(load, 3000);
})();
</script>
<?php endif; ?>
</body>
</html>

