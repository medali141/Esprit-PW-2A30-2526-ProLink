<?php
require_once __DIR__ . '/../config/mail.php';

class MailOtpService {
    public static function sendPasswordResetOtp(string $toEmail, string $otpPlain, int $ttlSeconds): bool {
        return self::sendOtpEmail(
            $toEmail,
            $otpPlain,
            $ttlSeconds,
            'ProLink - Code de reinitialisation mot de passe',
            'Votre code de verification ProLink'
        );
    }

    public static function sendPaymentVerificationOtp(string $toEmail, string $otpPlain, int $ttlSeconds): bool {
        return self::sendOtpEmail(
            $toEmail,
            $otpPlain,
            $ttlSeconds,
            'ProLink - Code de confirmation paiement',
            'Votre code de confirmation paiement ProLink'
        );
    }

    private static function sendOtpEmail(
        string $toEmail,
        string $otpPlain,
        int $ttlSeconds,
        string $subject,
        string $title
    ): bool {
        $mins = (int) ceil(max(60, $ttlSeconds) / 60);
        $html = '<p>Bonjour,</p>'
            . '<p>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . ': <strong style="font-size:1.2em;letter-spacing:0.1em;">'
            . htmlspecialchars($otpPlain, ENT_QUOTES, 'UTF-8')
            . '</strong></p>'
            . '<p>Ce code expire dans ' . $mins . ' minute' . ($mins > 1 ? 's' : '') . '.</p>'
            . '<p>Si vous n etes pas a l origine de cette demande, ignorez ce message.</p>';
        $alt = 'Votre code ProLink: ' . $otpPlain . '. Valide ' . $mins . ' min.';

        if (defined('PROLINK_SMTP_HOST') && PROLINK_SMTP_HOST !== '' && defined('PROLINK_SMTP_USER') && PROLINK_SMTP_USER !== '') {
            require_once __DIR__ . '/SmtpSimpleClient.php';
            $pass = defined('PROLINK_SMTP_PASS') ? (string) PROLINK_SMTP_PASS : '';
            $port = defined('PROLINK_SMTP_PORT') ? (int) PROLINK_SMTP_PORT : 587;
            $secure = defined('PROLINK_SMTP_SECURE') ? (string) PROLINK_SMTP_SECURE : 'tls';
            return SmtpSimpleClient::sendHtml(
                (string) PROLINK_MAIL_FROM,
                (string) PROLINK_MAIL_FROM_NAME,
                $toEmail,
                $subject,
                $html,
                $alt,
                (string) PROLINK_SMTP_HOST,
                $port,
                (string) PROLINK_SMTP_USER,
                $pass,
                $secure
            );
        }

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/plain;charset=UTF-8\r\n";
        $headers .= "From: " . (string) PROLINK_MAIL_FROM . "\r\n";
        return @mail($toEmail, $subject, $alt, $headers);
    }
}
