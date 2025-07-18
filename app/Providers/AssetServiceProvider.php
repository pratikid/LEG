<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

final class AssetServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Add custom Blade directive for asset versioning
        Blade::directive('assetVersion', function ($expression) {
            return "<?php echo asset($expression) . '?v=' . filemtime(public_path($expression)); ?>";
        });

        // Add custom Blade directive for Vite assets with cache busting
        Blade::directive('viteAsset', function ($expression) {
            return "<?php echo Vite::asset($expression); ?>";
        });

        // Add custom helper for asset URLs with version
        if (! function_exists('asset_versioned')) {
            function asset_versioned(string $path): string
            {
                $fullPath = public_path($path);
                $version = file_exists($fullPath) ? filemtime($fullPath) : '1';

                return asset($path).'?v='.$version;
            }
        }

        // Add custom helper for build assets
        if (! function_exists('build_asset')) {
            function build_asset(string $path): string
            {
                return Vite::asset($path);
            }
        }
    }
}
