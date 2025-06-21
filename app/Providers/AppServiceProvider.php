<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewContract;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[\Override]
    public function register(): void
    {
        $this->app->singleton(\App\Services\CacheService::class, function ($app) {
            return new \App\Services\CacheService(Cache::store());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTP for local development
        if (app()->environment('local')) {
            URL::forceScheme('http');
        }

        View::composer('*', function (ViewContract $view) {
            $route = Route::currentRouteName();
            $tabMap = [
                // Dashboard
                'dashboard' => 'dashboard',
                // Trees
                'trees.index' => 'trees',
                'trees.create' => 'create-tree',
                'trees.import' => 'import-gedcom',
                'trees.visualization' => 'tree-visualization',
                // Individuals
                'individuals.index' => 'individuals',
                'individuals.create' => 'add-individual',
                'individuals.timeline' => 'timeline',
                // Groups
                'groups.index' => 'groups',
                'groups.create' => 'create-group',
                // Sources
                'sources.index' => 'sources',
                'sources.create' => 'add-source',
                'sources.show' => 'sources',
                'sources.edit' => 'sources',
                // Media
                'media.index' => 'media',
                'media.create' => 'upload-media',
                'media.show' => 'media',
                'media.edit' => 'media',
                // Stories
                'stories.index' => 'stories',
                'stories.create' => 'add-story',
                'stories.show' => 'stories',
                'stories.edit' => 'stories',
                // Events
                'events.index' => 'events',
                'events.create' => 'add-event',
                'events.calendar' => 'calendar',
                'events.show' => 'events',
                'events.edit' => 'events',
                // Community
                'community.directory' => 'community',
                'community.my-groups' => 'my-groups',
                'community.forums' => 'forums',
                // Tools
                'tools.templates' => 'templates',
                'tools.export' => 'export',
                'tools.reports' => 'reports',
                // Search
                'search' => 'search',
                // Admin
                'admin.users' => 'users',
                'admin.logs' => 'logs',
                'admin.settings' => 'settings',
                'admin.notifications' => 'notifications',
                // Help
                'help.user-guide' => 'user-guide',
                'help.tutorials' => 'tutorials',
                'help.support' => 'support',
                // Profile
                'profile.settings' => 'profile',
                'profile.preferences' => 'preferences',
            ];
            $activeTab = $tabMap[$route] ?? null;
            $view->with('activeTab', $activeTab);
        });
    }
}
