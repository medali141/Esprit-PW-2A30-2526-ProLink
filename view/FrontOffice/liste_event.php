<?php
include "../../controller/eventC.php";
include "../../config.php";
$eventC = new EventC();
$liste  = $eventC->listeEvent();

$events = is_array($liste) ? $liste : [];
$categories = [];
$locations = [];
$upcomingEvents = $events;

foreach ($events as $event) {
    $type = trim((string)($event['type_event'] ?? 'General'));
    $lieu = trim((string)($event['lieu_event'] ?? 'Sans lieu'));
    $categories[$type] = ($categories[$type] ?? 0) + 1;
    $locations[$lieu]  = ($locations[$lieu]  ?? 0) + 1;
}

arsort($categories);
arsort($locations);

usort($upcomingEvents, static function ($a, $b) {
    return strtotime((string)($a['date_debut'] ?? '')) <=> strtotime((string)($b['date_debut'] ?? ''));
});

$eventPhotos = [
    'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1000&q=80',
    'https://images.unsplash.com/photo-1515187029135-18ee286d815b?auto=format&fit=crop&w=1000&q=80',
    'https://images.unsplash.com/photo-1558403194-611308249627?auto=format&fit=crop&w=1000&q=80',
    'https://images.unsplash.com/photo-1523580846011-d3a5bc25702b?auto=format&fit=crop&w=1000&q=80',
    'https://images.unsplash.com/photo-1540317580384-e5d43616b9aa?auto=format&fit=crop&w=1000&q=80',
    'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1000&q=80',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ProLink - Evenements</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #ffffff;
            margin: 0;
            color: #1f2937;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 30px 20px; }
        .main { flex: 1; }
        .page-header { margin-bottom: 24px; }
        .page-title { margin: 0; font-size: 32px; font-weight: 700; color: #0f172a; }
        .blog-layout {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(260px, 340px);
            gap: 28px;
            align-items: start;
        }
        .event-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 22px; }
        .event-card {
            border: 1px solid #e5e7eb;
            background: #ffffff;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(15,23,42,0.06);
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }
        .event-card:hover { transform: translateY(-4px); box-shadow: 0 10px 24px rgba(15,23,42,0.12); }
        .event-thumb {
            height: 160px;
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            position: relative;
            overflow: hidden;
        }
        .event-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .event-thumb::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(2,132,199,0.08), rgba(2,132,199,0.25));
            pointer-events: none;
        }
        .event-thumb.image-fallback::after { background: none; }
        .event-type {
            position: absolute;
            left: 12px; top: 12px;
            background: #0284c7;
            color: #fff;
            font-size: 11px; font-weight: 600;
            padding: 5px 10px;
            border-radius: 3px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .event-content { padding: 14px 14px 16px; }
        .event-meta { display: flex; gap: 12px; font-size: 12px; color: #6b7280; margin-bottom: 8px; flex-wrap: wrap; }
        .event-title { margin: 0 0 8px; font-size: 24px; color: #0f172a; line-height: 1.2; text-transform: lowercase; }
        .event-desc {
            font-size: 13px; color: #4b5563; line-height: 1.45;
            min-height: 38px; margin: 0 0 12px;
            display: -webkit-box; -webkit-line-clamp: 2;
            -webkit-box-orient: vertical; overflow: hidden;
        }
        .event-footer { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
        .status-badge {
            font-size: 11px; font-weight: 600;
            text-transform: uppercase;
            padding: 4px 8px; border-radius: 999px;
            background: #eff6ff; color: #1d4ed8;
        }

        /* ===== BOUTON S'INSCRIRE ===== */
        .btn-participer {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #0284c7;
            color: #fff;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            padding: 8px 14px;
            border-radius: 4px;
            transition: background 0.15s ease;
        }
        .btn-participer:hover { background: #0369a1; }

        .sidebar .widget { border: 1px solid #e5e7eb; padding: 14px; margin-bottom: 18px; background: #ffffff; }
        .widget-title { margin: 0 0 12px; font-size: 24px; color: #0f172a; }
        .search-box { display: flex; gap: 8px; }
        .search-input { flex: 1; border: 1px solid #d1d5db; padding: 8px 10px; font-size: 13px; border-radius: 2px; }
        .search-btn { border: none; background: #0284c7; color: #fff; width: 34px; border-radius: 2px; cursor: pointer; font-weight: 700; }
        .list-clean { margin: 0; padding: 0; list-style: none; }
        .list-clean li { border-bottom: 1px solid #eef2f7; padding: 8px 0; font-size: 13px; display: flex; justify-content: space-between; gap: 8px; }
        .recent-item { display: flex; gap: 8px; align-items: start; margin-bottom: 10px; font-size: 13px; }
        .recent-thumb { width: 56px; height: 48px; border-radius: 2px; background: linear-gradient(135deg,#93c5fd,#38bdf8); flex-shrink: 0; }
        .tag-cloud { display: flex; flex-wrap: wrap; gap: 8px; }
        .tag-chip { display: inline-block; border: 1px solid #cbd5e1; font-size: 12px; color: #334155; padding: 5px 8px; border-radius: 3px; text-decoration: none; }
        .empty-state { border: 1px dashed #cbd5e1; border-radius: 6px; padding: 26px; text-align: center; color: #64748b; font-size: 14px; background: #f8fafc; }
        @media (max-width: 1024px) { .event-grid { grid-template-columns: 1fr; } }
        @media (max-width: 900px)  { .blog-layout { grid-template-columns: 1fr; } }
    </style>
</head>

<body>
<?php include 'components/navbar.php'; ?>

<main class="main container">
    <header class="page-header">
        <h1 class="page-title">Liste des evenements</h1>
    </header>

    <div class="blog-layout">
        <section>
            <?php if (!empty($events)): ?>
                <div class="event-grid" id="eventGrid">
                    <?php foreach ($events as $event): ?>
                        <?php
                        $titre       = (string)($event['titre_event']       ?? 'Evenement sans titre');
                        $description = (string)($event['description_event'] ?? '');
                        $type        = (string)($event['type_event']        ?? 'General');
                        $dateDebut   = (string)($event['date_debut']        ?? '');
                        $dateFin     = (string)($event['date_fin']          ?? '');
                        $lieu        = (string)($event['lieu_event']        ?? 'Sans lieu');
                        $capacite    = (string)($event['capacite_max']      ?? '-');
                        $statut      = (string)($event['statut']            ?? 'Inconnu');
                        $idEvent     = (int)($event['id_event']             ?? 0);
                        $seed        = (string)($event['id_event']          ?? $titre);
                        $photoIndex  = abs((int)crc32($seed)) % count($eventPhotos);
                        $photoUrl    = $eventPhotos[$photoIndex];
                        ?>
                        <article class="event-card"
                                 data-search="<?= htmlspecialchars(strtolower($titre.' '.$description.' '.$type.' '.$lieu.' '.$statut)) ?>">
                            <div class="event-thumb">
                                <img src="<?= htmlspecialchars($photoUrl) ?>"
                                     alt="<?= htmlspecialchars($titre) ?>"
                                     loading="lazy"
                                     onerror="this.remove(); this.parentElement.classList.add('image-fallback');">
                                <span class="event-type"><?= htmlspecialchars($type) ?></span>
                            </div>
                            <div class="event-content">
                                <div class="event-meta">
                                    <span>📅 <?= htmlspecialchars($dateDebut) ?></span>
                                    <span>📍 <?= htmlspecialchars($lieu) ?></span>
                                </div>
                                <h2 class="event-title"><?= htmlspecialchars($titre) ?></h2>
                                <p class="event-desc"><?= htmlspecialchars($description) ?></p>
                                <div class="event-meta">
                                    <span>Fin: <?= htmlspecialchars($dateFin) ?></span>
                                    <span>Capacite: <?= htmlspecialchars($capacite) ?></span>
                                </div>
                                <div class="event-footer">
                                    <span class="status-badge"><?= htmlspecialchars($statut) ?></span>
                                    <!-- ✅ BOUTON S'INSCRIRE avec le bon id_event -->
                                    <a href="inscription_event.php?id_event=<?= $idEvent ?>"
                                       class="btn-participer">S'inscrire</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">Aucun evenement disponible pour le moment.</div>
            <?php endif; ?>
        </section>

        <aside class="sidebar">
            <div class="widget">
                <h3 class="widget-title">Recherche</h3>
                <div class="search-box">
                    <input class="search-input" id="searchInput" placeholder="Mot-cle...">
                    <button class="search-btn" type="button">Q</button>
                </div>
            </div>
            <div class="widget">
                <h3 class="widget-title">Categories</h3>
                <ul class="list-clean">
                    <?php foreach ($categories as $name => $count): ?>
                        <li>
                            <span><?= htmlspecialchars($name) ?></span>
                            <strong><?= (int)$count ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="widget">
                <h3 class="widget-title">Recent post</h3>
                <?php foreach (array_slice($upcomingEvents, 0, 4) as $recent): ?>
                    <div class="recent-item">
                        <div class="recent-thumb"></div>
                        <div>
                            <div><strong><?= htmlspecialchars((string)($recent['titre_event'] ?? 'Evenement')) ?></strong></div>
                            <small><?= htmlspecialchars((string)($recent['date_debut'] ?? 'Date inconnue')) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="widget">
                <h3 class="widget-title">Tag cloud</h3>
                <div class="tag-cloud">
                    <?php foreach (array_slice(array_keys($locations), 0, 10) as $city): ?>
                        <a href="#" class="tag-chip"><?= htmlspecialchars($city) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>
    </div>

    <script>
        const searchInput = document.getElementById('searchInput');
        const cards = document.querySelectorAll('#eventGrid .event-card');
        if (searchInput && cards.length) {
            searchInput.addEventListener('input', function(e) {
                const q = e.target.value.toLowerCase().trim();
                cards.forEach(card => {
                    const text = card.getAttribute('data-search') || '';
                    card.style.display = text.includes(q) ? '' : 'none';
                });
            });
        }
    </script>
</main>

<?php include 'components/footer.php'; ?>
</body>
</html>