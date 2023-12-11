<?php

namespace App;

use Closure;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Csrf
{
    public function __construct(private readonly ResponseFactoryInterface $responseFactory)
    {
    }

    public function failureHandler(): Closure
    {
        return function (ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
            $response = $this->responseFactory->createResponse();
            $response->getBody()->write('Csrf token not provided or invalid');

            return $response->withStatus(419);
        };
    }
}