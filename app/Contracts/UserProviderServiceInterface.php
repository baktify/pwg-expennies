<?php

namespace App\Contracts;

interface UserProviderServiceInterface
{
    public function find(int $id): ?UserInterface;

    public function findOneBy(array $credentials): ?UserInterface;
}