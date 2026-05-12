<?php
require_once "User.php";

class Admin extends User {
    public function getType() {
        return "admin";
    }
}