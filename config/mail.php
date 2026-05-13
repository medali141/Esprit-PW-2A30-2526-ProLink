<?php
/**
 * Mail settings for ProLink.
 * Optional local override: config/mail.local.php
 */
if (is_file(__DIR__ . '/mail.local.php')) {
    require_once __DIR__ . '/mail.local.php';
}

if (!defined('PROLINK_MAIL_FROM')) {
    define('PROLINK_MAIL_FROM', 'noreply@prolink.local');
}
if (!defined('PROLINK_MAIL_FROM_NAME')) {
    define('PROLINK_MAIL_FROM_NAME', 'ProLink');
}
if (!defined('PROLINK_PWD_RESET_OTP_TTL')) {
    define('PROLINK_PWD_RESET_OTP_TTL', 15 * 60);
}
