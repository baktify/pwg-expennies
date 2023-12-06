<?php

namespace App\Services;

use App\Contracts\SessionInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestService
{
    public function __construct(private readonly SessionInterface $session)
    {
    }

    public function getReferer(ServerRequestInterface $request)
    {
        $referer = $request->getHeader('referer')[0] ?? '';
        
        if (!$referer) {
            $referer = $this->session->get('previousUrl');
        }

        $host = parse_url($referer, PHP_URL_HOST);

        if ($host !== $request->getUri()->getHost()) {
            $referer = $this->session->get('previousUrl');
        }

        return $referer;
    }
}