<?php

namespace App\DataObjects;

class UserRegisterData
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
    )
    {
    }
}