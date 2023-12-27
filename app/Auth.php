<?php

namespace App;

use App\Contracts\AuthInterface;
use App\Contracts\SessionInterface;
use App\DataObjects\UserRegisterData;
use App\Services\UserService;
use App\Entities\User;

class Auth implements AuthInterface
{
    private ?User $user = null;

    public function __construct(
        private readonly UserService      $userService,
        private readonly SessionInterface $session,
    )
    {
    }

    public function user(): ?User
    {
        if ($this->user !== null) {
            return $this->user;
        }

        if (($userId = $this->session->get('user')) === null) {
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
        $user = $this->userService->getByCredentials($credentials);

        if (!$user || !$this->checkCredentials($user, $credentials)) {
            return false;
        }

        return $this->authenticate($user);
    }

    public function checkCredentials(User $user, array $credentials): bool
    {
        return password_verify($credentials['password'], $user->getPassword());
    }

    public function logOut(): void
    {
        $this->session->forget('user');

        $this->user = null;
    }

    public function register(UserRegisterData $data): User
    {
        $user = $this->userService->createUser($data);

        $this->authenticate($user);

        return $user;
    }

    public function authenticate(User $user)
    {
        $this->user = $user;

        if (!$this->session->regenerate()) {
            return false;
        };

        $this->session->put('user', $user->getId());

        return true;
    }
}