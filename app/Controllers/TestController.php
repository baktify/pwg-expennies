<?php

namespace App\Controllers;

use App\Entities\Category;
use App\Entities\Transaction;
use App\Entities\User;
use Doctrine\ORM\EntityManager;

class TestController
{
    public function __construct(private readonly EntityManager $em)
    {
    }

    public function test()
    {
        var_dump(bin2hex(random_bytes(25 )));

        die;
    }
}