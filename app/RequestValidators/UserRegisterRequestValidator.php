<?php

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Entities\User;
use App\Exceptions\ValidationException;
use Doctrine\ORM\EntityManager;
use Valitron\Validator;

class UserRegisterRequestValidator implements RequestValidatorInterface
{
    public function __construct(private readonly EntityManager $em)
    {
    }

    public function validate(array $data): array
    {
        $v = new Validator($data);
        $v->rule('required', ['name', 'email', 'password']);
        $v->rule('required', 'confirmPassword')->label('Confirm Password');
        $v->rule('email', 'email');
        $v->rule('equals', 'confirmPassword', 'password');

        $v->rule(function ($field, $value, $params, $fields) {
            return !$this->em->getRepository(User::class)->count(['email' => $value]);
        }, 'email')->message('Entered email address already exists');

        if (!$v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}