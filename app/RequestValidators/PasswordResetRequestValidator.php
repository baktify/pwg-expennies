<?php

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Entities\User;
use App\Exceptions\ValidationException;
use Doctrine\ORM\EntityManagerInterface;
use Valitron\Validator;

class PasswordResetRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): array
    {
        $v = new Validator($data);
        $v->rule('required', ['password']);
        $v->rule('required', 'confirmPassword')->label('Confirm Password');
        $v->rule('equals', 'confirmPassword', 'password');

        if (!$v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}