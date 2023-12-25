<?php

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\Contracts\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\BadMethodCallException;

/**
 * @mixin EntityManagerInterface
 */
class EntityManagerService implements EntityManagerServiceInterface
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    public function __call(string $name, array $arguments)
    {
        if (method_exists($this->em, $name)) {
            return call_user_func_array([$this->em, $name], $arguments);
        }

        throw new BadMethodCallException('Undefined call method "' . $name . '"');
    }

    public function sync($entity = null, bool $flush = true): void
    {
        if ($entity) {
            $this->em->persist($entity);
        }

        if ($flush) {
            $this->em->flush();
        }
    }

    public function delete($entity, bool $sync = false): void
    {
        $this->em->remove($entity);

        if ($sync) {
            $this->sync(flush: true);
        }
    }

    public function clear(?string $entityClass = null): void
    {
        if ($entityClass === null) {
            $this->em->clear();

            return;
        }

        $unitOfWork = $this->em->getUnitOfWork();
        $entities = $unitOfWork->getIdentityMap()[$entityClass] ?? [];

        foreach ($entities as $entity) {
            $this->em->detach($entity);
        }
    }

    public function enableAuthenticatedUserFilter(int $userId): void
    {
        $this->getFilters()->enable('user')->setParameter('user_id', $userId);
    }
}