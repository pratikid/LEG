<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Individual;
use App\Models\TimelineEvent;
use App\Policies\IndividualPolicy;
use App\Policies\TimelineEventPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

final class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        TimelineEvent::class => TimelineEventPolicy::class,
        Individual::class => IndividualPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
