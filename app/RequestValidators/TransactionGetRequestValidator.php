<?php

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Entities\Transaction;
use App\Exceptions\ValidationException;
use Doctrine\ORM\EntityManager;
use Valitron\Validator;

class TransactionGetRequestValidator implements RequestValidatorInterface
{
    public function __construct(private readonly EntityManager $em)
    {
    }

    public function validate(array $data): array
    {
        $v = new Validator($data);

        $v->rule(function ($field, $value) use ($data) {
            return $this->em->getRepository(Transaction::class)->count(['id' =>$data['id']]);
        }, $data['id'])->message("{field} does not exist");

        if (!$v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}