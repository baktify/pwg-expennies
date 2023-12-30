<?php

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exceptions\ValidationException;
use Valitron\Validator;

class PasswordUpdateRequestValidator implements RequestValidatorInterface
{
    public function validate(array $data): array
    {
        $v = new Validator($data);
        $v->rule('required', 'currentPassword')->label('Current password');
        $v->rule('required', 'newPassword')->label('New password');

        $v->rule(function ($field, $value, $params, $fields) {
            return $value !== $fields['currentPassword'];
        }, 'newPassword')->message('New password cannot be old password');

        if (!$v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}