<?php
require_once __DIR__ . '/../../../controller/UserP.php';

if (isset($_GET['id'])) {
    $userP = new UserP();
    $id = (int) $_GET['id'];

    if ($userP->hasCommandes($id)) {
        header('Location: listUsers.php?error=hasCommandes');
        exit;
    }

    $userP->deleteUser($id);
    header('Location: listUsers.php?deleted=1');
    exit;
}
