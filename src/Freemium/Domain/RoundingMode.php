<?php

declare(strict_types=1);

namespace Freemium\Domain;

enum RoundingMode: int
{
    case HALF_UP = 1;   // PHP_ROUND_HALF_UP
    case HALF_DOWN = 2; // PHP_ROUND_HALF_DOWN
    case HALF_EVEN = 3; // PHP_ROUND_HALF_EVEN
    case CEILING = 4;   // PHP_ROUND_CEILING (PHP 8.4+) or ceil()
    case FLOOR = 5;    // PHP_ROUND_FLOOR (PHP 8.4+) or floor()

    public function roundToMinor(string $value): string
    {
        $float = (float) $value;
        $rounded = match ($this) {
            self::HALF_UP => (int) \round($float, 0, \PHP_ROUND_HALF_UP),
            self::HALF_DOWN => (int) \round($float, 0, \PHP_ROUND_HALF_DOWN),
            self::HALF_EVEN => (int) \round($float, 0, \PHP_ROUND_HALF_EVEN),
            self::CEILING => (int) \ceil($float),
            self::FLOOR => (int) \floor($float),
        };

        return (string) $rounded;
    }
}
