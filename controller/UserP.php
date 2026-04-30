<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/User.php';

class UserP {

    // 🔹 LIST USERS
    public function listUsers() {
        $sql = "SELECT * FROM user";
        $db = Config::getConnexion();
        try {
            return $db->query($sql);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // 🔹 ADD USER
    public function addUser($user) {
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
                throw new RuntimeException('duplicate_email', 0, $e);
            }
            die('Error: ' . $e->getMessage());
        }
    }

    // 🔹 DELETE
    public function deleteUser($id) {
        $sql = "DELETE FROM user WHERE iduser = :id";
        $db = Config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);

        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // 🔹 CHECK IF USER HAS COMMANDES (orders)
    public function hasCommandes($id) {
        $sql = "SELECT COUNT(*) as cnt FROM commande WHERE id_acheteur = :id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            $row = $query->fetch();
            return ((int) ($row['cnt'] ?? 0)) > 0;
        } catch (Exception $e) {
            // On error, be conservative and return true to prevent accidental deletion
            return true;
        }
    }

    // 🔹 UPDATE
    public function updateUser($user, $id) {
        $db = Config::getConnexion();

        try {
            $query = $db->prepare(
                "UPDATE user SET 
                nom = :nom,
                prenom = :prenom,
                email = :email,
                type = :type,
                age = :age
                WHERE iduser = :id"
            );

            $query->execute([
                'id' => $id,
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
                'type' => $user->getType(),
                'age' => $user->getAge()
            ]);

        } catch (PDOException $e) {
            if (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062) {
                throw new RuntimeException('duplicate_email', 0, $e);
            }
            die('Error:' . $e->getMessage());
        }
    }

    // 🔹 SHOW USER
    public function showUser($id) {
        $sql = "SELECT * FROM user WHERE iduser = :id";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }
}