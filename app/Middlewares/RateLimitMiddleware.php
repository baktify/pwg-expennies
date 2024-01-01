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

class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly CacheInterface           $cache,
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly RequestService           $requestService,
        private readonly Config                   $config,
    )
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $trustedProxies = $this->config->get('trusted_proxies');
        $cacheKey = 'rate_limit_' . $this->requestService->getClientIp($request, $trustedProxies);
        $requests = (int)$this->cache->get($cacheKey);

        if ($requests > 3) {
            return $this->responseFactory->createResponse(429, 'Too many requests');
        }

        $this->cache->set($cacheKey, $requests + 1, 60);

        return $handler->handle($request);
    }
}