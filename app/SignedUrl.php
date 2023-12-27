<?php

namespace App;

use Slim\Interfaces\RouteParserInterface;

class SignedUrl
{
    public function __construct(
        private readonly RouteParserInterface $routeParser,
        private readonly Config               $config,
    )
    {
    }

    public function createFrom(string $routeName, array $routeParams, \DateTime $expirationDate): string
    {
        // {APP_URL}/verify/{userId}/{email}?expiration={EXPIRATION_TIMESTAMP}&signature={SIGNATURE}

        $baseUrl = $this->config->get('app_url');
        $secretKey = $this->config->get('app_key');

        $queryParams = [
            'expiration' => $expirationDate->getTimestamp()
        ];

        $url = $baseUrl . $this->routeParser->urlFor($routeName, $routeParams, $queryParams);

        $signature = hash_hmac('sha256', $url, $secretKey);

        return $baseUrl . $this->routeParser->urlFor($routeName, $routeParams, $queryParams + compact('signature'));
    }
}