<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Trait for models with common attributes and casts
 */
trait HasCommonModelAttributes
{
    /**
     * Get the model's display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?? $this->title ?? $this->first_name.' '.$this->last_name ?? 'Unknown';
    }

    /**
     * Format a date for display.
     */
    protected function formatDateForDisplay(?string $date): ?string
    {
        if (! $date) {
            return null;
        }

        return date('F j, Y', strtotime($date));
    }
}
