<?php

declare(strict_types=1);

namespace App\Services;

use Closure;
use DateInterval;
use DateTimeInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

/**
 * @template TCacheValue
 */
final class CacheService
{
    private CacheRepository $cache;

    public function __construct(CacheRepository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get a value from the cache.
     *
     * @template TDefault
     *
     * @param  TDefault  $default
     * @return TCacheValue|TDefault
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cache->get($key, $default);
    }

    /**
     * Put a value in the cache.
     */
    public function put(string $key, mixed $value, DateInterval|DateTimeInterface|int|null $ttl = null): void
    {
        $this->cache->put($key, $value, $ttl);
    }

    /**
     * Get an item or store the default value.
     *
     * @template TValue
     *
     * @param  TValue  $ttl
     * @param  Closure(): TValue  $callback
     * @return TValue
     */
    public function remember(string $key, DateInterval|DateTimeInterface|int|null $ttl, Closure $callback): mixed
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
