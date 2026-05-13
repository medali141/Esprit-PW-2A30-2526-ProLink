<?php
require_once __DIR__ . '/../config.php';
<<<<<<< HEAD
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../lib/MailOtpService.php';
=======
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5

class AuthController {

    // 🔹 LOGIN
    public function login($email, $mdp) {
        $sql = "SELECT * FROM user WHERE email = :email";
<<<<<<< HEAD
        $db = Config::getConnexion();
=======
        $db = config::getConnexion();
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5

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

<<<<<<< HEAD
    // 🔹 FORGOT PASSWORD — returns true if a user row was updated
    public function forgotPassword($email, $newPassword) {
        $sql = "UPDATE user SET mdp = :mdp WHERE email = :email";
        $db = Config::getConnexion();
=======
    // 🔹 FORGOT PASSWORD
    public function forgotPassword($email, $newPassword) {
        $sql = "UPDATE user SET mdp = :mdp WHERE email = :email";
        $db = config::getConnexion();
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'email' => $email,
                'mdp' => password_hash($newPassword, PASSWORD_DEFAULT)
            ]);
<<<<<<< HEAD
            return $query->rowCount() > 0;
=======
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

<<<<<<< HEAD
    /**
     * Génère un OTP (15 min), le stocke haché en base et l’envoie par e-mail.
     * @return int|null id utilisateur si le compte existe, null sinon (message générique côté vue)
     */
    public function requestPasswordResetOtp($email) {
        $sql = "SELECT iduser, email FROM user WHERE email = :email";
        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['email' => $email]);
        $row = $query->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $otp = (string) random_int(100000, 999999);
        $hash = password_hash($otp, PASSWORD_DEFAULT);
        $ttl = (int) PROLINK_PWD_RESET_OTP_TTL;
        $expires = date('Y-m-d H:i:s', time() + $ttl);

        $up = $db->prepare("UPDATE user SET mdp_reset_otp_hash = :h, mdp_reset_otp_expires = :e WHERE iduser = :id");
        $up->execute(['h' => $hash, 'e' => $expires, 'id' => (int) $row['iduser']]);

        MailOtpService::sendPasswordResetOtp($row['email'], $otp, $ttl);
        return (int) $row['iduser'];
    }

    /**
     * Vérifie l’OTP et met à jour le mot de passe ; efface l’OTP en base.
     */
    public function resetPasswordWithOtp($userId, $plainOtp, $newPassword) {
        if (strlen($newPassword) < 6) {
            return false;
        }
        $db = Config::getConnexion();
        $q = $db->prepare("SELECT mdp_reset_otp_hash, mdp_reset_otp_expires FROM user WHERE iduser = :id");
        $q->execute(['id' => (int) $userId]);
        $row = $q->fetch(PDO::FETCH_ASSOC);
        if (!$row || empty($row['mdp_reset_otp_hash']) || empty($row['mdp_reset_otp_expires'])) {
            return false;
        }
        $exp = strtotime($row['mdp_reset_otp_expires']);
        if ($exp === false || $exp < time()) {
            return false;
        }
        if (!password_verify($plainOtp, $row['mdp_reset_otp_hash'])) {
            return false;
        }

        $u = $db->prepare("UPDATE user SET mdp = :m, mdp_reset_otp_hash = NULL, mdp_reset_otp_expires = NULL WHERE iduser = :id");
        $u->execute(['m' => password_hash($newPassword, PASSWORD_DEFAULT), 'id' => (int) $userId]);
        return $u->rowCount() > 0;
    }

=======
>>>>>>> 96660fcd9ebe09e5096ec93bcc2fbc328e0aeca5

    //update profile    
    // A implémenter : updateProfile($user, $id)



}