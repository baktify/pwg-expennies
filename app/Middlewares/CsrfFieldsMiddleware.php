<?php

namespace App\Middlewares;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Csrf\Guard;
use Slim\Views\Twig;

class CsrfFieldsMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly Twig               $twig,
    )
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Guard $csrf */
        $csrf = $this->container->get('csrf');
        $csrfTokenNameKey = $csrf->getTokenNameKey();
        $csrfTokenName = $csrf->getTokenName();
        $csrfTokenValueKey = $csrf->getTokenValueKey();
        $csrfTokenValue = $csrf->getTokenValue();

        $this->twig->getEnvironment()->addGlobal('csrf', [
            'nameKey' => $csrfTokenNameKey,
            'name' => $csrfTokenName,
            'valueKey' => $csrfTokenValueKey,
            'value' => $csrfTokenValue,
        ]);

        return $handler->handle($request);
    }
}