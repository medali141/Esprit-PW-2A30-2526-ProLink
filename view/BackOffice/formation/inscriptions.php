<?php
require_once __DIR__ . '/../../../controller/AuthController.php';
require_once __DIR__ . '/../_layout/paths.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$auth = new AuthController();
$user = $auth->profile();
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
