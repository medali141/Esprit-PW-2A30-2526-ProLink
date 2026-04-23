<?php
require_once __DIR__ . '/../config.php';

class ParticipationC {

    // Liste toutes les participations avec jointure event
    public function listeParticipation() {
        $db = config::getConnexion();
        $sql = "SELECT p.*, e.titre_event, e.date_debut, e.lieu_event
                FROM participation p
                JOIN event e ON p.id_event = e.id_event
                ORDER BY p.date_inscription DESC";
        return $db->query($sql)->fetchAll();
    }

    // Participations d'un event précis
    public function participationsByEvent($id_event) {
        $db = config::getConnexion();
        $req = $db->prepare(
            "SELECT p.*, e.titre_event FROM participation p
             JOIN event e ON p.id_event = e.id_event
             WHERE p.id_event = :id"
        );
        $req->execute(['id' => $id_event]);
        return $req->fetchAll();
    }

    // Ajouter une participation
    public function addParticipation($p) {
        $db = config::getConnexion();
        try {
            // Vérifier doublon email/event
            $check = $db->prepare(
                "SELECT COUNT(*) FROM participation WHERE id_event = :id_event AND email = :email"
            );
            $check->execute(['id_event' => $p->getIdEvent(), 'email' => $p->getEmail()]);
            if ($check->fetchColumn() > 0) {
                return "Cet email est déjà inscrit à cet événement.";
            }

            // Vérifier capacité
            $cap = $db->prepare(
                "SELECT e.capacite_max,
                        COUNT(p.id_participation) AS inscrits
                 FROM event e
                 LEFT JOIN participation p ON p.id_event = e.id_event
                 WHERE e.id_event = :id
                 GROUP BY e.id_event"
            );
            $cap->execute(['id' => $p->getIdEvent()]);
            $row = $cap->fetch();
            if ($row && $row['inscrits'] >= $row['capacite_max']) {
                return "Capacité maximale atteinte pour cet événement.";
            }

            $req = $db->prepare(
                "INSERT INTO participation (id_event, nom, prenom, email, telephone, statut)
                 VALUES (:id_event, :nom, :prenom, :email, :telephone, :statut)"
            );
            $req->execute([
                'id_event'  => $p->getIdEvent(),
                'nom'       => $p->getNom(),
                'prenom'    => $p->getPrenom(),
                'email'     => $p->getEmail(),
                'telephone' => $p->getTelephone(),
                'statut'    => $p->getStatut()
            ]);
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    // Récupérer une participation
    public function getParticipation($id) {
        $db = config::getConnexion();
        $req = $db->prepare(
            "SELECT p.*, e.titre_event FROM participation p
             JOIN event e ON p.id_event = e.id_event
             WHERE p.id_participation = :id"
        );
        $req->execute(['id' => $id]);
        return $req->fetch();
    }

    // Modifier une participation
    public function updateParticipation($id) {
        $db = config::getConnexion();
        $participation = $this->getParticipation($id);
        if (!$participation) die("Participation introuvable.");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $req = $db->prepare(
                    "UPDATE participation SET
                        nom       = :nom,
                        prenom    = :prenom,
                        email     = :email,
                        telephone = :telephone,
                        statut    = :statut
                     WHERE id_participation = :id"
                );
                $req->execute([
                    'nom'       => $_POST['nom'],
                    'prenom'    => $_POST['prenom'],
                    'email'     => $_POST['email'],
                    'telephone' => $_POST['telephone'],
                    'statut'    => $_POST['statut'],
                    'id'        => $id
                ]);
                header('Location: liste_event.php');
                exit();
            } catch (PDOException $e) {
                die('Erreur PDO: ' . $e->getMessage());
            }
        }
        return $participation;
    }

    // Supprimer une participation
    public function deleteParticipation($id) {
        $db = config::getConnexion();
        $req = $db->prepare("DELETE FROM participation WHERE id_participation = :id");
        $req->execute(['id' => $id]);
    }

    // Liste des events disponibles (pour les selects)
    public function listeEvents() {
        $db = config::getConnexion();
        return $db->query("SELECT id_event, titre_event FROM event WHERE statut != 'annule'")->fetchAll();
    }
}
?>