<?php
declare(strict_types=1);

/**
 * Content moderation helper.
 * - Uses a local banned-words list for quick checks.
 * - Optionally calls OpenAI Moderation API when environment variable OPENAI_API_KEY is set.
 *
 * IMPORTANT: Do NOT hardcode API keys. Set the key in the environment as OPENAI_API_KEY.
 */
class ContentModeration
{
    /** @return array<int,string> */
    private static function localBannedWords(): array
    {
        // Expanded default list — edit to add/remove words appropriate for your app/language.
        // Keep entries lowercase. This list focuses on common profanity and insults
        // but avoids repeating slurs targeting protected groups. Use OpenAI moderation
        // in addition for broader policy checks.
        return [
            // French
            'merde','putain','putainde','salope','salopee','salopee','connard','connasse','con','conne',
            'pute','putes','enculer','enculé','encule','nique','niquer','niqueur','niqueuse','salaud','salauds',
            'batard','bâtard','bordel','chiant','chiants','chier','cul','bite','branleur','branleuse',
            'sucer','branler','pétasse','garce','taré','tarée','abruti','abrutie','nul','nullos',
            // English
            'fuck','fucked','fucking','motherfucker','motherfuck','shit','shitty','bitch','bitches','asshole',
            'bastard','damn','dammit','crap','cunt','cock','dick','piss','pissed','whore','slut',
            // Misc common obfuscation targets (short forms)
            'fkr','mf','wtf'
        ];
    }

    /**
     * Call OpenAI Moderation endpoint with a small retry/backoff strategy.
     * Returns array with keys: ok (bool - 2xx response), http_code, curl_err, response (raw body)
     */
    private static function callOpenAIModeration(string $text, string $key, int $attempts = 3, int $timeout = 6): array
    {
        $payload = json_encode(['model' => 'omni-moderation-latest', 'input' => $text]);
        $last = ['ok' => false, 'http_code' => 0, 'curl_err' => '', 'response' => null];
        for ($i = 0; $i < $attempts; $i++) {
            $ch = curl_init('https://api.openai.com/v1/moderations');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $key,
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            $last['http_code'] = (int) $code;
            $last['curl_err'] = $curlErr ?: '';
            $last['response'] = $resp;
            if ($resp !== false && $code >= 200 && $code < 300) {
                $last['ok'] = true;
                break;
            }

            // If rate limited, wait exponentially and retry
            if ((int) $code === 429) {
                $wait = (int) pow(2, $i); // 1,2,4...
                if ($wait < 1) $wait = 1;
                sleep($wait);
                continue;
            }

            // On timeout or network error, do a short retry
            if ($resp === false || (int) $code === 0) {
                sleep(1);
                continue;
            }

            // Other non-retriable error: stop
            break;
        }

        return $last;
    }

    /**
     * Normalize text for simple local matching: lower-case, transliterate accents,
     * map common leetspeak symbols to letters and remove non-alphanumerics.
     */
    private static function normalizeForMatching(string $text): string
    {
        $s = mb_strtolower($text, 'UTF-8');
        $trans = @iconv('UTF-8', 'ASCII//TRANSLIT', $s);
        if ($trans !== false) {
            $s = $trans;
        }
        $map = [
            '@' => 'a', '4' => 'a',
            '3' => 'e', '1' => 'i', '!' => 'i',
            '0' => 'o', '$' => 's', '5' => 's',
            '+' => 't', '7' => 't', '2' => 'z', '8' => 'b'
        ];
        $s = strtr($s, $map);
        $s = preg_replace('/[^a-z0-9]/', '', $s);
        return $s;
    }

