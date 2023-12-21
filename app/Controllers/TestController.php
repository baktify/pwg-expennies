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
        $t = $this->em->getRepository(Transaction::class)->find(231);

        dump($t->getCategory());

        die;
    }
}