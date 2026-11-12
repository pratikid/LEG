<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Subscription plan definitions with features and limits
 */
final class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'currency',
        'features',
        'limits',
        'stripe_price_id_monthly',
        'stripe_price_id_yearly',
        'trial_days',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'features' => 'array',
        'limits' => 'array',
        'is_active' => 'boolean',
        'trial_days' => 'integer'
    ];

    // Plan slugs for easy identification
    public const PLAN_FREE = 'free';
    public const PLAN_BASIC = 'basic';
    public const PLAN_PREMIUM = 'premium';
    public const PLAN_PROFESSIONAL = 'professional';
    public const PLAN_RESEARCHER = 'researcher';

    // Feature identifiers
    public const FEATURE_UNLIMITED_TREES = 'unlimited_trees';
    public const FEATURE_ADVANCED_ANALYTICS = 'advanced_analytics';
    public const FEATURE_PREMIUM_REPORTS = 'premium_reports';
    public const FEATURE_API_ACCESS = 'api_access';
    public const FEATURE_BULK_OPERATIONS = 'bulk_operations';
    public const FEATURE_PRIORITY_SUPPORT = 'priority_support';
    public const FEATURE_GEDCOM_ADVANCED = 'gedcom_advanced_import';
    public const FEATURE_DATA_EXPORT = 'advanced_data_export';
    public const FEATURE_COLLABORATION = 'collaboration_tools';
    public const FEATURE_RESEARCH_TOOLS = 'research_tools';
    public const FEATURE_CUSTOM_BRANDING = 'custom_branding';

    // Limit types
    public const LIMIT_TREES = 'max_trees';
    public const LIMIT_INDIVIDUALS = 'max_individuals_per_tree';
    public const LIMIT_GEDCOM_SIZE = 'max_gedcom_size_mb';
    public const LIMIT_API_CALLS = 'api_calls_per_month';
    public const LIMIT_REPORTS = 'reports_per_month';
    public const LIMIT_STORAGE = 'storage_mb';

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    /**
     * Check if plan has specific feature
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    /**
     * Get limit value for specific limit type
     */
    public function getLimit(string $limitType): ?int
    {
        return $this->limits[$limitType] ?? null;
    }

    /**
     * Get price for specific billing cycle
     */
    public function getPrice(string $billingCycle): ?float
    {
        return match ($billingCycle) {
            Subscription::CYCLE_MONTHLY => $this->price_monthly,
            Subscription::CYCLE_YEARLY => $this->price_yearly,
            default => null
        };
    }

    /**
     * Get Stripe price ID for specific billing cycle
     */
    public function getStripePriceId(string $billingCycle): ?string
    {
        return match ($billingCycle) {
            Subscription::CYCLE_MONTHLY => $this->stripe_price_id_monthly,
            Subscription::CYCLE_YEARLY => $this->stripe_price_id_yearly,
            default => null
        };
    }

    /**
     * Calculate yearly savings compared to monthly
     */
    public function getYearlySavings(): float
    {
        if (!$this->price_monthly || !$this->price_yearly) {
            return 0.0;
        }

        $monthlyYearly = $this->price_monthly * 12;
        return $monthlyYearly - $this->price_yearly;
    }

    /**
     * Calculate yearly savings percentage
     */
    public function getYearlySavingsPercentage(): float
    {
        if (!$this->price_monthly || !$this->price_yearly) {
            return 0.0;
        }

        $monthlyYearly = $this->price_monthly * 12;
        return round((($monthlyYearly - $this->price_yearly) / $monthlyYearly) * 100, 1);
    }

    /**
     * Check if plan is recommended
     */
    public function isRecommended(): bool
    {
        return $this->slug === self::PLAN_PREMIUM;
    }

    /**
     * Get plan comparison data
     */
    public function getComparisonData(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'price_monthly' => $this->price_monthly,
            'price_yearly' => $this->price_yearly,
            'yearly_savings' => $this->getYearlySavings(),
            'yearly_savings_percentage' => $this->getYearlySavingsPercentage(),
            'features' => $this->getFeatureList(),
            'limits' => $this->getLimitList(),
            'is_recommended' => $this->isRecommended(),
            'trial_days' => $this->trial_days
        ];
    }

    /**
     * Get human-readable feature list
     */
    public function getFeatureList(): array
    {
        $featureDescriptions = [
            self::FEATURE_UNLIMITED_TREES => 'Unlimited family trees',
            self::FEATURE_ADVANCED_ANALYTICS => 'Advanced analytics and insights',
            self::FEATURE_PREMIUM_REPORTS => 'Premium report generation',
            self::FEATURE_API_ACCESS => 'Full API access',
            self::FEATURE_BULK_OPERATIONS => 'Bulk operations',
            self::FEATURE_PRIORITY_SUPPORT => 'Priority customer support',
            self::FEATURE_GEDCOM_ADVANCED => 'Advanced GEDCOM import/export',
            self::FEATURE_DATA_EXPORT => 'Advanced data export options',
            self::FEATURE_COLLABORATION => 'Collaboration tools',
            self::FEATURE_RESEARCH_TOOLS => 'Research and analysis tools',
            self::FEATURE_CUSTOM_BRANDING => 'Custom branding options'
        ];

        $features = [];
        foreach ($this->features ?? [] as $feature) {
            $features[] = [
                'key' => $feature,
                'name' => $featureDescriptions[$feature] ?? ucwords(str_replace('_', ' ', $feature)),
                'included' => true
            ];
        }

        return $features;
    }

    /**
     * Get human-readable limit list
     */
    public function getLimitList(): array
    {
        $limitDescriptions = [
            self::LIMIT_TREES => 'Maximum family trees',
            self::LIMIT_INDIVIDUALS => 'Individuals per tree',
            self::LIMIT_GEDCOM_SIZE => 'GEDCOM file size (MB)',
            self::LIMIT_API_CALLS => 'API calls per month',
            self::LIMIT_REPORTS => 'Reports per month',
            self::LIMIT_STORAGE => 'Storage space (MB)'
        ];

        $limits = [];
        foreach ($this->limits ?? [] as $limitType => $value) {
            $limits[] = [
                'key' => $limitType,
                'name' => $limitDescriptions[$limitType] ?? ucwords(str_replace('_', ' ', $limitType)),
                'value' => $value,
                'unlimited' => $value === null
            ];
        }

        return $limits;
    }

    /**
     * Get default plans configuration
     */
    public static function getDefaultPlans(): array
    {
        return [
            [
                'name' => 'Free',
                'slug' => self::PLAN_FREE,
                'description' => 'Perfect for getting started with your family history',
                'price_monthly' => 0.00,
                'price_yearly' => 0.00,
                'features' => [],
                'limits' => [
                    self::LIMIT_TREES => 1,
                    self::LIMIT_INDIVIDUALS => 100,
                    self::LIMIT_GEDCOM_SIZE => 1
                ],
                'trial_days' => 0,
                'sort_order' => 1
            ],
            [
                'name' => 'Basic',
                'slug' => self::PLAN_BASIC,
                'description' => 'Great for small family trees and basic research',
                'price_monthly' => 9.99,
                'price_yearly' => 99.99,
                'features' => [
                    self::FEATURE_DATA_EXPORT,
                    self::FEATURE_COLLABORATION
                ],
                'limits' => [
                    self::LIMIT_TREES => 5,
                    self::LIMIT_INDIVIDUALS => 1000,
                    self::LIMIT_GEDCOM_SIZE => 10,
                    self::LIMIT_STORAGE => 1000
                ],
                'trial_days' => 14,
                'sort_order' => 2
            ],
            [
                'name' => 'Premium',
                'slug' => self::PLAN_PREMIUM,
                'description' => 'Advanced features for serious genealogists',
                'price_monthly' => 19.99,
                'price_yearly' => 199.99,
                'features' => [
                    self::FEATURE_UNLIMITED_TREES,
                    self::FEATURE_ADVANCED_ANALYTICS,
                    self::FEATURE_PREMIUM_REPORTS,
                    self::FEATURE_API_ACCESS,
                    self::FEATURE_GEDCOM_ADVANCED,
                    self::FEATURE_DATA_EXPORT,
                    self::FEATURE_COLLABORATION
                ],
                'limits' => [
                    self::LIMIT_GEDCOM_SIZE => 50,
                    self::LIMIT_API_CALLS => 10000,
                    self::LIMIT_REPORTS => 100,
                    self::LIMIT_STORAGE => 5000
                ],
                'trial_days' => 30,
                'sort_order' => 3
            ],
            [
                'name' => 'Professional',
                'slug' => self::PLAN_PROFESSIONAL,
                'description' => 'For professional genealogists and researchers',
                'price_monthly' => 39.99,
                'price_yearly' => 399.99,
                'features' => [
                    self::FEATURE_UNLIMITED_TREES,
                    self::FEATURE_ADVANCED_ANALYTICS,
                    self::FEATURE_PREMIUM_REPORTS,
                    self::FEATURE_API_ACCESS,
                    self::FEATURE_BULK_OPERATIONS,
                    self::FEATURE_PRIORITY_SUPPORT,
                    self::FEATURE_GEDCOM_ADVANCED,
                    self::FEATURE_DATA_EXPORT,
                    self::FEATURE_COLLABORATION,
                    self::FEATURE_RESEARCH_TOOLS,
                    self::FEATURE_CUSTOM_BRANDING
                ],
                'limits' => [
                    self::LIMIT_API_CALLS => 100000,
                    self::LIMIT_STORAGE => 20000
                ],
                'trial_days' => 30,
                'sort_order' => 4
            ]
        ];
    }
}
