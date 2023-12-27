<?php

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\DataObjects\UserRegisterData;
use App\Entities\User;

class UserService
{
    public function __construct(private readonly EntityManagerServiceInterface $entityManagerService)
    {
    }

    public function find(int $id): ?User
    {
        return $this->entityManagerService->find(User::class, $id);
    }

    public function getByCredentials(array $credentials): ?User
    {
        return $this->entityManagerService->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);
    }

    public function createUser(UserRegisterData $data): User
    {
        $user = new User();
        $user->setEmail($data->email);
        $user->setName($data->name);
        $user->setPassword(password_hash($data->password, PASSWORD_BCRYPT, ['cost' => 12]));

        $this->entityManagerService->persist($user);
        $this->entityManagerService->flush();

        return $user;
    }

    public function verifyUser(User $user): void
    {
        $user->setVerifiedAt(new \DateTime());
        $this->entityManagerService->sync();
    }
}