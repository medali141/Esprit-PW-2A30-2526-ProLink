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
        // Default list — edit to add/remove words appropriate for your app/language.
        return [
            'merde', 'putain', 'salope', 'connard', 'pute', // french common swearwords
            'fuck', 'shit', 'bitch', 'asshole' // english common swearwords
        ];
    }

    /**
     * Moderates a given text.
     * Returns ['ok' => bool, 'reason' => string, 'matched' => array]
     */
    public static function moderate(string $text): array
    {
        $res = ['ok' => true, 'reason' => '', 'matched' => []];
        $t = mb_strtolower($text);

        // Local banned words check (word boundaries)
        $found = [];
        foreach (self::localBannedWords() as $w) {
            if ($w === '') continue;
            if (preg_match('/\b' . preg_quote($w, '/') . '\b/ui', $t)) {
                $found[] = $w;
            }
        }
        if (!empty($found)) {
            $res['ok'] = false;
            $res['reason'] = 'Message refusé : langage inapproprié détecté.';
            $res['matched'] = $found;
            return $res;
        }

        // Optional: use OpenAI Moderation if API key provided (via environment)
        $key = getenv('OPENAI_API_KEY');
        if ($key && trim($key) !== '') {
            $payload = json_encode(['model' => 'omni-moderation-latest', 'input' => $text]);
            $ch = curl_init('https://api.openai.com/v1/moderations');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $key,
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            $resp = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($resp !== false && $code >= 200 && $code < 300) {
                $data = json_decode($resp, true);
                if (is_array($data) && isset($data['results'][0]['flagged']) && $data['results'][0]['flagged']) {
                    $res['ok'] = false;
                    $res['reason'] = 'Message refusé : bloqué par le filtre de modération.';
                    return $res;
                }
            }
            // on network/error, fallback to local check (already done)
        }

        return $res;
    }
}
