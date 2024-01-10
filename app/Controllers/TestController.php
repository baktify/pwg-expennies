<?php

namespace App\Controllers;

use App\Config;
use App\Contracts\AuthInterface;
use App\Contracts\EntityManagerServiceInterface;
use App\DataObjects\UserRegisterData;
use App\Entities\Category;
use App\Entities\Receipt;
use App\Entities\Transaction;
use App\Entities\UserLoginCode;
use App\Services\CategoryService;
use App\Services\TransactionService;
use App\Services\UserLoginCodeService;
use App\Services\UserService;
use Clockwork\Clockwork;
use Clockwork\DataSource\DoctrineDataSource;
use Clockwork\Request\LogLevel;
use Clockwork\Storage\FileStorage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ManyToOne;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\SimpleCache\CacheInterface;
use Slim\Views\Twig;

class TestController
{
    public function __construct(
        private readonly EntityManagerServiceInterface $entityManager,
        private readonly Config                        $config,
        private readonly Filesystem                    $filesystem,
        private readonly Clockwork                     $clockwork,
        private readonly CategoryService               $categoryService,
        private readonly TransactionService            $transactionService,
        private readonly AuthInterface                 $auth,
        private readonly UserService                   $userService,
        private readonly UserLoginCodeService          $userLoginCodeService,
        private readonly CacheInterface                $cache,
        private readonly Twig                          $twig,
    )
    {
    }

    public function test(Request $request, Response $response): Response
    {
        $this->cache->clear();

        dd(123);

//        return $this->twig->render($response, 'test.twig');
    }
}