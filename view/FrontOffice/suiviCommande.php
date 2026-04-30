<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/CommandeController.php';

$auth = new AuthController();
$u = $auth->profile();
if (!$u) {
    header('Location: ../login.php');
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: mesCommandes.php');
    exit;
}

$cp = new CommandeController();
$cmd = $cp->getById($id);
if (!$cmd || (int) $cmd['id_acheteur'] !== (int) $u['iduser']) {
    header('Location: mesCommandes.php');
    exit;
}

$lignes = $cp->getLignes($id);
$track = $cp->getTrackingTimeline($cmd);

$labels = [
    'brouillon' => 'Brouillon',
    'en_attente_paiement' => 'En attente de paiement',
    'payee' => 'Payée',
    'en_preparation' => 'En préparation',
    'expediee' => 'En cours de livraison',
    'livree' => 'Livrée',
    'annulee' => 'Annulée',
];
$st = $cmd['statut'] ?? '';
$badgeClass = 'fo-badge fo-badge--' . preg_replace('/[^a-z0-9_]/', '', $st);
$ns = trim((string) ($cmd['numero_suivi'] ?? ''));
$fullAddress = trim(
    (string) ($cmd['adresse_livraison'] ?? '') . ', ' .
    (string) ($cmd['code_postal'] ?? '') . ' ' .
    (string) ($cmd['ville'] ?? '') . ', ' .
    (string) ($cmd['pays'] ?? 'Tunisie')
);
$mapStatuses = ['payee', 'en_preparation', 'expediee', 'livree'];
$showCourierMap = in_array($st, $mapStatuses, true);
$isLiveTracking = in_array($st, ['payee', 'en_preparation', 'expediee'], true);
$stockName = 'Entrepot central Lac 2';
$stockLat = 36.8442;
$stockLng = 10.2722;
$adrLine = trim((string) ($cmd['adresse_livraison'] ?? ''));
$adrCp = trim((string) ($cmd['code_postal'] ?? ''));
$adrCity = trim((string) ($cmd['ville'] ?? ''));
$adrCountry = trim((string) ($cmd['pays'] ?? 'Tunisie'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Suivi commande #<?= $id ?> — ProLink</title>
    <script>try{if(localStorage.getItem('prolink-theme')==='dark')document.documentElement.classList.add('dark-mode');}catch(e){}</script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/storefront.css">
</head>
<body>
<?php include __DIR__ . '/components/navbar.php'; ?>
<main class="container fo-page">
    <header class="fo-hero fo-hero--track">
        <div class="fo-track-head">
            <div>
                <p class="fo-track-kicker">Suivi logistique</p>
                <h1>Commande #<?= $id ?></h1>
                <p class="fo-lead">État en temps quasi réel après validation du paiement : stock, préparation, livreur et livraison.</p>
            </div>
            <div class="fo-track-head-meta">
                <span id="trackStatusBadge" class="<?= htmlspecialchars($badgeClass) ?>"><?= htmlspecialchars($labels[$st] ?? $st) ?></span>
                <p class="fo-track-amount"><?= number_format((float) $cmd['montant_total'], 3, ',', ' ') ?> TND</p>
                <p class="fo-track-date hint">Passée le <?= htmlspecialchars(substr((string) ($cmd['date_commande'] ?? ''), 0, 16)) ?></p>
            </div>
        </div>
    </header>

    <?php if (!empty($track['delivery_hint'])): ?>
        <div id="trackDeliveryHint" class="fo-track-estimate" role="status">
            <strong><?= htmlspecialchars((string) $track['delivery_hint']) ?></strong>
        </div>
    <?php endif; ?>

    <?php if (!empty($track['cancelled'])): ?>
        <div class="fo-track-cancelled">
            <p><?= htmlspecialchars((string) ($track['message'] ?? '')) ?></p>
            <a href="mesCommandes.php" class="fo-btn fo-btn--secondary" style="text-decoration:none">Retour à mes commandes</a>
        </div>
    <?php else: ?>
        <ol class="fo-track-timeline" aria-label="Étapes de livraison">
            <?php foreach ($track['steps'] as $idx => $step):
                $state = $step['state'] ?? 'pending';
                $liClass = 'fo-track-step fo-track-step--' . $state;
            ?>
                <li class="<?= htmlspecialchars($liClass) ?>" data-step-key="<?= htmlspecialchars((string) ($step['key'] ?? '')) ?>">
                    <div class="fo-track-dot" aria-hidden="true"></div>
                    <div class="fo-track-card">
                        <div class="fo-track-step-num">Étape <?= $idx + 1 ?></div>
                        <h2 class="fo-track-title"><?= htmlspecialchars((string) ($step['title'] ?? '')) ?></h2>
                        <p class="fo-track-sub"><?= htmlspecialchars((string) ($step['subtitle'] ?? '')) ?></p>
                        <?php if (!empty($step['meta'])): ?>
                            <p class="fo-track-meta"><?= htmlspecialchars((string) $step['meta']) ?></p>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>

    <?php if ($ns !== ''): ?>
        <div class="fo-track-suivi-box">
            <div>
                <p class="fo-track-suivi-label">Numéro de suivi transporteur</p>
                <p class="fo-track-suivi-val" id="trackNs"><?= htmlspecialchars($ns) ?></p>
            </div>
            <button type="button" class="fo-btn fo-btn--secondary" id="copyNs">Copier</button>
        </div>
        <script>
        (function(){
            var b=document.getElementById('copyNs'),t=document.getElementById('trackNs');
            if(b&&t)b.addEventListener('click',function(){
                var v=t.textContent.trim();
                if(navigator.clipboard)navigator.clipboard.writeText(v).then(function(){b.textContent='Copié';setTimeout(function(){b.textContent='Copier';},1600);});
                else{var a=document.createElement('textarea');a.value=v;document.body.appendChild(a);a.select();try{document.execCommand('copy');b.textContent='Copié';}catch(e){}document.body.removeChild(a);setTimeout(function(){b.textContent='Copier';},1600);}
            });
        })();
        </script>
    <?php endif; ?>

    <section class="fo-track-address">
        <h2 class="fo-track-section-title">Adresse de livraison</h2>
        <p><?= htmlspecialchars((string) ($cmd['adresse_livraison'] ?? '')) ?><br>
            <?= htmlspecialchars((string) ($cmd['code_postal'] ?? '')) ?> <?= htmlspecialchars((string) ($cmd['ville'] ?? '')) ?>,
            <?= htmlspecialchars((string) ($cmd['pays'] ?? 'Tunisie')) ?>
            <?php if (!empty($cmd['telephone_livraison'])): ?>
                <br><strong>Téléphone:</strong> <?= htmlspecialchars((string) $cmd['telephone_livraison']) ?>
            <?php endif; ?>
        </p>
    </section>

    <?php if ($showCourierMap): ?>
        <section class="fo-track-map-wrap">
            <h2 class="fo-track-section-title">Course livreur en temps reel</h2>
            <p class="hint fo-track-map-hint">
                Itineraire reel: prise en charge au <strong><?= htmlspecialchars($stockName) ?></strong> puis livraison a votre adresse.
            </p>
            <div id="foLiveRideMeta" class="fo-track-live-meta">Initialisation du suivi...</div>
            <div
                id="foDeliveryMap"
                class="fo-track-map"
                data-address="<?= htmlspecialchars($fullAddress) ?>"
                data-street="<?= htmlspecialchars($adrLine) ?>"
                data-postcode="<?= htmlspecialchars($adrCp) ?>"
                data-city="<?= htmlspecialchars($adrCity) ?>"
                data-country="<?= htmlspecialchars($adrCountry) ?>"
                data-stock-name="<?= htmlspecialchars($stockName) ?>"
                data-stock-lat="<?= htmlspecialchars((string) $stockLat) ?>"
                data-stock-lng="<?= htmlspecialchars((string) $stockLng) ?>"
                data-status="<?= htmlspecialchars((string) $st) ?>"
                data-live="<?= $isLiveTracking ? '1' : '0' ?>"
                data-order="<?= (int) $id ?>"
                data-csrf="<?= htmlspecialchars((string) $_SESSION['csrf_token']) ?>"
            ></div>
        </section>
    <?php else: ?>
        <section class="fo-track-map-wrap">
            <h2 class="fo-track-section-title">Course livreur</h2>
            <p class="fo-track-live-meta">
                La carte s'affichera des que le paiement est valide et qu'un livreur commence la prise en charge au <?= htmlspecialchars($stockName) ?>.
            </p>
        </section>
    <?php endif; ?>

    <?php if (!empty($lignes)): ?>
    <section class="fo-track-lines">
        <h2 class="fo-track-section-title">Contenu du colis</h2>
        <div class="fo-table-wrap">
            <table class="table-modern">
                <thead><tr><th>Produit</th><th>Vendeur</th><th>Qté</th><th>Prix u.</th></tr></thead>
                <tbody>
                <?php foreach ($lignes as $l): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) ($l['designation'] ?? '')) ?> <span class="hint">(<?= htmlspecialchars((string) ($l['reference'] ?? '')) ?>)</span></td>
                        <td><?= htmlspecialchars(trim(($l['v_prenom'] ?? '') . ' ' . ($l['v_nom'] ?? ''))) ?></td>
                        <td><?= (int) ($l['quantite'] ?? 0) ?></td>
                        <td><?= number_format((float) ($l['prix_unitaire'] ?? 0), 3, ',', ' ') ?> TND</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>

    <div class="fo-track-actions">
        <a href="mesCommandes.php" class="fo-btn fo-btn--secondary" style="text-decoration:none">← Mes commandes</a>
        <a href="catalogue.php" class="fo-btn fo-btn--primary" style="text-decoration:none">Continuer mes achats</a>
    </div>
