<?php

declare(strict_types=1);

namespace Freemium\Infrastructure\Service;

use Freemium\Domain\IdGenerator;

use function ord;
use function mb_strlen;
use function random_bytes;

class CustomIdGenerator implements IdGenerator
{
    public function generate(?string $prefix = '', int $length = 36): string
    {
        $length = $length - strlen($prefix);
        $charlist = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $listLen = mb_strlen($charlist, '8bit');

        $pos = 0;
        $bytes = random_bytes($length);
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $pos = ($pos + ord($bytes[$i])) % $listLen;
            $result .= $charlist[$pos];
        }

        return $prefix . $result;
    }
}
