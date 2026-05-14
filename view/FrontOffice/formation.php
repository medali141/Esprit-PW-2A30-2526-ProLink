<?php
<<<<<<< HEAD
require_once __DIR__ . '/../../init.php';
requireLogin('Connectez-vous pour accéder aux formations.');
require_once __DIR__ . '/../../controller/FormationP.php';
=======
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once 'C:/xampp/htdocs/prolink/controller/FormationP.php';
>>>>>>> formation
$fp = new FormationP();
$list = $fp->listAll();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Formations — ProLink</title>
<<<<<<< HEAD
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
=======
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .fo-event-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        .fo-event-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s;
            position: relative;
        }
        .fo-event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        .fo-event-card__head {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
        }
        .fo-event-title {
            font-size: 1.3rem;
            margin: 0 0 5px 0;
        }
        .fo-event-type {
            font-size: 0.75rem;
            opacity: 0.9;
        }
        .certif-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #f5af19, #f12711);
            color: white;
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 5px;
            z-index: 10;
        }
        .fo-event-excerpt {
            padding: 20px;
            color: #666;
            line-height: 1.5;
            min-height: 120px;
        }
        .fo-event-date {
            margin-top: 10px;
            font-size: 12px;
            color: #888;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .places {
            margin-top: 8px;
            font-size: 12px;
            color: #28a745;
            font-weight: 500;
        }
        .certif-info {
            margin-top: 10px;
            font-size: 11px;
            color: #f5af19;
            background: #fef9e6;
            padding: 8px;
            border-radius: 8px;
            display: inline-block;
        }
        .fo-event-actions {
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .fo-btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        .fo-btn--primary {
            background: #0073b1;
            color: white;
        }
        .fo-btn--primary:hover {
            background: #005f8d;
            transform: scale(1.02);
        }
        .fo-hero {
            text-align: center;
            margin-bottom: 30px;
        }
        .fo-hero h1 {
            font-size: 2rem;
            color: #1a2a3a;
        }
        .fo-lead {
            color: #666;
        }
        .fo-empty {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 16px;
        }

        .chatbot-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
        .chatbot-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            cursor: pointer;
            font-size: 28px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: transform 0.3s;
        }
        .chatbot-btn:hover {
            transform: scale(1.1);
        }
        .chatbot-window {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 380px;
            height: 550px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: none;
            flex-direction: column;
            overflow: hidden;
            z-index: 1000;
        }
        .chatbot-window.open {
            display: flex;
        }
        .chatbot-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chatbot-header h4 {
            margin: 0;
            font-size: 16px;
        }
        .chatbot-header h4 i {
            margin-right: 8px;
        }
        .chatbot-close {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }
        .chatbot-messages {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            background: #f8f9fa;
            font-size: 14px;
        }
        .chatbot-message {
            margin-bottom: 15px;
            display: flex;
        }
        .chatbot-message.user {
            justify-content: flex-end;
        }
        .chatbot-message.bot {
            justify-content: flex-start;
        }
        .chatbot-message .bubble {
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 18px;
            word-wrap: break-word;
            white-space: pre-line;
        }
        .chatbot-message.user .bubble {
            background: #0073b1;
            color: white;
            border-bottom-right-radius: 4px;
        }
        .chatbot-message.bot .bubble {
            background: white;
            color: #333;
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .chatbot-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #667eea;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
            font-size: 14px;
        }
        .chatbot-input {
            display: flex;
            padding: 12px;
            border-top: 1px solid #eee;
            background: white;
            gap: 10px;
        }
        .chatbot-input input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 25px;
            outline: none;
        }
        .chatbot-input button {
            background: #0073b1;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 8px 18px;
            cursor: pointer;
        }
        .quick-btns {
            display: flex;
            gap: 8px;
            padding: 10px;
            flex-wrap: wrap;
            border-top: 1px solid #eee;
            background: white;
        }
        .quick-btns button {
            background: #f0f2f5;
            border: none;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 11px;
            cursor: pointer;
        }
        .typing {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 8px 15px;
        }
        .typing span {
            width: 8px;
            height: 8px;
            background: #aaa;
            border-radius: 50%;
            animation: typingBounce 1.4s infinite;
        }
        .typing span:nth-child(2) { animation-delay: 0.2s; }
        .typing span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typingBounce {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-8px); }
        }
    </style>
