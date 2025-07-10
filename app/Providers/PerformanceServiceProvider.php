<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PerformanceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register performance configuration
        $this->mergeConfigFrom(
            __DIR__.'/../../config/performance.php', 'performance'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Apply performance optimizations only in production
        if ($this->app->environment('production')) {
            $this->applyPerformanceOptimizations();
        }
    }

    /**
     * Apply performance optimizations.
     */
    private function applyPerformanceOptimizations(): void
    {
        // Optimize database connections
        $this->optimizeDatabaseConnections();

        // Optimize cache settings
        $this->optimizeCacheSettings();

        // Optimize logging
        $this->optimizeLogging();
    }

    /**
     * Optimize database connections.
     */
    private function optimizeDatabaseConnections(): void
    {
        // Set persistent connections for better performance
        config([
            'database.connections.pgsql.options' => [
                \PDO::ATTR_PERSISTENT => true,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ],
        ]);

        // Optimize Redis connections
        config([
            'database.redis.options.persistent' => true,
            'database.redis.options.timeout' => 5,
            'database.redis.options.read_timeout' => 60,
        ]);
    }

    /**
     * Optimize cache settings.
     */
    private function optimizeCacheSettings(): void
    {
        // Enable cache compression and serialization
        config([
            'cache.stores.redis.serialize' => true,
            'cache.stores.redis.compression' => true,
        ]);

        // Set default cache TTL
        config([
            'cache.ttl_default' => config('performance.cache.ttl_default', 3600),
        ]);
    }

    /**
     * Optimize logging.
     */
    private function optimizeLogging(): void
    {
        // Set production log level
        config([
            'logging.channels.single.level' => config('performance.logging.log_level_production', 'warning'),
            'logging.channels.daily.level' => config('performance.logging.log_level_production', 'warning'),
        ]);

        // Reduce log file retention
        config([
            'logging.channels.daily.days' => config('performance.logging.max_log_files', 7),
        ]);
    }
} 