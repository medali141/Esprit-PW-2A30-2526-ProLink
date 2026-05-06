<?php
// Simple server-side wrapper for OpenAI Chat completions (POST only)
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}
$input = json_decode(file_get_contents('php://input'), true);
$prompt = isset($input['prompt']) ? (string) $input['prompt'] : '';
if ($prompt === '') {
    echo json_encode(['ok' => false, 'error' => 'Prompt vide']);
    exit;
}
// Retrieve API key from environment
$key = null;
if (!empty($_SERVER['OPENAI_API_KEY'])) $key = $_SERVER['OPENAI_API_KEY'];
elseif (!empty($_ENV['OPENAI_API_KEY'])) $key = $_ENV['OPENAI_API_KEY'];
else $key = getenv('OPENAI_API_KEY');
if (!$key || trim($key) === '') {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'OpenAI key not configured on server']);
    exit;
}
// Call OpenAI Chat Completions (gpt-4o-mini or gpt-4o if available)
$payload = json_encode([
    'model' => 'gpt-4o-mini',
    'messages' => [ ['role' => 'user', 'content' => $prompt] ],
    'temperature' => 0.6,
    'max_tokens' => 400,
]);
$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $key,
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_TIMEOUT, 12);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);
if ($resp === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Request failed: ' . $err]);
    exit;
}
$data = json_decode($resp, true);
if (!is_array($data)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Invalid response']);
    exit;
}
// Extract text
$text = '';
if (isset($data['choices'][0]['message']['content'])) {
    $text = (string) $data['choices'][0]['message']['content'];
} elseif (isset($data['choices'][0]['text'])) {
    $text = (string) $data['choices'][0]['text'];
}
echo json_encode(['ok' => true, 'reply' => $text, 'raw' => $data]);
