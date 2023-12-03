<?php

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Views\Twig;

class TwigValidationRegisterOldValuesMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Twig $twig)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $old = $_SESSION['old'] ?? [];

        if (! empty($old)) {
            $this->twig->getEnvironment()->addGlobal('old', $old);

            unset($_SESSION['old']);
        }

        return $handler->handle($request);
    }
}