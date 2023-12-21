<?php

namespace App\Controllers;

use App\Entities\Category;
use App\Entities\Transaction;
use Clockwork\Clockwork;
use Clockwork\DataSource\DoctrineDataSource;
use Clockwork\Storage\FileStorage;
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
        $clockwork = new Clockwork();
        $clockwork->setStorage(new FileStorage(STORAGE_PATH . '/clockwork'));
        $clockwork->addDataSource(new DoctrineDataSource($this->em));
        dump($clockwork->getStorage());
        dump($clockwork->getDataSources());
        dump($clockwork->getAuthenticator());
        dump($clockwork->getRequest());

        die;
    }
}