    /**
     * Moderates a given text.
     * Returns ['ok' => bool, 'reason' => string, 'matched' => array]
     */
    public static function moderate(string $text): array
    {
        $res = ['ok' => true, 'reason' => '', 'matched' => []];
        $t = mb_strtolower($text);

        // Try multiple places for the key so PHP under Apache picks it up:
        $key = null;
        if (!empty($_SERVER['OPENAI_API_KEY'])) {
            $key = $_SERVER['OPENAI_API_KEY'];
        } elseif (!empty($_ENV['OPENAI_API_KEY'])) {
            $key = $_ENV['OPENAI_API_KEY'];
        } else {
            $key = getenv('OPENAI_API_KEY');
        }

        // Local banned words check (word boundaries) + normalized check to catch obfuscations
        $found = [];
        $normalized = self::normalizeForMatching($text);
        foreach (self::localBannedWords() as $w) {
            if ($w === '') continue;
            if (preg_match('/\b' . preg_quote($w, '/') . '\b/ui', $t) || strpos($normalized, $w) !== false) {
                $found[] = $w;
            }
        }

        // Prepare logging path (used when calling OpenAI)
        $logDir = __DIR__ . '/../logs';
        $logFile = $logDir . '/moderation.log';
        if (!is_dir($logDir)) {@mkdir($logDir, 0755, true);} 

        // If local matches found, prefer to confirm with OpenAI when possible
        if (!empty($found)) {
            // No API key -> return local decision immediately
            if (!$key || trim($key) === '') {
                $res['ok'] = false;
                $res['reason'] = 'Message refusé : langage inapproprié détecté.';
                $res['matched'] = $found;
                return $res;
            }

            // Call OpenAI to confirm; if OpenAI explicitly says NOT flagged, allow the message.
            $call = self::callOpenAIModeration($text, $key, 3, 6);
            $resp = $call['response'];
            $code = $call['http_code'];
            $curlErr = $call['curl_err'];

            $flagged = false;
            if ($resp !== false && $code >= 200 && $code < 300) {
                $data = json_decode($resp, true);
                if (is_array($data) && isset($data['results'][0]['flagged']) && $data['results'][0]['flagged']) {
                    // OpenAI flagged -> block
                    $flagged = true;
                    $res['ok'] = false;
                    $res['reason'] = 'Message refusé : bloqué par le filtre de modération.';
                    $res['matched'] = $found;
                    $entry = date('c') . " | openai_moderation | flagged=1 | http=" . (int)
                        $code . " | curl_err=" . ($curlErr ?: '-') . " | matched_local=" . implode(',', $found) . PHP_EOL;
                    @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
                    return $res;
                }

                // OpenAI returned OK (not flagged) -> allow the message despite local match
                $entry = date('c') . " | openai_moderation | allowed_by_openai | flagged=0 | http=" . (int)
                    $code . " | curl_err=" . ($curlErr ?: '-') . " | matched_local=" . implode(',', $found) . PHP_EOL;
                @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
                $res['ok'] = true;
                $res['reason'] = '';
                $res['matched'] = [];
                return $res;
            }

            // On network error or non-2xx (timeout, 429, etc.), log and fall back to local block
            $entry = date('c') . " | openai_moderation | error_or_no_response | flagged=" . ($flagged ? '1' : '0') . " | http=" . (int)
                $code . " | curl_err=" . ($curlErr ?: '-') . " | matched_local=" . implode(',', $found) . PHP_EOL;
            @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);

            $res['ok'] = false;
            $res['reason'] = 'Message refusé : langage inapproprié détecté.';
            $res['matched'] = $found;
            return $res;
        }

        // If no local match, optionally call OpenAI to detect other policy violations
        if ($key && trim($key) !== '') {
            $call = self::callOpenAIModeration($text, $key, 3, 6);
            $resp = $call['response'];
            $code = $call['http_code'];
            $curlErr = $call['curl_err'];

            $flagged = false;
            if ($resp !== false && $code >= 200 && $code < 300) {
                $data = json_decode($resp, true);
                if (is_array($data) && isset($data['results'][0]['flagged']) && $data['results'][0]['flagged']) {
                    $flagged = true;
                    $res['ok'] = false;
                    $res['reason'] = 'Message refusé : bloqué par le filtre de modération.';
                    $entry = date('c') . " | openai_moderation | flagged=1 | http=" . (int)
                        $code . " | curl_err=" . ($curlErr ?: '-') . " | matched_local=" . implode(',', $found) . PHP_EOL;
                    @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
                    return $res;
                }
            }

            $entry = date('c') . " | openai_moderation | flagged=" . ($flagged ? '1' : '0') . " | http=" . (int)
                $code . " | curl_err=" . ($curlErr ?: '-') . " | matched_local=" . implode(',', $found) . PHP_EOL;
            @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
        }

        return $res;
    }
}
