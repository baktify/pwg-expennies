<?php

declare(strict_types=1);

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
        $user->setPassword($this->hash_password($data->password));

        $this->entityManagerService->persist($user);
        $this->entityManagerService->flush();

        return $user;
    }

    public function verifyUser(User $user): void
    {
        $user->setVerifiedAt(new \DateTime());
        $this->entityManagerService->sync();
    }

    public function updatePassword(User $user, string $password): User
    {
        $user->setPassword($this->hash_password($password));

        return $user;
    }

    public function hash_password(string $password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public function checkPasswordMatch(User $user, string $password): bool
    {
        $userCurrentPassword = $user->getPassword();

        return password_verify($password, $userCurrentPassword);
    }
}