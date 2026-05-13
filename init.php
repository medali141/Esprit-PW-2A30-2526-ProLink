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

// --- Session helpers ---------------------------------------------------------

function isLoggedIn() {
    return !empty($_SESSION['user']);
}

function currentUser() {
    return $_SESSION['user'] ?? null;
}

function currentRole() {
    $u = $_SESSION['user'] ?? null;
    return $u ? strtolower((string) ($u['type'] ?? '')) : '';
}

function hasRole($role) {
    return currentRole() === strtolower((string) $role);
}

/**
 * One-shot flash messages stored in the session. flashSet() writes a value
 * under a key, flashGet() reads and clears it. Used to surface auth messages
 * (e.g. "Veuillez vous connecter") on the login page after a redirect.
 */
function flashSet($key, $message) {
    if (!isset($_SESSION['__flash']) || !is_array($_SESSION['__flash'])) {
        $_SESSION['__flash'] = [];
    }
    $_SESSION['__flash'][$key] = (string) $message;
}

function flashGet($key) {
    if (!isset($_SESSION['__flash'][$key])) return null;
    $v = $_SESSION['__flash'][$key];
    unset($_SESSION['__flash'][$key]);
    return $v;
}

/**
 * Redirect the user to the login page if not authenticated. The current URL
 * is saved as `intended_url` so login.php can send the user back here after
 * a successful login.
 */
function requireLogin($message = null) {
    global $baseUrl;
    if (!empty($_SESSION['user'])) return;

    $intended = $_SERVER['REQUEST_URI'] ?? '';
    if ($intended !== '' && stripos($intended, '/login.php') === false
        && stripos($intended, '/register.php') === false) {
        $_SESSION['intended_url'] = $intended;
    }

    flashSet('auth', $message ?? 'Veuillez vous connecter pour accéder à cette page.');
    header('Location: ' . ($baseUrl ?? '') . '/login.php');
    exit;
}

/**
 * Require an authenticated user with a specific role (e.g. 'entrepreneur',
 * 'admin'). If not logged in, redirects to login. If logged in but with the
 * wrong role, redirects to the front-office home with a flash message.
 */
function requireRole($role, $message = null) {
    requireLogin($message);
    if (!hasRole($role)) {
        global $baseUrl;
        flashSet('auth', $message ?? 'Accès refusé : cette page est réservée aux ' . htmlspecialchars((string) $role) . 's.');
        header('Location: ' . ($baseUrl ?? '') . '/FrontOffice/home.php');
        exit;
    }
}

if (!defined('APP_INIT')) define('APP_INIT', true);