>>>>>>> formation
</head>
<body class="fo-store-page">
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero">
<<<<<<< HEAD
        <h1>Formations</h1>
        <p class="fo-lead">Parcourez et inscrivez-vous aux prochaines formations.</p>
=======
        <h1>📚 Formations certifiantes</h1>
        <p class="fo-lead">Parcourez et inscrivez-vous aux prochaines formations. Obtenez votre certification à la fin !</p>
>>>>>>> formation
    </header>

    <?php if (empty($list)): ?>
        <div class="fo-empty">
            <p class="hint">Aucune formation programmée pour le moment.</p>
<<<<<<< HEAD
            <a href="home.php">Retour à l’accueil</a>
=======
            <a href="home.php">Retour à l'accueil</a>
>>>>>>> formation
        </div>
    <?php else: ?>
        <div class="fo-event-grid">
            <?php foreach ($list as $f): ?>
                <article class="fo-event-card">
<<<<<<< HEAD
                    <div class="fo-event-card__head">
                        <h3 class="fo-event-title"><?= htmlspecialchars($f['titre']) ?></h3>
                    </div>
                    <div class="fo-event-excerpt"><?= nl2br(htmlspecialchars(substr($f['description'] ?? '', 0, 240))) ?></div>
                    <div class="fo-event-actions">
                        <a class="fo-btn fo-btn--primary" href="formation_detail.php?id=<?= (int)$f['id_formation'] ?>">Voir / S'inscrire</a>
