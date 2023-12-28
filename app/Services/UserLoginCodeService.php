<?php

namespace App\Services;

use App\Entities\User;
use App\Entities\UserLoginCode;

class UserLoginCodeService
{

    public function generate(User $user): UserLoginCode
    {
        $code = random_int(100_000, 999_999);

        $userLoginCode = new UserLoginCode();
        $userLoginCode->setCode((string)$code);
        $userLoginCode->setExpiration(new \DateTime('+10 minutes'));
        $userLoginCode->setUser($user);

        return $userLoginCode;
    }
}