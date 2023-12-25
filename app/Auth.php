<?php

namespace App;

use App\Contracts\AuthInterface;
use App\Contracts\SessionInterface;
use App\Contracts\UserInterface;
use App\Contracts\UserProviderServiceInterface;
use App\DataObjects\UserRegisterData;

class Auth implements AuthInterface
{
    private ?UserInterface $user = null;

    public function __construct(
        private readonly UserProviderServiceInterface $userProviderService,
        private readonly SessionInterface             $session,
    )
    {
    }

    public function user(): ?UserInterface
    {
        if ($this->user !== null) {
            return $this->user;
        }

        if (($userId = $this->session->get('user')) === null) {
            return null;
        }

        $user = $this->userProviderService->find($userId);

        if ($user === null) {
            return null;
        }

        return $this->user = $user;
    }

    public function attempt(array $credentials): bool
    {
        $user = $this->userProviderService->getByCredentials($credentials);

        if (!$user || !$this->checkCredentials($user, $credentials)) {
            return false;
        }

        return $this->authenticate($user);
    }

    public function checkCredentials(UserInterface $user, array $credentials): bool
    {
        return password_verify($credentials['password'], $user->getPassword());
    }

    public function logOut(): void
    {
        $this->session->forget('user');

        $this->user = null;
    }

    public function register(UserRegisterData $data): void
    {
        $user = $this->userProviderService->createUser($data);

        $this->authenticate($user);
    }

    public function authenticate(UserInterface $user)
    {
        $this->user = $user;

        if (!$this->session->regenerate()) {
            return false;
        };

        $this->session->put('user', $user->getId());

        return true;
    }
}