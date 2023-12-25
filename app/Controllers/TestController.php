<?php

namespace App\Controllers;

use App\Contracts\AuthInterface;
use App\Contracts\EntityManagerServiceInterface;
use App\Entities\Category;
use App\Entities\Receipt;
use App\Entities\Transaction;
use App\Services\CategoryService;
use App\Services\TransactionService;
use Cassandra\Date;
use Clockwork\Clockwork;
use Clockwork\DataSource\DoctrineDataSource;
use Clockwork\Request\LogLevel;
use Clockwork\Storage\FileStorage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ManyToOne;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TestController
{
    public function __construct(
        private readonly EntityManagerServiceInterface $entityManager,
        private readonly Filesystem                    $filesystem,
        private readonly Clockwork                     $clockwork,
        private readonly CategoryService               $categoryService,
        private readonly TransactionService            $transactionService,
        private readonly AuthInterface                 $auth,
    )
    {
    }

    public function test(Request $request, Response $response): Response
    {
        $x = new \ReflectionMethod(TestController::class, 'foo');

        $param = $x->getParameters()[2];

        return $response;
    }

    public function foo(string $firstname, int $age, $bar): string
    {

    }
}