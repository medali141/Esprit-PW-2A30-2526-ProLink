<?php
/**
 * Téléchargement self-service du certificat ProLink.
 *
 * URL : formation_certificat.php?id_inscription=N
 *
 * Vérifie que :
 *  1. L'utilisateur est connecté ;
 *  2. L'inscription lui appartient (id_user ou e-mail correspondant) ;
 *  3. Le quiz associé a bien été réussi (quiz_passed = 1).
 *
 * Si tout est OK, délègue le rendu PDF à FormationCertificatePdf. Sinon
 * redirige vers la page de la formation avec un message d'erreur (flash).
 */
require_once __DIR__ . '/../../init.php';
requireLogin('Connectez-vous pour télécharger votre certificat.');
require_once __DIR__ . '/../../controller/FormationP.php';
require_once __DIR__ . '/../../lib/FormationCertificatePdf.php';

$fp = new FormationP();
$u = currentUser();
$uid = (int) ($u['iduser'] ?? 0);
$uemail = (string) ($u['email'] ?? '');

$idInscription = isset($_GET['id_inscription']) ? (int) $_GET['id_inscription'] : 0;
$row = $idInscription > 0 ? $fp->getInscription($idInscription) : null;

if (!$row || !$fp->inscriptionBelongsToUser($row, $uid, $uemail)) {
    flashSet('auth', 'Cette inscription ne vous appartient pas.');
    header('Location: formation.php');
    exit;
}

if ((int) ($row['quiz_passed'] ?? 0) !== 1) {
    flashSet('auth', 'Vous devez d\'abord réussir le quiz pour obtenir votre certificat.');
    header('Location: formation_detail.php?id=' . (int) $row['id_formation']);
    exit;
}

FormationCertificatePdf::render($row);
