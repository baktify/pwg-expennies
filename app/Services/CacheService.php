<?php

namespace App\Services;

use Psr\SimpleCache\CacheInterface;

class CacheService
{
    public function __construct(
        private readonly CacheInterface $cache,
    )
    {
    }

    public function getOrSet(string $currentCacheKey, callable $method): mixed
    {
        if ($this->cache->has($currentCacheKey)) {
            return $this->cache->get($currentCacheKey);
        }

        $this->cache->set($currentCacheKey, $result = $method());
        return $result;
    }
}