<?php
/**
 * SMTP minimal (AUTH LOGIN + SSL/465 ou STARTTLS/587) pour Gmail sans class.smtp.php.
 */
class SmtpSimpleClient {

    /**
     * @return bool
     */
    public static function sendHtml(
        $fromEmail,
        $fromName,
        $toEmail,
        $subject,
        $htmlBody,
        $altBody,
        $host,
        $port,
        $user,
        $pass,
        $secure
    ) {
        $secure = strtolower((string) $secure);
        $ctx = stream_context_create([
            'ssl' => [
                'verify_peer'       => true,
                'verify_peer_name'  => true,
                'allow_self_signed' => false,
            ],
        ]);

        if ($secure === 'ssl') {
            $uri = 'ssl://' . $host . ':' . (int) $port;
        } else {
            $uri = 'tcp://' . $host . ':' . (int) $port;
        }

        $fp = @stream_socket_client($uri, $errno, $errstr, 25, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) {
            return false;
        }
        stream_set_timeout($fp, 25);

        if (!self::expect($fp, [220])) {
            fclose($fp);
            return false;
        }

        $ehlo = 'EHLO prolink.local';
        fwrite($fp, $ehlo . "\r\n");
        if (!self::expect($fp, [250])) {
            fclose($fp);
            return false;
        }

        if ($secure === 'tls') {
            fwrite($fp, "STARTTLS\r\n");
            if (!self::expect($fp, [220])) {
                fclose($fp);
                return false;
            }
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($fp);
                return false;
            }
            fwrite($fp, $ehlo . "\r\n");
            if (!self::expect($fp, [250])) {
                fclose($fp);
                return false;
            }
        }

        fwrite($fp, "AUTH LOGIN\r\n");
        if (!self::expect($fp, [334])) {
            fclose($fp);
            return false;
        }
        fwrite($fp, base64_encode($user) . "\r\n");
        if (!self::expect($fp, [334])) {
            fclose($fp);
            return false;
        }
        fwrite($fp, base64_encode($pass) . "\r\n");
        if (!self::expect($fp, [235])) {
            fclose($fp);
            return false;
        }

        fwrite($fp, 'MAIL FROM:<' . self::cleanAddr($fromEmail) . ">\r\n");
        if (!self::expect($fp, [250])) {
            fclose($fp);
            return false;
        }
        fwrite($fp, 'RCPT TO:<' . self::cleanAddr($toEmail) . ">\r\n");
        if (!self::expect($fp, [250, 251])) {
            fclose($fp);
            return false;
        }
        fwrite($fp, "DATA\r\n");
        if (!self::expect($fp, [354])) {
            fclose($fp);
            return false;
        }

        $subj = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $fromHdr = self::encodeHeaderName($fromName) . ' <' . self::cleanAddr($fromEmail) . '>';
        $boundary = 'b' . bin2hex(random_bytes(8));

        $msg = "From: $fromHdr\r\n";
        $msg .= "To: <" . self::cleanAddr($toEmail) . ">\r\n";
        $msg .= "Subject: $subj\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
        $msg .= "\r\n";
        $msg .= "--$boundary\r\n";
        $msg .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $msg .= quoted_printable_encode($altBody) . "\r\n";
        $msg .= "--$boundary\r\n";
        $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $msg .= quoted_printable_encode($htmlBody) . "\r\n";
        $msg .= "--$boundary--\r\n";

        $msg = str_replace("\r\n", "\n", $msg);
        $msg = str_replace("\n", "\r\n", $msg);
        $lines = explode("\r\n", $msg);
        $dotted = [];
        foreach ($lines as $line) {
            if ($line !== '' && $line[0] === '.') {
                $dotted[] = '.' . $line;
            } else {
                $dotted[] = $line;
            }
        }
        $msg = implode("\r\n", $dotted);

        fwrite($fp, $msg . "\r\n.\r\n");
        if (!self::expect($fp, [250])) {
            fclose($fp);
            return false;
        }
        fwrite($fp, "QUIT\r\n");
        fclose($fp);
        return true;
    }

    private static function cleanAddr($e) {
        return trim(preg_replace('/[\r\n]+/', '', (string) $e));
    }

    private static function encodeHeaderName($name) {
        $name = trim((string) $name);
        if ($name === '') {
            return '';
        }
        return '=?UTF-8?B?' . base64_encode($name) . '?=';
    }

    private static function readLines($fp) {
        $buf = '';
        while (!feof($fp)) {
            $line = fgets($fp, 2048);
            if ($line === false) {
                break;
            }
            $buf .= $line;
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }
        return $buf;
    }

    private static function expect($fp, array $codes) {
        $data = self::readLines($fp);
        if ($data === '') {
            return false;
        }
        $code = (int) substr($data, 0, 3);
        foreach ($codes as $c) {
            if ($code === (int) $c) {
                return true;
            }
        }
        return false;
    }
}
