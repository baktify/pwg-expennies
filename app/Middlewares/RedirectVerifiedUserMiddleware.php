<?php

namespace App\Middlewares;

use App\Entities\User;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RedirectVerifiedUserMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
    )
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var User $user */
        $user = $request->getAttribute('user');
        $path = $request->getUri()->getPath();

        if ($path === '/verify' && $user?->getVerifiedAt()) {
            return $this->responseFactory->createResponse(302)->withHeader('Location', '/');
        }

        return $handler->handle($request);
    }
}