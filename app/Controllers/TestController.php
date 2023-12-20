<?php

namespace App\Controllers;

use App\Entities\Category;
use App\Entities\Transaction;
use Doctrine\ORM\EntityManager;
use League\Flysystem\Filesystem;

class TestController
{
    public function __construct(
        private readonly EntityManager $em,
        private readonly Filesystem $filesystem,
    )
    {
    }

    public function test()
    {
        $c =$this->em->getRepository(Category::class)->find(212);

        dd($c->getTransactions());
        die;
    }
}