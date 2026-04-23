<?php
/**
 * init.php
 * Central bootstrap: session hardening, base URL, and small helpers.
 * Include this file at the very top of every public page (before any output).
 */

// --- Secure session cookie params ---
// Only set cookie params if a session has NOT yet been started. Changing cookie params
// on an active session triggers a PHP warning (can't change after headers sent).
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
$httponly = true;
$samesite = 'Lax';
if (session_status() === PHP_SESSION_NONE) {
    // PHP >= 7.3 supports array options including samesite
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite,
        ]);
    } else {
        // samesite cannot be set via session_set_cookie_params on older PHP; keep best-effort
        session_set_cookie_params(0, '/', $_SERVER['HTTP_HOST'] ?? '', $secure, $httponly);
    }

    // --- Start session if not already started ---
    session_start();
} else {
    // Session already active; do not change cookie parameters.
}

// --- Basic inactivity timeout (30 minutes) ---
$timeout = 1800; // seconds
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
    // last request was more than $timeout ago
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['LAST_ACTIVITY'] = time();

// --- Periodically regenerate session id (every 5 minutes) ---
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} elseif (time() - $_SESSION['CREATED'] > 300) {
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
}

// --- Compute a base URL pointing to the /view folder so views can build absolute links ---
$projectFolder = basename(__DIR__);
$root = $projectFolder ? '/' . $projectFolder : '';
$viewRoot = $root . '/view';
$host = $_SERVER['HTTP_HOST'] ?? '';
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$baseUrl = $host ? $scheme . '://' . $host . $viewRoot : $viewRoot;

// Expose small helper functions
function requireLogin() {
    global $baseUrl;
    if (empty($_SESSION['user'])) {
        header('Location: ' . ($baseUrl ?? '') . '/FrontOffice/login.php');
        exit;
    }
}

function isLoggedIn() {
    return !empty($_SESSION['user']);
}

// mark as included so other components can check
if (!defined('APP_INIT')) define('APP_INIT', true);

// End of init.php
