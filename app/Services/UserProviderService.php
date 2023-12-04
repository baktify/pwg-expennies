<?php

namespace App\Services;

use App\Contracts\UserInterface;
use App\Contracts\UserProviderServiceInterface;
use App\Entities\User;
use Doctrine\ORM\EntityManager;

class UserProviderService implements UserProviderServiceInterface
{
    public function __construct(private readonly EntityManager $em)
    {
    }

    public function find(int $id): ?UserInterface
    {
        return $this->em->find(User::class, $id);
    }

    public function findOneBy(array $credentials): ?UserInterface
    {
        return $this->em->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);
    }
}