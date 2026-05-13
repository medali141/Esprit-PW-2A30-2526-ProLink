<?php
/**
 * Envoi d’e-mails d’OTP : SMTP natif (Gmail) si configuré, sinon PHPMailer en mode mail().
 */
class MailOtpService {

    private static function phpmailerPath() {
        return __DIR__ . '/../PHPMailer-FE_v4.11/_lib/class.phpmailer.php';
    }

    /**
     * Envoi générique : SMTP si configuré, sinon PHPMailer (mail()).
     * @return bool
     */
    private static function send($toEmail, $subject, $html, $alt) {
        require_once __DIR__ . '/../config/mail.php';

        if (defined('PROLINK_SMTP_HOST') && PROLINK_SMTP_HOST !== ''
            && defined('PROLINK_SMTP_USER') && PROLINK_SMTP_USER !== '') {
            require_once __DIR__ . '/SmtpSimpleClient.php';
            $pass = defined('PROLINK_SMTP_PASS') ? PROLINK_SMTP_PASS : '';
            $port = defined('PROLINK_SMTP_PORT') ? (int) PROLINK_SMTP_PORT : 587;
            $sec = (defined('PROLINK_SMTP_SECURE') && PROLINK_SMTP_SECURE !== '')
                ? PROLINK_SMTP_SECURE
                : 'tls';
            return SmtpSimpleClient::sendHtml(
                PROLINK_MAIL_FROM,
                PROLINK_MAIL_FROM_NAME,
                $toEmail,
                $subject,
                $html,
                $alt,
                PROLINK_SMTP_HOST,
                $port,
                PROLINK_SMTP_USER,
                $pass,
                $sec
            );
        }

        if (!is_file(self::phpmailerPath())) {
            return false;
        }
        require_once self::phpmailerPath();
        if (!class_exists('PHPMailer', false)) {
            return false;
        }

        $mail = new PHPMailer();
        $mail->CharSet  = 'UTF-8';
        $mail->From     = PROLINK_MAIL_FROM;
        $mail->FromName = PROLINK_MAIL_FROM_NAME;
        $mail->AddAddress($toEmail);
        $mail->Subject  = $subject;
        $mail->IsHTML(true);
        $mail->Body     = $html;
        $mail->AltBody  = $alt;
        return (bool) $mail->Send();
    }

    /**
     * @return bool true si l’e-mail a été accepté par PHPMailer / SMTP.
     */
    public static function sendPasswordResetOtp($toEmail, $otpPlain, $ttlSeconds) {
        $subject = 'ProLink — code de réinitialisation de mot de passe';
        $mins = (int) ceil($ttlSeconds / 60);
        $html = "<p>Bonjour,</p>"
            . "<p>Votre code de vérification ProLink : <strong style=\"font-size:1.2em;letter-spacing:0.1em;\">" . htmlspecialchars($otpPlain, ENT_QUOTES, 'UTF-8') . "</strong></p>"
            . "<p>Ce code expire dans " . (int) $mins . " minute" . ($mins > 1 ? 's' : '') . ".</p>"
            . "<p>Si vous n'avez pas demandé cette réinitialisation, ignorez ce message.</p>";
        $alt = "Votre code ProLink : $otpPlain. Valide " . (int) $mins . " min.";

        return self::send($toEmail, $subject, $html, $alt);
    }

    /**
     * Envoi du code MFA administrateur via PHPMailer / SMTP en utilisant le template HTML
     * `view/emails/admin_mfa.php`.
     *
     * @param string $toEmail
     * @param string $codePlain  6 chiffres
     * @param int    $expiresAt  timestamp UNIX d'expiration
     * @return bool
     */
    public static function sendAdminMfaCode($toEmail, $codePlain, $expiresAt) {
        require_once __DIR__ . '/../config/mail.php';

        $subject   = 'ProLink — Code de connexion administrateur';
        $siteName  = 'ProLink';
        $helpEmail = defined('PROLINK_MAIL_FROM') ? PROLINK_MAIL_FROM : 'support@prolink.local';

        // Variables consommées par le template
        $code    = (string) $codePlain;
        $expires = (int) $expiresAt;

        $tpl = __DIR__ . '/../view/emails/admin_mfa.php';
        if (is_file($tpl)) {
            ob_start();
            include $tpl;
            $html = (string) ob_get_clean();
        } else {
            $mins = max(1, (int) ceil(($expires - time()) / 60));
            $html = "<p>Bonjour,</p>"
                . "<p>Votre code de connexion administrateur ProLink : <strong style=\"font-size:1.4em;letter-spacing:0.2em;\">" . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . "</strong></p>"
                . "<p>Ce code expire dans " . (int) $mins . " minute" . ($mins > 1 ? 's' : '') . ".</p>";
        }

        $minsAlt = max(1, (int) ceil(($expires - time()) / 60));
        $alt = "Code administrateur ProLink : $code. Valide " . (int) $minsAlt . " min.";

        return self::send($toEmail, $subject, $html, $alt);
    }
}
