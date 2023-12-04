<?php

namespace App;

use App\Contracts\AuthInterface;
use App\Contracts\UserInterface;
use App\Contracts\UserProviderServiceInterface;

class Auth implements AuthInterface
{
    private ?UserInterface $user;

    public function __construct(private readonly UserProviderServiceInterface $userService)
    {
    }

    public function user(): ?UserInterface
    {
        $userId = $_SESSION['user'] ?? null;

        if ($userId === null) {
            return null;
        }

        $user = $this->userService->find($userId);

        if ($user === null) {
            return null;
        }

        return $this->user = $user;
    }

    public function attempt(array $credentials): bool
    {
        $user = $this->userService->findOneBy($credentials);

        if (!$user || !password_verify($credentials['password'], $user->getPassword())) {
            return false;
        }

        $this->user = $user;

        session_regenerate_id();

        $_SESSION['user'] = $user->getId();

        return true;
    }

    public function logOut(): void
    {
        unset($_SESSION['user']);

        $this->user = null;
    }
}