<?php
error_reporting(E_ALL);
$fp = stream_socket_client("tcp://smtp.gmail.com:587", $errno, $errstr, 10);
if (!$fp) { echo "ERR $errno: $errstr\n"; exit(1); }
$line = fgets($fp, 1024); echo "SERVER: " . trim($line) . "\n";
fwrite($fp, "EHLO test\r\n");
while (($line = fgets($fp, 1024)) !== false) {
    echo "EHLO: " . trim($line) . "\n";
    if (strlen($line) >= 4 && $line[3] === ' ') break;
}
fwrite($fp, "STARTTLS\r\n");
$line = fgets($fp, 1024); echo "STARTTLS: " . trim($line) . "\n";
$crypto = stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
echo "CRYPTO: "; var_export($crypto); echo "\n";
if ($crypto) { fwrite($fp, "EHLO test\r\n"); while (($line = fgets($fp, 1024)) !== false) { echo "EHLO2: " . trim($line) . "\n"; if (strlen($line) >= 4 && $line[3] === ' ') break; } }
fclose($fp);