<?php

namespace App\Middlewares;

use App\Config;
use App\Services\RequestService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;
use Slim\Routing\RouteContext;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly RequestService           $requestService,
        private readonly Config                   $config,
        private readonly RateLimiterFactory       $rateLimiterFactory,
    )
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $clientIp = $this->requestService->getClientIp($request, $this->config->get('trusted_proxies'));
        $routeName = RouteContext::fromRequest($request)->getRoute()->getName();
        $limiter = $this->rateLimiterFactory->create($routeName . '_' . $clientIp);

        if ($limiter->consume()->isAccepted() === false) {
            return $this->responseFactory->createResponse(429, 'Too many requests');
        }

        return $handler->handle($request);
    }
}