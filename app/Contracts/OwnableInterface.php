<?php

namespace App\Contracts;

use App\Entities\User;

interface OwnableInterface
{
    public function getUser(): User;
}