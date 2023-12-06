<?php

namespace App\Contracts;

use App\DataObjects\UserRegisterData;

interface AuthInterface
{
    public function user(): ?UserInterface;

    public function attempt(array $credentials): bool;

    public function checkCredentials(UserInterface $user, array $credentials): bool;

    public function logOut(): void;

    public function register(UserRegisterData $data): void;
}