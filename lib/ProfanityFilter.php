<?php
declare(strict_types=1);

/**
 * Filtre de gros mots français / anglais / arabe (transliteré).
 * Utilisé pour refuser la création / modification de catégories et sujets
 * dont le titre, la description ou le contenu contient une insulte.
 *
 * La détection :
 *  - normalise le texte (minuscules, accents retirés, substitutions leet,
 *    répétitions collapsées) avant de chercher chaque mot de la liste avec
 *    une frontière de mot \b pour éviter les faux positifs ("cul" vs
 *    "culture").
 *
 * NB : la liste vise les insultes claires et reste volontairement courte
 * pour limiter les faux positifs sur un projet pédagogique.
 */
class ProfanityFilter
{
    /** @var list<string> mots interdits (déjà normalisés via normalize()) */
    private static array $words = [
        'putain', 'putin', 'merde', 'connard', 'connasse', 'enculer', 'encule',
        'salope', 'pute', 'pd', 'pede', 'pedale', 'niquer', 'nique', 'fdp',
        'batard', 'tg', 'ta gueule', 'gros con', 'connard',
        'fuck', 'fucking', 'shit', 'bitch', 'asshole', 'bastard', 'dick',
        'cunt', 'whore', 'slut', 'nigger', 'faggot', 'retard', 'motherfucker',
        'zebbi', 'sharmota', 'sharmouta', 'kahba', 'kos', 'khra',
    ];

    /**
     * Permet d'ajouter dynamiquement des mots (utile pour les tests ou pour
     * compléter la liste sans toucher au code).
     */
    public static function addWords(array $words): void
    {
        foreach ($words as $w) {
            $n = self::normalize((string) $w);
            if ($n !== '' && !in_array($n, self::$words, true)) {
                self::$words[] = $n;
            }
        }
    }

    public static function containsProfanity(string $text): bool
    {
        return self::firstMatch($text) !== null;
    }

    /**
     * Retourne le premier mot interdit trouvé (forme normalisée) ou null.
     */
    public static function firstMatch(string $text): ?string
    {
        $norm = self::normalize($text);
        if ($norm === '') {
            return null;
        }
        foreach (self::$words as $word) {
            $pattern = '/\b' . preg_quote($word, '/') . '\b/u';
            if (preg_match($pattern, $norm)) {
                return $word;
            }
        }
        return null;
    }

    /**
     * Vérifie plusieurs champs en une seule passe. Renvoie un message
     * d'erreur lisible si l'un d'eux contient une insulte, sinon null.
     */
    public static function checkAll(array $fields): ?string
    {
        foreach ($fields as $label => $value) {
            $hit = self::firstMatch((string) $value);
            if ($hit !== null) {
                return 'Le champ "' . $label . '" contient un terme interdit (« ' . $hit . ' »). Reformulez s\'il vous plaît.';
            }
        }
        return null;
    }

    /**
     * Normalisation : minuscule, accents retirés, substitutions leet (3->e,
     * 4->a, ...), répétitions collapsées (fuuuuck -> fuck).
     */
    private static function normalize(string $s): string
    {
        if ($s === '') return '';
        $s = function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
        $s = strtr($s, [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'å' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n', 'ÿ' => 'y',
        ]);
        $s = strtr($s, [
            '0' => 'o', '1' => 'i', '3' => 'e', '4' => 'a', '5' => 's',
            '7' => 't', '8' => 'b', '@' => 'a', '$' => 's', '!' => 'i',
        ]);
        $s = preg_replace('/(.)\1{2,}/u', '$1', $s) ?? $s;
        return $s;
    }
}
