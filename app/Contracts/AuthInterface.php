<?php

namespace App\Contracts;

interface AuthInterface
{
    public function user(): ?UserInterface;
}