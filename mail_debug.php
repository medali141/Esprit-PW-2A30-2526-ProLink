<?php
/**
 * mail_debug.php - Diagnostic SMTP pour le serveur web.
 * Accédez à http://localhost/Esprit-PW-2A30-2526-ProLink-main1/mail_debug.php
 */
header('Content-Type: text/plain; charset=UTF-8');

echo "=== MAIL DEBUG ===\n";
echo "PHP SAPI: " . php_sapi_name() . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Loaded php.ini: " . (php_ini_loaded_file() ?: 'none') . "\n";
echo "OpenSSL loaded: " . (extension_loaded('openssl') ? 'yes' : 'no') . "\n";
echo "OpenSSL version: " . (defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : 'n/a') . "\n";
$extDir = ini_get('extension_dir');
echo "extension_dir: " . $extDir . "\n";
echo "php_openssl.dll exists: " . (file_exists($extDir . DIRECTORY_SEPARATOR . 'php_openssl.dll') ? 'yes' : 'no') . "\n";
echo "php_openssl.dll readable: " . (is_readable($extDir . DIRECTORY_SEPARATOR . 'php_openssl.dll') ? 'yes' : 'no') . "\n";
echo "php_ini_scanned_files: " . (php_ini_scanned_files() ?: 'none') . "\n";
echo "Loaded extensions: " . implode(', ', get_loaded_extensions()) . "\n";
echo "\n";

echo "--- SMTP CONFIG ---\n";
require_once __DIR__ . '/config/mail.php';
echo "PROLINK_SMTP_HOST: " . (defined('PROLINK_SMTP_HOST') ? PROLINK_SMTP_HOST : 'undefined') . "\n";
echo "PROLINK_SMTP_USER: " . (defined('PROLINK_SMTP_USER') ? PROLINK_SMTP_USER : 'undefined') . "\n";
echo "PROLINK_SMTP_PORT: " . (defined('PROLINK_SMTP_PORT') ? PROLINK_SMTP_PORT : 'undefined') . "\n";
echo "PROLINK_SMTP_SECURE: " . (defined('PROLINK_SMTP_SECURE') ? PROLINK_SMTP_SECURE : 'undefined') . "\n";
echo "PROLINK_SMTP_DEBUG: " . (defined('PROLINK_SMTP_DEBUG') ? (PROLINK_SMTP_DEBUG ? 'true' : 'false') : 'undefined') . "\n";
echo "\n";

echo "--- SMTP DEBUG LOG ---\n";
$logFile = __DIR__ . '/smtp_debug.log';
if (is_file($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $last = array_slice($lines, -40);
    foreach ($last as $line) {
        echo $line . "\n";
    }
} else {
    echo "Aucun fichier smtp_debug.log trouvé.\n";
}
