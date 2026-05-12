<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controller/ForumController.php';

$fc = new ForumController();
$categories = $fc->listCategoriesWithStats();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forum — ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
        <h1>Forum</h1>
        <p class="fo-lead">Échangez avec la communauté ProLink : questions, annonces et discussions par thème.</p>
    </header>

    <div class="fo-product-grid fo-forum-grid">
        <?php foreach ($categories as $c):
            $id = (int) $c['id_categorie'];
            $nb = (int) ($c['nb_sujets'] ?? 0);
        ?>
            <article class="fo-product-card">
                <h2 class="fo-forum-cat__title"><?= htmlspecialchars((string) $c['titre']) ?></h2>
                <span class="fo-ref">Rubrique</span>
                <?php if (!empty($c['description'])): ?>
                    <p class="fo-desc"><?= nl2br(htmlspecialchars((string) $c['description'])) ?></p>
                <?php else: ?>
                    <p class="fo-desc" style="color:var(--sf-muted)">Aucune description.</p>
                <?php endif; ?>
                <div class="fo-price fo-forum-cat__stat"><?= $nb ?> <span class="fo-event-places__label" style="display:inline">sujet<?= $nb > 1 ? 's' : '' ?></span></div>
                <div class="fo-meta">Discussions et réponses de la communauté</div>
                <a class="fo-btn fo-btn--primary" href="forum_categorie.php?id=<?= $id ?>">Entrer</a>
            </article>
        <?php endforeach; ?>
    </div>

    <!-- Chatbot widget (uses storefront theme classes) -->
    <div class="fo-form-card" role="region" aria-label="Assistant forum" style="max-width:900px;margin:30px auto;padding:18px;">
        <h3>Assistant forum</h3>
        <p class="hint">Posez une question rapide au chatbot (réponses courtes).</p>
        <div style="display:flex;gap:12px;align-items:center">
            <input id="chat-prompt" type="text" placeholder="Posez votre question (ex: comment créer un sujet ?)" autocomplete="off">
            <button id="chat-send" class="fo-btn fo-btn--primary">Envoyer</button>
        </div>
        <div id="chat-reply" aria-live="polite" style="display:none;margin-top:12px"></div>
    </div>

    <?php if (empty($categories)): ?>
        <div class="fo-empty">
            <p class="hint" style="margin:0 0 12px">Aucune catégorie pour le moment.</p>
            <a href="catalogue.php">Retour à la boutique</a>
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html>
<script src="../assets/puter.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('chat-send');
    var inp = document.getElementById('chat-prompt');
    var out = document.getElementById('chat-reply');
    if (!btn || !inp || !out) return;

    function updateUI(state) {
        if (state.busy || state.reply || state.error) {
            out.style.display = 'block';
        } else {
            out.style.display = 'none';
        }

        if (state.error) {
            out.className = 'field-error';
            out.textContent = state.error;
        } else if (state.reply) {
            out.className = 'fo-banner fo-banner--ok';
            out.textContent = state.reply;
        } else if (state.busy) {
            out.className = 'fo-banner';
            out.textContent = '… en cours';
        } else {
            out.className = '';
            out.textContent = '';
        }

        btn.disabled = !!state.busy;
        inp.disabled = !!state.busy;
        btn.textContent = state.busy ? 'Envoi…' : 'Envoyer';
    }

    var app;
    if (window.Puter && typeof window.Puter.create === 'function') {
        app = window.Puter.create({ state: { busy: false, reply: '', error: '' }, render: updateUI });
    } else {
        app = { setState: function(partial) { updateUI(Object.assign({ busy: false, reply: '', error: '' }, partial)); } };
    }

    function generateLocalReply(v) {
        var p = (v || '').toLowerCase().trim();
        function hasAny(keys) { for (var i=0;i<keys.length;i++){ if (p.indexOf(keys[i]) !== -1) return true; } return false; }

        if (!p) return 'Veuillez préciser votre question.';

        if (hasAny(['créer un sujet','creer un sujet','nouveau sujet','ouvrir un sujet']) || (p.indexOf('sujet') !== -1 && hasAny(['créer','creer','comment','ouvrir']))) {
            return 'Pour créer un sujet : connectez-vous, allez dans la rubrique souhaitée, cliquez sur « Nouveau sujet », donnez un titre et rédigez votre message puis cliquez sur « Publier ». ';
        }

        if (p.indexOf('recherch') !== -1 || hasAny(['chercher','trouver','recherche'])) {
            return 'Pour rechercher un sujet : utilisez le champ de recherche du forum avec des mots‑clés courts et précis (ex : "installation php", "erreur login").';
        }

        if (hasAny(['profil','photo','avatar'])) {
            return 'Pour modifier votre profil : cliquez sur votre avatar en haut à droite, puis "Modifier le profil". Vous pouvez téléverser une photo depuis l’onglet profil.';
        }

        if (hasAny(['panier','commande','acheter','paiement','achat'])) {
            return 'Les commandes sont accessibles via "Mes commandes". Le panier se remplit depuis les pages produits et le paiement se fait à la validation du panier.';
        }

        if (hasAny(['règles','regles','modération','moderation','charte'])) {
            return 'Le forum applique une charte de bonne conduite : pas d’insultes, pas de spam. Les messages contraires peuvent être modérés. Contactez l’administrateur pour les cas particuliers.';
        }

        // Fallback
        return 'Je suis un assistant local basé sur Puter.js. Je peux aider pour : créer un sujet, rechercher, modifier votre profil ou consulter vos commandes. Précisez votre question pour une réponse plus ciblée.';
    }

    function send() {
        var v = inp.value.trim();
        if (v.length < 2) { app.setState({ error: 'Veuillez saisir une question plus longue.'}); return; }
        app.setState({ busy: true, reply: '', error: '' });

        // Generate reply locally using Puter-driven UI — no external API calls
        setTimeout(function () {
            try {
                var reply = generateLocalReply(v);
                app.setState({ reply: reply });
            } catch (e) {
                app.setState({ error: 'Erreur interne du chatbot local.' });
            } finally {
                app.setState({ busy: false });
            }
        }, 300);
    }

    btn.addEventListener('click', send);
    inp.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); send(); } });
});
</script>
