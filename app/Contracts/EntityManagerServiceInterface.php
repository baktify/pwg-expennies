<?php

namespace App\Contracts;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @mixin EntityManagerInterface
 */
interface EntityManagerServiceInterface
{
    public function sync($entity = null): void;

    public function delete($entity, bool $sync = false): void;

    public function clear(?string $entityClass = null): void;
}