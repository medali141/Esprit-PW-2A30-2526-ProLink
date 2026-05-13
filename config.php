<?php
<<<<<<< HEAD
date_default_timezone_set('Africa/Tunis');
=======
>>>>>>> 2e69571f221ab3f69eb82da93a61caf94ba48839
class Config {
    private static $host = "localhost";
    private static $db_name = "prolink";
    private static $username = "root";
    private static $password = "";

    public static function getConnexion() {
        try {
            $conn = new PDO(
                "mysql:host=" . self::$host . ";dbname=" . self::$db_name,
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