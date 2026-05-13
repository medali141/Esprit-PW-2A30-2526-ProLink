<?php
/**
 * Règles fonctionnelles du commerce catalogue / commandes.
 */
class CommerceRegles {
    public const POINTS_PER_10_TND = 1; // 1000 TND => 100 points
    public const DINAR_PER_POINT = 0.1; // 100 points => 10 TND
    /** @return list<string> */
    public static function allowedStatuts(): array {
        return [
            'brouillon',
            'en_attente_paiement',
            'payee',
            'en_preparation',
            'expediee',
            'livree',
            'annulee',
        ];
    }

    public static function canTransitionStatut(string $from, string $to): bool {
        if ($from === $to) {
            return true;
        }
        $graph = [
            'brouillon' => ['en_attente_paiement', 'annulee'],
            'en_attente_paiement' => ['payee', 'annulee'],
            'payee' => ['en_preparation', 'annulee'],
            'en_preparation' => ['expediee', 'annulee'],
            'expediee' => ['livree', 'annulee'],
            'livree' => [],
            'annulee' => [],
        ];
        return in_array($to, $graph[$from] ?? [], true);
    }

    public static function sanitizePage(int $page, int $maxPage = PHP_INT_MAX): int {
        if ($page < 1) {
            $page = 1;
        }
        if ($maxPage >= 1 && $page > $maxPage) {
            $page = $maxPage;
        }
        return $page;
    }

    public static function pointsFromAmount(float $amountTnd): int {
        if ($amountTnd <= 0) {
            return 0;
        }
        return (int) floor($amountTnd / 10);
    }

    public static function dinarFromPoints(int $points): float {
        if ($points <= 0) {
            return 0.0;
        }
        return $points * self::DINAR_PER_POINT;
    }
}
