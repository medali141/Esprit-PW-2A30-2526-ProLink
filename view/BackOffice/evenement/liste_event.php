<?php
require_once __DIR__ . '/bo_event_bootstrap.php';
require_once __DIR__ . '/../../../controller/participationC.php';
require_once __DIR__ . '/../../../controller/eventC.php';
<<<<<<< HEAD
require_once __DIR__ . '/../../../controller/presenceC.php';
require_once __DIR__ . '/../_layout/paths.php';

if (isset($_GET['accept_p']) && (string)$_GET['accept_p'] !== '') {
    $pc0 = new ParticipationC();
    $pc0->updateStatut((int)$_GET['accept_p'], 'confirmé');
    $redir = $_GET; unset($redir['accept_p']); $redir['part_accepted'] = '1';
    header('Location: liste_event.php?' . http_build_query($redir)); exit();
}
if (isset($_GET['reject_p']) && (string)$_GET['reject_p'] !== '') {
    $pc0 = new ParticipationC();
    $pc0->updateStatut((int)$_GET['reject_p'], 'annulé');
    $redir = $_GET; unset($redir['reject_p']); $redir['part_rejected'] = '1';
    header('Location: liste_event.php?' . http_build_query($redir)); exit();
}
if (isset($_GET['delete_p']) && (string)$_GET['delete_p'] !== '') {
    $pc0 = new ParticipationC();
    $pc0->deleteParticipation($_GET['delete_p']);
    $redir = $_GET; unset($redir['delete_p']); $redir['part_deleted'] = '1';
    header('Location: liste_event.php?' . http_build_query($redir)); exit();
}

$eAllowed = ['id_event','titre_event','description_event','type_event','date_debut','date_fin','lieu_event','capacite_max','statut','created_at'];
$esort = (string)($_GET['esort'] ?? 'id_event');
$edir  = (string)($_GET['edir']  ?? 'asc');
if (!in_array($esort, $eAllowed, true)) $esort = 'id_event';
$edir = strtoupper($edir) === 'DESC' ? 'desc' : 'asc';

$pAllowed = ['id_participation','titre_event','nom','prenom','email','telephone','date_inscription','statut','id_event'];
$psort = (string)($_GET['psort'] ?? 'date_inscription');
$pdir  = (string)($_GET['pdir']  ?? 'desc');
if (!in_array($psort, $pAllowed, true)) $psort = 'date_inscription';
$pdir = strtoupper($pdir) === 'ASC' ? 'asc' : 'desc';

$eventC         = new EventC();
$liste          = $eventC->listeEvent($esort, $edir);
$pc             = new ParticipationC();
$participations = $pc->listeParticipation($psort, $pdir);
$nbEnAttente    = $pc->countEnAttente();
$presenceC      = new PresenceC();

$baseFlash = [];
foreach (['added','deleted','updated','part_ok','part_updated','part_deleted','part_accepted','part_rejected'] as $fk) {
    if (isset($_GET[$fk]) && (string)$_GET[$fk] === '1') $baseFlash[$fk] = '1';
}

$mkQs = static function (array $over = []) use ($baseFlash, $esort, $edir, $psort, $pdir) {
    return htmlspecialchars(http_build_query(array_merge($baseFlash,['esort'=>$esort,'edir'=>$edir,'psort'=>$psort,'pdir'=>$pdir],$over)), ENT_QUOTES,'UTF-8');
};
$sortUrlE  = static fn($c) => 'liste_event.php?'.$mkQs(['esort'=>$c,'edir'=>(strtolower($esort)===$c&&$edir==='asc')?'desc':'asc']);
$sortUrlP  = static fn($c) => 'liste_event.php?'.$mkQs(['psort'=>$c,'pdir'=>(strtolower($psort)===$c&&$pdir==='asc')?'desc':'asc']);
$sortMarkE = static fn($c) => strtolower($esort)===$c?($edir==='asc'?' ↑':' ↓'):'';
$sortMarkP = static fn($c) => strtolower($psort)===$c?($pdir==='asc'?' ↑':' ↓'):'';

