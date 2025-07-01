<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Individual;
use App\Models\User;

final class IndividualPolicy
{
    /**
     * Determine whether the user can create individuals.
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can update the individual.
     */
    public function update(User $user, Individual $individual): bool
    {
        return $user->id === $individual->user->id || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the individual.
     */
    public function delete(User $user, Individual $individual): bool
    {
        return $user->id === $individual->user->id || $user->isAdmin();
    }
}
