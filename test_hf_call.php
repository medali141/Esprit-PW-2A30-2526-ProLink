<?php
declare(strict_types=1);
// Debug helper: calls Hugging Face Inference API and prints HTTP code, curl error and parsed response.
header('Content-Type: application/json; charset=utf-8');

$hfToken = null;
if (!empty($_SERVER['HUGGINGFACE_TOKEN'])) $hfToken = $_SERVER['HUGGINGFACE_TOKEN'];
elseif (!empty($_ENV['HUGGINGFACE_TOKEN'])) $hfToken = $_ENV['HUGGINGFACE_TOKEN'];
else $hfToken = getenv('HUGGINGFACE_TOKEN');

if (!$hfToken || trim($hfToken) === '') {
    echo json_encode(['ok' => false, 'error' => 'HUGGINGFACE_TOKEN not found in $_SERVER/$_ENV/getenv'], JSON_PRETTY_PRINT);
    exit;
}

$hfModel = 'tiiuae/falcon-7b-instruct';
$envModel = null;
if (!empty($_SERVER['HUGGINGFACE_MODEL'])) $envModel = $_SERVER['HUGGINGFACE_MODEL'];
elseif (!empty($_ENV['HUGGINGFACE_MODEL'])) $envModel = $_ENV['HUGGINGFACE_MODEL'];
else $envModel = getenv('HUGGINGFACE_MODEL');
if (!empty($envModel)) $hfModel = $envModel;

$hfUrl = 'https://api-inference.huggingface.co/models/' . rawurlencode($hfModel);
$payload = json_encode(['inputs' => 'Bonjour, peux-tu répondre brièvement ?', 'parameters' => ['max_new_tokens' => 60]]);

$ch = curl_init($hfUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $hfToken,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

$out = ['ok' => true, 'http_code' => $code, 'curl_error' => $err, 'response_raw' => $resp];
if ($resp !== false) {
    $json = json_decode($resp, true);
    $out['response'] = $json;
}

echo json_encode($out, JSON_PRETTY_PRINT);
