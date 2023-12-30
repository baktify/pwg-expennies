<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\EntityManagerServiceInterface;
use App\Entities\PasswordReset;

class PasswordResetService
{
    public function __construct(
        private readonly EntityManagerServiceInterface $entityManagerService
    )
    {
    }

    public function create(string $email): PasswordReset
    {
        $passwordReset = new PasswordReset();
        $passwordReset->setEmail($email);
        $passwordReset->setExpiration(new \DateTime('+30 minutes'));
        $passwordReset->setToken(bin2hex(random_bytes(32)));

        return $passwordReset;
    }

    public function getByToken(string $token): PasswordReset
    {
        return $this->entityManagerService->getRepository(PasswordReset::class)->findOneBy(['token' => $token]);
    }

    public function deactivateAllActivePasswordResets(string $email): void
    {
        $this->entityManagerService->createQueryBuilder()
            ->update(PasswordReset::class, 'pr')
            ->set('pr.isActive', '0')
            ->where('pr.isActive = 1', 'pr.email = :email')
            ->setParameter(':email', $email)
            ->getQuery()
            ->execute();
    }

    public function verify(string $token): ?PasswordReset
    {
        return $this->entityManagerService->createQueryBuilder()
            ->select('pr')
            ->from(PasswordReset::class, 'pr')
            ->where(
                'pr.token = :token',
                'pr.isActive = 1',
                'pr.expiration >= :now'
            )
            ->setParameters([
                ':token' => $token,
                ':now' => new \DateTime()
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }
}