<?php

namespace App\Controllers;

use App\Contracts\AuthInterface;
use App\Entities\Category;
use App\Entities\Transaction;
use App\Services\CategoryService;
use App\Services\TransactionService;
use Cassandra\Date;
use Clockwork\Clockwork;
use Clockwork\DataSource\DoctrineDataSource;
use Clockwork\Request\LogLevel;
use Clockwork\Storage\FileStorage;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Filesystem $filesystem,
        private readonly Clockwork $clockwork,
        private readonly CategoryService $categoryService,
        private readonly TransactionService $transactionService,
        private readonly AuthInterface $auth,
    )
    {
    }

    public function test(Request $request, Response $response): Response
    {
        $user = $this->auth->user();

        $c = new Category();
        $c->setUser($user);
        $c->setName('X');
        $this->em->persist($c);

        $t = new Transaction();
        $t->setDescription('Z');
        $t->setAmount(10.5);
        $t->setDate(new \DateTime());
        $t->setUser($user);
        $t->setCategory($c);

        $this->em->persist($user);

        $this->em->flush();

        return $response;
    }
}