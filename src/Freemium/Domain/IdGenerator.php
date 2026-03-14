<?php

declare(strict_types=1);

namespace Freemium\Domain;

interface IdGenerator
{
    public function generate(string $prefix = '', int $length = 36): string;
}
