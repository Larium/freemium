<?php

declare(strict_types=1);

namespace Freemium\Math;

class Calculator
{
    public function divide(string $a, string $b, int $scale = 2): string
    {
        return bcdiv($a, $b, $scale);
    }

    public function multiple(string $a, string $b, int $scale = 2): string
    {
        return bcmul($a, $b, $scale);
    }
}
