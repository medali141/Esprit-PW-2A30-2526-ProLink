<?php
include '../../Controller/UserP.php';

if (isset($_GET['id'])) {
    $userP = new UserP();
    $userP->deleteUser($_GET['id']);
    header('Location:listUsers.php');
}