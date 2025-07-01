<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property \Illuminate\Support\Carbon $event_date
 * @property string $event_type
 * @property string|null $location
 * @property bool $is_public
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read User $user
 *
 * @method static \Database\Factories\TimelineEventFactory factory()
 */
final class TimelineEvent extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'title',
        'description',
        'event_date',
        'event_type',
        'location',
        'is_public',
        'user_id',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'is_public' => 'boolean',
    ];

    /**
     * @return BelongsTo<User, TimelineEvent>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function scopeByDate(Builder $query, ?string $startDate = null, ?string $endDate = null): Builder
    {
        if ($startDate) {
            $query->where('event_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('event_date', '<=', $endDate);
        }

        return $query;
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('event_type', $type);
    }
}
