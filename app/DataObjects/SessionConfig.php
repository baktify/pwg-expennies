<?php

namespace App\DataObjects;

use App\Enums\SameSite;

class SessionConfig
{
    public function __construct(
        public readonly string $sessionName,
        public readonly bool $secure,
        public readonly bool $httpOnly,
        public readonly SameSite $sameSite,
    )
    {
    }
}