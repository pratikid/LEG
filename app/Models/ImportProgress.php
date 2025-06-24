<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $tree_id
 * @property string $status
 * @property int $total_records
 * @property int $processed_records
 * @property string|null $error_message
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Tree $tree
 */
class ImportProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tree_id',
        'status',
        'total_records',
        'processed_records',
        'error_message',
        'status_message'
    ];

    protected $casts = [
        'total_records' => 'integer',
        'processed_records' => 'integer',
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    /**
     * Get the user that owns the import progress.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tree associated with the import progress.
     */
    public function tree(): BelongsTo
    {
        return $this->belongsTo(Tree::class);
    }

    /**
     * Get the progress percentage.
     */
    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_records === 0) {
            return 0;
        }

        return (int) round(($this->processed_records / $this->total_records) * 100);
    }

    /**
     * Check if the import is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if the import is failed.
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if the import is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }
}
