<?php
require_once __DIR__ . '/bo_event_bootstrap.php';
require_once __DIR__ . '/../../../controller/participationC.php';
require_once __DIR__ . '/../../../controller/eventC.php';
require_once __DIR__ . '/../_layout/paths.php';

if (isset($_GET['delete_p']) && (string) $_GET['delete_p'] !== '') {
    $pc0 = new ParticipationC();
    $pc0->deleteParticipation($_GET['delete_p']);
    $redir = $_GET;
    unset($redir['delete_p']);
    $redir['part_deleted'] = '1';
    header('Location: liste_event.php?' . http_build_query($redir));
    exit();
}

$eAllowed = ['id_event', 'titre_event', 'description_event', 'type_event', 'date_debut', 'date_fin', 'lieu_event', 'capacite_max', 'statut', 'created_at'];
$esort = (string) ($_GET['esort'] ?? 'id_event');
$edir  = (string) ($_GET['edir']  ?? 'asc');
if (!in_array($esort, $eAllowed, true)) {
    $esort = 'id_event';
}
$edir = strtoupper($edir) === 'DESC' ? 'desc' : 'asc';

$pAllowed = ['id_participation', 'titre_event', 'nom', 'prenom', 'email', 'telephone', 'date_inscription', 'statut', 'id_event'];
$psort = (string) ($_GET['psort'] ?? 'date_inscription');
$pdir  = (string) ($_GET['pdir']  ?? 'desc');
if (!in_array($psort, $pAllowed, true)) {
    $psort = 'date_inscription';
}
$pdir = strtoupper($pdir) === 'ASC' ? 'asc' : 'desc';

$eventC = new EventC();
$liste = $eventC->listeEvent($esort, $edir);
$pc = new ParticipationC();
$participations = $pc->listeParticipation($psort, $pdir);

$baseFlash = [];
foreach (['added', 'deleted', 'updated', 'part_ok', 'part_updated', 'part_deleted'] as $fk) {
    if (isset($_GET[$fk]) && (string) $_GET[$fk] === '1') {
        $baseFlash[$fk] = '1';
    }
}

$mkQs = static function (array $over = []) use ($baseFlash, $esort, $edir, $psort, $pdir) {
    $q = array_merge($baseFlash, [
        'esort' => $esort,
        'edir'  => $edir,
        'psort' => $psort,
        'pdir'  => $pdir,
    ], $over);
    return htmlspecialchars(http_build_query($q), ENT_QUOTES, 'UTF-8');
};

$sortUrlE = static function (string $col) use ($mkQs, $esort, $edir) {
    $is = strtolower($esort) === strtolower($col);
    $next = ($is && strtolower($edir) === 'asc') ? 'desc' : 'asc';
    if (!$is) {
        $next = 'asc';
    }
    return 'liste_event.php?' . $mkQs(['esort' => $col, 'edir' => $next]);
};

$sortUrlP = static function (string $col) use ($mkQs, $psort, $pdir) {
    $is = strtolower($psort) === strtolower($col);
    $next = ($is && strtolower($pdir) === 'asc') ? 'desc' : 'asc';
    if (!$is) {
        $next = 'asc';
    }
    return 'liste_event.php?' . $mkQs(['psort' => $col, 'pdir' => $next]);
};

$sortMarkE = static function (string $col) use ($esort, $edir) {
    if (strtolower($esort) !== strtolower($col)) {
        return '';
    }
    return strtolower($edir) === 'asc' ? ' &uarr;' : ' &darr;';
};

$sortMarkP = static function (string $col) use ($psort, $pdir) {
    if (strtolower($psort) !== strtolower($col)) {
        return '';
    }
    return strtolower($pdir) === 'asc' ? ' &uarr;' : ' &darr;';
};

