<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enlightn Analyzers
    |--------------------------------------------------------------------------
    |
    | The following array lists the analyzers that will be run when you
    | execute the `enlightn` command. You can customize this list to
    | include only the analyzers you want to run.
    |
    */

    'analyzers' => ['*'],
    /*
    'analyzers' => [
        // Security Analyzers
        \Enlightn\Enlightn\Analyzers\Security\DebugModeAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Security\EnvironmentVariableAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Security\SessionConfigurationAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Security\CookieSecurityAnalyzer::class,
        //\Enlightn\Enlightn\Analyzers\Security\XSSAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Security\SQLInjectionAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Security\MassAssignmentAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Security\CSRFAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Security\DirectoryTraversalAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Security\FileUploadAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Security\SecurityHeadersAnalyzer::class,

        // Performance Analyzers
        \Enlightn\Enlightn\Analyzers\Performance\NPlusOneQueryAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Performance\EagerLoadingAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Performance\CacheHitRatioAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Performance\QueueConnectionAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Performance\RedisConnectionAnalyzer::class,

        // Reliability Analyzers
        \Enlightn\Enlightn\Analyzers\Reliability\EnvironmentVariableAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Reliability\ComposerDependencyAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Reliability\ApplicationStructureAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Reliability\RouteConfigurationAnalyzer::class,
        \Enlightn\Enlightn\Analyzers\Reliability\ServiceProviderAnalyzer::class,

        // Best Practices Analyzers
        \Enlightn\Enlightn\Analyzers\Concerns\ValidatesConfiguration::class,
        \Enlightn\Enlightn\Analyzers\Concerns\ValidatesModelProperties::class,
        \Enlightn\Enlightn\Analyzers\Concerns\ValidatesRouteConfiguration::class,
    ],
    */

    /*
    |--------------------------------------------------------------------------
    | Analyzer Options
    |--------------------------------------------------------------------------
    |
    | Here you can configure options for specific analyzers.
    |
    */

    'options' => [
        'security' => [
            'allowed_hosts' => ['localhost', '127.0.0.1'],
            'allowed_origins' => ['http://localhost:8000'],
        ],
        'performance' => [
            'cache_hit_ratio_threshold' => 0.8,
            'n_plus_one_threshold' => 5,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Paths
    |--------------------------------------------------------------------------
    |
    | The following array lists the paths that should be excluded from analysis.
    |
    */

    'exclude' => [
        'vendor',
        'storage',
        'bootstrap/cache',
        'tests',
    ],
];
