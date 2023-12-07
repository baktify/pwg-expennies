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
        $fields = <<<CsrfField
            <input type="hidden" name="$csrfTokenNameKey" value="$csrfTokenName">
            <input type="hidden" name="$csrfTokenValueKey" value="$csrfTokenValue">
        CsrfField;


        $this->twig->getEnvironment()->addGlobal('csrf', [
//            'keys' => [
//                'name' => $csrfTokenNameKey,
//                'value' => $csrfTokenValueKey,
//            ],
//            'values' => [
//                'name' => $csrfTokenName,
//                'value' => $csrfTokenValue,
//            ],
            'fields' => $fields,
        ]);

        return $handler->handle($request);
    }
}