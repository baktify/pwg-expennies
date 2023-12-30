<?php

declare(strict_types=1);

namespace App;

use App\Contracts\AuthInterface;
use App\Contracts\SessionInterface;
use App\DataObjects\UserRegisterData;
use App\Enums\AuthAttemptStatus;
use App\Exceptions\ValidationException;
use App\Mail\TwoFactorAuthEmail;
use App\Services\UserLoginCodeService;
use App\Services\UserService;
use App\Entities\User;

class Auth implements AuthInterface
{
    private ?User $user = null;

    public function __construct(
        private readonly UserService          $userService,
        private readonly SessionInterface     $session,
        private readonly TwoFactorAuthEmail   $twoFactorAuthEmail,
        private readonly UserLoginCodeService $userLoginCodeService,
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

    public function attempt(array $credentials): AuthAttemptStatus
    {
        $user = $this->userService->getByCredentials($credentials);

        if (!$user || !$this->checkCredentials($user, $credentials)) {
            return AuthAttemptStatus::FAILED;
        }

        if ($user->hasTwoFactorAuthEnabled()) {
            return $this->startLoginWith2FA($user);
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

    public function authenticate(User $user): AuthAttemptStatus
    {
        if (!$this->session->regenerate()) {
            return AuthAttemptStatus::INTERNAL_SERVER_ERROR;
        };

        $this->session->put('user', $user->getId());

        return AuthAttemptStatus::SUCCESS;
    }

    private function startLoginWith2FA(User $user): AuthAttemptStatus
    {
        if (!$this->session->regenerate()) {
            return AuthAttemptStatus::INTERNAL_SERVER_ERROR;
        }

        $this->session->put('2FA', $user->getId());

        $this->userLoginCodeService->deactivateAllActiveCodes($user);

        $this->twoFactorAuthEmail->send(
            $this->userLoginCodeService->generate($user)
        );

        return AuthAttemptStatus::TWO_FACTOR_AUTH;
    }

    public function attempt2FA(string $code): AuthAttemptStatus
    {
        $userId = $this->session->get('2FA');

        if (!$userId) {
            return AuthAttemptStatus::FAILED;
        }

        if (!($user = $this->userService->find($userId))) {
            return AuthAttemptStatus::FAILED;
        }

        if (!$this->userLoginCodeService->verify($user, $code)) {
            throw new ValidationException(['code' => ['Invalid code']]);
        }

        $this->session->forget('2FA');
        $status = $this->authenticate($user);

        if ($status === AuthAttemptStatus::SUCCESS) {
            $this->userLoginCodeService->deactivateAllActiveCodes($user);
            $this->session->forget('2FA');
        }

        return $status;
    }
}