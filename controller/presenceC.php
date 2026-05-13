<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
date_default_timezone_set('Africa/Tunis');
class PresenceC
{
    public function scanTicket(string $qrData, int $idEvent): array
    {
        // QR format : CODE|EVENT|NOM|HASH
        $parts = explode('|', $qrData);
        if (count($parts) !== 4) {
            return ['ok' => false, 'msg' => 'QR code invalide.'];
        }

        [$code, $event, $nom, $hash] = $parts;

        // Vérifier le hash
        $expectedHash = strtoupper(substr(
            hash('sha256', $code . '|' . $event . '|' . $nom . '|prolink_secret_2025'), 0, 16
        ));
        if ($hash !== $expectedHash) {
            return ['ok' => false, 'msg' => 'Ticket falsifié ou invalide.'];
        }

        // Extraire id_participation : PRT-2026-0017 → 17
        $pid = (int) ltrim(substr($code, strrpos($code, '-') + 1), '0');
        if ($pid < 1) {
            return ['ok' => false, 'msg' => 'Code de participation invalide.'];
        }

        $db = Config::getConnexion();

        // Récupérer participation + event
        $st = $db->prepare('
            SELECT p.`id_participation`, p.`nom`, p.`prenom`, p.`statut`, p.`id_event`,
                   e.`titre_event`, e.`date_debut`, e.`date_fin`
            FROM `participation` p
            INNER JOIN `evenement` e ON p.`id_event` = e.`id_event`
            WHERE p.`id_participation` = :pid LIMIT 1
        ');
        $st->execute(['pid' => $pid]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return ['ok' => false, 'msg' => 'Participation introuvable.'];
        }
        if ((int)$row['id_event'] !== $idEvent) {
            return ['ok' => false, 'msg' => "Ce ticket n'appartient pas à cet événement."];
        }
        if ($row['statut'] !== 'confirmé') {
            return ['ok' => false, 'msg' => 'Participation non confirmée (statut : ' . $row['statut'] . ').'];
        }

        // Vérifier que aujourd'hui est dans la période de l'événement
        $today     = date('Y-m-d');
        $dateDebut = substr((string)$row['date_debut'], 0, 10);
        $dateFin   = substr((string)$row['date_fin'],   0, 10);
        if ($today < $dateDebut || $today > $dateFin) {
            return ['ok' => false, 'msg' => "L'événement n'est pas actif aujourd'hui ({$today}). Période : {$dateDebut} → {$dateFin}."];
        }

        // Déjà scanné aujourd'hui ?
        $chk = $db->prepare('
            SELECT `id_presence` FROM `presence`
            WHERE `id_participation` = :pid AND `date_scan` = :d LIMIT 1
        ');
        $chk->execute(['pid' => $pid, 'd' => $today]);
        if ($chk->fetch()) {
            return [
                'ok'      => false,
                'already' => true,
                'msg'     => 'Ce ticket a déjà été scanné aujourd\'hui.',
                'nom'     => $row['prenom'] . ' ' . $row['nom'],
            ];
        }

        // Enregistrer la présence
        $ins = $db->prepare('
            INSERT INTO `presence` (`id_participation`, `id_event`, `date_scan`, `heure_scan`)
            VALUES (:pid, :eid, :d, :h)
        ');
        $ins->execute([
            'pid' => $pid,
            'eid' => $idEvent,
            'd'   => $today,
            'h'   => date('H:i:s'),
        ]);

        return [
            'ok'    => true,
            'msg'   => 'Bienvenue ' . $row['prenom'] . ' ' . $row['nom'] . ' !',
            'nom'   => $row['prenom'] . ' ' . $row['nom'],
            'event' => $row['titre_event'],
            'code'  => $code,
        ];
    }

    public function listePresences(int $idEvent): array
    {
        $db = Config::getConnexion();
        $st = $db->prepare('
            SELECT pr.`id_presence`, pr.`date_scan`, pr.`heure_scan`,
                   p.`nom`, p.`prenom`, p.`email`, p.`telephone`,
                   p.`id_participation`
            FROM `presence` pr
            INNER JOIN `participation` p ON pr.`id_participation` = p.`id_participation`
            WHERE pr.`id_event` = :eid
            ORDER BY pr.`date_scan` DESC, pr.`heure_scan` DESC
        ');
        $st->execute(['eid' => $idEvent]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function countPresences(int $idEvent): int
    {
        $db = Config::getConnexion();
        $st = $db->prepare('SELECT COUNT(*) FROM `presence` WHERE `id_event` = :eid');
        $st->execute(['eid' => $idEvent]);
        return (int) $st->fetchColumn();
    }
}