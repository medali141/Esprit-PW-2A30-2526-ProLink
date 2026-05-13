(function () {
    function onReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn, { once: true });
        } else {
            fn();
        }
    }

    function normalizeText(value) {
        return (value || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/\s+/g, ' ')
            .trim();
    }

    onReady(function () {
        var baseUrl = (window.PROLINK_BASE_URL || '').replace(/\/+$/, '');
        if (!baseUrl) {
            return;
        }

        var routes = {
            accueil: baseUrl + '/FrontOffice/home.php',
            boutique: baseUrl + '/FrontOffice/catalogue.php',
            panier: baseUrl + '/FrontOffice/panier.php',
            checkout: baseUrl + '/FrontOffice/checkout.php',
            commandes: baseUrl + '/FrontOffice/mesCommandes.php',
            reclamations: baseUrl + '/FrontOffice/reclamationsCommandes.php',
            messageAdmin: baseUrl + '/FrontOffice/messagesAdmin.php',
            profil: baseUrl + '/FrontOffice/profile.php'
        };

        var wrap = document.createElement('section');
        wrap.className = 'fo-assistant';
        wrap.innerHTML =
            '<button type="button" class="fo-assistant__voice-fab" data-label="Voice" aria-label="Lancer la commande vocale" title="Commande vocale">' +
                '<span class="fo-assistant__voice-fab-icon" aria-hidden="true">🎙️</span>' +
            '</button>' +
            '<button type="button" class="fo-assistant__fab" data-label="Chat" aria-expanded="false" aria-controls="fo-assistant-panel" aria-label="Ouvrir l assistant">' +
                '<span class="fo-assistant__fab-icon" aria-hidden="true">💬</span>' +
            '</button>' +
            '<div id="fo-assistant-panel" class="fo-assistant__panel" hidden>' +
                '<div class="fo-assistant__head">' +
                    '<div class="fo-assistant__head-title"><strong>Assistant ProLink</strong><span class="fo-assistant__status">En ligne</span></div>' +
                    '<div class="fo-assistant__head-actions">' +
                        '<button type="button" class="fo-assistant__vol" title="Niveau voix">VOL</button>' +
                        '<button type="button" class="fo-assistant__lang" title="Basculer langue vocale">FR</button>' +
                        '<button type="button" class="fo-assistant__close" aria-label="Fermer">X</button>' +
                    '</div>' +
                '</div>' +
                '<div class="fo-assistant__log" aria-live="polite"></div>' +
                '<div class="fo-assistant__quick">' +
                    '<button type="button" data-cmd="ouvre boutique">Boutique</button>' +
                    '<button type="button" data-cmd="aller panier">Panier</button>' +
                    '<button type="button" data-cmd="go to checkout">Checkout</button>' +
                    '<button type="button" data-cmd="contact service clients">Service clients</button>' +
                    '<button type="button" data-cmd="quels moyens de paiement">Paiement</button>' +
                '</div>' +
                '<form class="fo-assistant__form">' +
                    '<input type="text" class="fo-assistant__input" placeholder="Ex: ouvre boutique / search laptop / reclamation commande #12">' +
                    '<button type="submit">Envoyer</button>' +
                '</form>' +
            '</div>';
        document.body.appendChild(wrap);
        var voiceOverlay = document.createElement('div');
        voiceOverlay.className = 'fo-voice-overlay';
        voiceOverlay.hidden = true;
        voiceOverlay.innerHTML =
            '<div class="fo-voice-overlay__inner">' +
                '<div class="fo-voice-overlay__title">Assistant vocal actif...</div>' +
                '<div class="fo-voice-overlay__state" aria-live="polite">Micro en attente...</div>' +
                '<div class="fo-voice-overlay__band" aria-hidden="true">' +
                    '<div class="fo-voice-overlay__band-level"></div>' +
                '</div>' +
                '<div class="fo-voice-overlay__hint">Parlez maintenant (FR/EN)</div>' +
                '<div class="fo-voice-overlay__transcript" aria-live="polite">En attente de votre voix...</div>' +
                '<div class="fo-voice-overlay__actions">' +
                    '<button type="button" class="fo-voice-overlay__dictation">Dictee navigateur</button>' +
                    '<button type="button" class="fo-voice-overlay__stop">Stop</button>' +
                '</div>' +
            '</div>';
        document.body.appendChild(voiceOverlay);

        var fab = wrap.querySelector('.fo-assistant__fab');
        var panel = wrap.querySelector('.fo-assistant__panel');
        var closeBtn = wrap.querySelector('.fo-assistant__close');
        var log = wrap.querySelector('.fo-assistant__log');
        var form = wrap.querySelector('.fo-assistant__form');
        var input = wrap.querySelector('.fo-assistant__input');
        var voiceFabBtn = wrap.querySelector('.fo-assistant__voice-fab');
        var volBtn = wrap.querySelector('.fo-assistant__vol');
        var langBtn = wrap.querySelector('.fo-assistant__lang');
        var statusEl = wrap.querySelector('.fo-assistant__status');
        var quickWrap = wrap.querySelector('.fo-assistant__quick');
        var voiceOverlayStopBtn = voiceOverlay.querySelector('.fo-voice-overlay__stop');
        var voiceOverlayDictationBtn = voiceOverlay.querySelector('.fo-voice-overlay__dictation');
        var voiceOverlayTranscript = voiceOverlay.querySelector('.fo-voice-overlay__transcript');
        var voiceOverlayState = voiceOverlay.querySelector('.fo-voice-overlay__state');
        var voiceOverlayBandLevel = voiceOverlay.querySelector('.fo-voice-overlay__band-level');
        var typingTimer = null;
        var typingEl = null;
        var audioCtx = null;
        var speechEnabled = true;
        var speechVolume = 1.0;
        var preferredVoice = null;
        var volumeModes = [
            { key: 'N', label: 'VOL N', value: 0.75 },
            { key: 'F', label: 'VOL F', value: 1.0 },
            { key: 'T', label: 'VOL T', value: 1.0 }
        ];
        var volumeModeIndex = 1;

        var voiceLang = 'fr-FR';
        var pendingAction = null;
        var languageLabels = {
            'fr-FR': 'FR',
            'en-US': 'EN'
        };
        var lastSuggestions = [
            'cherche clavier gamer',
            'ajouter [nom produit] au panier',
            'reclamation commande #12',
            'contact service clients',
            'quels moyens de paiement ?',
            'je veux un pc 32 go ram',
            'quelles caracteristiques pour une souris gaming ?',
            'donne max resultats pc 16 ram',
            'go to checkout'
        ];

        function formatClock(date) {
            var h = String(date.getHours()).padStart(2, '0');
            var m = String(date.getMinutes()).padStart(2, '0');
            return h + ':' + m;
        }

        function addLog(from, text) {
            var row = document.createElement('div');
            row.className = 'fo-assistant__msg-wrap fo-assistant__msg-wrap--' + from;
            var bubble = document.createElement('p');
            bubble.className = 'fo-assistant__msg fo-assistant__msg--' + from;
            bubble.textContent = text;
            row.appendChild(bubble);

            var meta = document.createElement('span');
            meta.className = 'fo-assistant__meta';
            meta.textContent = (from === 'bot' ? 'ProLink Bot' : 'Vous') + ' • ' + formatClock(new Date());
            row.appendChild(meta);
            log.appendChild(row);
            log.scrollTop = log.scrollHeight;
        }

        function addBotLinks(text, links) {
            var row = document.createElement('div');
            row.className = 'fo-assistant__msg-wrap fo-assistant__msg-wrap--bot';

            var bubble = document.createElement('p');
            bubble.className = 'fo-assistant__msg fo-assistant__msg--bot';
            bubble.textContent = text;
            row.appendChild(bubble);

            var linksWrap = document.createElement('div');
            linksWrap.style.display = 'flex';
            linksWrap.style.flexWrap = 'wrap';
            linksWrap.style.gap = '6px';
            linksWrap.style.marginTop = '6px';
            for (var i = 0; i < links.length; i++) {
                var link = links[i];
                if (!link || !link.url) continue;
                var a = document.createElement('a');
                a.href = String(link.url);
                a.textContent = String(link.label || 'Ouvrir');
                a.className = 'fo-btn fo-btn--secondary';
                a.style.textDecoration = 'none';
                a.style.padding = '6px 10px';
                a.style.fontSize = '0.78rem';
                linksWrap.appendChild(a);
            }
            row.appendChild(linksWrap);

            var meta = document.createElement('span');
            meta.className = 'fo-assistant__meta';
            meta.textContent = 'ProLink Bot' + ' • ' + formatClock(new Date());
            row.appendChild(meta);

            log.appendChild(row);
            log.scrollTop = log.scrollHeight;
        }

        function showTyping() {
            hideTyping();
            var row = document.createElement('div');
            row.className = 'fo-assistant__msg-wrap fo-assistant__msg-wrap--bot';
            row.setAttribute('data-typing', '1');
            row.innerHTML =
                '<p class="fo-assistant__msg fo-assistant__msg--bot fo-assistant__msg--typing">' +
                    '<span></span><span></span><span></span>' +
                '</p>' +
                '<span class="fo-assistant__meta">ProLink Bot • typing...</span>';
            typingEl = row;
            log.appendChild(row);
            log.scrollTop = log.scrollHeight;
        }

        function hideTyping() {
            if (typingTimer) {
                clearTimeout(typingTimer);
                typingTimer = null;
            }
            if (typingEl && typingEl.parentNode) {
                typingEl.parentNode.removeChild(typingEl);
            }
            typingEl = null;
        }

        function playBotNotify() {
            try {
                var Ctx = window.AudioContext || window.webkitAudioContext;
                if (!Ctx) return;
                if (!audioCtx) audioCtx = new Ctx();
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
                var now = audioCtx.currentTime;
                var osc = audioCtx.createOscillator();
                var gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, now);
                osc.frequency.exponentialRampToValueAtTime(660, now + 0.12);
                gain.gain.setValueAtTime(0.0001, now);
                gain.gain.exponentialRampToValueAtTime(0.05, now + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.14);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(now);
                osc.stop(now + 0.15);
            } catch (e) {
                // ignore audio errors silently
            }
        }

        function speakBot(text) {
            if (!speechEnabled || !('speechSynthesis' in window)) return;
            var raw = compactSpaces(String(text || ''));
            if (!raw) return;
            try {
                window.speechSynthesis.cancel();
                window.speechSynthesis.resume();
                var utter = new SpeechSynthesisUtterance(raw);
                utter.lang = voiceLang === 'en-US' ? 'en-US' : 'fr-FR';
                utter.rate = volumeModeIndex === 2 ? 0.96 : 1.02;
                utter.pitch = volumeModeIndex === 2 ? 1.0 : 1.08;
                utter.volume = speechVolume;

                var voices = window.speechSynthesis.getVoices ? window.speechSynthesis.getVoices() : [];
                if (!preferredVoice && voices && voices.length) {
                    var targetLang = utter.lang.toLowerCase();
                    for (var i = 0; i < voices.length; i++) {
                        var v = voices[i];
                        var vLang = String(v.lang || '').toLowerCase();
                        var vName = String(v.name || '').toLowerCase();
                        if (vLang.indexOf(targetLang) === 0 && (vName.indexOf('google') >= 0 || vName.indexOf('microsoft') >= 0)) {
                            preferredVoice = v;
                            break;
                        }
                    }
                    if (!preferredVoice) {
                        for (var j = 0; j < voices.length; j++) {
                            var v2 = voices[j];
                            if (String(v2.lang || '').toLowerCase().indexOf(targetLang) === 0) {
                                preferredVoice = v2;
                                break;
                            }
                        }
                    }
                }
                if (preferredVoice) {
                    utter.voice = preferredVoice;
                }
                window.speechSynthesis.speak(utter);
            } catch (e) {
                // ignore TTS errors silently
            }
        }

        function botReply(text, delayMs, options) {
            var delay = typeof delayMs === 'number' ? delayMs : 280;
            var shouldSpeak = !!(options && options.speak);
            showTyping();
            typingTimer = setTimeout(function () {
                hideTyping();
                addLog('bot', text);
                playBotNotify();
                if (shouldSpeak) {
                    speakBot(text);
                }
            }, Math.max(120, delay));
        }

        function setVoiceLang(lang) {
            voiceLang = lang === 'en-US' ? 'en-US' : 'fr-FR';
            langBtn.textContent = languageLabels[voiceLang];
            langBtn.setAttribute('aria-label', 'Langue vocale ' + (voiceLang === 'fr-FR' ? 'francaise' : 'anglaise'));
            if (statusEl) {
                statusEl.textContent = 'En ligne • ' + languageLabels[voiceLang];
            }
        }

        function applyVolumeMode(index) {
            var idx = Math.max(0, Math.min(volumeModes.length - 1, index));
            volumeModeIndex = idx;
            speechVolume = volumeModes[idx].value;
            if (volBtn) {
                volBtn.textContent = volumeModes[idx].label;
                volBtn.setAttribute('aria-label', 'Niveau voix ' + volumeModes[idx].label);
                volBtn.title = 'Niveau voix: ' + volumeModes[idx].label;
            }
            try {
                localStorage.setItem('prolink-assistant-volume-mode', String(volumeModeIndex));
            } catch (e) {}
        }

        function openPanel() {
            panel.hidden = false;
            fab.setAttribute('aria-expanded', 'true');
            fab.classList.add('is-open');
            setTimeout(function () { input.focus(); }, 30);
        }

        function closePanel() {
            panel.hidden = true;
            fab.setAttribute('aria-expanded', 'false');
            fab.classList.remove('is-open');
        }

        function navigateTo(url, message, speak) {
            botReply(message, 220, { speak: !!speak });
            window.setTimeout(function () {
                window.location.href = url;
            }, 350);
        }

        function currentPath() {
            return String(window.location.pathname || '').toLowerCase();
        }

        function isOnCatalogue() {
            return currentPath().indexOf('/frontoffice/catalogue.php') >= 0;
        }

        function hasAny(text, words) {
            for (var i = 0; i < words.length; i++) {
                if (text.indexOf(words[i]) >= 0) return true;
            }
            return false;
        }

        function compactSpaces(v) {
            return v.replace(/\s+/g, ' ').trim();
        }

        function extractOrderId(text) {
            var m = text.match(/(?:commande|order)\s*#?\s*(\d{1,10})/);
            return m ? m[1] : '';
        }

        function extractQuantity(text) {
            var m = text.match(/(?:\bqty\b|\bqte\b|quantite|quantity|x)\s*[:=]?\s*(\d{1,3})/);
            if (m) return Math.max(1, Math.min(99, parseInt(m[1], 10) || 1));
            m = text.match(/(?:^|\s)(\d{1,3})\s*(?:piece|pieces|pcs|x)(?:\s|$)/);
            if (m) return Math.max(1, Math.min(99, parseInt(m[1], 10) || 1));
            return 1;
        }

        function extractSearchQuery(text) {
            var patterns = [
                /(?:cherche|recherche|trouve|find|search|look for)\s+(.+)/,
                /(?:produit|product)\s+(.+)/,
                /(?:ajoute|ajouter|add|buy|acheter)\s+(.+?)\s+(?:au panier|dans le panier|to cart|panier)$/
            ];
            for (var i = 0; i < patterns.length; i++) {
                var m = text.match(patterns[i]);
                if (m && m[1]) {
                    var query = compactSpaces(
                        m[1]
                            .replace(/^(de|du|des|the)\s+/, '')
                            .replace(/\b(please|pls|svp|stp)\b/g, '')
                    );
                    if (query.length >= 2) return query;
                }
            }
            return '';
        }

        function getAddToCartCandidates() {
            var forms = Array.prototype.slice.call(document.querySelectorAll('form[action="cart_add.php"], form[action$="/cart_add.php"]'));
            return forms.map(function (form) {
                var idInput = form.querySelector('input[name="id"]');
                var qteInput = form.querySelector('input[name="qte"]');
                var card = form.closest('.fo-product-card');
                var titleEl = card ? card.querySelector('h2') : null;
                var refEl = card ? card.querySelector('.fo-ref') : null;
                return {
                    form: form,
                    id: idInput ? String(idInput.value || '') : '',
                    qteInput: qteInput || null,
                    name: normalizeText(titleEl ? titleEl.textContent : ''),
                    ref: normalizeText(refEl ? refEl.textContent : '')
                };
            }).filter(function (item) {
                return item.id && item.form;
            });
        }

        function bestProductMatch(queryNorm, candidates) {
            if (!queryNorm || !candidates.length) return null;
            var terms = queryNorm.split(' ').filter(Boolean);
            var best = null;
            var bestScore = 0;
            for (var i = 0; i < candidates.length; i++) {
                var c = candidates[i];
                var hay = c.name + ' ' + c.ref;
                var score = 0;
                if (hay.indexOf(queryNorm) >= 0) score += 8;
                for (var t = 0; t < terms.length; t++) {
                    if (terms[t].length >= 2 && hay.indexOf(terms[t]) >= 0) score += 2;
                }
                if (score > bestScore) {
                    bestScore = score;
                    best = c;
                }
            }
            if (bestScore < 2) return null;
            return best;
        }

        function setPendingAction(label, runFn, speak) {
            pendingAction = { label: label, run: runFn };
            botReply(label + ' Repondez "oui" pour confirmer ou "non" pour annuler.', 220, { speak: !!speak });
        }

        function tryResolvePendingAction(textNorm) {
            if (!pendingAction) return false;
            var isYes = /^(oui|ok|okay|yep|yes|confirme|confirm|vas y|go)$/.test(textNorm);
            var isNo = /^(non|no|annule|cancel|stop)$/.test(textNorm);
            if (isYes) {
                var action = pendingAction;
                pendingAction = null;
                action.run();
                return true;
            }
            if (isNo) {
                pendingAction = null;
                botReply('Action annulee.');
                return true;
            }
            botReply('Je suis en attente de confirmation: repondez "oui" ou "non".');
            return true;
        }

        function detectInfoIntent(text) {
            var infos = [
                {
                    keys: ['livraison', 'delivery', 'delai', 'shipping'],
                    answer: 'Livraison suivie via "Mes commandes" puis "Suivi". Vous voyez etat, numero de suivi et timeline logistique.'
                },
                {
                    keys: ['paiement', 'payment', 'payer', 'card', 'cash'],
                    answer: 'Modes de paiement disponibles: carte bancaire et cash a la livraison (selon le checkout).'
                },
                {
                    keys: ['point', 'fidelite', 'loyalty'],
                    answer: 'Les points fidelite sont visibles dans la barre du haut. Ils evoluent selon vos commandes et compensations SAV.'
                },
                {
                    keys: ['reclamation', 'complaint', 'support', 'sav'],
                    answer: 'Pour une reclamation: ouvrez "Reclamations" et selectionnez la commande concernee. Vous pouvez aussi dire "reclamation commande #123".'
                },
                {
                    keys: ['contact service clients', 'message service clients', 'contacter admin', 'parler admin', 'human support', 'agent humain', 'chat admin', 'message admin'],
                    answer: 'Vous pouvez ecrire directement au service clients via la page "Contact service clients". Je peux aussi l ouvrir maintenant.'
                },
                {
                    keys: ['commande', 'order status', 'suivi', 'track'],
                    answer: 'Etat de commande: allez dans "Mes commandes", puis ouvrez "Suivi" sur la commande souhaitee.'
                },
                {
                    keys: ['profil', 'profile', 'compte', 'account'],
                    answer: 'Votre profil contient vos infos personnelles et vos acces compte. Je peux aussi vous y rediriger.'
                },
                {
                    keys: ['inscription', 'register', 'creer compte', 'nouveau compte'],
                    answer: 'Pour creer un compte: utilisez la page Inscription, puis connectez-vous pour acceder au panier, commandes et support.'
                },
                {
                    keys: ['connexion', 'login', 'se connecter', 'mot de passe', 'forgot password'],
                    answer: 'Connexion via email + mot de passe. En cas d oubli, utilisez "Mot de passe oublie" depuis la page login.'
                },
                {
                    keys: ['vendeur', 'mes ventes', 'mes produits', 'ajouter produit'],
                    answer: 'Mode vendeur: gerez vos produits dans "Mes produits", suivez les ventes dans "Mes ventes" et ajustez le stock.'
                },
                {
                    keys: ['facture', 'invoice', 'pdf', 'export'],
                    answer: 'Vous pouvez consulter la facture d une commande et l exporter en PDF depuis les pages de commande/facture.'
                },
                {
                    keys: ['securite', '2fa', 'authenticator', 'face id', 'biometrie'],
                    answer: 'Paiement carte securise: verification par code email + Authenticator ou Face ID selon votre configuration profil.'
                },
                {
                    keys: ['retour', 'refund', 'remboursement', 'annulation'],
                    answer: 'Pour retour/remboursement, ouvrez une reclamation commande. Le support analyse le cas et propose la resolution.'
                },
                {
                    keys: ['temps reponse', 'support delay', 'disponible', 'horaire'],
                    answer: 'Le support client est accessible via chat. Les messages sont traites en priorite selon urgence et volume.'
                },
                {
                    keys: ['points fidelite', 'convertir points', 'valeur points'],
                    answer: 'Les points fidelite reduisent le total en checkout. Leur valeur et solde sont visibles sur votre profil.'
                },
                {
                    keys: ['offre', 'appel offre', 'sourcing', 'achat'],
                    answer: 'La plateforme couvre aussi la gestion achats (sourcing, fournisseurs, appels d offres) dans le BackOffice.'
                }
            ];
            for (var i = 0; i < infos.length; i++) {
                if (hasAny(text, infos[i].keys)) {
                    return infos[i].answer;
                }
            }
            return '';
        }

        function detectIntent(textRaw) {
            var text = normalizeText(textRaw);
            var goWords = [
                'go to', 'open', 'ouvre', 'ouvrir', 'aller', 'va vers', 'navigate',
                'amene moi', 'amener moi', 'emmene moi', 'emmener moi', 'emener moi', 'emmen moi'
            ];
            var isNavigation = hasAny(text, goWords) || /^(go|open|ouvre|aller)/.test(text);
            var orderId = extractOrderId(text);
            var qty = extractQuantity(text);
            var asksAdd = hasAny(text, ['ajoute', 'ajouter', 'add', 'buy', 'acheter', 'commander']);
            var asksCart = hasAny(text, ['panier', 'cart']);

            if (orderId && hasAny(text, ['reclamation', 'complaint', 'claim', 'sav', 'probleme'])) {
                return {
                    type: 'navigate',
                    url: routes.reclamations + '?commande=' + encodeURIComponent(orderId),
                    label: 'Ouverture de la reclamation pour la commande #' + orderId + '.'
                };
            }
            if (orderId && hasAny(text, ['suivi', 'track', 'where', 'etat', 'status'])) {
                return {
                    type: 'navigate',
                    url: baseUrl + '/FrontOffice/suiviCommande.php?id=' + encodeURIComponent(orderId),
                    label: 'Ouverture du suivi pour la commande #' + orderId + '.'
                };
            }
            if (orderId && hasAny(text, ['facture', 'invoice'])) {
                return {
                    type: 'navigate',
                    url: baseUrl + '/FrontOffice/factureCommande.php?id=' + encodeURIComponent(orderId),
                    label: 'Ouverture de la facture pour la commande #' + orderId + '.'
                };
            }

            if (asksAdd && asksCart) {
                var queryForAdd = extractSearchQuery(text);
                return {
                    type: 'add_to_cart',
                    query: queryForAdd,
                    quantity: qty
                };
            }
            if (hasAny(text, ['checkout', 'paiement', 'passer commande', 'finaliser'])) {
                return {
                    type: 'navigate',
                    url: routes.checkout,
                    label: 'Ouverture de la page de validation de commande.'
                };
            }

            var searchQuery = extractSearchQuery(text);
            if (searchQuery && !asksAdd) {
                return {
                    type: 'navigate',
                    url: routes.boutique + '?q=' + encodeURIComponent(searchQuery),
                    label: 'Je lance la recherche "' + searchQuery + '" dans la boutique.'
                };
            }

            if (hasAny(text, ['boutique', 'catalogue', 'shop', 'store'])) {
                return { type: 'navigate', url: routes.boutique, label: 'J ouvre la boutique.' };
            }
            if (hasAny(text, ['panier', 'cart', 'basket'])) {
                return { type: 'navigate', url: routes.panier, label: 'Navigation vers le panier.' };
            }
            if (hasAny(text, ['commande', 'orders', 'order', 'mes achats', 'my orders'])) {
                return { type: 'navigate', url: routes.commandes, label: 'Navigation vers vos commandes.' };
            }
            if (hasAny(text, ['reclamation', 'complaint', 'claim', 'support'])) {
                return { type: 'navigate', url: routes.reclamations, label: 'Ouverture de la page reclamations.' };
            }
            if (hasAny(text, ['contact service clients', 'message service clients', 'message admin', 'contacter admin', 'parler admin', 'chat admin', 'human support', 'agent humain'])) {
                return { type: 'navigate', url: routes.messageAdmin, label: 'Ouverture de la page Contact service clients.' };
            }
            if (hasAny(text, ['profil', 'profile', 'compte', 'account'])) {
                return { type: 'navigate', url: routes.profil, label: 'Navigation vers votre profil.' };
            }
            if (hasAny(text, ['accueil', 'home', 'start page'])) {
                return { type: 'navigate', url: routes.accueil, label: 'Retour a l accueil.' };
            }
            if (hasAny(text, ['help', 'aide', 'what can you do', 'que peux tu'])) {
                return { type: 'help' };
            }
            if (hasAny(text, ['hello', 'hi', 'bonjour', 'salut', 'slm', 'hey'])) {
                return { type: 'greeting' };
            }
            if (hasAny(text, ['info', 'informations', 'renseigne', 'explain', 'expliquer', 'comment'])) {
                return { type: 'info' };
            }

            if (isNavigation && hasAny(text, ['produit', 'product'])) {
                return { type: 'navigate', url: routes.boutique, label: 'Je vous emmene vers la boutique pour choisir le produit.' };
            }

            var wantsProduct = hasAny(text, ['je veux', 'i want', 'besoin', 'need', 'cherche', 'search', 'find', 'trouve', 'pc', 'ordinateur', 'laptop']);
            if (wantsProduct) {
                var ramMatch = text.match(/(?:ram|memoire)\s*(\d{1,3})|(\d{1,3})\s*(?:go|gb)\s*(?:ram|memoire)?/);
                var ram = 0;
                if (ramMatch) {
                    ram = parseInt(ramMatch[1] || ramMatch[2] || '0', 10) || 0;
                }
                var qNeed = extractSearchQuery(text) || text;
                return { type: 'product_need', query: qNeed, ram: ram };
            }

            var asksProductQuestion =
                hasAny(text, ['pc', 'ordinateur', 'laptop', 'souris', 'mouse', 'clavier', 'keyboard', 'micro', 'webcam', 'telephone', 'smartphone', 'tablette', 'chaise', 'siege', 'cable', 'accessoire', 'produit']) &&
                (/caracter|spec|detail|config|ram|prix|price|stock|dispo|compare|best|meilleur|conseille|recommande|question/.test(text) ||
                hasAny(text, ['quel', 'quelle', 'quels', 'which', 'what']));
            if (asksProductQuestion) {
                var wantsMore = hasAny(text, ['max', 'maximum', 'more', 'plus de reponses', 'plus de resultats', 'beaucoup']);
                return { type: 'product_qa', query: text, maxResults: wantsMore ? 5 : 3 };
            }

            return { type: 'unknown' };
        }

        function extractRam(textNorm) {
            var m = textNorm.match(/(?:ram|memoire)\s*(\d{1,3})|(\d{1,3})\s*(?:go|gb)\s*(?:ram|memoire)?/);
            return m ? (parseInt(m[1] || m[2] || '0', 10) || 0) : 0;
        }

        function parsePriceValue(text) {
            var raw = String(text || '').replace(/[^\d,.\s]/g, '').replace(/\s+/g, '');
            if (!raw) return 0;
            raw = raw.replace(',', '.');
            var n = parseFloat(raw);
            return isFinite(n) ? n : 0;
        }

        function classifyProductNeed(textNorm) {
            if (/pc|ordinateur|laptop/.test(textNorm)) return 'pc';
            if (/souris|mouse/.test(textNorm)) return 'souris';
            if (/clavier|keyboard/.test(textNorm)) return 'clavier';
            if (/micro|microphone|webcam/.test(textNorm)) return 'audio';
            if (/telephone|smartphone|mobile/.test(textNorm)) return 'phone';
            if (/tablette|tablet/.test(textNorm)) return 'tablette';
            if (/chaise|siege|seat|chair/.test(textNorm)) return 'siege';
            if (/cable|connectique|accessoire|hub/.test(textNorm)) return 'accessoire';
            return '';
        }

        function getCatalogKnowledge() {
            var cards = Array.prototype.slice.call(document.querySelectorAll('.fo-product-card'));
            var items = [];
            for (var i = 0; i < cards.length; i++) {
                var card = cards[i];
                var titleEl = card.querySelector('h2');
                var refEl = card.querySelector('.fo-ref');
                var descEl = card.querySelector('.fo-desc');
                var catEl = card.querySelector('.fo-cat-pill');
                var priceEl = card.querySelector('.fo-price');
                var stockEl = card.querySelector('.fo-stock-pill');
                var detailEl = card.querySelector('a[href*="produitDetails.php"]');
                if (!titleEl || !detailEl) continue;

                var title = compactSpaces(String(titleEl.textContent || ''));
                var ref = compactSpaces(String(refEl ? refEl.textContent : ''));
                var desc = compactSpaces(String(descEl ? descEl.textContent : ''));
                var category = compactSpaces(String(catEl ? catEl.textContent : ''));
                var priceText = compactSpaces(String(priceEl ? priceEl.textContent : ''));
                var stockText = compactSpaces(String(stockEl ? stockEl.textContent : ''));
                var stockN = 0;
                var sm = stockText.match(/(\d{1,5})/);
                if (sm) stockN = parseInt(sm[1], 10) || 0;

                items.push({
                    title: title,
                    ref: ref,
                    desc: desc,
                    category: category,
                    priceText: priceText,
                    priceValue: parsePriceValue(priceText),
                    stockText: stockText,
                    stockValue: stockN,
                    url: new URL(detailEl.getAttribute('href'), window.location.href).toString(),
                    textNorm: normalizeText([title, ref, desc, category, priceText, stockText].join(' '))
                });
            }
            return items;
        }

        function getProductRecommendations(queryNorm, maxItems) {
            var cards = Array.prototype.slice.call(document.querySelectorAll('.fo-product-card'));
            var terms = compactSpaces(queryNorm || '').split(' ').filter(function (t) { return t.length >= 2; });
            var ramNeed = extractRam(queryNorm || '');
            var scored = [];
            for (var i = 0; i < cards.length; i++) {
                var card = cards[i];
                var titleEl = card.querySelector('h2');
                var refEl = card.querySelector('.fo-ref');
                var descEl = card.querySelector('.fo-desc');
                var detailEl = card.querySelector('a[href*="produitDetails.php"]');
                if (!titleEl || !detailEl) continue;
                var title = String(titleEl.textContent || '').trim();
                var text = normalizeText((titleEl.textContent || '') + ' ' + (refEl ? refEl.textContent : '') + ' ' + (descEl ? descEl.textContent : ''));
                var score = 0;
                for (var t = 0; t < terms.length; t++) {
                    if (text.indexOf(terms[t]) >= 0) score += 2;
                }
                if (/pc|ordinateur|laptop/.test(queryNorm) && /pc|ordinateur|laptop/.test(text)) {
                    score += 4;
                }
                if (ramNeed > 0) {
                    var ramRe = new RegExp('\\b' + ramNeed + '\\s*(go|gb)\\b');
                    if (ramRe.test(text)) score += 5;
                }
                if (score <= 0) continue;
                scored.push({
                    score: score,
                    title: title,
                    url: new URL(detailEl.getAttribute('href'), window.location.href).toString()
                });
            }
            scored.sort(function (a, b) { return b.score - a.score; });
            return scored.slice(0, Math.max(1, maxItems || 3));
        }

        function executeProductNeed(intent, speak) {
            var query = compactSpaces(String(intent.query || ''));
            var queryForUrl = query !== '' ? query : ((intent.ram > 0 ? intent.ram + ' go ram ' : '') + 'pc');
            if (!isOnCatalogue()) {
                var shopUrl = routes.boutique + '?q=' + encodeURIComponent(queryForUrl);
                addBotLinks('Je vous propose ce lien direct selon votre besoin.', [
                    { label: 'Voir des produits: ' + queryForUrl, url: shopUrl }
                ]);
                if (speak) speakBot('Je vous ouvre la boutique avec ce besoin: ' + queryForUrl);
                return;
            }

            var reco = getProductRecommendations(normalizeText(queryForUrl), 3);
            if (!reco.length) {
                var fallbackUrl = routes.boutique + '?q=' + encodeURIComponent(queryForUrl);
                addBotLinks('Je n ai pas trouve un match exact sur cette vue. Essayez ce lien filtre.', [
                    { label: 'Rechercher: ' + queryForUrl, url: fallbackUrl }
                ]);
                if (speak) speakBot('Je n ai pas trouve un match exact. Je vous propose un lien filtre.');
                return;
            }
            addBotLinks('Top resultats trouves pour "' + queryForUrl + '".', reco.map(function (r, idx) {
                return { label: (idx + 1) + '. ' + r.title, url: r.url };
            }));
            if (speak) speakBot('J ai trouve les meilleurs resultats pour ' + queryForUrl + '.');
        }

        function executeProductQa(intent, speak) {
            var query = compactSpaces(String(intent.query || ''));
            if (!isOnCatalogue()) {
                addBotLinks('Pour une reponse detaillee (caracteristiques), je vous ouvre la boutique filtree.', [
                    { label: 'Voir: ' + query, url: routes.boutique + '?q=' + encodeURIComponent(query) }
                ]);
                if (speak) speakBot('Je vous ouvre la boutique pour repondre en detail.');
                return;
            }

            var items = getCatalogKnowledge();
            if (!items.length) {
                botReply('Aucun produit visible a analyser sur cette page.');
                return;
            }

            var qNorm = normalizeText(query);
            var type = classifyProductNeed(qNorm);
            var terms = qNorm.split(' ').filter(function (t) { return t.length >= 2; });
            var ramNeed = extractRam(qNorm);
            var askPrice = hasAny(qNorm, ['prix', 'price', 'budget', 'cher']);
            var askStock = hasAny(qNorm, ['stock', 'dispo', 'disponible', 'availability']);
            var askSpecs = /caracter|spec|detail|config|ram|dpi|usb|bluetooth|gaming/.test(qNorm);

            var scored = items.map(function (it) {
                var s = 0;
                for (var i = 0; i < terms.length; i++) {
                    if (it.textNorm.indexOf(terms[i]) >= 0) s += 2;
                }
                if (type) {
                    var typeMap = {
                        pc: /pc|ordinateur|laptop/,
                        souris: /souris|mouse/,
                        clavier: /clavier|keyboard/,
                        audio: /micro|microphone|webcam/,
                        phone: /telephone|smartphone|mobile/,
                        tablette: /tablette|tablet/,
                        siege: /chaise|siege|seat|chair/,
                        accessoire: /cable|connectique|accessoire|hub/
                    };
                    var re = typeMap[type];
                    if (re && re.test(it.textNorm)) s += 4;
                }
                if (ramNeed > 0) {
                    var ramRe = new RegExp('\\b' + ramNeed + '\\s*(go|gb)\\b');
                    if (ramRe.test(it.textNorm)) s += 5;
                }
                if (askPrice && it.priceValue > 0) s += 1;
                if (askStock && it.stockValue > 0) s += 1;
                if (askSpecs && it.desc.length > 8) s += 1;
                return { item: it, score: s };
            }).filter(function (x) { return x.score > 0; });

            if (!scored.length) {
                addBotLinks('Aucun match exact. Essayez ce filtre pour plus de resultats.', [
                    { label: 'Rechercher: ' + query, url: routes.boutique + '?q=' + encodeURIComponent(query) }
                ]);
                if (speak) speakBot('Aucun match exact trouve. Je vous propose un filtre.');
                return;
            }

            scored.sort(function (a, b) { return b.score - a.score; });
            var limit = Math.max(1, Math.min(5, intent.maxResults || 3));
            var top = scored.slice(0, limit);
            var lines = [];
            for (var k = 0; k < top.length; k++) {
                var p = top[k].item;
                var descShort = p.desc.length > 82 ? p.desc.slice(0, 79) + '...' : p.desc;
                lines.push((k + 1) + ') ' + p.title + ' | ' + (p.category || '-') + ' | ' + (p.priceText || '-') + ' | ' + (p.stockText || '-') + ' | ' + (descShort || '-'));
            }
            botReply('Reponses produit pour "' + query + '" : ' + lines.join(' || '), 200);
            addBotLinks('Fiches detaillees:', top.map(function (x, idx) {
                return { label: (idx + 1) + '. ' + x.item.title, url: x.item.url };
            }));
            if (speak) speakBot('Voici les resultats les plus pertinents pour votre demande.');
        }

        function executeAddToCart(intent, speak) {
            if (!isOnCatalogue()) {
                var q = intent.query ? ('?q=' + encodeURIComponent(intent.query)) : '';
                navigateTo(routes.boutique + q, 'Pour ajouter au panier, je vous ouvre d abord la boutique.', speak);
                return;
            }
            var candidates = getAddToCartCandidates();
            if (!candidates.length) {
                botReply('Aucun produit ajoutable trouve sur cette page.', 220, { speak: !!speak });
                return;
            }
            var selected = null;
            if (intent.query) {
                selected = bestProductMatch(normalizeText(intent.query), candidates);
            } else {
                selected = candidates[0];
            }
            if (!selected) {
                botReply('Je n ai pas trouve ce produit dans la liste visible. Essayez une recherche plus precise.', 220, { speak: !!speak });
                return;
            }

            setPendingAction('Ajouter ce produit au panier (qte ' + intent.quantity + ')?', function () {
                if (selected.qteInput) {
                    selected.qteInput.value = String(intent.quantity || 1);
                }
                botReply('Ajout en cours...', 140, { speak: !!speak });
                setTimeout(function () {
                    selected.form.submit();
                }, 250);
            }, speak);
        }

        function respond(textRaw, options) {
            var opts = options || {};
            var wantsVocalReply = !!opts.vocal;
            var normalized = normalizeText(textRaw);
            if (!opts.skipPending && tryResolvePendingAction(normalized)) {
                return;
            }

            var infoAnswer = detectInfoIntent(normalized);
            var intent = detectIntent(textRaw);
            if (intent.type === 'navigate') {
                navigateTo(intent.url, intent.label, wantsVocalReply);
                return;
            }
            if (intent.type === 'add_to_cart') {
                executeAddToCart(intent, wantsVocalReply);
                return;
            }
            if (intent.type === 'product_need') {
                executeProductNeed(intent, wantsVocalReply);
                return;
            }
            if (intent.type === 'product_qa') {
                executeProductQa(intent, wantsVocalReply);
                return;
            }
            if (intent.type === 'greeting') {
                botReply('Bonjour. Je peux repondre aux questions, guider vos pages et executer des actions (recherche, ajout panier, suivi commande).', 220, { speak: wantsVocalReply });
                return;
            }
            if (intent.type === 'help') {
                botReply('Je peux aider sur navigation, commandes, paiements, reclamations, profil, support, vendeur et questions plateforme. Essayez: "comment marche Face ID", "suivi commande #25", "ouvrir service clients".', 220, { speak: wantsVocalReply });
                return;
            }
            if (infoAnswer) {
                botReply(infoAnswer, 220, { speak: wantsVocalReply });
                if (intent.type === 'info') {
                    botReply('Si vous voulez, je peux aussi executer l action correspondante.', 220, { speak: wantsVocalReply });
                }
                return;
            }
            botReply('Je n ai pas compris. Essayez: ' + lastSuggestions.join(' | ') + '.', 220, { speak: wantsVocalReply });
        }

        fab.addEventListener('click', function () {
            if (panel.hidden) {
                openPanel();
            } else {
                closePanel();
            }
        });
        closeBtn.addEventListener('click', closePanel);
        function sendAssistantMessage(rawValue) {
            var value = String(rawValue || '').trim();
            if (!value) return;
            addLog('user', value);
            input.value = '';
            respond(value);
        }
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            sendAssistantMessage(input.value);
        });
        if (quickWrap) {
            quickWrap.addEventListener('click', function (event) {
                var target = event.target;
                if (!target || target.tagName !== 'BUTTON') return;
                var cmd = String(target.getAttribute('data-cmd') || '').trim();
                if (!cmd) return;
                sendAssistantMessage(cmd);
            });
        }

        langBtn.addEventListener('click', function () {
            setVoiceLang(voiceLang === 'fr-FR' ? 'en-US' : 'fr-FR');
            botReply('Langue vocale: ' + (voiceLang === 'fr-FR' ? 'francais' : 'english') + '.', 120);
        });

        if (volBtn) {
            volBtn.addEventListener('click', function () {
                var next = (volumeModeIndex + 1) % volumeModes.length;
                applyVolumeMode(next);
                botReply('Niveau vocal: ' + volumeModes[next].label + '.', 120, { speak: true });
            });
        }

        setVoiceLang('fr-FR');
        try {
            var storedMode = parseInt(localStorage.getItem('prolink-assistant-volume-mode') || '1', 10);
            if (isNaN(storedMode)) storedMode = 1;
            applyVolumeMode(storedMode);
        } catch (e) {
            applyVolumeMode(1);
        }
        botReply('Assistant intelligent pret. Tapez ou dictez une commande FR/EN.', 180);

        var Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!Recognition) {
            voiceFabBtn.disabled = true;
            voiceFabBtn.title = 'Reconnaissance vocale non supportee sur ce navigateur';
            return;
        }

        var listening = false;
        var recognition = null;
        var voiceStopTimer = null;
        var voiceHardStopTimer = null;
        var bestConfidence = 0;
        var liveTranscriptFinal = '';
        var voiceSessionToken = 0;
        var voiceIsStopping = false;
        var voiceShouldRun = false;
        var voiceRunDeadline = 0;
        var voiceRestartCount = 0;
        var VOICE_IDLE_MS = 30000;
        var VOICE_MAX_SESSION_MS = 300000;
        var voiceMeterStream = null;
        var voiceMeterCtx = null;
        var voiceMeterAnalyser = null;
        var voiceMeterData = null;
        var voiceMeterRaf = 0;
        var voiceLastSoundAt = 0;
        var voiceLastResultAt = 0;
        var voiceLastRecoverAt = 0;
        var voiceNoiseFloor = 0.02;
        var voiceDynamicThreshold = 0.09;
        var voiceLastSpeechAt = 0;
        var voiceMeterCompatTimer = null;
        var forceCompatTranscriptMode = /brave|chrome/i.test(navigator.userAgent || '');
        var dictationRecognition = null;
        var dictationActive = false;
        var dictationSessionToken = 0;

        function setListening(active) {
            listening = active;
            voiceFabBtn.classList.toggle('is-listening', active);
            voiceFabBtn.setAttribute('aria-label', active ? 'Arreter la commande vocale' : 'Lancer la commande vocale');
            voiceFabBtn.title = active ? 'Arreter la commande vocale' : 'Commande vocale';
            voiceOverlay.hidden = !active;
            document.body.classList.toggle('is-voice-listening', active);
            var icon = voiceFabBtn.querySelector('.fo-assistant__voice-fab-icon');
            if (icon) {
                icon.textContent = active ? '⏹️' : '🎙️';
            }
        }

        function setVoiceTranscript(text) {
            if (!voiceOverlayTranscript) return;
            var clean = compactSpaces(String(text || ''));
            voiceOverlayTranscript.textContent = clean !== '' ? clean : 'En attente de votre voix...';
        }

        function setVoiceState(text, heard) {
            if (!voiceOverlayState) return;
            voiceOverlayState.textContent = compactSpaces(String(text || 'Micro en attente...'));
            voiceOverlay.classList.toggle('is-heard', !!heard);
            voiceOverlay.classList.toggle('is-silent', !heard);
        }

        function setVoiceBandLevel(level) {
            if (!voiceOverlayBandLevel) return;
            var v = Math.max(0, Math.min(1, Number(level) || 0));
            voiceOverlayBandLevel.style.width = Math.round(8 + (v * 92)) + '%';
            voiceOverlayBandLevel.style.opacity = String(0.35 + (v * 0.65));
        }

        function shouldAcceptTranscript(text, confidence) {
            var clean = compactSpaces(String(text || ''));
            if (clean === '') return false;
            var norm = normalizeText(clean);
            if (norm.length < 2) return false;
            // In compat mode, prioritize user words over strict filtering.
            if (forceCompatTranscriptMode) return true;
            var words = norm.split(' ').filter(Boolean);
            if (words.length < 1 && norm.length < 4) return false;
            if (confidence > 0 && confidence < 0.2) return false;
            return true;
        }

        function stopAudioMeter() {
            if (voiceMeterCompatTimer) {
                clearTimeout(voiceMeterCompatTimer);
                voiceMeterCompatTimer = null;
            }
            if (voiceMeterRaf) {
                cancelAnimationFrame(voiceMeterRaf);
                voiceMeterRaf = 0;
            }
            if (voiceMeterStream) {
                var tracks = voiceMeterStream.getTracks ? voiceMeterStream.getTracks() : [];
                for (var i = 0; i < tracks.length; i++) {
                    try { tracks[i].stop(); } catch (e1) {}
                }
                voiceMeterStream = null;
            }
            if (voiceMeterCtx) {
                try { voiceMeterCtx.close(); } catch (e2) {}
                voiceMeterCtx = null;
            }
            voiceMeterAnalyser = null;
            voiceMeterData = null;
            setVoiceBandLevel(0);
        }

        function tickAudioMeter() {
            if (!voiceMeterAnalyser || !voiceMeterData || !voiceShouldRun) return;
            voiceMeterAnalyser.getByteTimeDomainData(voiceMeterData);
            var sum = 0;
            for (var i = 0; i < voiceMeterData.length; i++) {
                var centered = (voiceMeterData[i] - 128) / 128;
                sum += centered * centered;
            }
            var rms = Math.sqrt(sum / voiceMeterData.length);
            var level = Math.max(0, Math.min(1, rms * 6));
            if (level < 0.18) {
                voiceNoiseFloor = (voiceNoiseFloor * 0.94) + (level * 0.06);
            }
            voiceDynamicThreshold = Math.max(0.09, Math.min(0.28, (voiceNoiseFloor * 2.8) + 0.03));
            setVoiceBandLevel(level);
            if (level > voiceDynamicThreshold) {
                voiceLastSoundAt = Date.now();
                voiceLastSpeechAt = Date.now();
                setVoiceState('Son detecte... continuez a parler', true);
            } else if (Date.now() - voiceLastSoundAt > 1200) {
                setVoiceState('Bruit ambiant ignore - parlez plus clairement', false);
            }
            voiceMeterRaf = requestAnimationFrame(tickAudioMeter);
        }

        function startAudioMeter() {
            stopAudioMeter();
            var media = navigator.mediaDevices;
            if (!media || typeof media.getUserMedia !== 'function') {
                setVoiceState('Analyse audio non supportee par ce navigateur', false);
                return;
            }
            setVoiceState('Initialisation analyse audio...', false);
            media.getUserMedia({
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true
                }
            }).then(function (stream) {
                if (!voiceShouldRun) {
                    var t = stream.getTracks ? stream.getTracks() : [];
                    for (var j = 0; j < t.length; j++) {
                        try { t[j].stop(); } catch (e3) {}
                    }
                    return;
                }
                var Ctx = window.AudioContext || window.webkitAudioContext;
                if (!Ctx) {
                    setVoiceState('Micro actif (analyse visuelle limitee)', false);
                    return;
                }
                voiceMeterStream = stream;
                voiceMeterCtx = new Ctx();
                var src = voiceMeterCtx.createMediaStreamSource(stream);
                voiceMeterAnalyser = voiceMeterCtx.createAnalyser();
                voiceMeterAnalyser.fftSize = 256;
                voiceMeterData = new Uint8Array(voiceMeterAnalyser.fftSize);
                src.connect(voiceMeterAnalyser);
                voiceNoiseFloor = 0.02;
                voiceDynamicThreshold = 0.09;
                voiceLastSpeechAt = 0;
                voiceLastSoundAt = Date.now();
                setVoiceBandLevel(0.02);
                setVoiceState('Micro actif - filtre anti bruit active', false);
                tickAudioMeter();
            }).catch(function () {
                setVoiceState('Micro inaccessible: autorisez le micro puis reessayez', false);
            });
        }

        function stopVoiceNow(reason, silent) {
            voiceIsStopping = true;
            voiceShouldRun = false;
            clearVoiceTimer();
            stopAudioMeter();
            setListening(false);
            setVoiceTranscript('');
            setVoiceState('Assistant vocal arrete', false);
            var rec = recognition;
            recognition = null;
            if (rec) {
                try { rec.abort(); } catch (e1) {}
                try { rec.stop(); } catch (e2) {}
            }
            // Safety UI reset even if browser does not emit onend.
            setTimeout(function () {
                setListening(false);
                setVoiceTranscript('');
                setVoiceState('Assistant vocal arrete', false);
            }, 120);
            if (!silent && reason) {
                botReply(reason, 100);
            }
        }

        function stopDictationFallback(silent) {
            dictationActive = false;
            dictationSessionToken += 1;
            if (dictationRecognition) {
                try { dictationRecognition.abort(); } catch (e1) {}
                try { dictationRecognition.stop(); } catch (e2) {}
            }
            dictationRecognition = null;
            if (!silent) {
                botReply('Dictee navigateur arretee.', 120);
            }
        }

        function launchDictationFallback(token) {
            if (!dictationActive || token !== dictationSessionToken) return;
            var rec = new Recognition();
            dictationRecognition = rec;
            rec.lang = voiceLang;
            rec.interimResults = true;
            rec.continuous = false;
            rec.maxAlternatives = 1;
            rec.onstart = function () {
                if (!dictationActive || token !== dictationSessionToken) return;
                setListening(true);
                setVoiceBandLevel(0.6);
                setVoiceState('Dictee navigateur active - parlez', true);
                setVoiceTranscript('Dictee en cours... votre texte va etre saisi');
                openPanel();
            };
            rec.onresult = function (evt) {
                if (!dictationActive || token !== dictationSessionToken) return;
                var finalText = '';
                var interim = '';
                if (evt && evt.results) {
                    for (var i = 0; i < evt.results.length; i++) {
                        var res = evt.results[i];
                        if (!res) continue;
                        var t = String((res[0] && res[0].transcript) || '').trim();
                        if (!t) continue;
                        if (res.isFinal) finalText += (finalText ? ' ' : '') + t;
                        else interim += (interim ? ' ' : '') + t;
                    }
                }
                var live = compactSpaces((finalText + ' ' + interim).trim());
                if (live) {
                    input.value = live;
                    setVoiceTranscript(live);
                    voiceLastResultAt = Date.now();
                }
                if (finalText) {
                    var message = compactSpaces(finalText);
                    setVoiceTranscript('Vous avez dit: ' + message);
                    setVoiceState('Texte capte et envoye', true);
                    sendAssistantMessage(message);
                }
            };
            rec.onerror = function (evt) {
                if (!dictationActive || token !== dictationSessionToken) return;
                var code = (evt && evt.error) ? evt.error : 'unknown';
                if (code === 'no-speech') {
                    setVoiceState('Aucune parole detectee en mode dictee', false);
                    return;
                }
                if (code === 'not-allowed') {
                    setVoiceState('Permission micro refusee', false);
                    stopDictationFallback(true);
                    setListening(false);
                    return;
                }
                setVoiceState('Dictee erreur: ' + code, false);
            };
            rec.onend = function () {
                if (token !== dictationSessionToken) return;
                if (dictationActive) {
                    setTimeout(function () {
                        launchDictationFallback(token);
                    }, 140);
                    return;
                }
                setListening(false);
                setVoiceBandLevel(0.02);
            };
            try {
                rec.start();
            } catch (e) {
                stopDictationFallback(true);
                setListening(false);
                botReply('Impossible de lancer la dictee navigateur.', 120);
            }
        }

        function toggleDictationFallback() {
            if (dictationActive) {
                stopDictationFallback(false);
                stopVoiceNow('', true);
                return;
            }
            stopVoiceNow('', true);
            dictationActive = true;
            dictationSessionToken += 1;
            setListening(true);
            setVoiceBandLevel(0.6);
            setVoiceState('Activation dictee navigateur...', false);
            setVoiceTranscript('Parlez, je vais ecrire puis envoyer automatiquement.');
            launchDictationFallback(dictationSessionToken);
        }

        function clearVoiceTimer() {
            if (voiceStopTimer) {
                clearTimeout(voiceStopTimer);
                voiceStopTimer = null;
            }
            if (voiceHardStopTimer) {
                clearTimeout(voiceHardStopTimer);
                voiceHardStopTimer = null;
            }
        }

        function startVoiceTimeout() {
            clearVoiceTimer();
            voiceStopTimer = setTimeout(function () {
                if (listening && voiceShouldRun) {
                    stopVoiceNow('Session vocale terminee automatiquement.', true);
                }
            }, VOICE_IDLE_MS);
            // Hard safety stop in case browser keeps interim events alive.
            voiceHardStopTimer = setTimeout(function () {
                if (listening && voiceShouldRun) {
                    stopVoiceNow('Session vocale terminee automatiquement.');
                }
            }, VOICE_MAX_SESSION_MS);
        }

        function launchRecognition(token, announceStart) {
            if (!voiceShouldRun || token !== voiceSessionToken) return;
            recognition = new Recognition();
            recognition.lang = voiceLang;
            recognition.interimResults = true;
            recognition.continuous = true;
            recognition.maxAlternatives = 3;
            bestConfidence = 0;
            recognition.onstart = function () {
                if (token !== voiceSessionToken || voiceIsStopping) return;
                liveTranscriptFinal = '';
                setListening(true);
                setVoiceTranscript('Parlez maintenant... transcription en cours');
                setVoiceState('Micro actif - parlez maintenant', false);
                voiceRestartCount = 0;
                voiceLastResultAt = Date.now();
                if (forceCompatTranscriptMode) {
                    stopAudioMeter();
                    setVoiceBandLevel(0.35);
                    setVoiceState('Mode compatibilite: transcription prioritaire', false);
                } else {
                    startAudioMeter();
                }
                if (announceStart) {
                    botReply('Je vous ecoute... (' + (voiceLang === 'fr-FR' ? 'FR' : 'EN') + ')', 120, { speak: true });
                }
                startVoiceTimeout();
            };
            recognition.onresult = function (evt) {
                if (token !== voiceSessionToken || voiceIsStopping) return;
                startVoiceTimeout();
                var finalChunk = '';
                var interimChunk = '';
                if (!evt || !evt.results) return;
                // Brave/Chrome can provide unstable resultIndex; scan full results for reliability.
                for (var i = 0; i < evt.results.length; i++) {
                    var res = evt.results[i];
                    if (!res) continue;
                    var alt = res[0] || {};
                    var t = String(alt.transcript || '').trim();
                    if (res.isFinal && t) {
                        finalChunk += (finalChunk ? ' ' : '') + t;
                    } else if (t) {
                        interimChunk += (interimChunk ? ' ' : '') + t;
                    }
                    if (typeof alt.confidence === 'number') {
                        bestConfidence = Math.max(bestConfidence, alt.confidence);
                    }
                }
                finalChunk = compactSpaces(finalChunk);
                interimChunk = compactSpaces(interimChunk);
                if (finalChunk !== '') {
                    liveTranscriptFinal = compactSpaces((liveTranscriptFinal + ' ' + finalChunk).trim());
                    setVoiceState('Voix captee avec succes', true);
                    voiceLastResultAt = Date.now();
                }
                var liveText = compactSpaces((liveTranscriptFinal + ' ' + interimChunk).trim());
                if (liveText !== '') {
                    setVoiceTranscript(liveText);
                    voiceLastResultAt = Date.now();
                } else {
                    setVoiceTranscript('Parlez maintenant... transcription en cours');
                }

                if (finalChunk === '') return;
                if (!shouldAcceptTranscript(liveTranscriptFinal, bestConfidence)) {
                    setVoiceTranscript('Bruit detecte puis ignore. Parlez plus pres du micro.');
                    setVoiceState('Filtre anti bruit: parole non validee', false);
                    liveTranscriptFinal = '';
                    return;
                }
                addLog('user', liveTranscriptFinal);
                if (bestConfidence > 0 && bestConfidence < 0.45) {
                    botReply('Je ne suis pas totalement sur d avoir bien entendu. Vous pouvez repeter plus lentement.', 140);
                }
                respond(liveTranscriptFinal, { skipPending: false, vocal: true });
                // Keep a short on-screen trace before next phrase replaces it.
                var shownFinal = liveTranscriptFinal;
                liveTranscriptFinal = '';
                setVoiceTranscript('Vous avez dit: ' + shownFinal);
            };
            recognition.onerror = function (evt) {
                if (token !== voiceSessionToken || voiceIsStopping) return;
                var code = (evt && evt.error) ? evt.error : 'unknown';
                if (code === 'no-speech' || code === 'aborted' || code === 'network') {
                    // silent retry path
                    setVoiceState('Aucun son detecte, nouvelle ecoute...', false);
                    setVoiceTranscript('Aucun texte detecte. Parlez plus clairement...');
                    return;
                } else if (code === 'audio-capture') {
                    botReply('Micro non detecte. Verifiez le micro puis reessayez.', 120);
                    setVoiceState('Erreur micro: audio non capte', false);
                    stopVoiceNow('', true);
                } else if (code === 'not-allowed') {
                    botReply('Permission micro refusee. Autorisez le micro dans le navigateur.', 120);
                    setVoiceState('Permission micro refusee', false);
                    stopVoiceNow('', true);
                } else {
                    botReply('Voice indisponible (' + code + '). Reessayez.', 120);
                    setVoiceState('Erreur vocale: ' + code, false);
                    stopVoiceNow('', true);
                }
            };
            recognition.onend = function () {
                if (token !== voiceSessionToken) return;
                clearVoiceTimer();
                var shouldRetry = voiceShouldRun && !voiceIsStopping && Date.now() < voiceRunDeadline;
                recognition = null;
                if (shouldRetry) {
                    voiceRestartCount += 1;
                    var retryDelay = Math.min(700, 120 + (voiceRestartCount * 50));
                    setVoiceState('Reconnexion vocale...', false);
                    setTimeout(function () {
                        launchRecognition(token, false);
                    }, retryDelay);
                    return;
                }
                stopAudioMeter();
                setListening(false);
                setVoiceTranscript('');
                setVoiceState('Assistant vocal arrete', false);
                voiceIsStopping = false;
            };
            try {
                recognition.start();
            } catch (e) {
                stopVoiceNow('', true);
                botReply('Impossible de lancer la reconnaissance vocale. Reessayez.', 120);
            }
        }

        function startOrStopVoice() {
            if (dictationActive) {
                stopDictationFallback(true);
                stopVoiceNow('Dictee navigateur arretee.');
                return;
            }
            if (listening || voiceShouldRun) {
                stopVoiceNow('Assistant vocal arrete.');
                return;
            }
            voiceSessionToken += 1;
            var token = voiceSessionToken;
            voiceIsStopping = false;
            voiceShouldRun = true;
            voiceRestartCount = 0;
            voiceRunDeadline = Date.now() + VOICE_MAX_SESSION_MS;
            // Keep UI active while engine is initializing/restarting.
            setListening(true);
            setVoiceTranscript('Initialisation du micro...');
            setVoiceState('Initialisation assistant vocal...', false);
            setVoiceBandLevel(0.02);
            setVoiceState('Bienvenue sur ProLink. Je suis pret a vous aider.', true);
            setVoiceTranscript('Bienvenue! Posez votre question a voix haute.');
            speakBot(voiceLang === 'fr-FR'
                ? 'Bienvenue sur ProLink. Je suis votre assistant vocal. Posez votre question.'
                : 'Welcome to ProLink. I am your voice assistant. Ask me your question.');
            launchRecognition(token, true);
        }

        voiceFabBtn.addEventListener('click', startOrStopVoice);
        if (voiceOverlayDictationBtn) {
            voiceOverlayDictationBtn.addEventListener('click', function () {
                toggleDictationFallback();
            });
        }
        if (voiceOverlayStopBtn) {
            voiceOverlayStopBtn.addEventListener('click', function () {
                stopDictationFallback(true);
                stopVoiceNow('Assistant vocal arrete.');
            });
        }
        document.addEventListener('keydown', function (evt) {
            if (!listening) return;
            if (evt.key === 'Escape' || evt.key === 'Esc') {
                stopDictationFallback(true);
                stopVoiceNow('Assistant vocal arrete (Esc).');
            }
        });
    });
})();
