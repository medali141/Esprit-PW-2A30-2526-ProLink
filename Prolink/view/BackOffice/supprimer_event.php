<?php 
include "../../controller/eventC.php";  
include "../../config.php";  

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $eventC = new EventC();
    $eventC->deleteEvent($_GET['id']);
    header('Location: liste_event.php');
    exit();
} else {
    header('Location: liste_event.php');
    exit();
}
?>