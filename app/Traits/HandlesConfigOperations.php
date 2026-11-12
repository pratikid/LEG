<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

/**
 * Trait for handling configuration operations
 */
trait HandlesConfigOperations
{
    /**
     * Get a configuration value.
     *
     * @param  string  $key  The configuration key
     * @param  mixed  $default  The default value if the key doesn't exist
     * @return mixed The configuration value
     */
    protected function getConfig(string $key, $default = null)
    {
        return Config::get($key, $default);
    }

    /**
     * Set a configuration value.
     *
     * @param  string|array  $key  The configuration key or an array of key/value pairs
     * @param  mixed  $value  The configuration value (if key is a string)
     */
    protected function setConfig($key, $value = null): void
    {
        Config::set($key, $value);
    }

    /**
     * Determine if a configuration value exists.
     *
     * @param  string  $key  The configuration key
     * @return bool True if the configuration value exists, false otherwise
     */
    protected function hasConfig(string $key): bool
    {
        return Config::has($key);
    }

    /**
     * Get all configuration values for a given key.
     *
     * @param  string|null  $key  The configuration key
     * @return array The configuration values
     */
    protected function getAllConfig(?string $key = null): array
    {
        return $key ? Config::get($key, []) : Config::all();
    }

    /**
     * Get a cached configuration value.
     *
     * @param  string  $key  The configuration key
     * @param  mixed  $default  The default value if the key doesn't exist
     * @param  int  $ttl  The cache TTL in seconds
     * @return mixed The configuration value
     */
    protected function getCachedConfig(string $key, $default = null, int $ttl = 3600)
    {
        $cacheKey = 'config:'.$key;

        return Cache::remember($cacheKey, $ttl, function () use ($key, $default) {
            return $this->getConfig($key, $default);
        });
    }

    /**
     * Clear a cached configuration value.
     *
     * @param  string  $key  The configuration key
     */
    protected function clearCachedConfig(string $key): void
    {
        $cacheKey = 'config:'.$key;
        Cache::forget($cacheKey);
    }

    /**
     * Get a configuration value with dot notation support.
     *
     * @param  string  $key  The configuration key with dot notation
     * @param  mixed  $default  The default value if the key doesn't exist
     * @return mixed The configuration value
     */
    protected function getDotConfig(string $key, $default = null)
    {
        return data_get($this->getAllConfig(), $key, $default);
    }

    /**
     * Set a configuration value with dot notation support.
     *
     * @param  string  $key  The configuration key with dot notation
     * @param  mixed  $value  The configuration value
     */
    protected function setDotConfig(string $key, $value): void
    {
        $keys = explode('.', $key);
        $configKey = array_shift($keys);
        $config = $this->getConfig($configKey, []);

        data_set($config, implode('.', $keys), $value);
        $this->setConfig($configKey, $config);
    }

    /**
     * Get a typed configuration value.
     *
     * @param  string  $key  The configuration key
     * @param  string  $type  The expected type (string, int, bool, array)
     * @param  mixed  $default  The default value if the key doesn't exist
     * @return mixed The configuration value cast to the specified type
     */
    protected function getTypedConfig(string $key, string $type, $default = null)
    {
        $value = $this->getConfig($key, $default);

        switch ($type) {
            case 'string':
                return (string) $value;
            case 'int':
            case 'integer':
                return (int) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'array':
                return (array) $value;
            default:
                return $value;
        }
    }

    /**
     * Get a boolean configuration value.
     *
     * @param  string  $key  The configuration key
     * @param  bool  $default  The default value if the key doesn't exist
     * @return bool The configuration value as a boolean
     */
    protected function getBoolConfig(string $key, bool $default = false): bool
    {
        return $this->getTypedConfig($key, 'bool', $default);
    }

    /**
     * Get an integer configuration value.
     *
     * @param  string  $key  The configuration key
     * @param  int  $default  The default value if the key doesn't exist
     * @return int The configuration value as an integer
     */
    protected function getIntConfig(string $key, int $default = 0): int
    {
        return $this->getTypedConfig($key, 'int', $default);
    }

    /**
     * Get a string configuration value.
     *
     * @param  string  $key  The configuration key
     * @param  string  $default  The default value if the key doesn't exist
     * @return string The configuration value as a string
     */
    protected function getStringConfig(string $key, string $default = ''): string
    {
        return $this->getTypedConfig($key, 'string', $default);
    }

    /**
     * Get an array configuration value.
     *
     * @param  string  $key  The configuration key
     * @param  array  $default  The default value if the key doesn't exist
     * @return array The configuration value as an array
     */
    protected function getArrayConfig(string $key, array $default = []): array
    {
        return $this->getTypedConfig($key, 'array', $default);
    }

    /**
     * Get a float configuration value.
     *
     * @param  string  $key  The configuration key
     * @param  float  $default  The default value if the key doesn't exist
     * @return float The configuration value as a float
     */
    protected function getFloatConfig(string $key, float $default = 0.0): float
    {
        return $this->getTypedConfig($key, 'float', $default);
    }

