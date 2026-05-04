<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/event.php';
require_once __DIR__ . '/participationC.php';

class EventC
{
    /** @return list<array<string, mixed>> */
    public function listeEvent(string $sort = 'id_event', string $order = 'asc'): array
    {
        $allowed = [
            'id_event', 'titre_event', 'description_event', 'type_event', 'date_debut', 'date_fin',
            'lieu_event', 'capacite_max', 'statut', 'created_at',
        ];
        if (!in_array($sort, $allowed, true)) {
            $sort = 'id_event';
        }
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $db = Config::getConnexion();
        $col = in_array($sort, $allowed, true) ? $sort : 'id_event';
        $sql = 'SELECT * FROM `evenement` ORDER BY `' . $col . '` ' . $order;
        $st = $db->query($sql);
        $rows = $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
        return is_array($rows) ? $rows : [];
    }

    /** @return true|string */
    public function addEvent(Event $event)
    {
        $db = Config::getConnexion();
        $sql = 'INSERT INTO `evenement` (
            `titre_event`, `description_event`, `type_event`, `date_debut`, `date_fin`,
            `lieu_event`, `capacite_max`, `statut`
        ) VALUES (
            :titre, :description, :type, :d1, :d2, :lieu, :cap, :statut
        )';
        try {
            $st = $db->prepare($sql);
            $st->execute([
                'titre' => $event->getTitreEvent(),
                'description' => $event->getDescriptionEvent(),
                'type' => $event->getTypeEvent(),
                'd1' => $event->getDateDebut(),
                'd2' => $event->getDateFin(),
                'lieu' => $event->getLieuEvent(),
                'cap' => (int) $event->getCapaciteMax(),
                'statut' => 'Ouvert',
            ]);
        } catch (Exception $e) {
            return 'Erreur : ' . $e->getMessage();
        }
        return true;
    }

    /** @return array<string, mixed>|false */
    public function getEvent($id)
    {
        $id = (int) $id;
        if ($id < 1) {
            return false;
        }
        $db = Config::getConnexion();
        $st = $db->prepare('SELECT * FROM `evenement` WHERE `id_event` = :id LIMIT 1');
        $st->execute(['id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: false;
    }

    public function updateEvent($id): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            return;
        }
        $id = (int) $id;
        if ($id < 1) {
            return;
        }
        $titre = trim((string) ($_POST['titre_event'] ?? ''));
        $description = (string) ($_POST['description_event'] ?? '');
        $type = (string) ($_POST['type_event'] ?? '');
        $d1 = (string) ($_POST['date_debut'] ?? '');
        $d2 = (string) ($_POST['date_fin'] ?? '');
        $lieu = (string) ($_POST['lieu_event'] ?? '');
        $cap = (int) ($_POST['capacite_max'] ?? 0);

        if ($titre === '' || $d1 === '' || $d2 === '' || $lieu === '' || $type === '' || $cap < 1) {
            return;
        }

        $db = Config::getConnexion();
        $sql = 'UPDATE `evenement` SET
            `titre_event` = :titre,
            `description_event` = :description,
            `type_event` = :type,
            `date_debut` = :d1,
            `date_fin` = :d2,
            `lieu_event` = :lieu,
            `capacite_max` = :cap
            WHERE `id_event` = :id';
        try {
            $st = $db->prepare($sql);
            $st->execute([
                'id' => $id,
                'titre' => $titre,
                'description' => $description,
                'type' => $type,
                'd1' => $d1,
                'd2' => $d2,
                'lieu' => $lieu,
                'cap' => $cap,
            ]);
        } catch (Exception $e) {
            return;
        }
        (new ParticipationC())->recomputeEventStatut($id);
        $tail = self::listeReturnFromPost();
        header('Location: liste_event.php?' . http_build_query(array_merge(['updated' => '1'], $tail)));
        exit;
    }

    /** @return array<string, string> */
    private static function listeReturnFromPost(): array
    {
        $eAllowed = ['id_event', 'titre_event', 'description_event', 'type_event', 'date_debut', 'date_fin', 'lieu_event', 'capacite_max', 'statut', 'created_at'];
        $pAllowed = ['id_participation', 'titre_event', 'nom', 'prenom', 'email', 'telephone', 'date_inscription', 'statut', 'id_event'];
        $q = [];
        if (isset($_POST['bo_esort']) && in_array((string) $_POST['bo_esort'], $eAllowed, true)) {
            $q['esort'] = (string) $_POST['bo_esort'];
        }
        if (isset($_POST['bo_edir']) && in_array(strtolower((string) $_POST['bo_edir']), ['asc', 'desc'], true)) {
            $q['edir'] = strtolower((string) $_POST['bo_edir']);
        }
        if (isset($_POST['bo_psort']) && in_array((string) $_POST['bo_psort'], $pAllowed, true)) {
            $q['psort'] = (string) $_POST['bo_psort'];
        }
        if (isset($_POST['bo_pdir']) && in_array(strtolower((string) $_POST['bo_pdir']), ['asc', 'desc'], true)) {
            $q['pdir'] = strtolower((string) $_POST['bo_pdir']);
        }
        return $q;
    }

    public function deleteEvent($id): void
    {
        $id = (int) $id;
        if ($id < 1) {
            return;
        }
        $db = Config::getConnexion();
        $st = $db->prepare('DELETE FROM `evenement` WHERE `id_event` = :id');
        try {
            $st->execute(['id' => $id]);
        } catch (Exception $e) {
            // leave quietly
        }
    }

    /**
     * Front office : événements dont la date de fin n’est pas passée, avec effectif d’inscrits.
     * @return list<array<string, mixed>>
     */
    public function listeEvenementsPublic(): array
    {
        $db = Config::getConnexion();
        $sql = 'SELECT e.*, (
            SELECT COUNT(*) FROM `participation` p
            WHERE p.`id_event` = e.`id_event` AND p.`statut` <> \'annulé\'
        ) AS inscrits
        FROM `evenement` e
        WHERE e.`date_fin` >= CURDATE()
        ORDER BY e.`date_debut` ASC, e.`id_event` ASC';
        $st = $db->query($sql);
        $rows = $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
        return is_array($rows) ? $rows : [];
    }

    /**
     * Front office : détail si la date de fin de l’événement n’est pas passée.
     * @return array<string, mixed>|false
     */
    public function getEventPublic(int $id)
    {
        if ($id < 1) {
            return false;
        }
        $row = $this->getEvent($id);
        if ($row === false) {
            return false;
        }
        $end = (string) ($row['date_fin'] ?? '');
        if ($end === '' || strtotime($end) < strtotime('today')) {
            return false;
        }
        $db = Config::getConnexion();
        $c = $db->prepare("SELECT COUNT(*) AS c FROM `participation` WHERE `id_event` = :id AND `statut` <> 'annulé'");
        $c->execute(['id' => $id]);
        $cnt = (int) ($c->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
        $row['inscrits'] = $cnt;
        return $row;
    }
}
