<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Http\Middleware\ContentSecurityPolicy;

class ContentSecurityPolicyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ContentSecurityPolicy::class, function ($app) {
            return new ContentSecurityPolicy();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Add nonce to all script tags
        Blade::directive('nonce', function () {
            return '<?php echo "nonce=\"" . app(\App\Http\Middleware\ContentSecurityPolicy::class)->generateNonce() . "\""; ?>';
        });
    }
} 