$exportEventsHref = 'exportEventsPdf.php?'.htmlspecialchars(http_build_query(['sort'=>$esort,'dir'=>$edir]),ENT_QUOTES,'UTF-8');
$exportPartHref   = 'exportParticipationsPdf.php?'.htmlspecialchars(http_build_query(['sort'=>$psort,'dir'=>$pdir]),ENT_QUOTES,'UTF-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Événements & participations</title>
<link rel="stylesheet" href="<?= htmlspecialchars(bo_url('commerce.css')) ?>">
<script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>
<style>
.table-modern thead th a{color:inherit;text-decoration:none}
.table-modern thead th a:hover{text-decoration:underline}
.badge-statut{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.78rem;font-weight:600;white-space:nowrap}
.badge-confirme{background:#d1fae5;color:#065f46}
.badge-attente{background:#fef3c7;color:#92400e}
.badge-annule{background:#fee2e2;color:#991b1b}
.badge-default{background:#e5e7eb;color:#374151}
.btn-accept,.btn-reject{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:6px;border:none;font-size:1rem;cursor:pointer;text-decoration:none;transition:transform .1s}
.btn-accept{background:#d1fae5;color:#065f46;border:1.5px solid #6ee7b7}
.btn-accept:hover{background:#6ee7b7;transform:scale(1.12)}
.btn-reject{background:#fee2e2;color:#991b1b;border:1.5px solid #fca5a5}
.btn-reject:hover{background:#fca5a5;transform:scale(1.12)}
.action-group{display:flex;gap:4px;align-items:center;flex-wrap:wrap}
.btn-scanner{background:#6c4daf;color:#fff;border:none;padding:6px 12px;border-radius:6px;font-size:.82rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:4px;transition:background .2s}
.btn-scanner:hover{background:#5a3d9a}
.btn-presence{background:#1a2560;color:#a5b4fc;border:1.5px solid #6c4daf;padding:6px 12px;border-radius:6px;font-size:.82rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:4px;transition:background .2s}
.btn-presence:hover{background:#0f173c;color:#fff}
.nb-badge{background:#6c4daf;color:#fff;padding:1px 7px;border-radius:12px;font-size:.72rem;margin-left:2px}

/* ═══════════ MODAL ═══════════ */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(8,10,30,.82);backdrop-filter:blur(5px);z-index:9000;align-items:center;justify-content:center}
.modal-overlay.active{display:flex}
.modal-box{background:#0f173c;border:1.5px solid #6c4daf55;border-radius:18px;width:100%;max-width:460px;box-shadow:0 28px 70px rgba(0,0,0,.75),0 0 0 1px #6c4daf22;overflow:hidden;animation:mIn .28s cubic-bezier(.22,.68,0,1.2)}
@keyframes mIn{from{opacity:0;transform:scale(.9) translateY(24px)}to{opacity:1;transform:scale(1) translateY(0)}}

/* Header */
.m-head{background:linear-gradient(135deg,#1a2560 0%,#0f173c 100%);border-bottom:1px solid #6c4daf33;padding:16px 20px;display:flex;align-items:center;justify-content:space-between}
.m-head-left h3{color:#fff;font-size:.95rem;margin:0;display:flex;align-items:center;gap:7px}
.m-head-left p{color:#a5b4fc;font-size:.75rem;margin:3px 0 0}
.m-close{background:#ffffff0f;border:1px solid #6c4daf44;color:#a0a0c0;width:30px;height:30px;border-radius:8px;font-size:1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .2s,color .2s}
.m-close:hover{background:#ef4444;color:#fff;border-color:#ef4444}

/* Body */
.m-body{padding:18px 20px}
.m-badge-ok  {display:inline-block;background:#065f4620;color:#6ee7b7;border:1px solid #6ee7b733;padding:3px 12px;border-radius:20px;font-size:.72rem;font-weight:700;margin-bottom:12px}
.m-badge-err {display:inline-block;background:#7f1d1d20;color:#fca5a5;border:1px solid #fca5a533;padding:3px 12px;border-radius:20px;font-size:.72rem;font-weight:700;margin-bottom:12px}

/* Vidéo */
.vid-wrap{position:relative;border-radius:12px;overflow:hidden;border:2px solid #6c4daf;box-shadow:0 0 28px #6c4daf22;background:#000;line-height:0}
#m-video{width:100%;max-height:260px;object-fit:cover;display:block}
.scan-bar{position:absolute;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,#00d4ff,transparent);animation:sb 2s linear infinite}
@keyframes sb{0%{top:0}100%{top:100%}}

/* Résultat */
#m-result{margin-top:12px;padding:14px 16px;border-radius:10px;font-weight:700;text-align:center;display:none;animation:fi .25s ease}
@keyframes fi{from{opacity:0;transform:scale(.96)}to{opacity:1;transform:scale(1)}}
.r-ok  {background:#065f4628;color:#6ee7b7;border:1.5px solid #6ee7b733}
.r-err {background:#7f1d1d28;color:#fca5a5;border:1.5px solid #fca5a533}
.r-warn{background:#78350f28;color:#fcd34d;border:1.5px solid #fcd34d33}
.r-icon{font-size:1.6rem;margin-bottom:5px}
.r-sub {font-size:.78rem;opacity:.8;margin-top:4px;font-weight:400}

/* Inactif */
.m-inactive{padding:28px 20px;text-align:center}
.m-inactive .big{font-size:2.8rem;margin-bottom:10px}
.m-inactive h4{color:#fca5a5;margin:0 0 8px}
.m-inactive p{color:#a0a0c0;font-size:.82rem;line-height:1.6}
.m-inactive strong{color:#a5b4fc}

/* Footer */
.m-foot{padding:12px 20px;border-top:1px solid #6c4daf1a;display:flex;justify-content:space-between;align-items:center}
.m-foot a{color:#a5b4fc;font-size:.8rem;text-decoration:none;display:flex;align-items:center;gap:4px}
.m-foot a:hover{color:#fff}
.m-foot-hint{color:#6c4daf88;font-size:.72rem}

/* Toast */
#win-toast{position:fixed;bottom:20px;right:20px;z-index:9999;width:360px;background:#1e1e2e;color:#e0e0f0;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,.55);display:flex;flex-direction:column;overflow:hidden;animation:slideUp .4s cubic-bezier(.22,.68,0,1.2) both}
@keyframes slideUp{from{opacity:0;transform:translateY(60px) scale(.96)}to{opacity:1;transform:translateY(0) scale(1)}}
#win-toast.hiding{animation:slideDown .3s ease-in forwards}
@keyframes slideDown{to{opacity:0;transform:translateY(60px) scale(.96)}}
.wt-bar{height:4px;background:linear-gradient(90deg,#f59e0b,#ef4444)}
.wt-body{display:flex;align-items:flex-start;gap:14px;padding:14px 16px 12px}
.wt-icon{font-size:2rem;line-height:1;flex-shrink:0;animation:pulse 1.8s ease-in-out infinite}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.15)}}
.wt-text{flex:1}
.wt-title{font-weight:700;font-size:.95rem;color:#f59e0b;margin-bottom:3px}
.wt-msg{font-size:.82rem;color:#a5b4fc;line-height:1.45}
.wt-count{display:inline-block;background:#ef4444;color:#fff;font-weight:800;padding:1px 8px;border-radius:12px;font-size:.82rem;margin-left:4px}
.wt-actions{display:flex;gap:8px;padding:0 16px 14px;justify-content:flex-end}
.wt-btn{padding:5px 16px;border-radius:6px;border:none;font-size:.8rem;font-weight:600;cursor:pointer}
.wt-btn-view{background:#f59e0b;color:#1e1e2e}
.wt-btn-close{background:#374151;color:#e0e0f0}
.wt-close-x{position:absolute;top:10px;right:10px;background:none;border:none;color:#9ca3af;font-size:1rem;cursor:pointer}
.wt-close-x:hover{color:#fff}
</style>
</head>
=======
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

>>>>>>> 2e69571f221ab3f69eb82da93a61caf94ba48839
<body>
<?php require_once __DIR__ . '/../_layout/sidebar.php'; ?>

<div class="content commerce-page">
<<<<<<< HEAD
<div class="container">

<?php
$flashMap=['added'=>'Événement ajouté.','deleted'=>'Événement supprimé.','updated'=>'Événement mis à jour.','part_ok'=>'Participation ajoutée.','part_updated'=>'Participation enregistrée.','part_deleted'=>'Participation supprimée.','part_accepted'=>'✅ Participation confirmée.','part_rejected'=>'❌ Participation annulée.'];
foreach($flashMap as $k=>$m): if(isset($_GET[$k])&&$_GET[$k]==='1'): ?>
<div class="alert alert-success" style="padding:12px 16px;border-radius:8px;margin-bottom:16px;"><?=htmlspecialchars($m)?></div>
<?php endif;endforeach;?>

<!-- EVENTS TABLE -->
<div class="topbar">
    <div class="page-title">Liste des événements</div>
    <div class="actions">
        <input class="search-input" placeholder="Rechercher un événement…" id="searchEventInput">
        <a href="<?=$exportEventsHref?>" class="btn btn-secondary">Export PDF</a>
        <a href="ajout_event.php" class="btn btn-primary">+ Événement</a>
    </div>
</div>

<table class="table-modern" id="eventTable">
    <thead><tr>
        <th><a href="<?=$sortUrlE('id_event')?>">Id</a><?=$sortMarkE('id_event')?></th>
        <th><a href="<?=$sortUrlE('titre_event')?>">Titre</a><?=$sortMarkE('titre_event')?></th>
        <th><a href="<?=$sortUrlE('description_event')?>">Description</a><?=$sortMarkE('description_event')?></th>
        <th><a href="<?=$sortUrlE('type_event')?>">Type</a><?=$sortMarkE('type_event')?></th>
        <th><a href="<?=$sortUrlE('date_debut')?>">Date début</a><?=$sortMarkE('date_debut')?></th>
        <th><a href="<?=$sortUrlE('date_fin')?>">Date fin</a><?=$sortMarkE('date_fin')?></th>
        <th><a href="<?=$sortUrlE('lieu_event')?>">Lieu</a><?=$sortMarkE('lieu_event')?></th>
        <th><a href="<?=$sortUrlE('capacite_max')?>">Capacité</a><?=$sortMarkE('capacite_max')?></th>
        <th><a href="<?=$sortUrlE('statut')?>">Statut</a><?=$sortMarkE('statut')?></th>
        <th>Actions</th>
    </tr></thead>
    <tbody>
    <?php foreach($liste as $event):
        $today    = date('Y-m-d');
        $dDebut   = substr((string)$event['date_debut'],0,10);
        $dFin     = substr((string)$event['date_fin'],0,10);
        $isActive = ($today>=$dDebut && $today<=$dFin);
        $nbP      = $presenceC->countPresences((int)$event['id_event']);
    ?>
    <tr>
        <td><?=htmlspecialchars((string)$event['id_event'])?></td>
        <td><?=htmlspecialchars((string)$event['titre_event'])?></td>
        <td><?=htmlspecialchars(mb_strimwidth((string)$event['description_event'],0,80,'…'))?></td>
        <td><?=htmlspecialchars((string)$event['type_event'])?></td>
        <td><?=htmlspecialchars((string)$event['date_debut'])?></td>
        <td><?=htmlspecialchars((string)$event['date_fin'])?></td>
        <td><?=htmlspecialchars((string)$event['lieu_event'])?></td>
        <td><?=htmlspecialchars((string)$event['capacite_max'])?></td>
        <td><?=htmlspecialchars((string)$event['statut'])?></td>
        <td>
            <div class="action-group">
                <a href="supprimer_event.php?id=<?=(int)$event['id_event']?>&<?=$mkQs()?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Supprimer cet événement ?')">Supprimer</a>
                <a href="modifier_event.php?id=<?=(int)$event['id_event']?>&<?=$mkQs()?>"
                   class="btn btn-secondary btn-sm">Modifier</a>
                <button class="btn-scanner" onclick="openScanner(
                    <?=(int)$event['id_event']?>,
                    '<?=htmlspecialchars(addslashes((string)$event['titre_event']),ENT_QUOTES)?>',
                    '<?=$dDebut?>','<?=$dFin?>',
                    <?=$isActive?'true':'false'?>)">
                    📷 Scanner
                </button>
                <a href="presence_list.php?id_event=<?=(int)$event['id_event']?>" class="btn-presence">
                    📋 Présences<?php if($nbP>0):?><span class="nb-badge"><?=$nbP?></span><?php endif;?>
                </a>
            </div>
        </td>
    </tr>
    <?php endforeach;?>
    </tbody>
</table>

<!-- PARTICIPATIONS TABLE -->
<div class="topbar" style="margin-top:48px;">
    <div class="page-title">Participations</div>
    <div class="actions">
        <input class="search-input" placeholder="Rechercher…" id="searchPartInput">
        <a href="<?=$exportPartHref?>" class="btn btn-secondary">Export PDF</a>
    </div>
</div>

<?php if(empty($participations)):?>
<p style="text-align:center;color:#888;padding:20px;">Aucune participation enregistrée.</p>
<?php else:?>
<table class="table-modern" id="partTable">
    <thead><tr>
        <th><a href="<?=$sortUrlP('id_participation')?>">Id</a><?=$sortMarkP('id_participation')?></th>
        <th><a href="<?=$sortUrlP('titre_event')?>">Événement</a><?=$sortMarkP('titre_event')?></th>
        <th><a href="<?=$sortUrlP('nom')?>">Nom</a><?=$sortMarkP('nom')?></th>
        <th><a href="<?=$sortUrlP('prenom')?>">Prénom</a><?=$sortMarkP('prenom')?></th>
        <th><a href="<?=$sortUrlP('email')?>">Email</a><?=$sortMarkP('email')?></th>
        <th><a href="<?=$sortUrlP('telephone')?>">Téléphone</a><?=$sortMarkP('telephone')?></th>
        <th><a href="<?=$sortUrlP('date_inscription')?>">Date</a><?=$sortMarkP('date_inscription')?></th>
        <th><a href="<?=$sortUrlP('statut')?>">Statut</a><?=$sortMarkP('statut')?></th>
        <th>Actions</th>
    </tr></thead>
    <tbody>
    <?php foreach($participations as $p):
        $st=(string)$p['statut'];
        $bc=match($st){'confirmé'=>'badge-confirme','en attente'=>'badge-attente','annulé'=>'badge-annule',default=>'badge-default'};
        $pid=(int)$p['id_participation'];
    ?>
    <tr>
        <td><?=htmlspecialchars((string)$p['id_participation'])?></td>
        <td><?=htmlspecialchars((string)$p['titre_event'])?></td>
        <td><?=htmlspecialchars((string)$p['nom'])?></td>
        <td><?=htmlspecialchars((string)$p['prenom'])?></td>
        <td><?=htmlspecialchars((string)$p['email'])?></td>
        <td><?=htmlspecialchars((string)$p['telephone'])?></td>
        <td><?=htmlspecialchars((string)$p['date_inscription'])?></td>
        <td><span class="badge-statut <?=$bc?>"><?=htmlspecialchars($st)?></span></td>
        <td>
            <div class="action-group">
            <?php if($st==='en attente'):?>
                <a href="liste_event.php?accept_p=<?=$pid?>&<?=$mkQs()?>" class="btn-accept" title="Confirmer" onclick="return confirm('Confirmer ?')">✓</a>
                <a href="liste_event.php?reject_p=<?=$pid?>&<?=$mkQs()?>"  class="btn-reject" title="Annuler"   onclick="return confirm('Annuler ?')">✕</a>
            <?php endif;?>
            </div>
        </td>
    </tr>
    <?php endforeach;?>
    </tbody>
</table>
<?php endif;?>

</div><!-- /container -->
</div><!-- /content -->

<!-- ═══════════════════ MODAL SCANNER ═══════════════════ -->
<div class="modal-overlay" id="scannerModal" onclick="if(event.target===this)closeScanner()">
<div class="modal-box">

    <!-- Header -->
    <div class="m-head">
        <div class="m-head-left">
            <h3>📷 Scanner un ticket</h3>
            <p id="m-titre"></p>
        </div>
        <button class="m-close" onclick="closeScanner()">✕</button>
    </div>

    <!-- Contenu actif -->
    <div id="m-active">
        <div class="m-body">
            <div id="m-badge"></div>
            <div class="vid-wrap">
                <video id="m-video" autoplay muted playsinline></video>
                <div class="scan-bar"></div>
            </div>
            <div id="m-result"></div>
        </div>
        <div class="m-foot">
            <a href="#" id="m-pres-link">📋 Voir les présences</a>
            <span class="m-foot-hint">Pointez le QR code vers la caméra</span>
        </div>
    </div>

    <!-- Contenu inactif -->
    <div id="m-inactive" style="display:none">
        <div class="m-inactive">
            <div class="big">⛔</div>
            <h4>Scan non disponible</h4>
            <p>Le scan est uniquement disponible<br>pendant la période de l'événement.</p>
            <p><strong id="m-dates"></strong></p>
        </div>
        <div class="m-foot">
            <a href="#" id="m-pres-link2">📋 Voir les présences</a>
            <button onclick="closeScanner()" style="background:#374151;color:#e0e0f0;border:none;padding:6px 16px;border-radius:6px;cursor:pointer;font-size:.82rem;">Fermer</button>
        </div>
    </div>

</div>
</div>

<!-- TOAST -->
<?php if($nbEnAttente>0):?>
<div id="win-toast" role="alert">
    <div class="wt-bar"></div>
    <button class="wt-close-x" onclick="closeToast()">✕</button>
    <div class="wt-body">
        <div class="wt-icon">🔔</div>
        <div class="wt-text">
            <div class="wt-title">Action requise</div>
            <div class="wt-msg">Vous avez <span class="wt-count"><?=$nbEnAttente?></span> participation<?=$nbEnAttente>1?'s':''?> en attente.</div>
        </div>
    </div>
    <div class="wt-actions">
        <button class="wt-btn wt-btn-view"  onclick="scrollToParticipations()">Voir maintenant</button>
        <button class="wt-btn wt-btn-close" onclick="closeToast()">Ignorer</button>
    </div>
</div>
<?php endif;?>

<script>
// Recherche
(function(){
    var s1=document.getElementById('searchEventInput');
    if(s1)s1.addEventListener('input',function(){
        var q=this.value.toLowerCase();
        document.querySelectorAll('#eventTable tbody tr').forEach(r=>{
            r.style.display=[...r.cells].some(c=>c.textContent.toLowerCase().includes(q))?'':'none';
        });
    });
    var s2=document.getElementById('searchPartInput');
    if(s2)s2.addEventListener('input',function(){
        var q=this.value.toLowerCase();
        document.querySelectorAll('#partTable tbody tr').forEach(r=>{
            r.style.display=[...r.cells].some(c=>c.textContent.toLowerCase().includes(q))?'':'none';
        });
    });
})();

// Toast
function closeToast(){var t=document.getElementById('win-toast');if(!t)return;t.classList.add('hiding');setTimeout(()=>t.remove(),300);}
function scrollToParticipations(){var el=document.getElementById('partTable');if(el)el.scrollIntoView({behavior:'smooth',block:'start'});closeToast();}
setTimeout(closeToast,12000);

// ═══ MODAL SCANNER ═══
let codeReader=null, scanning=false, lastScan='', currentEventId=0;

function openScanner(idEvent, titre, dateDebut, dateFin, isActive){
    currentEventId=idEvent;
    document.getElementById('m-titre').textContent=titre;
    document.getElementById('m-pres-link').href ='presence_list.php?id_event='+idEvent;
    document.getElementById('m-pres-link2').href='presence_list.php?id_event='+idEvent;

    var mActive=document.getElementById('m-active');
    var mInact =document.getElementById('m-inactive');
    var mResult=document.getElementById('m-result');
    var mBadge =document.getElementById('m-badge');

    mResult.style.display='none'; mResult.className='';

    if(isActive){
        mActive.style.display='block';
        mInact.style.display='none';
        mBadge.innerHTML='<span class="m-badge-ok">✅ Événement actif aujourd\'hui</span>';
        startCamera();
    } else {
        mActive.style.display='none';
        mInact.style.display='block';
        document.getElementById('m-dates').textContent=dateDebut+' → '+dateFin;
    }

    document.getElementById('scannerModal').classList.add('active');
    document.body.style.overflow='hidden';
}

function closeScanner(){
    stopCamera();
    document.getElementById('scannerModal').classList.remove('active');
    document.body.style.overflow='';
}

function startCamera(){
    if(!window.ZXing)return;
    scanning=true; lastScan='';
    codeReader=new ZXing.BrowserQRCodeReader();
    codeReader.decodeFromVideoDevice(null,'m-video',(result,err)=>{
        if(!result||!scanning)return;
        var qr=result.getText();
        if(qr===lastScan)return;
        lastScan=qr; scanning=false;

        fetch('../../../ajax/verify_ticket.php',{
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'qr_data='+encodeURIComponent(qr)+'&id_event='+currentEventId
        })
        .then(r=>r.json())
        .then(data=>{
            var el=document.getElementById('m-result');
            el.style.display='block'; el.className='';
            if(data.ok){
                el.classList.add('r-ok');
                el.innerHTML='<div class="r-icon">✅</div><div>'+data.msg+'</div><div class="r-sub">Présence enregistrée</div>';
            } else if(data.already){
                el.classList.add('r-warn');
                el.innerHTML='<div class="r-icon">⚠️</div><div>'+data.msg+'</div><div class="r-sub">'+(data.nom||'')+'</div>';
            } else {
                el.classList.add('r-err');
                el.innerHTML='<div class="r-icon">❌</div><div>'+data.msg+'</div>';
            }
            setTimeout(()=>{scanning=true;lastScan='';el.style.display='none';},3000);
        })
        .catch(()=>{
            var el=document.getElementById('m-result');
            el.className='r-err'; el.style.display='block';
            el.innerHTML='<div class="r-icon">❌</div><div>Erreur réseau.</div>';
            setTimeout(()=>{scanning=true;lastScan='';el.style.display='none';},3000);
        });
    });
}

function stopCamera(){
    scanning=false;
    if(codeReader){try{codeReader.reset();}catch(e){}codeReader=null;}
}

document.addEventListener('keydown',e=>{if(e.key==='Escape')closeScanner();});
</script>
</body>
</html>
=======
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
                <a href="ajout_participation.php" class="btn btn-primary">+ Participation</a>
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
>>>>>>> 2e69571f221ab3f69eb82da93a61caf94ba48839