=======
                    <?php if(!empty($f['certification_titre'])): ?>
                        <div class="certif-badge">
                            <i class="fas fa-certificate"></i> Certifié
                        </div>
                    <?php endif; ?>
                    <div class="fo-event-card__head">
                        <h3 class="fo-event-title"><?= htmlspecialchars($f['titre']) ?></h3>
                        <div class="fo-event-type">
                            <?= $f['type'] == 'presentiel' ? '🏢 Présentiel' : '💻 En ligne' ?>
                        </div>
                    </div>
                    <div class="fo-event-excerpt">
                        <?= nl2br(htmlspecialchars(substr($f['description'] ?? '', 0, 120))) ?>
                        <?php if(strlen($f['description'] ?? '') > 120) echo '...'; ?>
                        <div class="fo-event-date">
                            <span>📅 Du <?= date('d/m/Y', strtotime($f['date_debut'])) ?></span>
                            <span>au <?= date('d/m/Y', strtotime($f['date_fin'])) ?></span>
                        </div>
                        <div class="places">
                            👥 Places disponibles : <?= $f['places_max'] ?>
                        </div>
                        <?php if(!empty($f['certification_titre'])): ?>
                            <div class="certif-info">
                                <i class="fas fa-certificate"></i> Certification : <?= htmlspecialchars($f['certification_titre']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="fo-event-actions">
                        <a class="fo-btn fo-btn--primary" href="formation_detail.php?id=<?= (int)$f['id_formation'] ?>">
                            📝 S'inscrire / Voir la formation
                        </a>
>>>>>>> formation
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>
<?php include __DIR__ . '/components/footer.php'; ?>
<<<<<<< HEAD
</body>
</html>
=======

<!-- ========== CHATBOT FORMATION ========== -->
<div class="chatbot-float">
    <button class="chatbot-btn" id="chatbotToggle">💬</button>
    <div class="chatbot-window" id="chatbotWindow">
        <div class="chatbot-header">
            <h4><i class="fas fa-robot"></i> Coach Formations</h4>
            <button class="chatbot-close" id="chatbotClose">✕</button>
        </div>
        <div class="chatbot-messages" id="chatbotMessages">
            <div class="chatbot-message bot">
                <div class="chatbot-avatar">🤖</div>
                <div class="bubble">
                    👋 Bonjour ! Je suis votre coach spécialisé dans les formations.<br><br>
                    Je peux vous aider avec :<br>
                    • 📚 Les formations disponibles<br>
                    • 📝 Comment s'inscrire<br>
                    • 💰 Les tarifs<br>
                    • 🎓 Les certifications<br><br>
                    Que souhaitez-vous savoir ?
                </div>
            </div>
        </div>
        <div class="quick-btns">
            <button class="quick-btn" data-msg="Quelles formations sont disponibles ?">📚 Formations</button>
            <button class="quick-btn" data-msg="Comment s'inscrire à une formation ?">📝 Inscription</button>
            <button class="quick-btn" data-msg="Quels sont les prix des formations ?">💰 Prix</button>
            <button class="quick-btn" data-msg="Comment obtenir une certification ?">🎓 Certification</button>
            <button class="quick-btn" data-msg="J'ai besoin de motivation">💪 Motivation</button>
        </div>
        <div class="chatbot-input">
            <input type="text" id="chatbotInput" placeholder="Écrivez votre message...">
            <button id="chatbotSend">Envoyer</button>
        </div>
    </div>
</div>

<script>
    const toggleBtn = document.getElementById('chatbotToggle');
    const closeBtn = document.getElementById('chatbotClose');
    const windowChat = document.getElementById('chatbotWindow');
    const messagesDiv = document.getElementById('chatbotMessages');
    const input = document.getElementById('chatbotInput');
    const sendBtn = document.getElementById('chatbotSend');
    
    toggleBtn.addEventListener('click', () => {
        windowChat.classList.toggle('open');
    });
    closeBtn.addEventListener('click', () => {
        windowChat.classList.remove('open');
    });
    
    function addMessage(text, isUser = true) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `chatbot-message ${isUser ? 'user' : 'bot'}`;
        if(isUser) {
            msgDiv.innerHTML = `<div class="bubble">${escapeHtml(text)}</div>`;
        } else {
            msgDiv.innerHTML = `<div class="chatbot-avatar">🤖</div><div class="bubble">${escapeHtml(text).replace(/\n/g, '<br>')}</div>`;
        }
        messagesDiv.appendChild(msgDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }
    
    function showTyping() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'chatbot-message bot';
        typingDiv.id = 'typingMsg';
        typingDiv.innerHTML = `<div class="chatbot-avatar">🤖</div><div class="typing"><span></span><span></span><span></span> Coach réfléchit...</div>`;
        messagesDiv.appendChild(typingDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }
    
    function hideTyping() {
        const typing = document.getElementById('typingMsg');
        if(typing) typing.remove();
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    async function sendMessage() {
        const message = input.value.trim();
        if(!message) return;
        
        addMessage(message, true);
        input.value = '';
        showTyping();
        
        try {
            const formData = new FormData();
            formData.append('message', message);
            const response = await fetch('/prolink/view/FrontOffice/coach/chatbot_formation.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            hideTyping();
            addMessage(data.reponse, false);
        } catch(error) {
            console.error('Erreur:', error);
            hideTyping();
            addMessage("❌ Désolé, une erreur s'est produite. Veuillez réessayer.", false);
        }
    }
    
    sendBtn.addEventListener('click', sendMessage);
    input.addEventListener('keypress', (e) => {
        if(e.key === 'Enter') sendMessage();
    });
    
    document.querySelectorAll('.quick-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            input.value = btn.dataset.msg;
            sendMessage();
        });
    });
</script>
</body>
</html>
>>>>>>> formation