</main>
<?php include __DIR__ . '/components/footer.php'; ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    var mapEl = document.getElementById('foDeliveryMap');
    if (!mapEl || typeof L === 'undefined') return;

    var address = mapEl.getAttribute('data-address') || '';
    var street = mapEl.getAttribute('data-street') || '';
    var postcode = mapEl.getAttribute('data-postcode') || '';
    var city = mapEl.getAttribute('data-city') || '';
    var country = mapEl.getAttribute('data-country') || 'Tunisie';
    var isLive = mapEl.getAttribute('data-live') === '1';
    var orderStatus = mapEl.getAttribute('data-status') || '';
    var csrfToken = mapEl.getAttribute('data-csrf') || '';
    var stockName = mapEl.getAttribute('data-stock-name') || 'Point stock';
    var stockLatConst = parseFloat(mapEl.getAttribute('data-stock-lat') || '36.8442');
    var stockLngConst = parseFloat(mapEl.getAttribute('data-stock-lng') || '10.2722');
    var orderId = parseInt(mapEl.getAttribute('data-order') || '0', 10) || 1;
    var fallback = [36.8065, 10.1815]; // Tunis center

    function seeded(seed) {
        var x = Math.sin(seed) * 10000;
        return x - Math.floor(x);
    }

    function metersBetween(a, b) {
        var R = 6371000;
        var dLat = (b[0] - a[0]) * Math.PI / 180;
        var dLng = (b[1] - a[1]) * Math.PI / 180;
        var lat1 = a[0] * Math.PI / 180;
        var lat2 = b[0] * Math.PI / 180;
        var x = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.sin(dLng / 2) * Math.sin(dLng / 2) * Math.cos(lat1) * Math.cos(lat2);
        var c = 2 * Math.atan2(Math.sqrt(x), Math.sqrt(1 - x));
        return R * c;
    }

    function formatKm(meters) {
        if (meters < 1000) return Math.round(meters) + ' m';
        return (meters / 1000).toFixed(1).replace('.', ',') + ' km';
    }

    function formatEta(minutes) {
        if (minutes <= 1) return 'moins d\'1 min';
        if (minutes < 60) return Math.round(minutes) + ' min';
        var h = Math.floor(minutes / 60);
        var m = Math.round(minutes % 60);
        return h + ' h ' + (m > 0 ? m + ' min' : '');
    }

    function setStatusCard(html) {
        var box = document.getElementById('foLiveRideMeta');
        if (box) box.innerHTML = html;
    }

    function timelineIndexForStatus(status) {
        var map = {
            brouillon: 0,
            en_attente_paiement: 1,
            payee: 2,
            en_preparation: 3,
            expediee: 4,
            livree: 5
        };
        return Object.prototype.hasOwnProperty.call(map, status) ? map[status] : 0;
    }

    function applyTrackingStatusUI(newStatus) {
        orderStatus = newStatus;
        var statusTexts = {
            brouillon: 'Brouillon',
            en_attente_paiement: 'En attente de paiement',
            payee: 'Payée',
            en_preparation: 'En préparation',
            expediee: 'En cours de livraison',
            livree: 'Livrée',
            annulee: 'Annulée'
        };
        var badge = document.getElementById('trackStatusBadge');
        if (badge) {
            badge.className = 'fo-badge fo-badge--' + String(newStatus).replace(/[^a-z0-9_]/g, '');
            badge.textContent = statusTexts[newStatus] || newStatus;
        }

        var steps = document.querySelectorAll('.fo-track-step[data-step-key]');
        var currentIdx = timelineIndexForStatus(newStatus);
        for (var i = 0; i < steps.length; i++) {
            var state = 'pending';
            if (newStatus === 'livree' || i < currentIdx) state = 'done';
            else if (i === currentIdx) state = 'current';
            steps[i].className = 'fo-track-step fo-track-step--' + state;
        }

        var transitStep = document.querySelector('.fo-track-step[data-step-key="transit"] .fo-track-meta');
        if (!transitStep && newStatus === 'expediee') {
            var transitCard = document.querySelector('.fo-track-step[data-step-key="transit"] .fo-track-card');
            if (transitCard) {
                transitStep = document.createElement('p');
                transitStep.className = 'fo-track-meta';
                transitCard.appendChild(transitStep);
            }
        }
        if (transitStep && newStatus === 'expediee') {
            var trackNumberEl = document.getElementById('trackNs');
            var trackNumber = trackNumberEl ? trackNumberEl.textContent.trim() : '';
            transitStep.textContent = trackNumber !== ''
                ? ('En cours de livraison — suivi transporteur : ' + trackNumber)
                : 'Le colis est pris en charge par le van et en route vers votre adresse.';
        }
    }

    function syncStatusToInDelivery() {
        if (orderStatus !== 'en_preparation') return;
        var body = new URLSearchParams();
        body.set('id', String(orderId));
        body.set('csrf_token', csrfToken);
        fetch('autoInDelivery.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: body.toString()
        }).then(function (r) { return r.json(); }).then(function (res) {
            if (!res || !res.ok) return;
            applyTrackingStatusUI('expediee');
            setStatusCard('<strong>Pickup confirme</strong> · la van a pris le colis, statut passe a <strong>En cours de livraison</strong>.');
        }).catch(function () {});
    }

    function initMap(destLat, destLng) {
        var map = L.map(mapEl).setView([destLat, destLng], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var stockLat = stockLatConst;
        var stockLng = stockLngConst;
        var riderStartLat = stockLat + 0.018 + (seeded(orderId * 17) - 0.5) * 0.012;
        var riderStartLng = stockLng - 0.02 + (seeded(orderId * 19) - 0.5) * 0.012;

        var stockIcon = L.divIcon({
            className: 'fo-stock-marker-wrap',
            html: '<div class="fo-stock-marker">📦</div>',
            iconSize: [34, 34],
            iconAnchor: [17, 17]
        });
        var deliveryIcon = L.divIcon({
            className: 'fo-delivery-marker-wrap',
            html: '<div class="fo-delivery-marker">📍</div>',
            iconSize: [34, 34],
            iconAnchor: [17, 17]
        });

        var stockMarker = L.marker([stockLat, stockLng], {icon: stockIcon}).addTo(map).bindPopup(stockName);
        var destMarker = L.marker([destLat, destLng], {icon: deliveryIcon}).addTo(map).bindPopup('Point a livrer');
        destMarker.openPopup();

        var courierLat = riderStartLat;
        var courierLng = riderStartLng;

        var vanIcon = L.divIcon({
            className: 'fo-van-marker-wrap',
            html: '<div class="fo-van-marker">🚚</div>',
            iconSize: [34, 34],
            iconAnchor: [17, 17]
        });
        var courierMarker = L.marker([courierLat, courierLng], {icon: vanIcon})
            .addTo(map)
            .bindPopup(isLive ? 'Livreur proche (van)' : 'Van non assigne');

        var route = L.polyline([[riderStartLat, riderStartLng], [stockLat, stockLng], [destLat, destLng]], {
            color: '#38bdf8',
            weight: 3,
            opacity: 0.8,
            dashArray: isLive ? null : '6,6'
        }).addTo(map);
        var progress = L.polyline([], {
            color: '#22c55e',
            weight: 4,
            opacity: 0.95
        }).addTo(map);

        var group = L.featureGroup([stockMarker, destMarker, courierMarker, route, progress]);
        map.fitBounds(group.getBounds().pad(0.35));

        function updateVanAngle(from, to) {
            var angle = Math.atan2(to[1] - from[1], to[0] - from[0]) * 180 / Math.PI;
            var el = courierMarker.getElement();
            if (!el) return;
            var van = el.querySelector('.fo-van-marker');
            if (!van) return;
            van.style.transform = 'rotate(' + angle + 'deg)';
        }

        function clamp(v, min, max) {
            return Math.max(min, Math.min(max, v));
        }

        function trafficProfile() {
            var hour = new Date().getHours();
            if ((hour >= 7 && hour <= 9) || (hour >= 17 && hour <= 20)) {
                return {factor: 0.62 + seeded(orderId * hour) * 0.18, label: 'embouteillage fort'};
            }
            if ((hour >= 10 && hour <= 16) || (hour >= 21 && hour <= 22)) {
                return {factor: 0.78 + seeded(orderId + hour * 5) * 0.2, label: 'trafic modere'};
            }
            return {factor: 0.9 + seeded(orderId + hour * 9) * 0.12, label: 'trafic fluide'};
        }

        function animateAlongRoute(coords, routeDurationSec, routeDistanceM, startIndex, stockIndex) {
            if (!coords || coords.length < 2) return;
            route.setLatLngs(coords.map(function (c) { return [c[1], c[0]]; }));
            var i = Math.max(0, Math.min(coords.length - 1, startIndex || 0));
            var traveled = [];
            var lastFrame = performance.now();
            var baseSpeedMps = 9.0; // ~32.4 km/h (urban default)
            if (routeDurationSec > 0 && routeDistanceM > 0) {
                baseSpeedMps = clamp(routeDistanceM / routeDurationSec, 5.0, 14.0); // 18 -> 50 km/h
            }
            var profile = trafficProfile();
            var trafficFactor = profile.factor;
            var trafficLabel = profile.label;
            var pickupStatusSynced = false;
            var preparationUiApplied = false;

            if (i > 0) {
                for (var p = 0; p <= i && p < coords.length; p++) {
                    traveled.push([coords[p][1], coords[p][0]]);
                }
                progress.setLatLngs(traveled);
                courierMarker.setLatLng([coords[i][1], coords[i][0]]);
            }

            function step(ts) {
                if (!isLive) return;
                var dt = Math.max(0.016, Math.min(1.5, (ts - lastFrame) / 1000));
                lastFrame = ts;
                var speedMps = clamp(baseSpeedMps * trafficFactor, 3.6, 14.0);

                if (i >= coords.length - 1) {
                    setStatusCard('<strong>Livreur arrive</strong> · destination atteinte.');
                    return;
                }

                var from = coords[i];
                var to = coords[i + 1];
                var dist = metersBetween([from[1], from[0]], [to[1], to[0]]);
                var t = Math.min(1, (dt * speedMps) / Math.max(dist, 1));
                var lng = from[0] + (to[0] - from[0]) * t;
                var lat = from[1] + (to[1] - from[1]) * t;
                coords[i] = [lng, lat];
                if (t >= 0.999) i++;

                traveled.push([lat, lng]);
                if (traveled.length > 1) progress.setLatLngs(traveled);
                courierMarker.setLatLng([lat, lng]);
                if (i < coords.length - 1) {
                    updateVanAngle([lat, lng], [coords[i + 1][1], coords[i + 1][0]]);
                }

                var remaining = 0;
                for (var k = i; k < coords.length - 1; k++) {
                    remaining += metersBetween([coords[k][1], coords[k][0]], [coords[k + 1][1], coords[k + 1][0]]);
                }
                var etaMin = remaining / Math.max(1, speedMps) / 60;
                var phase = 'pickup au stock';
                if (i < stockIndex) {
                    if (!preparationUiApplied && (orderStatus === 'payee' || orderStatus === 'en_attente_paiement')) {
                        // Tant que la van n'a pas atteint le stock, on reste en préparation/pick-up.
                        applyTrackingStatusUI('en_preparation');
                        preparationUiApplied = true;
                    }
                } else if (i >= stockIndex) {
                    phase = 'en livraison vers client';
                    if (!pickupStatusSynced) {
                        syncStatusToInDelivery();
                        pickupStatusSynced = true;
                    }
                }
                if (i >= coords.length - 1) phase = 'livre';
                setStatusCard('<strong>Distance restante:</strong> ' + formatKm(remaining) +
                    ' · <strong>ETA:</strong> ' + formatEta(etaMin) +
                    ' · <strong>Vitesse:</strong> ' + Math.round(speedMps * 3.6) + ' km/h' +
                    ' · <strong>Etat trafic:</strong> ' + trafficLabel +
                    ' · <strong>Phase:</strong> ' + phase);

                requestAnimationFrame(step);
            }

            setInterval(function () {
                var now = trafficProfile();
                var smooth = 0.65;
                trafficFactor = clamp(trafficFactor * smooth + now.factor * (1 - smooth) + (Math.random() - 0.5) * 0.04, 0.45, 1.12);
                trafficLabel = now.label;
            }, 6500);

            requestAnimationFrame(step);
        }

        if (!isLive) {
            setStatusCard('<strong>Points definis:</strong> stock -> livraison · suivi live actif quand la commande est en preparation/expedition.');
            return;
        }

        setStatusCard('Calcul de l\'itineraire livreur (pickup puis livraison)...');
        var leg1Url = 'https://router.project-osrm.org/route/v1/driving/' +
            encodeURIComponent(riderStartLng + ',' + riderStartLat + ';' + stockLng + ',' + stockLat) +
            '?overview=full&geometries=geojson';
        var leg2Url = 'https://router.project-osrm.org/route/v1/driving/' +
            encodeURIComponent(stockLng + ',' + stockLat + ';' + destLng + ',' + destLat) +
            '?overview=full&geometries=geojson';

        Promise.all([
            fetch(leg1Url).then(function (r) { return r.json(); }),
            fetch(leg2Url).then(function (r) { return r.json(); })
        ]).then(function (legsData) {
            var r1 = (((legsData[0] || {}).routes || [])[0]) || null;
            var r2 = (((legsData[1] || {}).routes || [])[0]) || null;
            var c1 = r1 && r1.geometry && r1.geometry.coordinates ? r1.geometry.coordinates : null;
            var c2 = r2 && r2.geometry && r2.geometry.coordinates ? r2.geometry.coordinates : null;

            if (!c1 || !c2 || c1.length < 2 || c2.length < 2) {
                throw new Error('route-missing');
            }
            var merged = c1.concat(c2.slice(1));
            var totalDuration = parseFloat(r1.duration || 0) + parseFloat(r2.duration || 0);
            var totalDistance = parseFloat(r1.distance || 0) + parseFloat(r2.distance || 0);
            var stockIndex = Math.max(1, c1.length - 1);
            var startAt = 0;
            if (orderStatus === 'payee') {
                startAt = Math.max(0, stockIndex - 2);
            } else if (orderStatus === 'en_preparation') {
                startAt = stockIndex;
            } else if (orderStatus === 'expediee') {
                startAt = Math.min(merged.length - 2, stockIndex + Math.max(1, Math.floor((merged.length - stockIndex) * 0.2)));
            } else if (orderStatus === 'livree') {
                startAt = merged.length - 1;
            }
            animateAlongRoute(merged, totalDuration, totalDistance, startAt, stockIndex);
        }).catch(function () {
            setStatusCard('<strong>Reseau route indisponible</strong> · suivi simplifie active (pickup -> livraison).');
            var d1 = metersBetween([riderStartLat, riderStartLng], [stockLat, stockLng]);
            var d2 = metersBetween([stockLat, stockLng], [destLat, destLng]);
            var fallbackCoords = [[riderStartLng, riderStartLat], [stockLng, stockLat], [destLng, destLat]];
            var fallbackStockIndex = 1;
            var fallbackStart = 0;
            if (orderStatus === 'en_preparation') fallbackStart = fallbackStockIndex;
            if (orderStatus === 'expediee') fallbackStart = fallbackStockIndex + 1;
            if (orderStatus === 'livree') fallbackStart = fallbackCoords.length - 1;
            animateAlongRoute(
                fallbackCoords,
                0,
                d1 + d2,
                fallbackStart,
                fallbackStockIndex
            );
        });
    }

    function scoreResult(row, expectedStreet, expectedCity, expectedCp) {
        var text = ((row.display_name || '') + ' ' + JSON.stringify(row.address || {})).toLowerCase();
        var s = 0;
        if (expectedStreet && text.indexOf(expectedStreet.toLowerCase()) >= 0) s += 6;
        if (expectedCity && text.indexOf(expectedCity.toLowerCase()) >= 0) s += 3;
        if (expectedCp && text.indexOf(expectedCp.toLowerCase()) >= 0) s += 1;
        return s;
    }

    function geocodeBest() {
        var queries = [];
        if (street && city) queries.push(street + ', ' + city + ', ' + country);
        if (street) queries.push(street + ', ' + country);
        if (street && /chotrana|soukra/i.test(street)) queries.push(street + ', La Soukra, Ariana, ' + country);
        if (street && postcode) queries.push(street + ', ' + postcode + ', ' + country);
        if (address) queries.push(address);

        var idx = 0;
        var best = null;
        function next() {
            if (idx >= queries.length) {
                if (best) initMap(parseFloat(best.lat), parseFloat(best.lon));
                else initMap(fallback[0], fallback[1]);
                return;
            }
            var q = queries[idx++];
            fetch('https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=5&q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (rows) {
                    if (rows && rows.length) {
                        for (var i = 0; i < rows.length; i++) {
                            rows[i]._score = scoreResult(rows[i], street, city, postcode);
                            if (!best || rows[i]._score > best._score) best = rows[i];
                        }
                        if (best && best._score >= 6) {
                            initMap(parseFloat(best.lat), parseFloat(best.lon));
                            return;
                        }
                    }
                    next();
                })
                .catch(function () { next(); });
        }
        next();
    }

    geocodeBest();
})();
</script>
</body>
</html>
