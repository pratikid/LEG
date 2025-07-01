<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Middleware\ContentSecurityPolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Override;

final class ContentSecurityPolicyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    #[Override]
    public function register(): void
    {
        $this->app->singleton(ContentSecurityPolicy::class, function ($app) {
            return new ContentSecurityPolicy;
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
