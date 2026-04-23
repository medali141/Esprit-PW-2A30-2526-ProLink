<?php
include '../../model/event.php';
include '../../controller/eventC.php';

if (isset($_POST['titre_event']) && isset($_POST['description_event']) &&
    isset($_POST['type_event'])  && isset($_POST['date_debut']) &&
    isset($_POST['date_fin'])    && isset($_POST['lieu_event']) &&
    isset($_POST['capacite_max'])&& isset($_POST['statut']))
{
    $event1 = new Event(
        $_POST['titre_event'],
        $_POST['description_event'],
        $_POST['type_event'],
        $_POST['date_debut'],
        $_POST['date_fin'],
        $_POST['lieu_event'],
        $_POST['capacite_max'],
        $_POST['statut']
    );

    $eventController = new EventC();

    var_dump($event1);
    $eventController->showEvent($event1);

} else {
    echo "Il y a un champ manquant !!";
}
?>