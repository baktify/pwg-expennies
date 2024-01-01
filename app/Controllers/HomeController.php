<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\SimpleCache\CacheInterface;
use Slim\Views\Twig;

class HomeController
{
    public function __construct(
        private readonly Twig           $twig,
        private readonly CacheInterface $cache,
    )
    {
    }

    public function index(Response $response): Response
    {
        $this->cache->set('a', 10);
        $this->cache->setMultiple(['b' => 20, 'c' => 30], 20);

        dump(
            $this->cache->getMultiple(['a', 'b', 'c']),
        );

        return $this->twig->render($response, 'dashboard.twig');
    }
}
