<?php
require_once '../../controller/FormationController.php';

$id = $_GET['id'];
$formationC = new FormationController();

if($formationC->supprimer($id)) {
    header('Location: liste.php?success=1');
} else {
    header('Location: liste.php?error=1');
}
exit();
?>