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
        return $this->twig->render($response, 'dashboard.twig');
    }
}
