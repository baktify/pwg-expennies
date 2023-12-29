<?php

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exceptions\ValidationException;
use Valitron\Validator;

class TwoFactorLoginRequestValidator implements RequestValidatorInterface
{

    public function validate(array $data): array
    {
        $v = new Validator($data);

        $v->rule('required', ['code']);

        if (!$v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}