<?php
require_once "User.php";

class Entrepreneur extends User {
    public function getType() {
        return "entrepreneur";
    }
}