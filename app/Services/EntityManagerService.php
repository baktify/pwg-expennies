<?php

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;

class EntityManagerService
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    public function flush(): void
    {
        $this->em->flush();
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
}