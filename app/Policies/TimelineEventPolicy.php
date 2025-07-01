<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TimelineEvent;
use App\Models\User;

final class TimelineEventPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create events
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TimelineEvent $timelineEvent): bool
    {
        return $user->id === $timelineEvent->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TimelineEvent $timelineEvent): bool
    {
        return $user->id === $timelineEvent->user_id;
    }
}
