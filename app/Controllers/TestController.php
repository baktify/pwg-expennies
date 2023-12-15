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
        $transaction = $this->em->getRepository(Transaction::class)->findOneBy([]);

        dd($transaction->getCategory()->getName());
    }
}