    /**
     * Get a configuration value with environment-specific override.
     *
     * @param  string  $key  The configuration key
     * @param  mixed  $default  The default value if the key doesn't exist
     * @return mixed The configuration value
     */
    protected function getEnvConfig(string $key, $default = null)
    {
        $env = app()->environment();
        $envKey = $key.'.'.$env;

        if ($this->hasConfig($envKey)) {
            return $this->getConfig($envKey);
        }

        return $this->getConfig($key, $default);
    }

    /**
     * Get a configuration value with tenant-specific override.
     *
     * @param  string  $key  The configuration key
     * @param  mixed  $tenantId  The tenant ID
     * @param  mixed  $default  The default value if the key doesn't exist
     * @return mixed The configuration value
     */
    protected function getTenantConfig(string $key, $tenantId, $default = null)
    {
        if (! $tenantId) {
            return $this->getConfig($key, $default);
        }

        $tenantKey = 'tenants.'.$tenantId.'.'.$key;

        if ($this->hasConfig($tenantKey)) {
            return $this->getConfig($tenantKey);
        }

        return $this->getConfig($key, $default);
    }

    /**
     * Get a configuration value with user-specific override.
     *
     * @param  string  $key  The configuration key
     * @param  mixed  $userId  The user ID
     * @param  mixed  $default  The default value if the key doesn't exist
     * @return mixed The configuration value
     */
    protected function getUserConfig(string $key, $userId, $default = null)
    {
        if (! $userId) {
            return $this->getConfig($key, $default);
        }

        $userKey = 'users.'.$userId.'.'.$key;

        if ($this->hasConfig($userKey)) {
            return $this->getConfig($userKey);
        }

        return $this->getConfig($key, $default);
    }

    /**
     * Get a feature flag configuration value.
     *
     * @param  string  $feature  The feature name
     * @param  bool  $default  The default value if the feature flag doesn't exist
     * @return bool Whether the feature is enabled
     */
    protected function isFeatureEnabled(string $feature, bool $default = false): bool
    {
        return $this->getBoolConfig('features.'.$feature, $default);
    }

    /**
     * Get all enabled features.
     *
     * @return array The enabled features
     */
    protected function getEnabledFeatures(): array
    {
        $features = $this->getArrayConfig('features', []);

        return array_keys(array_filter($features, function ($enabled) {
            return (bool) $enabled;
        }));
    }

    /**
     * Get a configuration value with fallback keys.
     *
     * @param  array  $keys  The configuration keys to try in order
     * @param  mixed  $default  The default value if none of the keys exist
     * @return mixed The configuration value from the first existing key
     */
    protected function getConfigWithFallback(array $keys, $default = null)
    {
        foreach ($keys as $key) {
            if ($this->hasConfig($key)) {
                return $this->getConfig($key);
            }
        }

        return $default;
    }

    /**
     * Get a configuration value with validation.
     *
     * @param  string  $key  The configuration key
     * @param  callable  $validator  The validation function
     * @param  mixed  $default  The default value if the key doesn't exist or validation fails
     * @return mixed The configuration value if valid, or the default value
     */
    protected function getValidatedConfig(string $key, callable $validator, $default = null)
    {
        $value = $this->getConfig($key, $default);

        if ($validator($value)) {
            return $value;
        }

        return $default;
    }

    /**
     * Get a configuration value with allowed values.
     *
     * @param  string  $key  The configuration key
     * @param  array  $allowedValues  The allowed values
     * @param  mixed  $default  The default value if the key doesn't exist or value is not allowed
     * @return mixed The configuration value if allowed, or the default value
     */
    protected function getConfigWithAllowedValues(string $key, array $allowedValues, $default = null)
    {
        $value = $this->getConfig($key, $default);

        if (in_array($value, $allowedValues, true)) {
            return $value;
        }

        return $default;
    }

    /**
     * Get a nested configuration value with a path.
     *
     * @param  string  $path  The configuration path (e.g., 'app.name')
     * @param  mixed  $default  The default value if the path doesn't exist
     * @return mixed The configuration value
     */
    protected function getConfigPath(string $path, $default = null)
    {
        return $this->getConfig($path, $default);
    }

    /**
     * Set a nested configuration value with a path.
     *
     * @param  string  $path  The configuration path (e.g., 'app.name')
     * @param  mixed  $value  The configuration value
     */
    protected function setConfigPath(string $path, $value): void
    {
        $this->setConfig($path, $value);
    }

    /**
     * Get a configuration value with a prefix.
     *
     * @param  string  $prefix  The configuration prefix
     * @param  string  $key  The configuration key
     * @param  mixed  $default  The default value if the key doesn't exist
     * @return mixed The configuration value
     */
    protected function getPrefixedConfig(string $prefix, string $key, $default = null)
    {
        return $this->getConfig($prefix.'.'.$key, $default);
    }

    /**
     * Get all configuration values with a prefix.
     *
     * @param  string  $prefix  The configuration prefix
     * @return array The configuration values
     */
    protected function getAllPrefixedConfig(string $prefix): array
    {
        return $this->getConfig($prefix, []);
    }
}
