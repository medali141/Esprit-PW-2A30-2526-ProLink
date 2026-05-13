<?php
require_once __DIR__ . '/../config.php';

class AuthController {

    /** Email déjà présent en base (MySQL 1062). */
    public const ERR_DUPLICATE_EMAIL = 'duplicate_email';

    // Login
    public function login($email, $mdp) {
        $sql = "SELECT * FROM user WHERE email = :email";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['email' => $email]);
            $user = $query->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($mdp, $user['mdp'])) {
                if (session_status() !== PHP_SESSION_ACTIVE) {
                    session_start();
                }
                $_SESSION['user'] = $user;
                return $user;
            } else {
                return null;
            }

        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // Register
    public function register($user) {
        $sql = "INSERT INTO user (nom, prenom, email, mdp, type, age)
                VALUES (:nom, :prenom, :email, :mdp, :type, :age)";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
                'mdp' => password_hash($user->getMdp(), PASSWORD_DEFAULT),
                'type' => $user->getType(),
                'age' => $user->getAge()
            ]);
        } catch (PDOException $e) {
            if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062) {
                throw new RuntimeException(self::ERR_DUPLICATE_EMAIL, 0, $e);
            }
            throw $e;
        }
    }

    // Profile
    public function profile() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $sessionUser = $_SESSION['user'] ?? null;
        if (!$sessionUser || empty($sessionUser['iduser'])) {
            return null;
        }
        try {
            $db = Config::getConnexion();
            $q = $db->prepare("SELECT * FROM user WHERE iduser = :id LIMIT 1");
            $q->execute(['id' => (int) $sessionUser['iduser']]);
            $fresh = $q->fetch(PDO::FETCH_ASSOC);
            if ($fresh) {
                $_SESSION['user'] = $fresh;
                return $fresh;
            }
        } catch (Throwable $e) {
            // Fallback on session snapshot if DB refresh fails.
        }
        return $sessionUser;
    }

    // Forgot password
    public function forgotPassword($email, $newPassword) {
        $sql = "UPDATE user SET mdp = :mdp WHERE email = :email";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'email' => $email,
                'mdp' => password_hash($newPassword, PASSWORD_DEFAULT)
            ]);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }


    //update profile    
    // A implémenter : updateProfile($user, $id)



}