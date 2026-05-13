<?php
/**
 * ProLink — chatbot d'aide à la navigation.
 *
 * Widget 100% local (HTML + CSS + JS) inclus sur toutes les pages front-office
 * via le composant footer. Pas d'appel réseau : le bot reconnaît des
 * mots-clés et renvoie une réponse pré-définie pour guider l'utilisateur
 * dans les rubriques du site (boutique, panier, forum, événements, etc.).
 *
 * Dépend de $baseUrl (défini par init.php) pour construire les liens
 * cliquables des réponses.
 */
if (!defined('APP_INIT')) {
    require_once dirname(__DIR__, 3) . '/init.php';
}
$__chatbot_base = (string) ($baseUrl ?? '');
?>
<div id="prolink-chatbot" data-base="<?= htmlspecialchars($__chatbot_base, ENT_QUOTES, 'UTF-8') ?>" aria-live="polite">
    <button type="button" id="pchat-toggle" class="pchat-toggle" aria-label="Ouvrir l'assistant ProLink" aria-expanded="false">
        <span class="pchat-toggle-icon" aria-hidden="true">💬</span>
        <span class="pchat-toggle-label">Aide</span>
    </button>

    <section id="pchat-panel" class="pchat-panel" role="dialog" aria-labelledby="pchat-title" hidden>
        <header class="pchat-header">
            <div>
                <p id="pchat-title" class="pchat-title">Assistant ProLink</p>
                <p class="pchat-sub">Je vous aide à trouver une rubrique</p>
            </div>
            <button type="button" id="pchat-close" class="pchat-close" aria-label="Fermer">✕</button>
        </header>
        <ol id="pchat-log" class="pchat-log" aria-live="polite"></ol>
        <div class="pchat-quick" id="pchat-quick"></div>
        <form id="pchat-form" class="pchat-form" autocomplete="off">
            <input type="text" id="pchat-input" class="pchat-input" placeholder="Posez votre question..." aria-label="Question pour l'assistant">
            <button type="submit" class="pchat-send" aria-label="Envoyer">➤</button>
        </form>
    </section>
</div>

