<?php
declare(strict_types=1);
// Debug helper: calls OpenAI Moderation endpoint and prints HTTP code, curl error and parsed response.
// WARNING: this prints the moderation API response but never prints the API key.
header('Content-Type: application/json; charset=utf-8');

$key = null;
if (!empty($_SERVER['OPENAI_API_KEY'])) {
    $key = $_SERVER['OPENAI_API_KEY'];
} elseif (!empty($_ENV['OPENAI_API_KEY'])) {
    $key = $_ENV['OPENAI_API_KEY'];
} else {
    $key = getenv('OPENAI_API_KEY');
}

if (!$key || trim($key) === '') {
    echo json_encode(['ok' => false, 'error' => 'OPENAI_API_KEY not found in $_SERVER/$_ENV/getenv'], JSON_PRETTY_PRINT);
    exit;
}

$payload = json_encode(['model' => 'omni-moderation-latest', 'input' => 'putain']);
$ch = curl_init('https://api.openai.com/v1/moderations');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $key,
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

$out = ['ok' => true, 'http_code' => $code, 'curl_error' => $curlErr, 'response' => null];
if ($resp !== false) {
    $json = json_decode($resp, true);
    $out['response'] = $json;
}

echo json_encode($out, JSON_PRETTY_PRINT);
