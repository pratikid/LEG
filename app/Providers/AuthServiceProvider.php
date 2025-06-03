<?php

namespace App\Providers;

use App\Models\TimelineEvent;
use App\Policies\TimelineEventPolicy;
use App\Models\Individual;
use App\Policies\IndividualPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        TimelineEvent::class => TimelineEventPolicy::class,
        Individual::class => IndividualPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
} 