<?php

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\DataObjects\UserProfileData;
use App\Entities\User;

class UserProfileService
{
    public function __construct(private readonly EntityManagerServiceInterface $entityManagerService)
    {
    }

    public function get(int $userId): UserProfileData
    {
        $user = $this->entityManagerService->find(User::class, $userId);

        return new UserProfileData(
            $user->getEmail(),
            $user->getName(),
            $user->hasTwoFactorAuthEnabled()
        );
    }

    public function update(User $user, UserProfileData $data): void
    {
        $user->setName($data->name);
        $user->setTwoFactor($data->twoFactor);

        $this->entityManagerService->sync($user);
    }
}