<?php

namespace App\Contracts;

use App\DataObjects\UserRegisterData;

interface UserProviderServiceInterface
{
    public function find(int $id): ?UserInterface;

    public function getByCredentials(array $credentials): ?UserInterface;

    public function createUser(UserRegisterData $data): UserInterface;
}