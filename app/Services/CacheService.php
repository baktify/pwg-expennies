<?php

namespace App\Services;

use App\Contracts\AuthInterface;
use Psr\SimpleCache\CacheInterface;

class CacheService
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly AuthInterface $auth,
    )
    {
    }

    public function getOrSet(string $currentCacheKey, callable $method): mixed
    {
        $userId = $this->auth->user()->getId();
        $currentCacheKey = $userId . '_' . $currentCacheKey;

        if ($this->cache->has($currentCacheKey)) {
            return $this->cache->get($currentCacheKey);
        }

        $this->cache->set($currentCacheKey, $result = $method());
        return $result;
    }
}