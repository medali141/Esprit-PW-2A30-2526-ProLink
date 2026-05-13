<?php
require_once __DIR__ . '/../config.php';
<<<<<<< HEAD
require_once __DIR__ . '/../model/User.php';

class UserP {

    /** @return list<array<string, mixed>> */
    public function listUsers(string $sort = 'iduser', string $order = 'asc'): array
    {
        $allowed = ['iduser', 'nom', 'prenom', 'email', 'type', 'age'];
        if (!in_array($sort, $allowed, true)) {
            $sort = 'iduser';
        }
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $sql = 'SELECT * FROM `user` ORDER BY `' . $sort . '` ' . $order;
        $db = Config::getConnexion();
        try {
            $st = $db->query($sql);
            if (!$st) {
                return [];
            }
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            return is_array($rows) ? $rows : [];
=======
require_once __DIR__ . '/../Model/User.php';

class UserP {

    // 🔹 LIST USERS
    public function listUsers() {
        $sql = "SELECT * FROM user";
        $db = config::getConnexion();
        try {
            return $db->query($sql);
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // 🔹 ADD USER
    public function addUser($user) {
        $sql = "INSERT INTO user (nom, prenom, email, mdp, type, age)
                VALUES (:nom, :prenom, :email, :mdp, :type, :age)";
<<<<<<< HEAD
        $db = Config::getConnexion();
=======
        $db = config::getConnexion();
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5

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
            die('Error: ' . $e->getMessage());
        }
    }

    // 🔹 DELETE
    public function deleteUser($id) {
        $sql = "DELETE FROM user WHERE iduser = :id";
<<<<<<< HEAD
        $db = Config::getConnexion();
=======
        $db = config::getConnexion();
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);

        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

<<<<<<< HEAD
    // 🔹 CHECK IF USER HAS COMMANDES (orders)
    public function hasCommandes($id) {
        $sql = "SELECT COUNT(*) as cnt FROM commande WHERE id_acheteur = :id";
        $db = config::getConnexion();
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
=======
    // 🔹 UPDATE
    public function updateUser($user, $id) {
        $db = config::getConnexion();
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5

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

        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

<<<<<<< HEAD
    /** Chemin relatif au dossier view, ex. uploads/profiles/user_7.jpg, ou null pour retirer la photo. */
    public function setUserPhoto(?string $relativePathFromView, int $id): void {
        $db = Config::getConnexion();
        $st = $db->prepare('UPDATE user SET photo = :p WHERE iduser = :id');
        $st->execute(['p' => $relativePathFromView, 'id' => $id]);
    }

    // 🔹 SHOW USER
    public function showUser($id) {
        $sql = "SELECT * FROM user WHERE iduser = :id";
        $db = Config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
=======
    // 🔹 SHOW USER
    public function showUser($id) {
        $sql = "SELECT * FROM user WHERE iduser = $id";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute();
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
            return $query->fetch();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }
}