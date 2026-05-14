<?php
require_once __DIR__ . '/../../../controller/AuthController.php';
require_once __DIR__ . '/../_layout/paths.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$auth = new AuthController();
$user = $auth->profile();
<<<<<<< HEAD
if (!$user || strtolower($user['type'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../../../controller/FormationP.php';
$fp = new FormationP();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$formation = $id ? $fp->get($id) : null;
$ins = $id ? $fp->listInscriptions($id) : [];
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Inscriptions — BackOffice</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(view_web_base()) ?>assets/style.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(bo_url('_layout/sidebar.css')) ?>">
    <link rel="stylesheet" href="formation.css">
</head>
<body>
<?php include __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content formation-page">
    <div class="container">
        <div class="topbar">
            <div>
                <h1 class="page-title">Inscriptions</h1>
                <?php if ($formation): ?>
                    <p class="page-subtitle">
                        Formation :
                        <strong><?= htmlspecialchars((string) $formation['titre']) ?></strong>
                        <?php if (!empty($formation['categorie'])): ?>
                            <span class="badge-cat" style="margin-left:8px"><?= htmlspecialchars((string) $formation['categorie']) ?></span>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
            <div class="actions">
                <a href="liste.php" class="btn btn-secondary">← Retour aux formations</a>
            </div>
        </div>

        <div class="formation-card">
            <?php if (empty($ins)): ?>
                <p style="color:#64748b">Aucune inscription pour cette formation.</p>
            <?php else: ?>
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Participant</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Inscrit le</th>
                            <th style="text-align:right">Certificat</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ins as $r): ?>
                        <?php
                        $idIns = (int) ($r['id_inscription'] ?? 0);
                        $fullName = trim((string) ($r['prenom'] ?? '') . ' ' . (string) ($r['nom'] ?? ''));
                        if ($fullName === '') $fullName = (string) ($r['nom'] ?? '—');
                        $dateIns = (string) ($r['date_inscription'] ?? '');
                        if ($dateIns) {
                            $tsIns = strtotime($dateIns);
                            if ($tsIns) $dateIns = date('d/m/Y H:i', $tsIns);
                        }
                        ?>
                        <tr>
                            <td><?= $idIns ?: '—' ?></td>
                            <td><strong><?= htmlspecialchars($fullName) ?></strong></td>
                            <td><?= htmlspecialchars((string) ($r['email'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string) ($r['telephone'] ?? '')) ?: '<span style="color:#94a3b8">—</span>' ?></td>
                            <td><?= htmlspecialchars($dateIns) ?: '<span style="color:#94a3b8">—</span>' ?></td>
                            <td style="text-align:right;white-space:nowrap">
                                <?php if ($idIns > 0): ?>
                                    <a class="btn btn-primary"
                                       href="certificat.php?id_inscription=<?= $idIns ?>"
                                       target="_blank" rel="noopener">
                                        📄 Télécharger
                                    </a>
                                <?php else: ?>
                                    <span style="color:#94a3b8">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
=======
if (!$user || strtolower($user['type'] ?? '') !== 'admin') { header('Location: ../login.php'); exit; }

require_once __DIR__ . '/../../../controller/FormationP.php';
$fp = new FormationP();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$ins = $id ? $fp->listInscriptions($id) : [];
?>
<?php include __DIR__ . '/../_layout/sidebar.php'; ?>
<div class="content"><div class="container">
    <h2>Inscriptions</h2>
    <?php if (empty($ins)): ?>
        <p>Aucune inscription pour cette formation.</p>
    <?php else: ?>
        <table class="table-modern"><thead><tr><th>Nom</th><th>Email</th><th>Date</th></tr></thead><tbody>
        <?php foreach ($ins as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['nom'] ?? ($r['prenom'] . ' ' . $r['nom'] ?? '')) ?></td>
                <td><?= htmlspecialchars($r['email'] ?? '') ?></td>
                <td><?= htmlspecialchars($r['date_inscription'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table>
    <?php endif; ?>
</div></div>
>>>>>>> formation
