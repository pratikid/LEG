<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Individual;

class IndividualPolicy
{
    public function view(User $user, Individual $individual): bool
    {
        return true; // Adjust logic as needed
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Individual $individual): bool
    {
        return $user->id === $individual->user_id || $user->isAdmin();
    }

    public function delete(User $user, Individual $individual): bool
    {
        return $user->id === $individual->user_id || $user->isAdmin();
    }
} 