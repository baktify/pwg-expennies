<?php

namespace App\Middlewares;

use App\Config;
use App\Exceptions\InvalidLinkException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidateSignatureMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Config $config,
    )
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $timestamp = (int) ($queryParams['expiration'] ?? 0);
        $signature = $queryParams['signature'] ?? '';
        unset($queryParams['signature']);

        $signatureCompare = hash_hmac(
            'sha256',
            $request->getUri()->withQuery(http_build_query($queryParams)),
            $this->config->get('app_key')
        );

        if (!hash_equals($signature, $signatureCompare) || $timestamp <= time()) {
            throw new InvalidLinkException('Invalid link');
        }

        return $handler->handle($request);
    }
}