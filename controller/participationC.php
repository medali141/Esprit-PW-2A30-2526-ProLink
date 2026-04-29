<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/participation.php';

class ParticipationC
{
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
            "SELECT COUNT(*) AS c FROM `participation` WHERE `id_event` = :id AND `statut` <> 'annulé'"
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
        $sql = 'INSERT INTO `participation` (
            `id_event`, `nom`, `prenom`, `email`, `telephone`, `statut`
        ) VALUES (:id_event, :nom, :prenom, :email, :telephone, :statut)';
        try {
            $st = $db->prepare($sql);
            $st->execute([
                'id_event' => $idEvent,
                'nom' => $p->getNom(),
                'prenom' => $p->getPrenom(),
                'email' => $p->getEmail(),
                'telephone' => $p->getTelephone(),
                'statut' => $p->getStatut(),
            ]);
        } catch (Exception $e) {
            return 'Erreur : ' . $e->getMessage();
        }
        $this->recomputeEventStatut($idEvent);
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

    public function updateParticipation($id)
    {
        $id = (int) $id;
        if ($id < 1) {
            header('Location: liste_event.php');
            exit;
        }
        $db = Config::getConnexion();

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $nom = trim((string) ($_POST['nom'] ?? ''));
            $prenom = trim((string) ($_POST['prenom'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $telephone = trim((string) ($_POST['telephone'] ?? ''));
            $statut = (string) ($_POST['statut'] ?? '');
            $idEvent = (int) ($_POST['id_event'] ?? 0);
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
                            'id' => $id,
                            'nom' => $nom,
                            'prenom' => $prenom,
                            'email' => $email,
                            'tel' => $telephone,
                            'st' => $statut,
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
}
