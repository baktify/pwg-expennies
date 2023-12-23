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
}