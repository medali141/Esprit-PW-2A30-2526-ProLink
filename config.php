<?php
class Config {
    private static $host = "localhost";
<<<<<<< HEAD
    private static $port = 3308;  // ← AJOUTE CETTE LIGNE
=======
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
    private static $db_name = "prolink";
    private static $username = "root";
    private static $password = "";

    public static function getConnexion() {
        try {
            $conn = new PDO(
<<<<<<< HEAD
                "mysql:host=" . self::$host . ";port=" . self::$port . ";dbname=" . self::$db_name,
=======
                "mysql:host=" . self::$host . ";dbname=" . self::$db_name,
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
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
?>
=======
}
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
