<?php

namespace App\Controllers;

use App\Entities\Category;
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
        $r = $this->em->getRepository(Category::class)->findOneBy(['name' => 'NON']);

        dd($r);
        die;
    }
}