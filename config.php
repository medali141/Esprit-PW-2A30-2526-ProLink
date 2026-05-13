<?php
class Config {
    private static $host = "localhost";
    private static $port = 3308;  // ← AJOUTE CETTE LIGNE
    private static $db_name = "prolink";
    private static $username = "root";
    private static $password = "";

    public static function getConnexion() {
        try {
            $conn = new PDO(
                "mysql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$db_name,
                self::$username,
                self::$password
            );
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch(PDOException $e) {
            die("Erreur DB: " . $e->getMessage());
        }
    }
}
?>