<?php
class Config {
    private static $host = "localhost";
<<<<<<< HEAD
=======
    private static $port = 3308;  // ← AJOUTE CETTE LIGNE
>>>>>>> formation
    private static $db_name = "prolink";
    private static $username = "root";
    private static $password = "";

    public static function getConnexion() {
        try {
            $conn = new PDO(
<<<<<<< HEAD
                "mysql:host=" . self::$host . ";dbname=" . self::$db_name,
=======
                "mysql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$db_name,
>>>>>>> formation
                self::$username,
                self::$password
            );
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch(PDOException $e) {
            die("Erreur DB: " . $e->getMessage());
        }
    }
<<<<<<< HEAD
}
=======
}
?>
>>>>>>> formation
