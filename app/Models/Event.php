<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $tree_id
 * @property int|null $individual_id
 * @property int|null $family_id
 * @property string $type
 * @property string $title
 * @property string|null $description
 * @property string|null $event_date
 * @property string|null $event_place
 * @property string|null $event_city
 * @property string|null $event_state
 * @property string|null $event_country
 * @property string|null $event_latitude
 * @property string|null $event_longitude
 * @property array|null $additional_data
 * @property string|null $gedcom_xref
 * @property int $created_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Tree $tree
 * @property-read \App\Models\Individual|null $individual
 * @property-read \App\Models\Family|null $family
 * @property-read \App\Models\User $creator
 */
class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'tree_id',
        'individual_id',
        'family_id',
        'type',
        'title',
        'description',
        'event_date',
        'event_place',
        'event_city',
        'event_state',
        'event_country',
        'event_latitude',
        'event_longitude',
        'additional_data',
        'gedcom_xref',
        'created_by',
    ];

    protected $casts = [
        'event_date' => 'date',
        'additional_data' => 'array',
    ];

    /**
     * Event types constants
     */
    public const TYPE_BIRTH = 'birth';
    public const TYPE_DEATH = 'death';
    public const TYPE_MARRIAGE = 'marriage';
    public const TYPE_DIVORCE = 'divorce';
    public const TYPE_BAPTISM = 'baptism';
    public const TYPE_BURIAL = 'burial';
    public const TYPE_CENSUS = 'census';
    public const TYPE_IMMIGRATION = 'immigration';
    public const TYPE_EMIGRATION = 'emigration';
    public const TYPE_NATURALIZATION = 'naturalization';
    public const TYPE_MILITARY = 'military';
    public const TYPE_EDUCATION = 'education';
    public const TYPE_OCCUPATION = 'occupation';
    public const TYPE_RESIDENCE = 'residence';
    public const TYPE_OTHER = 'other';

    /**
     * Get all available event types.
     */
    public static function getEventTypes(): array
    {
        return [
            self::TYPE_BIRTH => 'Birth',
            self::TYPE_DEATH => 'Death',
            self::TYPE_MARRIAGE => 'Marriage',
            self::TYPE_DIVORCE => 'Divorce',
            self::TYPE_BAPTISM => 'Baptism',
            self::TYPE_BURIAL => 'Burial',
            self::TYPE_CENSUS => 'Census',
            self::TYPE_IMMIGRATION => 'Immigration',
            self::TYPE_EMIGRATION => 'Emigration',
            self::TYPE_NATURALIZATION => 'Naturalization',
            self::TYPE_MILITARY => 'Military Service',
            self::TYPE_EDUCATION => 'Education',
            self::TYPE_OCCUPATION => 'Occupation',
            self::TYPE_RESIDENCE => 'Residence',
            self::TYPE_OTHER => 'Other',
        ];
    }

    /**
     * Get the tree that owns the event.
     */
    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    /**
     * Get the individual associated with this event.
     */
    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class);
    }

    /**
     * Get the family associated with this event.
     */
    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    /**
     * Get the user who created this event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the full location as a formatted string.
     */
    public function getFullLocationAttribute(): string
    {
        $parts = array_filter([
            $this->event_place,
            $this->event_city,
            $this->event_state,
            $this->event_country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Check if the event has location information.
     */
    public function hasLocation(): bool
    {
        return !empty($this->event_place) || !empty($this->event_city);
    }

    /**
     * Check if the event has coordinates.
     */
    public function hasCoordinates(): bool
    {
        return !empty($this->event_latitude) && !empty($this->event_longitude);
    }

    /**
     * Scope to filter by tree.
     */
    public function scopeForTree($query, int $treeId)
    {
        return $query->where('tree_id', $treeId);
    }

    /**
     * Scope to filter by individual.
     */
    public function scopeForIndividual($query, int $individualId)
    {
        return $query->where('individual_id', $individualId);
    }

    /**
     * Scope to filter by family.
     */
    public function scopeForFamily($query, int $familyId)
    {
        return $query->where('family_id', $familyId);
    }

    /**
     * Scope to filter by event type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeInDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('event_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter events with location.
     */
    public function scopeWithLocation($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('event_place')
              ->orWhereNotNull('event_city');
        });
    }

    /**
     * Scope to filter events with coordinates.
     */
    public function scopeWithCoordinates($query)
    {
        return $query->whereNotNull('event_latitude')
                    ->whereNotNull('event_longitude');
    }
}
