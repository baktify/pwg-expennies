<?php

namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MethodOverrideMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $methodName = $request->getHeaderLine('X-Http-Method-Override');

        if ($methodName) {
            $request->withMethod($methodName);
        } elseif (strtoupper($request->getMethod()) === 'POST') {
            $body = $request->getParsedBody();

            if (isset($body['_METHOD'])) {
                $request = $request->withMethod($body['_METHOD']);
            }
        }

        return $handler->handle($request);
    }
}