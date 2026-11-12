<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Privacy settings for individuals and trees with granular controls
 */
final class PrivacySetting extends Model
{
    use HasFactory;

    public const VISIBILITY_PUBLIC = 'public';

    public const VISIBILITY_FAMILY = 'family';

    public const VISIBILITY_PRIVATE = 'private';

    public const VISIBILITY_DECEASED_ONLY = 'deceased_only';

    public const LIVING_PRIVACY_FULL = 'full'; // Show all data

    public const LIVING_PRIVACY_LIMITED = 'limited'; // Show name and basic info only

    public const LIVING_PRIVACY_HIDDEN = 'hidden'; // Hide completely

    protected $fillable = [
        'individual_id',
        'tree_id',
        'user_id',
        'visibility_level',
        'living_privacy',
        'show_birth_date',
        'show_death_date',
        'show_marriage_date',
        'show_location',
        'show_media',
        'show_sources',
        'show_notes',
        'custom_restrictions',
        'expiry_date',
        'is_active',
    ];

    protected $casts = [
        'show_birth_date' => 'boolean',
        'show_death_date' => 'boolean',
        'show_marriage_date' => 'boolean',
        'show_location' => 'boolean',
        'show_media' => 'boolean',
        'show_sources' => 'boolean',
        'show_notes' => 'boolean',
        'custom_restrictions' => 'array',
        'expiry_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class);
    }

    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if data field is visible based on privacy settings
     */
    public function isFieldVisible(string $field, bool $isLiving = false): bool
    {
        if (! $this->is_active) {
            return true; // Default to visible if no privacy settings
        }

        // Apply living person restrictions first
        if ($isLiving) {
            return match ($this->living_privacy) {
                self::LIVING_PRIVACY_HIDDEN => false,
                self::LIVING_PRIVACY_LIMITED => in_array($field, ['name', 'birth_year']),
                default => $this->getFieldSetting($field)
            };
        }

        return $this->getFieldSetting($field);
    }

    private function getFieldSetting(string $field): bool
    {
        return match ($field) {
            'birth_date', 'birth_year' => $this->show_birth_date,
            'death_date', 'death_year' => $this->show_death_date,
            'marriage_date' => $this->show_marriage_date,
            'location' => $this->show_location,
            'media' => $this->show_media,
            'sources' => $this->show_sources,
            'notes' => $this->show_notes,
            default => true
        };
    }
}
