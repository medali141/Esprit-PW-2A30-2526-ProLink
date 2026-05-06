<?php
// Simple server-side wrapper for OpenAI Chat completions (POST only)
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$prompt = isset($input['prompt']) ? trim((string) $input['prompt']) : '';
if ($prompt === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Prompt vide'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Retrieve OpenAI API key if configured (may be empty if using Hugging Face only)
$key = null;
if (!empty($_SERVER['OPENAI_API_KEY'])) $key = $_SERVER['OPENAI_API_KEY'];
elseif (!empty($_ENV['OPENAI_API_KEY'])) $key = $_ENV['OPENAI_API_KEY'];
else $key = getenv('OPENAI_API_KEY');

// Allow model override via env var OPENAI_CHAT_MODEL (default to gpt-3.5-turbo)
$model = 'gpt-3.5-turbo';
if (!empty($_SERVER['OPENAI_CHAT_MODEL'])) $model = $_SERVER['OPENAI_CHAT_MODEL'];
elseif (!empty($_ENV['OPENAI_CHAT_MODEL'])) $model = $_ENV['OPENAI_CHAT_MODEL'];
else {
    $envModel = getenv('OPENAI_CHAT_MODEL');
    if (!empty($envModel)) $model = $envModel;
}

$debug = false;
if (!empty($_SERVER['OPENAI_DEBUG']) || !empty($_ENV['OPENAI_DEBUG']) || getenv('OPENAI_DEBUG')) {
    $debug = true;
}

/**
 * Local fallback answers for common questions when no provider is available.
 * Returns a short reply string or null if no canned answer matches.
 */
function localFallbackAnswer(string $prompt): ?string {
    $p = mb_strtolower($prompt, 'UTF-8');
    $clean = preg_replace('/[^a-z0-9\p{L}\s\-]/u', ' ', $p);
    if (preg_match('/\b(cr[eé]er|comment creer|comment cr[eé]er|nouveau sujet|cr[eé]ation de sujet)\b/u', $clean)) {
        return "Pour créer un sujet : choisissez une catégorie, cliquez sur \"Nouveau sujet\", saisissez un titre clair et votre message, puis cliquez sur Publier. Évitez les insultes ou spam, le message peut être modéré.";
    }
    if (preg_match('/\b(r[eè]gle|moderation|mod[eé]ration|charte|respect)\b/u', $clean)) {
        return "Règles du forum : restez poli, pas d'insultes ni de spam. Les messages abusifs peuvent être supprimés ou modérés automatiquement.";
    }
    if (preg_match('/\b(supprim|supprimer).*sujet\b/u', $clean)) {
        return "Seuls les modérateurs peuvent supprimer un sujet. Contactez un administrateur si nécessaire.";
    }
    if (preg_match('/\b(carri[eè]re|conseil).*informatique|quelle carri[eè]re|conseil carri[eè]re\b/u', $clean)) {
        return "Conseil carrière (bref) : apprenez les bases (algorithmes, SQL), pratiquez via des projets, puis spécialisez‑vous (dev, sécurité, data) selon vos intérêts.";
    }
    if (preg_match('/\b(bonjour|salut|hello)\b/u', $clean)) {
        return "Bonjour ! Posez votre question et je ferai de mon mieux pour aider.";
    }
    return null;
}
// Decide provider: prefer Hugging Face if token is present, otherwise OpenAI
$hfToken = null;
if (!empty($_SERVER['HUGGINGFACE_TOKEN'])) $hfToken = $_SERVER['HUGGINGFACE_TOKEN'];
elseif (!empty($_ENV['HUGGINGFACE_TOKEN'])) $hfToken = $_ENV['HUGGINGFACE_TOKEN'];
else $hfToken = getenv('HUGGINGFACE_TOKEN');

$hfModel = 'tiiuae/falcon-7b-instruct';
if (!empty($_SERVER['HUGGINGFACE_MODEL'])) $hfModel = $_SERVER['HUGGINGFACE_MODEL'];
elseif (!empty($_ENV['HUGGINGFACE_MODEL'])) $hfModel = $_ENV['HUGGINGFACE_MODEL'];
else { $m = getenv('HUGGINGFACE_MODEL'); if (!empty($m)) $hfModel = $m; }

$logDir = __DIR__ . '/../../logs';
$logFile = $logDir . '/chatbot.log';
if (!is_dir($logDir)) {@mkdir($logDir, 0755, true);} 

// Sanitize and log incoming prompt (short snippet only) for debugging
$sanitizedPrompt = preg_replace('/[\r\n\t]+/', ' ', trim($prompt));
if ($sanitizedPrompt === '') $sanitizedPrompt = '(empty)';
$snippet = mb_substr($sanitizedPrompt, 0, 300);
@file_put_contents($logFile, date('c') . " | incoming_prompt | snippet=" . str_replace("\n", ' ', $snippet) . PHP_EOL, FILE_APPEND | LOCK_EX);

// Helper: public error message to show users when provider fails
$publicProviderError = 'Assistant temporairement indisponible, veuillez réessayer plus tard.';

// If Hugging Face token present, try HF inference first
if ($hfToken && trim($hfToken) !== '') {
    $hfUrl = 'https://api-inference.huggingface.co/models/' . rawurlencode($hfModel);
    $hfPayload = json_encode(['inputs' => $prompt, 'parameters' => ['max_new_tokens' => 300, 'temperature' => 0.6]]);
    $ch = curl_init($hfUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $hfToken,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $hfPayload);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $hfResp = curl_exec($ch);
    $hfCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hfErr = curl_error($ch);
    curl_close($ch);

    // Log basic outcome (no secrets)
    $entry = date('c') . " | provider=hf | model=" . $hfModel . " | http=" . (int)$hfCode . " | curl_err=" . ($hfErr ?: '-') . PHP_EOL;
    @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);

    if ($hfResp !== false && $hfCode >= 200 && $hfCode < 300) {
        $hfData = json_decode($hfResp, true);
        $reply = '';
        if (is_array($hfData)) {
            // common formats: [{"generated_text": "..."}] or {"generated_text":"..."}
            if (isset($hfData[0]) && is_array($hfData[0]) && isset($hfData[0]['generated_text'])) {
                $reply = (string)$hfData[0]['generated_text'];
            } elseif (isset($hfData['generated_text'])) {
                $reply = (string)$hfData['generated_text'];
            } elseif (isset($hfData[0]) && is_string($hfData[0])) {
                $reply = (string)$hfData[0];
            } else {
                // Unexpected structure — fallback to full response text
                $reply = trim($hfResp);
            }
        } else {
            // Not JSON — use raw text
            $reply = trim($hfResp);
        }

        $reply = trim($reply);
        if ($reply === '') {
            // empty reply — treat as error
            if ($debug) {
                echo json_encode(['ok' => false, 'error' => 'Empty reply from HuggingFace', 'raw' => $hfResp], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['ok' => false, 'error' => $publicProviderError], JSON_UNESCAPED_UNICODE);
            }
            exit;
        }

        echo json_encode(['ok' => true, 'reply' => $reply], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // If HF returned an error or non-2xx, optionally fallback to OpenAI if key present
    $hfErrEntry = date('c') . " | provider=hf_error | http=" . (int)$hfCode . " | curl_err=" . ($hfErr ?: '-') . " | resp_snippet=" . substr(($hfResp ?: ''),0,400) . PHP_EOL;
    @file_put_contents($logFile, $hfErrEntry, FILE_APPEND | LOCK_EX);

    // fall through to OpenAI if available
}

// Fallback to OpenAI (or local canned answers if no OpenAI key)
if (!$key || trim($key) === '') {
    // Try a local canned fallback before giving up
    $local = localFallbackAnswer($prompt);
    if ($local !== null) {
        @file_put_contents($logFile, date('c') . " | provider=local_fallback | snippet=" . substr($local,0,200) . PHP_EOL, FILE_APPEND | LOCK_EX);
        echo json_encode(['ok' => true, 'reply' => $local], JSON_UNESCAPED_UNICODE);
        exit;
    }

    @file_put_contents($logFile, date('c') . " | no_openai_key_and_hf_failed" . PHP_EOL, FILE_APPEND | LOCK_EX);
    if ($debug) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'No OpenAI key configured and HuggingFace call failed.'], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => $publicProviderError], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

$payload = json_encode([
    'model' => $model,
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

// Log OpenAI basic result
@file_put_contents($logFile, date('c') . " | provider=openai | model=" . $model . " | http=" . (int)$code . " | curl_err=" . ($err ?: '-') . PHP_EOL, FILE_APPEND | LOCK_EX);

if ($resp === false) {
    if ($debug) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Request failed: ' . $err], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => $publicProviderError], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

$data = json_decode($resp, true);
if (!is_array($data)) {
    http_response_code(500);
    $out = ['ok' => false, 'error' => $publicProviderError];
    if ($debug) $out['raw'] = $resp;
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
}

// OpenAI returned an error structure
if (isset($data['error'])) {
    $msg = is_string($data['error']) ? $data['error'] : ($data['error']['message'] ?? json_encode($data['error']));
    // log raw error when debug
    @file_put_contents($logFile, date('c') . " | openai_error_raw=" . substr(json_encode($data),0,800) . PHP_EOL, FILE_APPEND | LOCK_EX);
    http_response_code($code >= 400 ? $code : 500);
    $out = ['ok' => false, 'error' => ($debug ? $msg : $publicProviderError)];
    if ($debug) $out['raw'] = $data;
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
}

// Ensure choices exist
if (empty($data['choices']) || !isset($data['choices'][0])) {
    http_response_code(500);
    $out = ['ok' => false, 'error' => $publicProviderError];
    if ($debug) $out['raw'] = $data;
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit;
}

$choice = $data['choices'][0];
$text = '';
if (isset($choice['message']['content'])) {
    $text = (string) $choice['message']['content'];
} elseif (isset($choice['text'])) {
    $text = (string) $choice['text'];
}

$text = trim($text);
$out = ['ok' => true, 'reply' => $text];
if ($debug) $out['raw'] = $data;
echo json_encode($out, JSON_UNESCAPED_UNICODE);
