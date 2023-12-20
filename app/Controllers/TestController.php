<?php

namespace App\Controllers;

use App\Entities\Category;
use App\Entities\Receipt;
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
        $transaction = $this->em->getRepository(Transaction::class)->find(42);

        dump($transaction->getReceipts()->map(fn(Receipt $receipt) => [
            'id' => $receipt->getId(),
            'name' => $receipt->getFilename()
        ])->toArray());
        die;
    }
}