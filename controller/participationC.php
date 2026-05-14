<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/participation.php';
<<<<<<< HEAD
require_once __DIR__ . '/../config/mailer.php';   // ← ajoute cette ligne

class ParticipationC
{
=======

class ParticipationC
{
    /** Inscriptions comptant pour la capacité (hors annulé). */
>>>>>>> formation
    public function recomputeEventStatut(int $idEvent): void
    {
        $db = Config::getConnexion();
        $stE = $db->prepare('SELECT `capacite_max` FROM `evenement` WHERE `id_event` = :id LIMIT 1');
        $stE->execute(['id' => $idEvent]);
        $rowE = $stE->fetch(PDO::FETCH_ASSOC);
        if (!$rowE) {
            return;
        }
        $max = (int) ($rowE['capacite_max'] ?? 0);
        if ($max < 1) {
            return;
        }
        $stC = $db->prepare(
<<<<<<< HEAD
            "SELECT COUNT(*) AS c FROM `participation` WHERE `id_event` = :id AND `statut` = 'confirmé'"
=======
            "SELECT COUNT(*) AS c FROM `participation` WHERE `id_event` = :id AND `statut` <> 'annulé'"
>>>>>>> formation
        );
        $stC->execute(['id' => $idEvent]);
        $cnt = (int) ($stC->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
        $statut = $cnt >= $max ? 'Complet' : 'Ouvert';
        $u = $db->prepare('UPDATE `evenement` SET `statut` = :s WHERE `id_event` = :id');
        $u->execute(['s' => $statut, 'id' => $idEvent]);
    }

    /** @return list<array<string, mixed>> */
    public function listeEvents(): array
    {
        $db = Config::getConnexion();
        $st = $db->query('SELECT `id_event`, `titre_event` FROM `evenement` ORDER BY `titre_event`');
        $rows = $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
        return is_array($rows) ? $rows : [];
    }

<<<<<<< HEAD
=======
    /** @return list<array<string, mixed>> */
>>>>>>> formation
    public function listeParticipation(string $sort = 'date_inscription', string $order = 'desc'): array
    {
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $byCol = [
            'id_participation' => 'p.`id_participation`',
            'titre_event'      => 'e.`titre_event`',
            'nom'              => 'p.`nom`',
            'prenom'           => 'p.`prenom`',
            'email'            => 'p.`email`',
            'telephone'        => 'p.`telephone`',
            'date_inscription' => 'p.`date_inscription`',
            'statut'           => 'p.`statut`',
            'id_event'         => 'p.`id_event`',
        ];
        if (!isset($byCol[$sort])) {
            $sort = 'date_inscription';
        }
        $ordExpr = $byCol[$sort] ?? 'p.`date_inscription`';
        $db = Config::getConnexion();
        $sql = "SELECT p.*, e.`titre_event`
            FROM `participation` p
            INNER JOIN `evenement` e ON p.`id_event` = e.`id_event`
            ORDER BY {$ordExpr} {$order}";
        $st = $db->query($sql);
        $rows = $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
        return is_array($rows) ? $rows : [];
    }

<<<<<<< HEAD
    /** Count participations with status 'en attente' */
    public function countEnAttente(): int
    {
        $db = Config::getConnexion();
        $st = $db->query("SELECT COUNT(*) AS c FROM `participation` WHERE `statut` = 'en attente'");
        if (!$st) {
            return 0;
        }
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['c'] ?? 0);
    }

    /** Accept or reject a participation by updating its statut */
    public function updateStatut(int $id, string $statut): bool
    {
        $allowed = ['confirmé', 'annulé', 'en attente'];
        if (!in_array($statut, $allowed, true)) {
            return false;
        }
        if ($id < 1) {
            return false;
        }
        $db = Config::getConnexion();

        // Fetch toutes les infos nécessaires (JOIN pour avoir titre_event + dates + lieu pour le ticket)
        $g = $db->prepare('
            SELECT p.`id_participation`, p.`id_event`, p.`email`, p.`nom`, p.`prenom`,
                   e.`titre_event`, e.`date_debut`, e.`date_fin`, e.`lieu_event`
            FROM `participation` p
            INNER JOIN `evenement` e ON p.`id_event` = e.`id_event`
            WHERE p.`id_participation` = :id LIMIT 1
        ');
        $g->execute(['id' => $id]);
        $row = $g->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return false;
        }
        $eid = (int) $row['id_event'];

        $q = $db->prepare('UPDATE `participation` SET `statut` = :st WHERE `id_participation` = :id');
        try {
            $q->execute(['st' => $statut, 'id' => $id]);
        } catch (Exception $e) {
            return false;
        }

        $this->recomputeEventStatut($eid);

        // ✉️ Envoi email + ticket PDF si confirmé
        if (in_array($statut, ['confirmé', 'annulé'], true)) {
            sendParticipationEmail(
                (string) $row['email'],
                trim((string) $row['prenom'] . ' ' . (string) $row['nom']),
                (string) $row['titre_event'],
                $statut,
                $row   // ← données complètes pour générer le ticket
            );
        }

        return true;
    }

=======
    /** @return true|string */
>>>>>>> formation
    public function addParticipation(Participation $p)
    {
        $idEvent = (int) $p->getIdEvent();
        if ($idEvent < 1) {
            return 'Événement invalide.';
        }
        $db = Config::getConnexion();
        $stE = $db->prepare('SELECT `capacite_max`, `statut` FROM `evenement` WHERE `id_event` = :id LIMIT 1');
        $stE->execute(['id' => $idEvent]);
        $ev = $stE->fetch(PDO::FETCH_ASSOC);
        if (!$ev) {
            return 'Événement introuvable.';
        }
        if (($ev['statut'] ?? '') === 'Complet') {
            return 'Cet événement est complet.';
        }
        $stDup = $db->prepare('SELECT 1 FROM `participation` WHERE `id_event` = :eid AND LOWER(`email`) = LOWER(:em) LIMIT 1');
        $stDup->execute(['eid' => $idEvent, 'em' => $p->getEmail()]);
        if ($stDup->fetch()) {
            return 'Cet email est déjà inscrit à cet événement.';
        }
<<<<<<< HEAD
        // ✅ Default statut is always 'en attente'
=======
>>>>>>> formation
        $sql = 'INSERT INTO `participation` (
            `id_event`, `nom`, `prenom`, `email`, `telephone`, `statut`
        ) VALUES (:id_event, :nom, :prenom, :email, :telephone, :statut)';
        try {
            $st = $db->prepare($sql);
            $st->execute([
<<<<<<< HEAD
                'id_event'  => $idEvent,
                'nom'       => $p->getNom(),
                'prenom'    => $p->getPrenom(),
                'email'     => $p->getEmail(),
                'telephone' => $p->getTelephone(),
                'statut'    => 'en attente',   // ← always 'en attente' on creation
=======
                'id_event' => $idEvent,
                'nom' => $p->getNom(),
                'prenom' => $p->getPrenom(),
                'email' => $p->getEmail(),
                'telephone' => $p->getTelephone(),
                'statut' => $p->getStatut(),
>>>>>>> formation
            ]);
        } catch (Exception $e) {
            return 'Erreur : ' . $e->getMessage();
        }
<<<<<<< HEAD
        // Do NOT mark event Complet yet (only confirmed count matters)
=======
        $this->recomputeEventStatut($idEvent);
>>>>>>> formation
        return true;
    }

