<?php
session_start();
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../controller/presenceC.php';
require_once __DIR__ . '/../_layout/paths.php';

$idEvent = (int)($_GET['id_event'] ?? 0);
if ($idEvent < 1) { header('Location: liste_event.php'); exit; }

$db = Config::getConnexion();
$st = $db->prepare('SELECT `titre_event`, `date_debut`, `date_fin`, `lieu_event` FROM `evenement` WHERE `id_event` = :id LIMIT 1');
$st->execute(['id' => $idEvent]);
$ev = $st->fetch(PDO::FETCH_ASSOC);
if (!$ev) { header('Location: liste_event.php'); exit; }

$c    = new PresenceC();
$list = $c->listePresences($idEvent);
$nb   = count($list);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Présences — <?= htmlspecialchars($ev['titre_event']) ?></title>
<link rel="stylesheet" href="<?= htmlspecialchars(bo_url('commerce.css')) ?>">
<style>
    .presence-header {
        background: linear-gradient(135deg, #0f173c, #1a2560);
        border-radius: 14px;
        padding: 24px 28px;
        margin-bottom: 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        border: 1px solid #6c4daf44;
    }
    .presence-header h2 { color: #fff; font-size: 1.3rem; margin-bottom: 4px; }
    .presence-header p  { color: #a0a0c0; font-size: .85rem; margin: 0; }
    .count-badge {
        background: #6c4daf;
        color: #fff;
        font-size: 2rem;
        font-weight: 800;
        padding: 12px 24px;
        border-radius: 12px;
        text-align: center;
        min-width: 90px;
    }
    .count-badge small { display: block; font-size: .7rem; font-weight: 400; opacity: .8; }

    .table-modern th, .table-modern td { vertical-align: middle; }
    .date-badge {
        display: inline-block;
        background: #1a2560;
        color: #a5b4fc;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: .78rem;
        font-weight: 600;
    }
    .heure-badge {
        display: inline-block;
        background: #065f46;
        color: #d1fae5;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: .78rem;
        font-weight: 600;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #a0a0c0;
    }
    .empty-state .icon { font-size: 3rem; margin-bottom: 12px; }
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        background: #6c4daf;
        color: #fff;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: .9rem;
        transition: background .2s;
        margin-right: 10px;
    }
    .btn-back:hover { background: #5a3d9a; }
    .btn-scan {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        background: #0f173c;
        color: #a0a0c0;
        border: 1.5px solid #6c4daf;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: .9rem;
        transition: background .2s, color .2s;
    }
    .btn-scan:hover { background: #1a2560; color: #fff; }
</style>
</head>
<body>
<?php require_once __DIR__ . '/../_layout/sidebar.php'; ?>

<div class="content commerce-page">
    <div class="container">

        <!-- En-tête -->
        <div class="presence-header">
            <div>
                <h2>📋 Liste de présence</h2>
                <p><strong style="color:#e0e0f0;"><?= htmlspecialchars($ev['titre_event']) ?></strong></p>
                <p>📅 <?= date('d/m/Y', strtotime($ev['date_debut'])) ?>
                   → <?= date('d/m/Y', strtotime($ev['date_fin'])) ?>
                   &nbsp;|&nbsp; 📍 <?= htmlspecialchars($ev['lieu_event']) ?>
                </p>
            </div>
            <div class="count-badge">
                <?= $nb ?>
                <small>présent<?= $nb > 1 ? 's' : '' ?></small>
            </div>
        </div>

        <!-- Actions -->
        <div style="margin-bottom: 20px;">
            <a href="liste_event.php" class="btn-back">← Retour</a>
            <a href="scanner.php?id_event=<?= $idEvent ?>" class="btn-scan">📷 Scanner un ticket</a>
        </div>

        <!-- Tableau -->
        <?php if (empty($list)): ?>
            <div class="empty-state">
                <div class="icon">🎫</div>
                <p>Aucun ticket scanné pour cet événement.</p>
                <a href="scanner.php?id_event=<?= $idEvent ?>" class="btn-back" style="margin-top:16px; display:inline-flex;">
                    📷 Commencer le scan
                </a>
            </div>
        <?php else: ?>
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Date scan</th>
                        <!--<th>Heure</th> -->
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($list as $i => $r): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= htmlspecialchars($r['nom']) ?></strong></td>
                        <td><?= htmlspecialchars($r['prenom']) ?></td>
                        <td><?= htmlspecialchars($r['email']) ?></td>
                        <td><?= htmlspecialchars($r['telephone']) ?></td>
                        <td><span class="date-badge"><?= htmlspecialchars($r['date_scan']) ?></span></td>
                        <!--<td><span class="heure-badge"><?= htmlspecialchars($r['heure_scan']) ?></span></td> -->
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </div>
</div>
</body>
</html>