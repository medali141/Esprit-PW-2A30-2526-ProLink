<?php
require_once __DIR__ . '/../config.php';

class EventC {

    public function showEvent($event) {
        $event->show();
    }

    public function listeEvent() {
        $db = config::getConnexion();
        try {
            $liste = $db->query("SELECT * FROM event");
            return $liste->fetchAll();
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    public function deleteEvent($id) {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('DELETE FROM event WHERE id_event = :id');
            $req->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function addEvent($event) {
        $db = config::getConnexion();
        try {
            $req = $db->prepare(
                'INSERT INTO event (titre_event, description_event, type_event, date_debut, date_fin, lieu_event, capacite_max, statut)
                 VALUES (:titre_event, :description_event, :type_event, :date_debut, :date_fin, :lieu_event, :capacite_max, :statut)'
            );
            $req->execute([
                'titre_event'       => $event->getTitreEvent(),
                'description_event' => $event->getDescriptionEvent(),
                'type_event'        => $event->getTypeEvent(),
                'date_debut'        => $event->getDateDebut(),
                'date_fin'          => $event->getDateFin(),
                'lieu_event'        => $event->getLieuEvent(),
                'capacite_max'      => $event->getCapaciteMax(),
                'statut'            => $event->getStatut()
            ]);
            return true;
        } catch (Exception $e) {
            return $e->getMessage(); // retourne le message d'erreur pour l'afficher
        }
    }

    public function getEvent($id) {
        $db = config::getConnexion();
        try {
            $req = $db->prepare('SELECT * FROM event WHERE id_event = :id');
            $req->execute(['id' => $id]);
            return $req->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function updateEvent($id) {
        $db = config::getConnexion();

        $event = $this->getEvent($id);
        if (!$event) {
            die("Événement introuvable.");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $req = $db->prepare(
                    'UPDATE event SET
                        titre_event       = :titre_event,
                        description_event = :description_event,
                        type_event        = :type_event,
                        date_debut        = :date_debut,
                        date_fin          = :date_fin,
                        lieu_event        = :lieu_event,
                        capacite_max      = :capacite_max,
                        statut            = :statut
                     WHERE id_event = :id'
                );
                $req->execute([
                    'titre_event'       => $_POST['titre_event'],
                    'description_event' => $_POST['description_event'],
                    'type_event'        => $_POST['type_event'],
                    'date_debut'        => $_POST['date_debut'],
                    'date_fin'          => $_POST['date_fin'],
                    'lieu_event'        => $_POST['lieu_event'],
                    'capacite_max'      => $_POST['capacite_max'],
                    'statut'            => $_POST['statut'],
                    'id'                => $id
                ]);
                header('Location: liste_event.php');
                exit();
            } catch (PDOException $e) {
                die('Erreur PDO: ' . $e->getMessage());
            }
        }

        return $event;
    }
}
?>