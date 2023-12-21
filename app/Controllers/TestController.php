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
        $amount = '-$2,599.33';

        dump($amount);

        $amount = str_replace(['$', ','], [''], $amount);

        dump($amount);

        die;
    }
}