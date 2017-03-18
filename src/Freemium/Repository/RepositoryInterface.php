<?php

declare(strict_types=1);

namespace Freemium\Repository;

interface RepositoryInterface
{
    public function find($id);

    public function insert($entity);

    public function update($entity);

    public function remove($entity);
}
