<?php
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

class Config {
    private static $host = "localhost";
    private static $db_name = "prolink";
    private static $username = "root";
    private static $password = "";

    public static function getConnexion() {
        try {
            $conn = new PDO(
                'mysql:host=' . self::$host . ';dbname=' . self::$db_name . ';charset=utf8mb4',
                self::$username,
                self::$password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
                ]
            );
            $conn->exec('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci');
            return $conn;
        } catch(PDOException $e) {
            die("Erreur DB: " . $e->getMessage());
        }
    }
}