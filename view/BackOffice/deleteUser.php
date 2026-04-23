<?php
include '../../Controller/UserP.php';

if (isset($_GET['id'])) {
    $userP = new UserP();
    $id = (int) $_GET['id'];

    // If user has commandes, redirect back with an error flag and don't delete
    if ($userP->hasCommandes($id)) {
        header('Location: listUsers.php?error=hasCommandes');
        exit;
    }

    $userP->deleteUser($id);
    header('Location: listUsers.php?deleted=1');
    exit;
}