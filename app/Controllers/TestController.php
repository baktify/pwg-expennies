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
        private readonly Filesystem             $filesystem,
        private readonly Clockwork              $clockwork,
        private readonly CategoryService        $categoryService,
        private readonly TransactionService     $transactionService,
        private readonly AuthInterface          $auth,
    )
    {
    }

    public function test(Request $request, Response $response): Response
    {
        $user = $this->auth->user();

        $transaction = $this->transactionService->create(
            'z', 10.5, new \DateTime(), $user
        );

        $this->em->persist($transaction);

        $this->em->flush();

        return $response;
    }
}