<?php

namespace App\Services;

use App\Contracts\SessionInterface;
use App\DataObjects\DataTableQueryParamsData;
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

    public function isXhr(ServerRequestInterface $request): bool
    {
        return $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    public function getDataTableQueryParams(ServerRequestInterface $request)
    {
        $params = $request->getQueryParams();
        $orderBy = $params['columns'][$params['order'][0]['column']]['data'] ?? '';
        $orderDir = $params['order'][0]['dir'];

        return new DataTableQueryParamsData(
            (int)$params['draw'],
            (int)$params['start'],
            (int)$params['length'],
            $orderBy,
            $orderDir,
            $params['search']['value'],
        );
    }

    public function getClientIp(ServerRequestInterface $request, array $trustedProxies): ?string
    {
        $serverParams = $request->getServerParams();
        $clientIp = $serverParams['REMOTE_ADDR'];

        if (in_array($clientIp, $trustedProxies, true)
            && isset($serverParams['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $serverParams['HTTP_X_FORWARDED_FOR']);

            return trim($ips[0]);
        }

        return $clientIp ?? null;
    }
}