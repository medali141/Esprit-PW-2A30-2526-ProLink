<?php
/**
 * Configuration e-mail : variables communes + optionnel `mail.local.php` (non versionné).
 * Copiez `mail.local.example.php` vers `mail.local.php` et renseignez SMTP.
 */
if (is_file(__DIR__ . '/mail.local.php')) {
    require_once __DIR__ . '/mail.local.php';
}

/**
 * Expéditeur par défaut (écrasé par mail.local.php si défini).
 */
if (!defined('PROLINK_MAIL_FROM')) {
    define('PROLINK_MAIL_FROM', 'noreply@' . (isset($_SERVER['HTTP_HOST']) ? preg_replace('/:\d+$/', '', (string) $_SERVER['HTTP_HOST']) : 'localhost'));
}
if (!defined('PROLINK_MAIL_FROM_NAME')) {
<<<<<<< HEAD
    define('PROLINK_MAIL_FROM_NAME', 'ProLink');
=======
    define('PROLINK_MAIL_FROM_NAME', 'prolink');
>>>>>>> formation
}
/** Durée de validité du code OTP (secondes) */
if (!defined('PROLINK_PWD_RESET_OTP_TTL')) {
    define('PROLINK_PWD_RESET_OTP_TTL', 15 * 60);
}
