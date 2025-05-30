<?php

namespace App\Providers;

use App\Models\TimelineEvent;
use App\Policies\TimelineEventPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        TimelineEvent::class => TimelineEventPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
} 