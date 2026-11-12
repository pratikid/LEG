<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Override;

final class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    #[Override]
    public function boot(): void
    {
        parent::boot();

        RateLimiter::for('api', static function (Request $request): Limit {
            return Limit::perMinute(60)->by(
                (string) ($request->user()?->getAuthIdentifier() ?? $request->ip())
            );
        });

        $this->routes(function (): void {
            Route::middleware('web')->group(base_path('routes/web.php'));
            Route::prefix('api')->middleware('api')->group(base_path('routes/api.php'));
        });
    }
}
