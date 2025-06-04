<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

class CacheService
{
    protected CacheRepository $cache;

    public function __construct(CacheRepository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get a value from the cache.
     */
    public function get(string $key, $default = null)
    {
        return $this->cache->get($key, $default);
    }

    /**
     * Put a value in the cache.
     */
    public function put(string $key, $value, $ttl = null): void
    {
        $this->cache->put($key, $value, $ttl);
    }

    /**
     * Get an item or store the default value.
     */
    public function remember(string $key, $ttl, \Closure $callback)
    {
        return $this->cache->remember($key, $ttl, $callback);
    }

    /**
     * Remove an item from the cache.
     */
    public function forget(string $key): void
    {
        $this->cache->forget($key);
    }

    /**
     * Check if the cache has a key.
     */
    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }
}
