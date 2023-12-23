<?php

namespace App\Services;

use App\Contracts\UserInterface;
use App\Contracts\UserProviderServiceInterface;
use App\DataObjects\UserRegisterData;
use App\Entities\User;
use Doctrine\ORM\EntityManagerInterface;

class UserProviderService implements UserProviderServiceInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function find(int $id): ?UserInterface
    {
        return $this->em->find(User::class, $id);
    }

    public function getByCredentials(array $credentials): ?UserInterface
    {
        return $this->em->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);
    }

    public function createUser(UserRegisterData $data): UserInterface
    {
        $user = new User();
        $user->setEmail($data->email);
        $user->setName($data->name);
        $user->setPassword(password_hash($data->password, PASSWORD_BCRYPT, ['cost' => 12]));

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}