<?php

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\Entities\User;
use App\Entities\UserLoginCode;

class UserLoginCodeService
{
    public function __construct(private readonly EntityManagerServiceInterface $entityManagerService)
    {
    }

    public function generate(User $user): UserLoginCode
    {
        $code = random_int(100_000, 999_999);

        $userLoginCode = new UserLoginCode();
        $userLoginCode->setCode((string)$code);
        $userLoginCode->setExpiration(new \DateTime('+10 minutes'));
        $userLoginCode->setUser($user);

        $this->entityManagerService->sync($userLoginCode);

        return $userLoginCode;
    }

    public function verify(User $user, string $code): bool
    {
        $userLoginCode = $this->entityManagerService->getRepository(UserLoginCode::class)
            ->findOneBy(
                ['user' => $user, 'code' => $code, 'isActive' => 1]
            );

        if (!$userLoginCode) {
            return false;
        }

        if ($userLoginCode->getExpiration() <= new \DateTime()) {
            return false;
        }

        return true;
    }

    public function deactivateAllActiveCodes(User $user): void
    {
        $this->entityManagerService->createQueryBuilder()
            ->update(UserLoginCode::class, 'u')
            ->set('u.isActive', '0')
            ->where('u.isActive = 1')
            ->andWhere('u.user = :user')
            ->setParameter(':user', $user)
            ->getQuery()
            ->execute();
    }
}