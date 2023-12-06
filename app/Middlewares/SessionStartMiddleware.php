<?php

namespace App\Middlewares;

use App\Contracts\SessionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function DI\get;
use function MongoDB\BSON\toPHP;

class SessionStartMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly SessionInterface $session)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->session->start();

        // TODO: Check if the request is not XHR request
        if ($request->getMethod() === 'GET') {
            $this->session->put('previousUrl', (string)$request->getUri());
        }

        $response = $handler->handle($request);

        $this->session->save();

        return $response;
    }
}