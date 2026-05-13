<?php
/**
 * SmtpSimpleClientAttachment
 * Envoie un email HTML avec une pièce jointe via SMTP (socket PHP natif).
 * Compatible avec SmtpSimpleClient existant.
 */
class SmtpSimpleClientAttachment
{
    /**
     * Envoie un email HTML avec une pièce jointe.
     *
     * @param string $from          Adresse expéditeur
     * @param string $fromName      Nom expéditeur
     * @param string $to            Adresse destinataire
     * @param string $subject       Sujet
     * @param string $htmlBody      Corps HTML
     * @param string $altBody       Corps texte alternatif
     * @param string $smtpHost      Hôte SMTP
     * @param int    $smtpPort      Port SMTP
     * @param string $smtpUser      Utilisateur SMTP
     * @param string $smtpPass      Mot de passe SMTP
     * @param string $smtpSecure    'tls', 'ssl' ou ''
     * @param string $attachPath    Chemin absolu vers le fichier à joindre
     * @param string $attachName    Nom du fichier dans l'email
     * @param string $attachMime    Type MIME de la pièce jointe
     */
    public static function sendWithAttachment(
        string $from,
        string $fromName,
        string $to,
        string $subject,
        string $htmlBody,
        string $altBody,
        string $smtpHost,
        int    $smtpPort,
        string $smtpUser,
        string $smtpPass,
        string $smtpSecure,
        string $attachPath,
        string $attachName  = 'attachment.pdf',
        string $attachMime  = 'application/pdf'
    ): bool {
        // ── Lire la pièce jointe ──────────────────────────────
        if (!is_file($attachPath) || !is_readable($attachPath)) {
            error_log('[SmtpSimpleClientAttachment] Fichier introuvable : ' . $attachPath);
            return false;
        }
        $attachData   = base64_encode(file_get_contents($attachPath));
        $attachChunks = chunk_split($attachData, 76, "\r\n");

        // ── Construire le message MIME multipart ─────────────
        $boundary = '==PROLINK_' . md5(uniqid((string)mt_rand(), true)) . '==';

        $headers  = 'From: ' . self::encodeHeader($fromName) . ' <' . $from . '>' . "\r\n";
        $headers .= 'To: ' . $to . "\r\n";
        $headers .= 'Subject: ' . self::encodeHeader($subject) . "\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-Type: multipart/mixed; boundary="' . $boundary . '"' . "\r\n";
        $headers .= 'X-Mailer: ProLink-Mailer/1.0' . "\r\n";

        $body  = '--' . $boundary . "\r\n";
        $body .= 'Content-Type: multipart/alternative; boundary="alt_' . $boundary . '"' . "\r\n\r\n";

        // Partie texte brut
        $body .= '--alt_' . $boundary . "\r\n";
        $body .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
        $body .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";
        $body .= quoted_printable_encode($altBody) . "\r\n\r\n";

        // Partie HTML
        $body .= '--alt_' . $boundary . "\r\n";
        $body .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
        $body .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";
        $body .= quoted_printable_encode($htmlBody) . "\r\n\r\n";

        $body .= '--alt_' . $boundary . "--\r\n\r\n";

        // Pièce jointe
        $body .= '--' . $boundary . "\r\n";
        $body .= 'Content-Type: ' . $attachMime . '; name="' . $attachName . '"' . "\r\n";
        $body .= 'Content-Transfer-Encoding: base64' . "\r\n";
        $body .= 'Content-Disposition: attachment; filename="' . $attachName . '"' . "\r\n\r\n";
        $body .= $attachChunks . "\r\n";

        $body .= '--' . $boundary . "--\r\n";

        // ── Connexion SMTP ────────────────────────────────────
        try {
            $smtpSecure = strtolower(trim($smtpSecure));

            if ($smtpSecure === 'ssl') {
                $host = 'ssl://' . $smtpHost;
            } else {
                $host = $smtpHost;
            }

            $errno  = 0;
            $errstr = '';
            $sock   = @fsockopen($host, $smtpPort, $errno, $errstr, 15);
            if (!$sock) {
                error_log('[SmtpSimpleClientAttachment] Connexion échouée : ' . $errstr);
                return false;
            }

            // Lire bannière
            self::read($sock);

            // EHLO
            self::send($sock, 'EHLO ' . ($smtpHost ?: 'localhost'));
            self::read($sock);

            // STARTTLS si besoin
            if ($smtpSecure === 'tls') {
                self::send($sock, 'STARTTLS');
                self::read($sock);
                if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    error_log('[SmtpSimpleClientAttachment] STARTTLS échoué');
                    fclose($sock);
                    return false;
                }
                self::send($sock, 'EHLO ' . ($smtpHost ?: 'localhost'));
                self::read($sock);
            }

            // AUTH LOGIN
            self::send($sock, 'AUTH LOGIN');
            self::read($sock);
            self::send($sock, base64_encode($smtpUser));
            self::read($sock);
            self::send($sock, base64_encode($smtpPass));
            $authResp = self::read($sock);
            if (strpos($authResp, '235') === false) {
                error_log('[SmtpSimpleClientAttachment] Authentification SMTP échouée : ' . $authResp);
                fclose($sock);
                return false;
            }

            // MAIL FROM
            self::send($sock, 'MAIL FROM:<' . $from . '>');
            self::read($sock);

            // RCPT TO
            self::send($sock, 'RCPT TO:<' . $to . '>');
            self::read($sock);

            // DATA
            self::send($sock, 'DATA');
            self::read($sock);

            self::send($sock, $headers . "\r\n" . $body . "\r\n.");
            $dataResp = self::read($sock);

            self::send($sock, 'QUIT');
            fclose($sock);

            if (strpos($dataResp, '250') === false) {
                error_log('[SmtpSimpleClientAttachment] Envoi échoué : ' . $dataResp);
                return false;
            }

            return true;

        } catch (Throwable $e) {
            error_log('[SmtpSimpleClientAttachment] Exception : ' . $e->getMessage());
            return false;
        }
    }

    // ── Helpers ───────────────────────────────────────────────

    private static function send($sock, string $data): void
    {
        fwrite($sock, $data . "\r\n");
    }

    private static function read($sock): string
    {
        $result = '';
        while ($line = fgets($sock, 515)) {
            $result .= $line;
            // Le 4e caractère est '-' si la réponse continue, ' ' si c'est la fin
            if (isset($line[3]) && $line[3] !== '-') {
                break;
            }
        }
        return $result;
    }

    private static function encodeHeader(string $value): string
    {
        if (preg_match('/[^\x20-\x7E]/', $value)) {
            return '=?UTF-8?B?' . base64_encode($value) . '?=';
        }
        return $value;
    }
}