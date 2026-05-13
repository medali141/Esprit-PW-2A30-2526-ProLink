<?php
if (!defined('APP_INIT')) {
    require_once dirname(__DIR__, 3) . '/init.php';
}
$__fb = htmlspecialchars((string) ($baseUrl ?? ''), ENT_QUOTES, 'UTF-8');
?>
<footer class="footer">
    <div class="footer__inner">
        <ul class="footer__links" aria-label="Liens utiles">
            <li><a href="<?= $__fb ?>/FrontOffice/catalogue.php">Boutique</a></li>
            <li><a href="<?= $__fb ?>/FrontOffice/panier.php">Panier</a></li>
            <li><a href="<?= $__fb ?>/FrontOffice/mesCommandes.php">Mes commandes</a></li>
            <li><a href="<?= $__fb ?>/FrontOffice/reclamationsCommandes.php">Réclamations</a></li>
            <li><a href="<?= $__fb ?>/FrontOffice/messagesAdmin.php">Contact service clients</a></li>
            <li><a href="<?= $__fb ?>/FrontOffice/mesCommandesStats.php">Statistiques achats</a></li>
        </ul>
        <p>© <?= (int) date('Y') ?> ProLink — Commerce &amp; réseau professionnel.</p>
    </div>
</footer>
