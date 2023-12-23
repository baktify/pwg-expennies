<?php

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Entities\Category;
use App\Exceptions\ValidationException;
use Doctrine\ORM\EntityManagerInterface;
use Valitron\Validator;

class TransactionCreateRequestValidator implements RequestValidatorInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function validate(array $data): array
    {
        $v = new Validator($data);

        $v->rule('required', ['description', 'amount', 'date']);
        $v->rule('lengthMax', 'description', 255);
        $v->rule('dateFormat', 'date', 'Y-m-d\TH:i');
        $v->rule('numeric', 'amount');
        $v->rule('optional', 'categoryId');

        $v->rule(function($field, $value, $params, $fields) use ($data) {
            return $this->em->getRepository(Category::class)->count(['id' => $value]);
        }, 'categoryId');

        if (!$v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}