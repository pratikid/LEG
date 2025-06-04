<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimelineEvent extends Model
{
    use HasFactory;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByDate($query, $startDate = null, $endDate = null)
    {
        if ($startDate) {
            $query->where('event_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('event_date', '<=', $endDate);
        }

        return $query;
    }

    public function scopeByType($query, $type)
    {
        return $query->where('event_type', $type);
    }
}
