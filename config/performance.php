<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains performance-related configuration settings
    | for optimizing server response times.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Database Connection Pooling
    |--------------------------------------------------------------------------
    |
    | Configure database connection pooling settings for better performance.
    |
    */

    'database' => [
        'pool_size' => env('DB_POOL_SIZE', 10),
        'max_connections' => env('DB_MAX_CONNECTIONS', 100),
        'connection_timeout' => env('DB_CONNECTION_TIMEOUT', 5),
        'query_timeout' => env('DB_QUERY_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Performance Settings
    |--------------------------------------------------------------------------
    |
    | Configure cache performance optimizations.
    |
    */

    'cache' => [
        'compression' => env('CACHE_COMPRESSION', true),
        'serialization' => env('CACHE_SERIALIZATION', true),
        'ttl_default' => env('CACHE_TTL_DEFAULT', 3600),
        'prefix_optimization' => env('CACHE_PREFIX_OPTIMIZATION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Performance Settings
    |--------------------------------------------------------------------------
    |
    | Configure session performance optimizations.
    |
    */

    'session' => [
        'garbage_collection_probability' => env('SESSION_GC_PROBABILITY', 1),
        'garbage_collection_divisor' => env('SESSION_GC_DIVISOR', 100),
        'cookie_lifetime' => env('SESSION_COOKIE_LIFETIME', 0),
        'secure_cookies' => env('SESSION_SECURE_COOKIES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Performance Settings
    |--------------------------------------------------------------------------
    |
    | Configure queue performance optimizations.
    |
    */

    'queue' => [
        'batch_size' => env('QUEUE_BATCH_SIZE', 100),
        'max_attempts' => env('QUEUE_MAX_ATTEMPTS', 3),
        'timeout' => env('QUEUE_TIMEOUT', 60),
        'block_timeout' => env('QUEUE_BLOCK_TIMEOUT', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Optimization
    |--------------------------------------------------------------------------
    |
    | Configure response optimization settings.
    |
    */

    'response' => [
        'gzip_compression' => env('RESPONSE_GZIP_COMPRESSION', true),
        'etag_generation' => env('RESPONSE_ETAG_GENERATION', true),
        'cache_headers' => env('RESPONSE_CACHE_HEADERS', true),
        'minify_html' => env('RESPONSE_MINIFY_HTML', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Performance
    |--------------------------------------------------------------------------
    |
    | Configure logging performance settings.
    |
    */

    'logging' => [
        'async_logging' => env('LOGGING_ASYNC', true),
        'log_level_production' => env('LOG_LEVEL_PRODUCTION', 'warning'),
        'max_log_files' => env('LOG_MAX_FILES', 7),
        'log_rotation' => env('LOG_ROTATION', 'daily'),
    ],

];
