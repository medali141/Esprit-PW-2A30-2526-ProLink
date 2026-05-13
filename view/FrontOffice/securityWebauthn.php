<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../../controller/AuthController.php';
require_once __DIR__ . '/../../controller/UserP.php';
require_once __DIR__ . '/../../model/User.php';

header('Content-Type: application/json; charset=UTF-8');

function b64urlEncode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function b64urlDecode(string $value): string {
    $raw = strtr($value, '-_', '+/');
    $pad = strlen($raw) % 4;
    if ($pad > 0) {
        $raw .= str_repeat('=', 4 - $pad);
    }
    return (string) base64_decode($raw, true);
}

function jsonOut(array $payload, int $code = 200): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

$auth = new AuthController();
$user = $auth->profile();
if (!$user) {
    jsonOut(['ok' => false, 'message' => 'Non authentifie'], 401);
}

$action = (string) ($_POST['action'] ?? $_GET['action'] ?? '');
$userId = (int) ($user['iduser'] ?? 0);
if ($userId <= 0) {
    jsonOut(['ok' => false, 'message' => 'Utilisateur invalide'], 400);
}

if ($action === 'register_challenge') {
    $challenge = b64urlEncode(random_bytes(32));
    $_SESSION['webauthn_register_challenge_' . $userId] = $challenge;
    jsonOut([
        'ok' => true,
        'challenge' => $challenge,
        'userId' => (string) $userId,
        'user_handle' => b64urlEncode('prolink-user-' . $userId),
    ]);
}

if ($action === 'register_finish') {
    $credentialId = trim((string) ($_POST['credential_id'] ?? ''));
    $clientDataB64 = trim((string) ($_POST['client_data_json'] ?? ''));
    $expected = (string) ($_SESSION['webauthn_register_challenge_' . $userId] ?? '');
    if ($expected === '' || $credentialId === '' || $clientDataB64 === '') {
        jsonOut(['ok' => false, 'message' => 'Données incomplètes'], 400);
    }
    $clientDataRaw = b64urlDecode($clientDataB64);
    $clientData = json_decode($clientDataRaw, true);
    if (!is_array($clientData)) {
        jsonOut(['ok' => false, 'message' => 'clientData invalide'], 400);
    }
    $challenge = (string) ($clientData['challenge'] ?? '');
    $type = (string) ($clientData['type'] ?? '');
    if (!hash_equals($expected, $challenge) || $type !== 'webauthn.create') {
        jsonOut(['ok' => false, 'message' => 'Challenge invalide'], 400);
    }

    $up = new UserP();
    $up->updateUser(
        new User(
            (string) ($user['nom'] ?? ''),
            (string) ($user['prenom'] ?? ''),
            (string) ($user['email'] ?? ''),
            (string) ($user['mdp'] ?? ''),
            (string) ($user['type'] ?? ''),
            (int) ($user['age'] ?? 0),
            User::KEEP_VALUE,
            User::KEEP_VALUE,
            User::KEEP_VALUE,
            User::KEEP_VALUE,
            User::KEEP_VALUE,
            $credentialId,
            1
        ),
        $userId
    );
    unset($_SESSION['webauthn_register_challenge_' . $userId]);
    $_SESSION['user']['face_id_credential_id'] = $credentialId;
    $_SESSION['user']['face_id_enabled'] = 1;
    jsonOut(['ok' => true, 'message' => 'Face ID active']);
}

if ($action === 'auth_challenge') {
    $challenge = b64urlEncode(random_bytes(32));
    $_SESSION['webauthn_auth_challenge_' . $userId] = $challenge;
    $credId = trim((string) ($user['face_id_credential_id'] ?? ''));
    if ($credId === '' || (int) ($user['face_id_enabled'] ?? 0) !== 1) {
        jsonOut(['ok' => false, 'message' => 'Face ID non configure'], 400);
    }
    jsonOut(['ok' => true, 'challenge' => $challenge, 'credential_id' => $credId]);
}

if ($action === 'auth_finish') {
    $credentialId = trim((string) ($_POST['credential_id'] ?? ''));
    $clientDataB64 = trim((string) ($_POST['client_data_json'] ?? ''));
    $expected = (string) ($_SESSION['webauthn_auth_challenge_' . $userId] ?? '');
    $saved = trim((string) ($user['face_id_credential_id'] ?? ''));
    if ($expected === '' || $credentialId === '' || $clientDataB64 === '' || $saved === '') {
        jsonOut(['ok' => false, 'message' => 'Données incomplètes'], 400);
    }
    if (!hash_equals($saved, $credentialId)) {
        jsonOut(['ok' => false, 'message' => 'Credential invalide'], 400);
    }
    $clientDataRaw = b64urlDecode($clientDataB64);
    $clientData = json_decode($clientDataRaw, true);
    if (!is_array($clientData)) {
        jsonOut(['ok' => false, 'message' => 'clientData invalide'], 400);
    }
    $challenge = (string) ($clientData['challenge'] ?? '');
    $type = (string) ($clientData['type'] ?? '');
    if (!hash_equals($expected, $challenge) || $type !== 'webauthn.get') {
        jsonOut(['ok' => false, 'message' => 'Challenge invalide'], 400);
    }
    if (!isset($_SESSION['checkout_faceid_ok']) || !is_array($_SESSION['checkout_faceid_ok'])) {
        $_SESSION['checkout_faceid_ok'] = [];
    }
    $_SESSION['checkout_faceid_ok'][$userId] = time();
    unset($_SESSION['webauthn_auth_challenge_' . $userId]);
    jsonOut(['ok' => true, 'message' => 'Face ID verifie']);
}

if ($action === 'disable_face_id') {
    $up = new UserP();
    $up->updateUser(
        new User(
            (string) ($user['nom'] ?? ''),
            (string) ($user['prenom'] ?? ''),
            (string) ($user['email'] ?? ''),
            (string) ($user['mdp'] ?? ''),
            (string) ($user['type'] ?? ''),
            (int) ($user['age'] ?? 0),
            User::KEEP_VALUE,
            User::KEEP_VALUE,
            User::KEEP_VALUE,
            User::KEEP_VALUE,
            User::KEEP_VALUE,
            '',
            0
        ),
        $userId
    );
    $_SESSION['user']['face_id_credential_id'] = null;
    $_SESSION['user']['face_id_enabled'] = 0;
    jsonOut(['ok' => true, 'message' => 'Face ID desactive']);
}

jsonOut(['ok' => false, 'message' => 'Action inconnue'], 400);
