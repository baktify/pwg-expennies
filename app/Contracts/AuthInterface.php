<?php

namespace App\Contracts;

use App\DataObjects\UserRegisterData;
use App\Entities\User;

interface AuthInterface
{
    public function user(): ?User;

    public function attempt(array $credentials): bool;

    public function checkCredentials(User $user, array $credentials): bool;

    public function logOut(): void;

    public function register(UserRegisterData $data): User;
}