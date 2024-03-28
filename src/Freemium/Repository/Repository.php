<?php

declare(strict_types=1);

namespace Freemium\Repository;

interface Repository
{
    public function insert($entity): void;

    public function update($entity): void;

    public function remove($entity): void;
}
