<?php

namespace App\RequestValidators;

use App\Contracts\RequestValidatorInterface;
use App\Exceptions\ValidationException;
use Valitron\Validator;

class CategoryLoadRequestValidator implements RequestValidatorInterface
{

    public function validate(array $data): array
    {
        $v = new Validator($data);

//        $orderBy = $columns[$order[0]['column']]['data'];
//        $orderDir = $order[0]['dir'];

        die;
        $v->rule('required', 'name');
        $v->rule('lengthMax', 'name', 50);

        if (!$v->validate()) {
            throw new ValidationException($v->errors());
        }

        return $data;
    }
}