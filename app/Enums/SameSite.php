<?php

namespace App\Enums;

enum SameSite: string
{
    case Strict = 'strict';
    case Lax = 'lax';
    case None = 'none';
}
