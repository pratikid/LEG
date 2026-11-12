<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * GDPR consent tracking for data processing activities
 */
final class DataConsent extends Model
{
    use HasFactory;

    // Consent types
    public const TYPE_ESSENTIAL = 'essential';

    public const TYPE_ANALYTICS = 'analytics';

    public const TYPE_MARKETING = 'marketing';

    public const TYPE_RESEARCH = 'research';

    public const TYPE_DATA_SHARING = 'data_sharing';

    // Legal basis under GDPR Article 6
    public const BASIS_CONSENT = 'consent';

    public const BASIS_CONTRACT = 'contract';

    public const BASIS_LEGAL_OBLIGATION = 'legal_obligation';

    public const BASIS_VITAL_INTERESTS = 'vital_interests';

    public const BASIS_PUBLIC_TASK = 'public_task';

    public const BASIS_LEGITIMATE_INTERESTS = 'legitimate_interests';

    // Data categories
    public const DATA_PERSONAL = 'personal_data';

    public const DATA_GENEALOGICAL = 'genealogical_data';

    public const DATA_USAGE = 'usage_data';

    public const DATA_TECHNICAL = 'technical_data';

    protected $fillable = [
        'user_id',
        'consent_type',
        'purpose',
        'legal_basis',
        'data_categories',
        'processing_activities',
        'third_party_sharing',
        'retention_period',
        'consent_given',
        'consent_date',
        'withdrawal_date',
        'consent_method',
        'ip_address',
        'user_agent',
        'consent_version',
        'is_active',
    ];

    protected $casts = [
        'data_categories' => 'array',
        'processing_activities' => 'array',
        'third_party_sharing' => 'array',
        'consent_given' => 'boolean',
        'consent_date' => 'datetime',
        'withdrawal_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if consent is valid and active
     */
    public function isValidConsent(): bool
    {
        if (! $this->is_active || ! $this->consent_given) {
            return false;
        }

        if ($this->withdrawal_date !== null) {
            return false;
        }

        // Check if consent has expired
        if ($this->retention_period && $this->consent_date->addMonths($this->retention_period)->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Withdraw consent
     */
    public function withdraw(): bool
    {
        $this->update([
            'is_active' => false,
            'withdrawal_date' => now(),
        ]);

        return true;
    }

    /**
     * Check if data category processing is consented
     */
    public function hasDataCategoryConsent(string $category): bool
    {
        if (! $this->isValidConsent()) {
            return false;
        }

        return in_array($category, $this->data_categories ?? []);
    }

    /**
     * Check if specific processing activity is consented
     */
    public function hasProcessingConsent(string $activity): bool
    {
        if (! $this->isValidConsent()) {
            return false;
        }

        return in_array($activity, $this->processing_activities ?? []);
    }
}
