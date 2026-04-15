<?php
require_once "User.php";

class Candidat extends User {
    public function getType() {
        return "candidat";
    }
}