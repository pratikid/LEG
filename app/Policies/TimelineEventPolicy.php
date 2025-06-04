<?php

namespace App\Policies;

use App\Models\TimelineEvent;
use App\Models\User;

class TimelineEventPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Anyone can view the timeline list
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, TimelineEvent $timelineEvent): bool
    {
        return $timelineEvent->is_public || ($user && $user->id === $timelineEvent->user_id);
    }

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

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TimelineEvent $timelineEvent): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TimelineEvent $timelineEvent): bool
    {
        return false;
    }
}
