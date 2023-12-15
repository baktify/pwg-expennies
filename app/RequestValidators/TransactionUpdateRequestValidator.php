<?php

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Entities\Category;
use App\Entities\Transaction;
use App\Exceptions\ValidationException;
use Doctrine\ORM\EntityManager;
use Valitron\Validator;

class TransactionUpdateRequestValidator implements RequestValidatorInterface
{
    public function __construct(private readonly EntityManager $em)
    {
    }

    public function validate(array $data): array
    {
        $v = new Validator($data);

        $v->rule('required', ['id', 'description', 'amount', 'date']);
        $v->rule('date', 'date');
        $v->rule('numeric', 'amount');
        $v->rule('optional', 'categoryId');

        // Does transaction exist in db?
        $v->rule(function ($field, $value, $params, $fields) {
            return $this->em->getRepository(Transaction::class)->count(['id' => $value]);
        }, 'id')->message('{field} is invalid');

        // Does category exist in db?
        $v->rule(function ($field, $value, $params, $fields) {
            return $this->em->getRepository(Category::class)->count(['id' => $value]);
        }, 'categoryId')->message('{field} is invalid');

        if (!$v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}