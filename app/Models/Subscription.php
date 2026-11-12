<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * User subscription management with tiered features
 */
final class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'price',
        'currency',
        'billing_cycle',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'canceled_at',
        'payment_method',
        'stripe_subscription_id',
        'stripe_price_id',
        'metadata'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'metadata' => 'array'
    ];

    // Subscription statuses
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_TRIALING = 'trialing';
    public const STATUS_INCOMPLETE = 'incomplete';

    // Billing cycles
    public const CYCLE_MONTHLY = 'monthly';
    public const CYCLE_YEARLY = 'yearly';
    public const CYCLE_LIFETIME = 'lifetime';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function usageRecords(): HasMany
    {
        return $this->hasMany(SubscriptionUsage::class);
    }

    /**
     * Check if subscription is currently active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE || $this->status === self::STATUS_TRIALING;
    }

    /**
     * Check if subscription is in trial period
     */
    public function isTrialing(): bool
    {
        return $this->status === self::STATUS_TRIALING && 
               $this->trial_ends_at && 
               $this->trial_ends_at->isFuture();
    }

    /**
     * Check if subscription has expired
     */
    public function hasExpired(): bool
    {
        if ($this->billing_cycle === self::CYCLE_LIFETIME) {
            return false;
        }

        return $this->ends_at && $this->ends_at->isPast();
    }

    /**
     * Check if user has access to specific feature
     */
    public function hasFeatureAccess(string $feature): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return $this->plan->hasFeature($feature);
    }

    /**
     * Check if subscription is within usage limits
     */
    public function isWithinLimits(string $limitType): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $limit = $this->plan->getLimit($limitType);
        if ($limit === null) {
            return true; // No limit
        }

        $currentUsage = $this->getCurrentUsage($limitType);
        return $currentUsage < $limit;
    }

    /**
     * Get current usage for a specific limit type
     */
    public function getCurrentUsage(string $limitType): int
    {
        return $this->usageRecords()
            ->where('usage_type', $limitType)
            ->where('period_start', '<=', now())
            ->where('period_end', '>=', now())
            ->sum('usage_count');
    }

    /**
     * Record usage for metering
     */
    public function recordUsage(string $usageType, int $quantity = 1, array $metadata = []): SubscriptionUsage
    {
        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();

        $usage = $this->usageRecords()
            ->firstOrCreate([
                'usage_type' => $usageType,
                'period_start' => $periodStart,
                'period_end' => $periodEnd
            ], [
                'usage_count' => 0,
                'metadata' => []
            ]);

        $usage->increment('usage_count', $quantity);
        
        if (!empty($metadata)) {
            $usage->update([
                'metadata' => array_merge($usage->metadata ?? [], $metadata)
            ]);
        }

        return $usage;
    }

    /**
     * Calculate days remaining in subscription
     */
    public function daysRemaining(): ?int
    {
        if ($this->billing_cycle === self::CYCLE_LIFETIME) {
            return null; // Lifetime subscription
        }

        if (!$this->ends_at) {
            return null;
        }

        return now()->diffInDays($this->ends_at, false);
    }

    /**
     * Check if subscription needs renewal soon
     */
    public function needsRenewal(int $daysThreshold = 7): bool
    {
        $daysRemaining = $this->daysRemaining();
        
        if ($daysRemaining === null) {
            return false; // Lifetime subscription
        }

        return $daysRemaining <= $daysThreshold && $daysRemaining >= 0;
    }

    /**
     * Cancel subscription
     */
    public function cancel(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELED,
            'canceled_at' => now()
        ]);
    }

    /**
     * Reactivate canceled subscription
     */
    public function reactivate(): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'canceled_at' => null
        ]);
    }
}
