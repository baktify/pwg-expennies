<?php

namespace App;

use App\Contracts\AuthInterface;
use App\Contracts\UserInterface;
use App\Entities\User;
use Doctrine\ORM\EntityManager;

class Auth implements AuthInterface
{
    private UserInterface $user;

    public function __construct(private readonly EntityManager $em)
    {
    }

    public function user(): ?UserInterface
    {
        $userId = $_SESSION['user'] ?? null;

        if ($userId === null) {
            return null;
        }

        $user = $this->em->getRepository(User::class)->find($userId);

        if ($user === null) {
            return null;
        }

        return $this->user = $user;
    }
}