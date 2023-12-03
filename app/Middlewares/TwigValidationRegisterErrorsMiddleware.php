<?php

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Views\Twig;

class TwigValidationRegisterErrorsMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly Twig $twig)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $errors = $_SESSION['errors'] ?? [];

        if (! empty($errors) ) {
            $this->twig->getEnvironment()->addGlobal('errors', $errors);

            unset($_SESSION['errors']);
        }

        return $handler->handle($request);
    }
}