$exportEventsHref = 'exportEventsPdf.php?' . htmlspecialchars(http_build_query(['sort' => $esort, 'dir' => $edir]), ENT_QUOTES, 'UTF-8');
$exportPartHref   = 'exportParticipationsPdf.php?' . htmlspecialchars(http_build_query(['sort' => $psort, 'dir' => $pdir]), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Événements & participations</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(bo_url('commerce.css')) ?>">
    <style>
        .table-modern thead th a { color: inherit; text-decoration: none; }
        .table-modern thead th a:hover { text-decoration: underline; }
    </style>
</head>

<body>
<?php require_once __DIR__ . '/../_layout/sidebar.php'; ?>

<div class="content commerce-page">
    <div class="container">

        <?php if (isset($_GET['added']) && (string) $_GET['added'] === '1'): ?>
            <div class="alert alert-success" style="padding:12px 16px;border-radius:8px;margin-bottom:16px;">Événement ajouté.</div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted']) && (string) $_GET['deleted'] === '1'): ?>
            <div class="alert alert-success" style="padding:12px 16px;border-radius:8px;margin-bottom:16px;">Événement supprimé.</div>
        <?php endif; ?>
        <?php if (isset($_GET['updated']) && (string) $_GET['updated'] === '1'): ?>
            <div class="alert alert-success" style="padding:12px 16px;border-radius:8px;margin-bottom:16px;">Événement mis à jour.</div>
        <?php endif; ?>
        <?php if (isset($_GET['part_ok']) && (string) $_GET['part_ok'] === '1'): ?>
            <div class="alert alert-success" style="padding:12px 16px;border-radius:8px;margin-bottom:16px;">Participation ajoutée.</div>
        <?php endif; ?>
        <?php if (isset($_GET['part_updated']) && (string) $_GET['part_updated'] === '1'): ?>
            <div class="alert alert-success" style="padding:12px 16px;border-radius:8px;margin-bottom:16px;">Participation enregistrée.</div>
        <?php endif; ?>
        <?php if (isset($_GET['part_deleted']) && (string) $_GET['part_deleted'] === '1'): ?>
            <div class="alert alert-success" style="padding:12px 16px;border-radius:8px;margin-bottom:16px;">Participation supprimée.</div>
        <?php endif; ?>

        <div class="topbar">
            <div class="page-title">Liste des événements</div>
            <div class="actions">
                <input class="search-input" placeholder="Rechercher un événement…" id="searchEventInput" aria-label="Rechercher">
                <a href="<?= $exportEventsHref ?>" class="btn btn-secondary">Export PDF</a>
                <a href="ajout_event.php" class="btn btn-primary">+ Événement</a>
            </div>
        </div>

        <table class="table-modern" id="eventTable">
            <thead>
                <tr>
                    <th><a href="<?= $sortUrlE('id_event'); ?>">Id</a><?= $sortMarkE('id_event') ?></th>
                    <th><a href="<?= $sortUrlE('titre_event'); ?>">Titre</a><?= $sortMarkE('titre_event') ?></th>
                    <th><a href="<?= $sortUrlE('description_event'); ?>">Description</a><?= $sortMarkE('description_event') ?></th>
                    <th><a href="<?= $sortUrlE('type_event'); ?>">Type</a><?= $sortMarkE('type_event') ?></th>
                    <th><a href="<?= $sortUrlE('date_debut'); ?>">Date début</a><?= $sortMarkE('date_debut') ?></th>
                    <th><a href="<?= $sortUrlE('date_fin'); ?>">Date fin</a><?= $sortMarkE('date_fin') ?></th>
                    <th><a href="<?= $sortUrlE('lieu_event'); ?>">Lieu</a><?= $sortMarkE('lieu_event') ?></th>
                    <th><a href="<?= $sortUrlE('capacite_max'); ?>">Capacité</a><?= $sortMarkE('capacite_max') ?></th>
                    <th><a href="<?= $sortUrlE('statut'); ?>">Statut</a><?= $sortMarkE('statut') ?></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($liste as $event): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $event['id_event']) ?></td>
                        <td><?= htmlspecialchars((string) $event['titre_event']) ?></td>
                        <td><?php
                            $d = (string) $event['description_event'];
                            if (function_exists('mb_strimwidth')) {
                                $d = mb_strimwidth($d, 0, 80, '…');
                            } elseif (strlen($d) > 80) {
                                $d = substr($d, 0, 77) . '…';
                            }
                            echo htmlspecialchars($d);
                        ?></td>
                        <td><?= htmlspecialchars((string) $event['type_event']) ?></td>
                        <td><?= htmlspecialchars((string) $event['date_debut']) ?></td>
                        <td><?= htmlspecialchars((string) $event['date_fin']) ?></td>
                        <td><?= htmlspecialchars((string) $event['lieu_event']) ?></td>
                        <td><?= htmlspecialchars((string) $event['capacite_max']) ?></td>
                        <td><?= htmlspecialchars((string) $event['statut']) ?></td>
                        <td>
                            <a href="supprimer_event.php?id=<?= (int) $event['id_event'] ?>&<?= $mkQs() ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Supprimer cet événement ?')">Supprimer</a>
                            <a href="modifier_event.php?id=<?= (int) $event['id_event'] ?>&<?= $mkQs() ?>"
                               class="btn btn-secondary">Modifier</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="topbar" style="margin-top:48px;">
            <div class="page-title">Participations</div>
            <div class="actions">
                <input class="search-input" placeholder="Rechercher…" id="searchPartInput" aria-label="Rechercher participations">
                <a href="<?= $exportPartHref ?>" class="btn btn-secondary">Export PDF</a>
            </div>
        </div>

        <?php if (empty($participations)): ?>
            <p style="text-align:center;color:#888;padding:20px;">Aucune participation enregistrée.</p>
        <?php else: ?>
        <table class="table-modern" id="partTable">
            <thead>
                <tr>
                    <th><a href="<?= $sortUrlP('id_participation'); ?>">Id</a><?= $sortMarkP('id_participation') ?></th>
                    <th><a href="<?= $sortUrlP('titre_event'); ?>">Événement</a><?= $sortMarkP('titre_event') ?></th>
                    <th><a href="<?= $sortUrlP('nom'); ?>">Nom</a><?= $sortMarkP('nom') ?></th>
                    <th><a href="<?= $sortUrlP('prenom'); ?>">Prénom</a><?= $sortMarkP('prenom') ?></th>
                    <th><a href="<?= $sortUrlP('email'); ?>">Email</a><?= $sortMarkP('email') ?></th>
                    <th><a href="<?= $sortUrlP('telephone'); ?>">Téléphone</a><?= $sortMarkP('telephone') ?></th>
                    <th><a href="<?= $sortUrlP('date_inscription'); ?>">Date</a><?= $sortMarkP('date_inscription') ?></th>
                    <th><a href="<?= $sortUrlP('statut'); ?>">Statut</a><?= $sortMarkP('statut') ?></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($participations as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $p['id_participation']) ?></td>
                        <td><?= htmlspecialchars((string) $p['titre_event']) ?></td>
                        <td><?= htmlspecialchars((string) $p['nom']) ?></td>
                        <td><?= htmlspecialchars((string) $p['prenom']) ?></td>
                        <td><?= htmlspecialchars((string) $p['email']) ?></td>
                        <td><?= htmlspecialchars((string) $p['telephone']) ?></td>
                        <td><?= htmlspecialchars((string) $p['date_inscription']) ?></td>
                        <td><?= htmlspecialchars((string) $p['statut']) ?></td>
                        <td>
                            <a href="modifier_participation.php?id=<?= (int) $p['id_participation'] ?>&<?= $mkQs() ?>"
                               class="btn btn-secondary">Modifier</a>
                            <a href="liste_event.php?delete_p=<?= (int) $p['id_participation'] ?>&<?= $mkQs() ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Supprimer cette participation ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

    </div>
</div>

<script>
(function () {
    var s1 = document.getElementById('searchEventInput');
    if (s1) s1.addEventListener('input', function () {
        var q = this.value.toLowerCase();
        document.querySelectorAll('#eventTable tbody tr').forEach(function (row) {
            row.style.display = Array.from(row.cells).some(function (c) {
                return c.textContent.toLowerCase().indexOf(q) !== -1;
            }) ? '' : 'none';
        });
    });
    var s2 = document.getElementById('searchPartInput');
    var pt = document.getElementById('partTable');
    if (s2 && pt) s2.addEventListener('input', function () {
        var q2 = this.value.toLowerCase();
        document.querySelectorAll('#partTable tbody tr').forEach(function (row) {
            row.style.display = Array.from(row.cells).some(function (c) {
                return c.textContent.toLowerCase().indexOf(q2) !== -1;
            }) ? '' : 'none';
        });
    });
})();
</script>
</body>
</html>
