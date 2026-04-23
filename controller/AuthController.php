<?php
require_once __DIR__ . '/../config.php';

class AuthController {

    // 🔹 LOGIN
    public function login($email, $mdp) {
        $sql = "SELECT * FROM user WHERE email = :email";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['email' => $email]);
            $user = $query->fetch();

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

    // 🔹 REGISTER
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
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // 🔹 PROFILE
    public function profile() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        return $_SESSION['user'] ?? null;
    }

    // 🔹 FORGOT PASSWORD
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