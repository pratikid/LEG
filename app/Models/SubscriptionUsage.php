<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Subscription usage tracking for metering and billing
 */
final class SubscriptionUsage extends Model
{
    use HasFactory;

    // Usage types for tracking different features
    public const TYPE_TREES_CREATED = 'trees_created';

    public const TYPE_INDIVIDUALS_ADDED = 'individuals_added';

    public const TYPE_GEDCOM_IMPORTS = 'gedcom_imports';

    public const TYPE_API_CALLS = 'api_calls';

    public const TYPE_REPORTS_GENERATED = 'reports_generated';

    public const TYPE_EXPORTS_PERFORMED = 'exports_performed';

    public const TYPE_STORAGE_USED = 'storage_used';

    public const TYPE_ANALYTICS_QUERIES = 'analytics_queries';

    protected $fillable = [
        'subscription_id',
        'usage_type',
        'usage_count',
        'period_start',
        'period_end',
        'metadata',
    ];

    protected $casts = [
        'usage_count' => 'integer',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'metadata' => 'array',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Check if usage is within current period
     */
    public function isCurrentPeriod(): bool
    {
        $now = now();

        return $now->between($this->period_start, $this->period_end);
    }

    /**
     * Get usage percentage of limit
     */
    public function getUsagePercentage(int $limit): float
    {
        if ($limit <= 0) {
            return 0.0;
        }

        return min(($this->usage_count / $limit) * 100, 100.0);
    }

    /**
     * Check if usage exceeds limit
     */
    public function exceedsLimit(int $limit): bool
    {
        return $this->usage_count >= $limit;
    }

    /**
     * Get remaining quota
     */
    public function getRemainingQuota(int $limit): int
    {
        return max(0, $limit - $this->usage_count);
    }
}