    public function deleteParticipation($id): void
    {
        $id = (int) $id;
        if ($id < 1) {
            return;
        }
        $db = Config::getConnexion();
        $g = $db->prepare('SELECT `id_event` FROM `participation` WHERE `id_participation` = :id LIMIT 1');
        $g->execute(['id' => $id]);
        $row = $g->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return;
        }
        $eid = (int) $row['id_event'];
        $d = $db->prepare('DELETE FROM `participation` WHERE `id_participation` = :id');
        try {
            $d->execute(['id' => $id]);
        } catch (Exception $e) {
            return;
        }
        $this->recomputeEventStatut($eid);
    }

<<<<<<< HEAD
=======
    /**
     * GET : retourne la participation pour le formulaire.
     * POST : met à jour et redirige.
     * @return array<string, mixed>|null
     */
>>>>>>> formation
    public function updateParticipation($id)
    {
        $id = (int) $id;
        if ($id < 1) {
            header('Location: liste_event.php');
            exit;
        }
        $db = Config::getConnexion();

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
<<<<<<< HEAD
            $nom       = trim((string) ($_POST['nom']       ?? ''));
            $prenom    = trim((string) ($_POST['prenom']    ?? ''));
            $email     = trim((string) ($_POST['email']     ?? ''));
            $telephone = trim((string) ($_POST['telephone'] ?? ''));
            $statut    = (string) ($_POST['statut']  ?? '');
            $idEvent   = (int)   ($_POST['id_event'] ?? 0);
=======
            $nom = trim((string) ($_POST['nom'] ?? ''));
            $prenom = trim((string) ($_POST['prenom'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $telephone = trim((string) ($_POST['telephone'] ?? ''));
            $statut = (string) ($_POST['statut'] ?? '');
            $idEvent = (int) ($_POST['id_event'] ?? 0);
>>>>>>> formation
            if ($nom !== '' && $prenom !== '' && $email !== '' && $telephone !== '' && $statut !== '') {
                $dup = $db->prepare(
                    'SELECT 1 FROM `participation` WHERE `id_event` = :eid AND LOWER(`email`) = LOWER(:em) AND `id_participation` <> :id LIMIT 1'
                );
                $dup->execute(['eid' => $idEvent, 'em' => $email, 'id' => $id]);
                if (!$dup->fetch()) {
                    $q = $db->prepare('UPDATE `participation` SET
                        `nom` = :nom, `prenom` = :prenom, `email` = :email, `telephone` = :tel, `statut` = :st
                        WHERE `id_participation` = :id');
                    try {
                        $q->execute([
<<<<<<< HEAD
                            'id'     => $id,
                            'nom'    => $nom,
                            'prenom' => $prenom,
                            'email'  => $email,
                            'tel'    => $telephone,
                            'st'     => $statut,
=======
                            'id' => $id,
                            'nom' => $nom,
                            'prenom' => $prenom,
                            'email' => $email,
                            'tel' => $telephone,
                            'st' => $statut,
>>>>>>> formation
                        ]);
                    } catch (Exception $e) {
                        // keep form
                    }
                    if ($idEvent > 0) {
                        $this->recomputeEventStatut($idEvent);
                    }
                }
            }
            $tail = self::listeReturnFromPost();
            header('Location: liste_event.php?' . http_build_query(array_merge(['part_updated' => '1'], $tail)));
            exit;
        }

        $st = $db->prepare('SELECT p.*, e.`titre_event` FROM `participation` p
            INNER JOIN `evenement` e ON p.`id_event` = e.`id_event`
            WHERE p.`id_participation` = :id LIMIT 1');
        $st->execute(['id' => $id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            header('Location: liste_event.php');
            exit;
        }
        return $row;
    }

<<<<<<< HEAD
=======
    /** @return array<string, string> */
>>>>>>> formation
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
<<<<<<< HEAD
}
=======
}
>>>>>>> formation
