<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Service;

use Ramsey\Uuid\Uuid;
use Freemium\Domain\IdGenerator;

class Uuid4IdGenerator implements IdGenerator
{
    public function generate(string $prefix = '', int $length = 36): string
    {
        return Uuid::uuid4()->__toString();
    }
}
