<?php

namespace App\Contracts;

use App\Entities\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @mixin EntityManagerInterface
 */
interface EntityManagerServiceInterface
{
    public function sync($entity = null, bool $flush = true): void;

    public function delete($entity, bool $sync = false): void;

    public function clear(?string $entityClass = null): void;

    public function enableAuthenticatedUserFilter(int $userId): void;
}