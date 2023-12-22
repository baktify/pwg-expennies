<?php

namespace App\Controllers;

use App\Entities\Category;
use App\Entities\Transaction;
use App\Services\CategoryService;
use Clockwork\Clockwork;
use Clockwork\DataSource\DoctrineDataSource;
use Clockwork\Request\LogLevel;
use Clockwork\Storage\FileStorage;
use Doctrine\ORM\EntityManager;
use League\Flysystem\Filesystem;

class TestController
{
    public function __construct(
        private readonly EntityManager $em,
        private readonly Filesystem $filesystem,
        private readonly Clockwork $clockwork,
        private readonly CategoryService $categoryService,
    )
    {
    }

    public function test()
    {
        $x = ['a' => 1];
        $a = 'b';

        if ($x[$a] ?? null) {
            echo 'Boom';
        }

        die;
    }
}