<style>
    .pchat-toggle {
        position: fixed; right: 22px; bottom: 22px; z-index: 2147483600;
        display: inline-flex; align-items: center; gap: 8px;
        padding: 12px 16px; border: none; border-radius: 999px;
        background: linear-gradient(135deg, #0073b1, #00a0dc);
        color: #fff; font-weight: 700; font-family: inherit; font-size: 0.95rem;
        cursor: pointer; box-shadow: 0 12px 30px rgba(0,115,177,0.35);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .pchat-toggle:hover { transform: translateY(-2px); box-shadow: 0 16px 36px rgba(0,115,177,0.45); }
    .pchat-toggle:focus { outline: 3px solid rgba(0,115,177,0.4); outline-offset: 2px; }
    .pchat-toggle-icon { font-size: 1.15rem; line-height: 1; }

    .pchat-panel {
        position: fixed; right: 22px; bottom: 86px; z-index: 2147483601;
        width: 340px; max-width: calc(100vw - 32px);
        height: 460px; max-height: calc(100vh - 120px);
        background: #fff; color: #0f172a;
        border-radius: 16px; box-shadow: 0 24px 48px rgba(15,23,42,0.25);
        display: flex; flex-direction: column; overflow: hidden;
        font-family: inherit;
        animation: pchat-pop 0.18s ease-out;
    }
    @keyframes pchat-pop {
        from { transform: translateY(8px) scale(0.98); opacity: 0; }
        to   { transform: translateY(0) scale(1); opacity: 1; }
    }
    .pchat-panel[hidden] { display: none; }

    .pchat-header {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        padding: 14px 16px;
        background: linear-gradient(135deg, #0073b1, #00a0dc); color: #fff;
    }
    .pchat-title { margin: 0; font-size: 1rem; font-weight: 800; }
    .pchat-sub   { margin: 2px 0 0; font-size: 0.78rem; opacity: 0.85; }
    .pchat-close {
        appearance: none; border: none; background: rgba(255,255,255,0.18);
        color: #fff; font-size: 0.85rem; line-height: 1;
        width: 28px; height: 28px; border-radius: 999px; cursor: pointer;
    }
    .pchat-close:hover { background: rgba(255,255,255,0.3); }

    .pchat-log {
        list-style: none; margin: 0; padding: 14px 14px 4px;
        flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 8px;
        background: #f8fafc;
    }
    .pchat-msg {
        max-width: 85%; padding: 9px 12px; border-radius: 14px;
        font-size: 0.88rem; line-height: 1.4; word-wrap: break-word;
    }
    .pchat-msg a { color: inherit; text-decoration: underline; }
    .pchat-msg--bot {
        align-self: flex-start; background: #fff; border: 1px solid #e2e8f0;
        border-bottom-left-radius: 4px; color: #0f172a;
    }
    .pchat-msg--user {
        align-self: flex-end; background: #0073b1; color: #fff;
        border-bottom-right-radius: 4px;
    }

    .pchat-quick {
        display: flex; flex-wrap: wrap; gap: 6px;
        padding: 8px 12px; background: #f8fafc; border-top: 1px solid #e2e8f0;
    }
    .pchat-quick button {
        appearance: none; border: 1px solid #cbd5e1; background: #fff; color: #0073b1;
        font-size: 0.78rem; padding: 5px 10px; border-radius: 999px; cursor: pointer;
        font-family: inherit;
    }
    .pchat-quick button:hover { background: #e0f2fe; }

    .pchat-form {
        display: flex; gap: 8px; padding: 10px 12px;
        background: #fff; border-top: 1px solid #e2e8f0;
    }
    .pchat-input {
        flex: 1; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 999px;
        font: inherit; font-size: 0.9rem; outline: none;
    }
    .pchat-input:focus { border-color: #0073b1; box-shadow: 0 0 0 3px rgba(0,115,177,0.18); }
    .pchat-send {
        appearance: none; border: none;
        background: #0073b1; color: #fff;
        width: 38px; height: 38px; border-radius: 999px; cursor: pointer;
        font-size: 1rem; line-height: 1;
    }
    .pchat-send:hover { background: #005f8d; }

    html.dark-mode .pchat-panel { background: #0f172a; color: #e2e8f0; box-shadow: 0 24px 48px rgba(0,0,0,0.55); }
    html.dark-mode .pchat-log   { background: #0b1220; }
    html.dark-mode .pchat-msg--bot { background: #1e293b; border-color: rgba(148,163,184,0.18); color: #e2e8f0; }
    html.dark-mode .pchat-msg--user { background: #38bdf8; color: #0f172a; }
    html.dark-mode .pchat-quick { background: #0b1220; border-top-color: rgba(148,163,184,0.18); }
    html.dark-mode .pchat-quick button { background: #1e293b; border-color: rgba(148,163,184,0.3); color: #7dd3fc; }
    html.dark-mode .pchat-form { background: #0f172a; border-top-color: rgba(148,163,184,0.18); }
    html.dark-mode .pchat-input { background: #1e293b; border-color: rgba(148,163,184,0.3); color: #f8fafc; }

    @media (max-width: 480px) {
        .pchat-panel { right: 10px; bottom: 76px; width: calc(100vw - 20px); height: calc(100vh - 110px); }
        .pchat-toggle-label { display: none; }
    }
</style>

<script>
(function () {
    var root = document.getElementById('prolink-chatbot');
    if (!root) return;
    var base = root.getAttribute('data-base') || '';
    var toggle = document.getElementById('pchat-toggle');
    var panel  = document.getElementById('pchat-panel');
    var closeBtn = document.getElementById('pchat-close');
    var form   = document.getElementById('pchat-form');
    var input  = document.getElementById('pchat-input');
    var log    = document.getElementById('pchat-log');
    var quick  = document.getElementById('pchat-quick');

    function link(href, label) {
        return '<a href="' + href + '">' + label + '</a>';
    }

    // Base de connaissances : chaque entrée a des mots-clés et une réponse.
    // L'ordre compte : la première correspondance gagne.
    var KB = [
        {
            keys: ['bonjour', 'salut', 'hello', 'hi', 'hey', 'coucou'],
            answer: 'Bonjour ! Je peux vous aider à trouver une rubrique du site. Essayez : "panier", "boutique", "forum", "événements", "projets", "formations" ou "profil".'
        },
        {
            keys: ['panier', 'cart', 'caddie'],
            answer: 'Votre panier se trouve dans la barre de navigation, rubrique ' + link(base + '/FrontOffice/panier.php', '« Panier »') + '. Vous pouvez modifier les quantités, puis passer au paiement.'
        },
        {
            keys: ['boutique', 'shop', 'produits', 'catalogue', 'acheter', 'achat'],
            answer: 'La boutique est accessible dans le menu, rubrique ' + link(base + '/FrontOffice/catalogue.php', '« Boutique »') + '. Vous y voyez tous les produits actifs des entrepreneurs.'
        },
        {
            keys: ['command', 'mes commande', 'order'],
            answer: 'Retrouvez vos achats dans ' + link(base + '/FrontOffice/mesCommandes.php', '« Mes commandes »') + ' (menu en haut, visible une fois connecté).'
        },
        {
            keys: ['paie', 'payer', 'checkout', 'commande'],
            answer: 'Cliquez sur le panier puis « Passer commande » pour aller au ' + link(base + '/FrontOffice/checkout.php', 'paiement') + '.'
        },
        {
            keys: ['vendre', 'vendeur', 'mes produits'],
            answer: 'Si vous êtes entrepreneur, gérez vos articles depuis ' + link(base + '/FrontOffice/mesProduits.php', '« Mes produits »') + ' et suivez vos ventes dans ' + link(base + '/FrontOffice/mesVentes.php', '« Mes ventes »') + '.'
        },
        {
            keys: ['forum', 'discussion', 'sujet', 'message'],
            answer: 'Le ' + link(base + '/FrontOffice/forum.php', 'Forum') + ' est dans la navbar. Entrez dans une catégorie, puis cliquez « Nouveau sujet » pour publier.'
        },
        {
            keys: ['evenement', 'événement', 'event'],
            answer: 'Les ' + link(base + '/FrontOffice/evenements.php', 'Événements') + ' sont listés dans la rubrique « Événements ». Vous pouvez participer en cliquant sur un événement.'
        },
        {
            keys: ['projet'],
            answer: 'Les ' + link(base + '/FrontOffice/projects.php', 'Projets') + ' sont accessibles depuis la navbar.'
        },
        {
            keys: ['formation', 'cours', 'apprend'],
            answer: 'Consultez les ' + link(base + '/FrontOffice/formation.php', 'Formations') + ' disponibles depuis le menu principal.'
        },
        {
            keys: ['profil', 'compte', 'mon compte', 'account', 'photo'],
            answer: 'Cliquez sur votre nom en haut à droite pour ouvrir votre ' + link(base + '/FrontOffice/profile/profile.php', 'profil') + ' (changer photo, e-mail, etc.).'
        },
        {
            keys: ['mot de passe', 'password', 'mdp', 'oublié'],
            answer: 'Sur la page de connexion, cliquez « Mot de passe oublié ? » : ' + link(base + '/forgotpwd.php', 'forgotpwd.php') + '. Un code vous sera envoyé par e-mail.'
        },
        {
            keys: ['inscri', 'register', 'créer un compte', 'creer un compte', 'sign up', 'signup'],
            answer: 'Créez un compte ici : ' + link(base + '/register.php', 'Inscription') + '.'
        },
        {
            keys: ['connect', 'login', 'se connecter'],
            answer: 'Le bouton « Login » est en haut à droite, ou rendez-vous directement sur ' + link(base + '/login.php', 'la page de connexion') + '.'
        },
        {
            keys: ['deconnect', 'déconnect', 'logout', 'quitter'],
            answer: 'Pour vous déconnecter, cliquez sur « Se déconnecter » en haut à droite de la barre de navigation.'
        },
        {
            keys: ['admin', 'backoffice', 'back office', 'administration'],
            answer: 'Si vous êtes administrateur, le bouton « Administration » dans le menu vous mène au back-office.'
        },
        {
            keys: ['accueil', 'home', 'page principale'],
            answer: 'Retournez à l\'' + link(base + '/FrontOffice/home.php', 'accueil') + ' à tout moment via le logo ProLink ou le lien « Accueil ».'
        },
        {
            keys: ['theme', 'thème', 'dark', 'sombre', 'mode'],
            answer: 'Activez le mode sombre via le bouton 🌙 en haut à droite de la barre de navigation.'
        },
        {
            keys: ['contact', 'aide', 'help', 'support'],
            answer: 'Vous pouvez me poser une question ci-dessous, ou créer un sujet dans le ' + link(base + '/FrontOffice/forum.php', 'forum') + ' pour demander de l\'aide à la communauté.'
        },
        {
            keys: ['merci', 'thanks', 'thx'],
            answer: 'Avec plaisir ! 🙂'
        }
    ];

    var QUICKS = [
        { label: 'Boutique',     text: 'boutique' },
        { label: 'Panier',       text: 'panier' },
        { label: 'Forum',        text: 'forum' },
        { label: 'Événements',   text: 'evenement' },
        { label: 'Mon profil',   text: 'profil' },
        { label: 'Mot de passe', text: 'mot de passe' }
    ];

    function normalize(s) {
        s = (s || '').toString().toLowerCase();
        s = s.normalize ? s.normalize('NFD').replace(/[\u0300-\u036f]/g, '') : s;
        return s;
    }

    function answerFor(text) {
        var norm = normalize(text);
        if (!norm.trim()) {
            return 'Posez-moi une question 🙂 — par exemple : panier, forum, profil.';
        }
        for (var i = 0; i < KB.length; i++) {
            var keys = KB[i].keys;
            for (var k = 0; k < keys.length; k++) {
                if (norm.indexOf(normalize(keys[k])) !== -1) {
                    return KB[i].answer;
                }
            }
        }
        return 'Désolé, je n\'ai pas compris. Essayez un mot-clé : <em>panier, boutique, forum, événements, projets, formations, profil, mot de passe, déconnexion</em>.';
    }

    function addMessage(html, who) {
        var li = document.createElement('li');
        li.className = 'pchat-msg pchat-msg--' + (who === 'user' ? 'user' : 'bot');
        li.innerHTML = html;
        log.appendChild(li);
        log.scrollTop = log.scrollHeight;
    }

    function escapeHtml(s) {
        return (s || '').toString().replace(/[&<>"']/g, function (c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
        });
    }

    function handleUserText(raw) {
        var text = (raw || '').trim();
        if (!text) return;
        addMessage(escapeHtml(text), 'user');
        setTimeout(function () { addMessage(answerFor(text), 'bot'); }, 180);
    }

    function renderQuicks() {
        quick.innerHTML = '';
        QUICKS.forEach(function (q) {
            var b = document.createElement('button');
            b.type = 'button';
            b.textContent = q.label;
            b.addEventListener('click', function () { handleUserText(q.text); });
            quick.appendChild(b);
        });
    }

    function openPanel() {
        panel.hidden = false;
        toggle.setAttribute('aria-expanded', 'true');
        setTimeout(function () { input && input.focus(); }, 50);
    }
    function closePanel() {
        panel.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
    }

    toggle.addEventListener('click', function () {
        if (panel.hidden) openPanel(); else closePanel();
    });
    closeBtn.addEventListener('click', closePanel);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !panel.hidden) closePanel();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var v = input.value;
        input.value = '';
        handleUserText(v);
    });

    renderQuicks();
    addMessage('Bonjour 👋 je suis l\'assistant ProLink. Tapez un mot-clé ou cliquez un raccourci ci-dessous.', 'bot');
})();
</script>
